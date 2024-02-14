<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Sponsor;

use Illuminate\Http\Request;

class SponsorController extends Controller
{

    public function all_sponsors($event_id)
    {
       $attends = Sponsor::where('event_id', $event_id)->get();

        if ($attends) {
            return response()->json([
                'status' => 200,
                'message' => 'All Sponsors By Event ID',
                'data' =>$attends
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Sponsor not Found',
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
        $attend = Sponsor::all();

        if ($attend) {
            return response()->json([
                'status' => 200,
                'message' => 'All Sponsor Details',
                'data' => $attend
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Sponsors not Found',
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

        $sponsor = new Sponsor();

        $sponsor->title = $request->title;
        $sponsor->description = $request->description;
        $sponsor->event_id = $request->event_id;

        // Handle image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/sponsors'), $imageName);
            $sponsor->image_path = 'uploads/sponsors/' . $imageName;
        }

        $sponsor->save();

        return response()->json([
            'status' => 201,
            'message' => 'Sponsor Details created successfully',
            'sponsor' => $sponsor
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
        $attend = Sponsor::findOrFail($id);

        if ($attend) {

            return response()->json([
                'status' => 200,
                'message' => 'Sponsor Details',
                'data' => $attend
            ]);
        } else {

            return response()->json([
                'status' => 200,
                'message' => 'Sponsor Not Found'
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
        $sponsor = Sponsor::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_id' => 'required',
        ]);

        $sponsor->title = $request->title;
        $sponsor->description = $request->description;
        $sponsor->event_id = $request->event_id;

        if ($request->hasFile('image_path')) {

            // Delete old image if exists
            if (file_exists(public_path('uploads/sponsors/' . $sponsor->image_path))) {
                unlink(public_path('uploads/sponsors/' . $sponsor->image_path));
            }

            // Upload new image
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/sponsors'), $imageName);
            $sponsor->image_path = 'uploads/sponsors/' . $imageName;
        }

        $sponsor->save();

        return response()->json([
            'status' => 200,
            'message' => 'Sponsor Details updated successfully',
            'sponsors' => $sponsor
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
        $sponsor = Sponsor::findOrFail($id);

        // Delete the image file
        if (file_exists(public_path('uploads/sponsors/' . $sponsor->image_path))) {
            unlink(public_path('uploads/sponsors/' . $sponsor->image_path));
        }

        $sponsor->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Sponsor Details deleted successfully'
        ]);
    }
}
