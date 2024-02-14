<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Speaker;
use App\Models\Title;

use Illuminate\Http\Request;

class SpeakerController extends Controller
{



    public function titles()
    {
        $titles = Title::all();

        if ($titles) {
            return response()->json([
                'status' => 200,
                'message' => 'All Titles',
                'data' => $titles
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Title not Found',
                'data' => []
            ]);
        }
    }

    public function all_speakers($event_id)
    {
        $Speakers = Speaker::where('event_id', $event_id)->get();

        if ($Speakers) {
            return response()->json([
                'status' => 200,
                'message' => 'All Speakers By Event ID',
                'data' => $Speakers
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Speaker not Found',
                'data' => []
            ]);
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $speakers = Speaker::all();

        if ($speakers) {
            return response()->json([
                'status' => 200,
                'message' => 'All Speakers',
                'data' => $speakers
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Speaker not Found',
                'data' => []
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'image_path' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'event_id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'designation' => 'required',
            'company' => 'required',
        ]);

        $speaker = new Speaker();

        $speaker->event_id  = $request->event_id;

        $speaker->title  = $request->title;
        $speaker->first_name  = $request->first_name;
        $speaker->middle_name  = $request->middle_name;
        $speaker->last_name  = $request->last_name;
        $speaker->about  = $request->about;
        $speaker->mobile  = $request->mobile;
        $speaker->email  = $request->email;
        $speaker->designation  = $request->designation;
        $speaker->company  = $request->company;
        $speaker->fb_link  = $request->fb_link;
        $speaker->instagram_link  = $request->instagram_link;
        $speaker->linkedin_link  = $request->linkedin_link;
        $speaker->youtube_link  = $request->youtube_link;
        $speaker->twitter_link  = $request->twitter_link;
        $speaker->google_link  = $request->google_link;
        $speaker->skype_link  = $request->skype_link;
        $speaker->whatsapp_link  = $request->whatsapp_link;

        // Handle image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/speakers'), $imageName);
            $speaker->image_path = 'uploads/speakers/' . $imageName;
        }

        $speaker->save();

        return response()->json([
            'status' => 201,
            'message' => 'Speaker created successfully',
            'speaker' => $speaker
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $speaker = Speaker::findOrFail($id);

        if ($speaker) {

            return response()->json([
                'status' => 200,
                'message' => 'Speaker Details',
                'data' => $speaker
            ]);
        } else {

            return response()->json([
                'status' => 200,
                'message' => 'Speaker Not Found'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $speaker = Speaker::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'event_id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'designation' => 'required',
            'company' => 'required',
        ]);


        $speaker->event_id  = $request->event_id;

        $speaker->title  = $request->title;
        $speaker->first_name  = $request->first_name;
        $speaker->middle_name  = $request->middle_name;
        $speaker->last_name  = $request->last_name;
        $speaker->about  = $request->about;
        $speaker->mobile  = $request->mobile;
        $speaker->email  = $request->email;
        $speaker->designation  = $request->designation;
        $speaker->company  = $request->company;
        $speaker->fb_link  = $request->fb_link;
        $speaker->instagram_link  = $request->instagram_link;
        $speaker->linkedin_link  = $request->linkedin_link;
        $speaker->youtube_link  = $request->youtube_link;
        $speaker->twitter_link  = $request->twitter_link;
        $speaker->google_link  = $request->google_link;
        $speaker->skype_link  = $request->skype_link;
        $speaker->whatsapp_link  = $request->whatsapp_link;

        if ($request->hasFile('image_path')) {

            // Delete old image if exists
            if (file_exists(public_path('uploads/speakers/' . $speaker->image_path))) {
                unlink(public_path('uploads/speakers/' . $speaker->image_path));
            }

            // Upload new image
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/speakers'), $imageName);
            $speaker->image_path = 'uploads/speakers/' . $imageName;
        }

        $speaker->save();

        return response()->json([
            'status' => 200,
            'message' => 'Speaker updated successfully',
            'speaker' => $speaker
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $speaker = Speaker::findOrFail($id);

        // Delete the image file
        if (file_exists(public_path('uploads/speakers/' . $speaker->image_path))) {
            unlink(public_path('uploads/speakers/' . $speaker->image_path));
        }

        $speaker->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Speaker deleted successfully'
        ]);
    }
}
