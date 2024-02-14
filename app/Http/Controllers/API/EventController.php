<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Event;
use Ramsey\Uuid\Uuid;
use App\Models\Attendee;
use Illuminate\Http\Request;
use App\Services\SmsServices;
use App\Services\EmailService;
use App\Mail\EventReminderEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\City;
use App\Models\EventAttribute;
use App\Models\State;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

class EventController extends Controller
{
    private $emailService;
    private $smsService;

    public function __construct(EmailService $emailService, SmsServices $smsService)
    {
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }
    /**
     * Display Dashboard Widgets (Analytics)
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = Auth::id();

        $event_data = [];

        $events = Event::all()->toArray();

        foreach ($events as $event) {

            $eventDetails = [];

            // $city = City::where('id', $event['city'])->first();

            // $state = State::where('id', $event['state'])->first();

            $eventDetails = array(
                "id" => $event['id'],
                "uuid" => $event['uuid'],
                "user_id" => $event['user_id'],
                "title" => $event['title'],
                "description" => $event['description'],
                "event_date" => $event['event_date'],
                "location" => !empty($event['city']) ? $event['city'] : "Others",
                "start_time" => $event['start_time'],
                "start_time_type" => $event['start_time_type'],
                "end_time" => $event['end_time'],
                "end_time_type" => $event['end_time_type'],
                "image" => $event['image'],
                "event_venue_name" => $event['event_venue_name'],
                "event_venue_address_1" => $event['event_venue_address_1'],
                "event_venue_address_2" => $event['event_venue_address_2'],
                "city" => !empty($event['city']) ? $event['city'] : "Others",
                "state" => !empty($event['state']) ? $event['state'] : "Others",
                "country" => !empty($event['country']) ? $event['country'] : "",
                "pincode" => !empty($event['pincode']) ? $event['pincode'] : "",
                "created_at" => $event['created_at'],
                "updated_at" => $event['updated_at'],
                "status" => $event['status'],
                "end_minute_time" => $event['end_minute_time'],
                "start_minute_time" => $event['start_minute_time'],
                "qr_code" => $event['qr_code'],
                "start_time_format" => $event['start_time_format'],
                "feedback" => $event['feedback'],
                "event_start_date" => $event['event_start_date'],
                "event_end_date" =>  $event['event_end_date'],
                "why_attend_info" =>  $event['why_attend_info'],
                "more_information" => $event['more_information'],
                "t_and_conditions" => $event['t_and_conditions']
            );

            $event_data[] = array_merge($eventDetails);
            unset($eventDetails);
        }

        if ($events) {
            return response()->json([
                'status' => 200,
                'message' => 'All Events',
                'data' => $event_data
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Event not Found',
                'data' => []
            ]);
        }
    }

    //Add 0 in single Digit
    public function prepandZerorIfSingleDigit($number)
    {
        $numberString = (string)$number;

        if (strlen($numberString) === 1) {
            return '0' . $numberString;
        }

        return $numberString;
    }

    /**
     * Store a newly created event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userId = Auth::id();

        //input validation 
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:100',
            'short_description' => 'required',
            'description' => 'required',
            'event_date' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:4098',
            // 'event_venue_name' => 'required|max:255',
            'start_time' => 'required',
            'start_minute_time' => 'required',
            'end_time' => 'required',
            'end_minute_time' => 'required',
            // 'event_venue_address_1' => 'required',
            // 'city' => 'required|max:50',
            // 'state' => 'required|max:50',
            // 'country' => 'required|max:50',
            // 'pincode' => 'required|min:6|max:6',
            'event_start_date' => 'nullable|date',
            // 'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ]);
        }

        $event = new Event();

        $event->uuid = Uuid::uuid4()->toString();

        $event->user_id = isset($userId) ? $userId : $request->user_id;

        $event->title = ucfirst($request->title);

        if (isset($request->slug) && !empty($request->slug)) {
            $event->slug = $request->slug;
        } else {
            // Create a slug from the title
            $slug = Str::slug($request->input('title'));

            // Check if the generated slug already exists
            $count = Event::where('slug', $slug)->count();

            if ($count > 0) {
                // If the slug exists, append a unique identifier
                $slug = $slug . '-' . uniqid();
            }

            $event->slug = $slug;
        }


        $event->description = $request->description;
        $event->event_date = $request->event_start_date;

        $event->event_start_date = $request->event_start_date;
        $event->start_time = $this->prepandZerorIfSingleDigit($request->start_time);
        $event->start_minute_time = $this->prepandZerorIfSingleDigit($request->start_minute_time);
        $event->start_time_type = strtoupper($request->start_time_type);

        $event->event_end_date = !empty($request->event_end_date) ? $request->event_end_date : $request->event_start_date;
        $event->end_time = $this->prepandZerorIfSingleDigit($request->end_time);
        $event->end_minute_time = $this->prepandZerorIfSingleDigit($request->end_minute_time);
        $event->end_time_type = strtoupper($request->end_time_type);

        $event_time = $this->prepandZerorIfSingleDigit($request->start_time) . ':' . $this->prepandZerorIfSingleDigit($request->start_minute_time) . ':00 ' . strtoupper($request->start_time_type);
        $carbonTime = Carbon::createFromFormat('h:i:s A', $event_time);

        $event->start_time_format = $carbonTime->format('H:i:s');

        //Handle image upload and store the image path
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            // $imagePath = $image->store('images', 'public');
            $filename = time() . '.' . $extension;
            $image->move(public_path('uploads/events/'), $filename);
            $event->image = 'uploads/events/' . $filename;
        }

        // $event->event_venue = strip_tags($request->event_venue_name);
        $event->event_venue_name = strip_tags($request->event_venue_name);
        $event->event_venue_address_1 = strip_tags($request->event_venue_address_1);
        $event->event_venue_address_2 = strip_tags($request->event_venue_address_2);
        $event->city = strip_tags($request->city);
        $event->location = strip_tags($request->city);
        $event->state =  strip_tags($request->state);
        $event->country =  strip_tags($request->country);
        $event->pincode = $request->pincode;
        $event->feedback = $request->feedback;
        $event->why_attend_info = $request->why_attend_info;
        $event->more_information = $request->more_information;
        $event->t_and_conditions = $request->t_and_conditions;

        $event->number_of_speakers = $request->number_of_speakers;
        $event->number_of_attendees = $request->number_of_attendees;
        $event->number_of_awards = $request->number_of_awards;
        $event->number_of_panel_discussions = $request->number_of_panel_discussions;


        $event->status = $request->status;

        if (!empty($request->attribute)) {
            $attributes = $request->attribute;

            foreach ($attributes as $row) {
            }
        }

        $success = $event->save();

        if ($success) {
            return response()->json([
                'status' => 200,
                'message' => 'Event Created Successfully',
                'event' => $event
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Something Went Wrong. Please try again later.'
            ]);
        }
    }

    /**
     * Display the specified event.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showEventBySlug($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $eventDetails = [];

        foreach ($event as $row) {

            $city = City::where('id', $row->city)->first();

            $state = State::where('id', $row->state)->first();

            $eventDetails = array(
                "id" => $row->id,
                "uuid" => $row->uuid,
                "user_id" => $row->user_id,
                "title" => $row->title,
                "description" => $row->description,
                "event_date" => $row->event_date,
                "location" => $row->city,
                "start_time" => $row->start_time,
                "start_time_type" => $row->start_time_type,
                "end_time" => $row->end_time,
                "end_time_type" => $row->end_time_type,
                "image" => $row->image,
                "event_venue_name" => $row->event_venue_name,
                "event_venue_address_1" => $row->event_venue_address_1,
                "event_venue_address_2" => $row->event_venue_address_2,
                "city" => $row->city,
                "state" => $row->state,
                "country" => $row->country,
                "pincode" => $row->pincode,
                "created_at" => $row->created_at,
                "updated_at" => $row->updated_at,
                "status" => $row->status,
                "end_minute_time" => $row->end_minute_time,
                "start_minute_time" => $row->start_minute_time,
                "qr_code" => $row->qr_code,
                "start_time_format" => $row->start_time_interval,
                "feedback" => $row->feedback,
                "event_start_date" => $row->event_start_date,
                "event_end_date" =>  $row->event_end_date,
                "why_attend_info" =>  $row->why_attend_info,
                "more_information" => $row->more_information,
                "t_and_conditions" => $row->t_and_condition
            );
        }

        if ($eventDetails) {

            return response()->json([
                'status' => 200,
                'message' => 'Event Details',
                'data' => $eventDetails
            ]);
        } else {

            return response()->json([
                'status' => 400,
                'message' => 'Event Not Found.'
            ]);
        }
    }
    public function display($id)
    {
        $event = Event::where('uuid', $id)->get();

        $eventDetails = [];

        foreach ($event as $row) {

            $city = City::where('id', $row->city)->first();

            $state = State::where('id', $row->state)->first();

            $eventDetails = array(
                "id" => $row->id,
                "uuid" => $row->uuid,
                "user_id" => $row->user_id,
                "title" => $row->title,
                "description" => $row->description,
                "event_date" => $row->event_date,
                "location" => !empty($row->city) ? $row->city : "Others",
                "start_time" => $row->start_time,
                "start_time_type" => $row->start_time_type,
                "end_time" => $row->end_time,
                "end_time_type" => $row->end_time_type,
                "image" => $row->image,
                "event_venue_name" => $row->event_venue_name,
                "event_venue_address_1" => $row->event_venue_address_1,
                "event_venue_address_2" => $row->event_venue_address_2,
                "city" => !empty($row->city) ? $row->city : "Others",
                "state" => !empty($row->state) ? $row->state : "Others",
                "country" => $row->country,
                "pincode" => $row->pincode,
                "created_at" => $row->created_at,
                "updated_at" => $row->updated_at,
                "status" => $row->status,
                "end_minute_time" => $row->end_minute_time,
                "start_minute_time" => $row->start_minute_time,
                "qr_code" => $row->qr_code,
                "start_time_format" => $row->start_time_interval,
                "feedback" => $row->feedback,
                "event_start_date" => $row->event_start_date,
                "event_end_date" =>  $row->event_end_date,
                "why_attend_info" =>  $row->why_attend_info,
                "more_information" => $row->more_information,
                "t_and_conditions" => $row->t_and_condition
            );
        }

        if ($eventDetails) {

            return response()->json([
                'status' => 200,
                'message' => 'Event Details',
                'data' => $eventDetails
            ]);
        } else {

            return response()->json([
                'status' => 400,
                'message' => 'Event Not Found.'
            ]);
        }
    }
    public function show($id)
    {
        //Get details of event 
        $event = Event::findOrFail($id);

        if ($event) {

            return response()->json([
                'status' => 200,
                'message' => 'Event Details',
                'data' => $event
            ]);
        } else {

            return response()->json([
                'status' => 400,
                'message' => 'Event Not Found.'
            ]);
        }
    }

    /**
     * Update the specified event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:100',
            // 'short_description' => 'required',
            'description' => 'required',
            'event_date' => 'required|date',
            // 'image' => 'required|image|mimes:jpeg,png,jpg|max:4098',
            // 'event_venue_name' => 'required|max:255',
            'start_time' => 'required',
            'start_minute_time' => 'required',
            'end_time' => 'required',
            'end_minute_time' => 'required',
            // 'event_venue_address_1' => 'required',
            // 'city' => 'required|max:50',
            // 'state' => 'required|max:50',
            // 'country' => 'required|max:50',
            // 'pincode' => 'required|min:6|max:6',
            'event_start_date' => 'nullable|date',
            // 'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
        ]);

        if ($request->hasFile('image')) {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg|max:4098',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->errors()
                ]);
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors()
            ]);
        } else {

            $event = Event::findOrFail($id);

            if ($event) {


                $event->uuid = Uuid::uuid4()->toString();
                $event->user_id = isset($userId) ? $userId : $request->user_id;
                $event->title = ucfirst($request->title);

                if (isset($request->slug) && !empty($request->slug)) {
                    $event->slug = $request->slug;
                } else {
                    // Create a slug from the title
                    $slug = Str::slug($request->input('title'));

                    // Check if the generated slug already exists
                    $count = Event::where('slug', $slug)->count();
                    if ($count > 0) {
                        // If the slug exists, append a unique identifier
                        $slug = $slug . '-' . uniqid();
                    }

                    $event->slug = $slug;
                }


                $event->description = $request->description;
                $event->event_date = $request->event_start_date;

                $event->event_start_date = $request->event_start_date;
                $event->start_time = $this->prepandZerorIfSingleDigit($request->start_time);
                $event->start_minute_time = $this->prepandZerorIfSingleDigit($request->start_minute_time);
                $event->start_time_type = strtoupper($request->start_time_type);

                $event->event_end_date = !empty($request->event_end_date) ? $request->event_end_date : $request->event_start_date;
                $event->end_time = $this->prepandZerorIfSingleDigit($request->end_time);
                $event->end_minute_time = $this->prepandZerorIfSingleDigit($request->end_minute_time);
                $event->end_time_type = strtoupper($request->end_time_type);

                $event_time = $this->prepandZerorIfSingleDigit($request->start_time) . ':' . $this->prepandZerorIfSingleDigit($request->start_minute_time) . ':00 ' . strtoupper($request->start_time_type);
                $carbonTime = Carbon::createFromFormat('h:i:s A', $event_time);

                $event->start_time_format = $carbonTime->format('H:i:s');

                //Handle image upload and store the image path
                if ($request->hasFile('image')) {

                    // $path = $event->image;

                    // if (Storage::exists($path)) {
                    //     Storage::delete($path);
                    // }

                    // Delete old image if exists
                    if (file_exists(public_path('uploads/events/' . $event->image))) {
                        unlink(public_path('uploads/events/' . $event->image));
                    }

                    $image = $request->file('image');
                    $extension = $image->getClientOriginalExtension();
                    $filename = time() . '.' . $extension;
                    $image->move(public_path('uploads/events/'), $filename);
                    $event->image = 'uploads/events/' . $filename;
                }

                $event->event_venue_name = strip_tags($request->event_venue_name);
                $event->event_venue_address_1 = strip_tags($request->event_venue_address_1);
                $event->event_venue_address_2 = strip_tags($request->event_venue_address_2);
                $event->city = strip_tags($request->city);
                $event->location = strip_tags($request->city);
                $event->state =  strip_tags($request->state);
                $event->country =  strip_tags($request->country);
                $event->pincode = $request->pincode;
                $event->feedback = $request->feedback;
                $event->why_attend_info = $request->why_attend_info;
                $event->more_information = $request->more_information;
                $event->t_and_conditions = $request->t_and_conditions;

                $event->number_of_speakers = $request->number_of_speakers;
                $event->number_of_attendees = $request->number_of_attendees;
                $event->number_of_awards = $request->number_of_awards;
                $event->number_of_panel_discussions = $request->number_of_panel_discussions;

                $event->status = $request->status;
                $success = $event->update();

                if ($success) {

                    return response()->json([
                        'status' => 200,
                        'message' => 'Event Updated Successfully',
                        'event' => $event
                    ]);
                } else {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Something Went Wrong. Please try again later.'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Event not Found.'
                ]);
            }
        }
    }

    /**
     * Remove the specified event.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //Delete event
        $event = Event::findOrFail($id);

        if ($event) {

            // $imagePath = public_path($event->image);
            // // Check if the file exists
            // if (File::exists($imagePath)) {
            //     // Delete the file
            //     File::delete($imagePath);
            // }

            if (file_exists(public_path('uploads/events/' . $event->image))) {
                unlink(public_path('uploads/events/' . $event->image));
            }

            $event->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Event Deleted Successfully.'
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Data not Found.'
            ]);
        }
    }
}
