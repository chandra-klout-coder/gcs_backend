<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Theme;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    
    public function all_themes($event_id)
    {
        $themes = Theme::where('event_id', $event_id)->get();

        if ($themes) {
            return response()->json([
                'status' => 200,
                'message' => 'All Themes By Event ID',
                'data' => $themes
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Theme not Found',
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
        $themes = Theme::all();

        if ($themes) {
            return response()->json([
                'status' => 200,
                'message' => 'All Themes',
                'data' => $themes
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Themes not Found',
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

        $theme = new Theme();

        $theme->title = $request->title;
        $theme->description = $request->description;
        $theme->event_id = $request->event_id;

        // Handle image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/themes'), $imageName);
            $theme->image_path = 'uploads/themes/'.$imageName;
        }

        $theme->save();

        return response()->json([
            'status' => 201,
            'message' => 'Theme created successfully',
            'theme' => $theme
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
        $theme = Theme::findOrFail($id);

        if ($theme) {

            return response()->json([
                'status' => 200,
                'message' => 'Theme Details',
                'data' => $theme
            ]);
        } else {

            return response()->json([
                'status' => 200,
                'message' => 'Theme Not Found'
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
        $theme = Theme::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_id' => 'required',
        ]);

        $theme->title = $request->title;
        $theme->description = $request->description;
        $theme->event_id = $request->event_id;

        if ($request->hasFile('image_path')) {

            // Delete old image if exists
            if (file_exists(public_path('uploads/themes/' . $theme->image_path))) {
                unlink(public_path('uploads/themes/' . $theme->image_path));
            }

            // Upload new image
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/themes'), $imageName);
            $theme->image_path = 'uploads/themes/'.$imageName;
        }

        $theme->save();

        return response()->json([
            'status' => 200,
            'message' => 'Theme updated successfully',
            'theme' => $theme
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
        $theme = Theme::findOrFail($id);

        // Delete the image file
        if (file_exists(public_path('uploads/themes/' . $theme->image_path))) {
            unlink(public_path('uploads/themes/' . $theme->image_path));
        }

        $theme->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Theme deleted successfully'
        ]);
    }
}
