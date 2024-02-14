<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Agenda;
use Illuminate\Http\Request;

class AgendaController extends Controller
{

    public function all_agendas($event_id)
    {
        $agendas = Agenda::where('event_id', $event_id)->orderBy('position', 'asc')->get();

        if ($agendas) {
            return response()->json([
                'status' => 200,
                'message' => 'All Agendas By Event ID',
                'data' => $agendas
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Agenda not Found',
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
        $agendas = Agenda::all();

        if ($agendas) {
            return response()->json([
                'status' => 200,
                'message' => 'All Agendas',
                'data' => $agendas
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Banner not Found',
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
            'description' => 'required',
            'event_id' => 'required',
        ]);

        $agenda = new Agenda();

        $agenda->title = $request->title;
        $agenda->description = $request->description;
        $agenda->event_id = $request->event_id;
        $agenda->event_date = $request->event_date;
        $agenda->start_time = $request->start_time;
        $agenda->start_minute_time = $request->start_minute_time;
        $agenda->start_time_type = $request->start_time_type;
        $agenda->end_time = $request->end_time;
        $agenda->end_time_type = $request->end_time_type;
        $agenda->end_minute_time = $request->end_minute_time;
        $agenda->position = $request->position;


        // Handle Image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/agendas'), $imageName);
            $agenda->image_path = 'uploads/agendas/' . $imageName;
        }

        $agenda->save();

        return response()->json([
            'status' => 201,
            'message' => 'Agenda created successfully',
            'agenda' => $agenda
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
        $agneda = Agenda::findOrFail($id);

        if ($agneda) {

            return response()->json([
                'status' => 200,
                'message' => 'Agenda Details',
                'data' => $agneda
            ]);
        } else {

            return response()->json([
                'status' => 200,
                'message' => 'Agenda Not Found'
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
        $agenda = Agenda::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_id' => 'required',
        ]);

        $agenda->title = $request->title;
        $agenda->description = $request->description;
        $agenda->event_id = $request->event_id;
        $agenda->event_date = $request->event_date;
        $agenda->start_time = $request->start_time;
        $agenda->start_minute_time = $request->start_minute_time;
        $agenda->start_time_type = $request->start_time_type;
        $agenda->end_time = $request->end_time;
        $agenda->end_minute_time = $request->end_minute_time;
        $agenda->end_time_type = $request->end_time_type;
        $agenda->position = $request->position;


        if ($request->hasFile('image_path')) {

            // Delete old image if exists
            if (file_exists(public_path('uploads/agendas/' . $agenda->image_path))) {
                unlink(public_path('uploads/agendas/' . $agenda->image_path));
            }

            // Upload new image
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/agendas'), $imageName);
            $agenda->image_path = 'uploads/agendas/' . $imageName;
        }

        $agenda->save();

        return response()->json([
            'status' => 200,
            'message' => 'Agenda updated successfully',
            'agenda' => $agenda
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
        $agenda = Agenda::findOrFail($id);

        // Delete the image file
        if (file_exists(public_path('uploads/agendas/' . $agenda->image_path))) {
            unlink(public_path('uploads/agendas/' . $agenda->image_path));
        }

        $agenda->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Agenda deleted successfully'
        ]);
    }
}
