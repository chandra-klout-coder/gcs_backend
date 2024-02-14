<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Highlight;
use Illuminate\Http\Request;

class HighlightController extends Controller
{

    public function all_highlights($event_id)
    {
        $highlights = Highlight::where('event_id', $event_id)->get();

        if ($highlights) {
            return response()->json([
                'status' => 200,
                'message' => 'All Highlights By Event ID',
                'data' => $highlights
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Highlight not Found',
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
        $highlights = Highlight::all();

        if ($highlights) {
            return response()->json([
                'status' => 200,
                'message' => 'All Highlights',
                'data' => $highlights
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Highlights not Found',
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

        $highlight = new Highlight();

        $highlight->title = $request->title;
        $highlight->description = $request->description;
        $highlight->event_id = $request->event_id;

        // Handle image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/highlights'), $imageName);
            $highlight->image_path = 'uploads/highlights/' . $imageName;
        }

        $highlight->save();

        return response()->json([
            'status' => 201,
            'message' => 'Highlight created successfully',
            'highlight' => $highlight
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
        $highlight = Highlight::findOrFail($id);

        if ($highlight) {

            return response()->json([
                'status' => 200,
                'message' => 'Highlight Details',
                'data' => $highlight
            ]);
        } else {

            return response()->json([
                'status' => 200,
                'message' => 'Highlight Not Found'
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
        $highlight = Highlight::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_id' => 'required',
        ]);

        $highlight->title = $request->title;
        $highlight->description = $request->description;
        $highlight->event_id = $request->event_id;

        if ($request->hasFile('image_path')) {

            // Delete old image if exists
            if (file_exists(public_path('uploads/highlights/' . $highlight->image_path))) {
                unlink(public_path('uploads/highlights/' . $highlight->image_path));
            }

            // Upload new image
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/highlights'), $imageName);
            $highlight->image_path = 'uploads/highlights/' . $imageName;
        }

        $highlight->save();

        return response()->json([
            'status' => 200,
            'message' => 'Highlight updated successfully',
            'banner' => $highlight
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
        $highlight = Highlight::findOrFail($id);

        // Delete the image file
        if (file_exists(public_path('uploads/highlights/' . $highlight->image_path))) {
            unlink(public_path('uploads/highlights/' . $highlight->image_path));
        }

        $highlight->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Highlight deleted successfully'
        ]);
    }
}
