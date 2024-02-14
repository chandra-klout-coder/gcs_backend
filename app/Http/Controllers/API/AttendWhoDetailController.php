<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\AttendDetail;
use Illuminate\Http\Request;

class AttendWhoDetailController extends Controller
{

    public function all_attends($event_id)
    {
       $attends = AttendDetail::where('event_id', $event_id)->get();

        if ($attends) {
            return response()->json([
                'status' => 200,
                'message' => 'All Who Attends By Event ID',
                'data' =>$attends
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Who Attend not Found',
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
        $attend = AttendDetail::all();

        if ($attend) {
            return response()->json([
                'status' => 200,
                'message' => 'All Who Attend Details',
                'data' => $attend
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Attend Details not Found',
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

        $attend = new AttendDetail();

        $attend->title = $request->title;
        $attend->description = $request->description;
        $attend->event_id = $request->event_id;

        // Handle image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/attends'), $imageName);
            $attend->image_path = 'uploads/attends/' . $imageName;
        }

        $attend->save();

        return response()->json([
            'status' => 201,
            'message' => 'Attend Details created successfully',
            'attends' => $attend
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
        $attend = AttendDetail::findOrFail($id);

        if ($attend) {

            return response()->json([
                'status' => 200,
                'message' => 'Attend Details',
                'data' => $attend
            ]);
        } else {

            return response()->json([
                'status' => 200,
                'message' => 'Attend Details Not Found'
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
        $attend = AttendDetail::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_id' => 'required',
        ]);

        $attend->title = $request->title;
        $attend->description = $request->description;
        $attend->event_id = $request->event_id;

        if ($request->hasFile('image_path')) {

            // Delete old image if exists
            if (file_exists(public_path('uploads/attends/' . $attend->image_path))) {
                unlink(public_path('uploads/attends/' . $attend->image_path));
            }

            // Upload new image
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/attends'), $imageName);
            $attend->image_path = 'uploads/attends/' . $imageName;
        }

        $attend->save();

        return response()->json([
            'status' => 200,
            'message' => 'Attend Details updated successfully',
            'attends' => $attend
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
        $attend = AttendDetail::findOrFail($id);

        // Delete the image file
        if (file_exists(public_path('uploads/attends/' . $attend->image_path))) {
            unlink(public_path('uploads/attends/' . $attend->image_path));
        }

        $attend->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Attend Details deleted successfully'
        ]);
    }
}
