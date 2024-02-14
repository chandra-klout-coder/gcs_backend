<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{

    public function all_banners($event_id)
    {
        $banners = Banner::where('event_id', $event_id)->get();

        if ($banners) {
            return response()->json([
                'status' => 200,
                'message' => 'All Banners By Event ID',
                'data' => $banners
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banners = Banner::all();

        if ($banners) {
            return response()->json([
                'status' => 200,
                'message' => 'All Banners',
                'data' => $banners
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

        $banner = new Banner();

        $banner->title = $request->title;
        $banner->description = $request->description;
        $banner->event_id = $request->event_id;

        // Handle image upload
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/banners'), $imageName);
            $banner->image_path = 'uploads/banners/' . $imageName;
        }

        $banner->save();

        return response()->json([
            'status' => 201,
            'message' => 'Banner created successfully',
            'banner' => $banner
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
        $banner = Banner::findOrFail($id);

        if ($banner) {

            return response()->json([
                'status' => 200,
                'message' => 'Banner Details',
                'data' => $banner
            ]);
        } else {

            return response()->json([
                'status' => 200,
                'message' => 'Banner Not Found'
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
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'event_id' => 'required',
        ]);

        $banner->title = $request->title;
        $banner->description = $request->description;
        $banner->event_id = $request->event_id;

        if ($request->hasFile('image_path')) {

            // Delete old image if exists
            if (file_exists(public_path('uploads/banners/' . $banner->image_path))) {
                unlink(public_path('uploads/banners/' . $banner->image_path));
            }

            // Upload new image
            $image = $request->file('image_path');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/banners'), $imageName);
            $banner->image_path = 'uploads/banners/' . $imageName;
        }

        $banner->save();

        return response()->json([
            'status' => 200,
            'message' => 'Banner updated successfully',
            'banner' => $banner
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
        $banner = Banner::findOrFail($id);

        // Delete the image file
        if (file_exists(public_path('uploads/banners/' . $banner->image_path))) {
            unlink(public_path('uploads/banners/' . $banner->image_path));
        }

        $banner->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Banner deleted successfully'
        ]);
    }
}
