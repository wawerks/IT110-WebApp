<?php

namespace App\Http\Controllers;

use App\Models\FoundItem;
use Illuminate\Http\Request;

class FoundItemController extends Controller
{
    /**
     * Display a listing of the found items.
     */
    public function index()
    {
        $foundItems = FoundItem::all();
        return response()->json($foundItems);
    }

    /**
     * Show the form for creating a new found item.
     */
    public function create()
    {
        return response()->json('Create found item form.');
    }

    /**
     * Store a newly created found item in storage.
     */
    public function store(Request $request)
    {
        $imageUrl = null;
        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $filename = time() . '.' . $image->extension();
            $image->move(public_path('assets/img'), $filename);
            $imageUrl = 'assets/img/' . $filename;
        }

        $request->validate([
            'found_date' => 'required|date',
            'item_name' => 'required|string|max:255',
            'facebook_link' => 'nullable|url',
            'contact_number' => 'nullable|string|max:15',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'location' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $foundItem = FoundItem::create([
            'found_date' => $request->input('found_date'),
            'item_name' => $request->input('item_name'),
            'facebook_link' => $request->input('facebook_link'),
            'contact_number' => $request->input('contact_number'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'location' => $request->input('location'),
            'user_id' => $request->input('user_id'),
            'image_url' => $imageUrl,
        ]);

        return redirect()->back()->with('success', 'found item created successfully.');
    }

    /**
     * Display the specified found item.
     */
    public function show($id)
    {
        $foundItem = FoundItem::with('user')->findOrFail($id);
        return response()->json($foundItem);
    }

    /**
     * Show the form for editing the specified found item.
     */
    public function edit($id)
    {
        $foundItem = FoundItem::findOrFail($id);
        return response()->json($foundItem);
    }

    /**
     * Update the specified found item in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $foundItem = FoundItem::findOrFail($id);

            $request->validate([
                'item_name' => 'required|string|max:255',
                'category' => 'required|string',
                'description' => 'nullable|string',
                'facebook_link' => 'nullable|url',
                'contact_number' => 'nullable|string|max:15',
            ]);

            $data = $request->only([
                'item_name',
                'category',
                'description',
                'facebook_link',
                'contact_number',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '.' . $image->extension();
                $image->move(public_path('assets/img'), $filename);
                $data['image_url'] = 'assets/img/' . $filename;

                // Delete old image if it exists
                if ($foundItem->image_url) {
                    $oldImagePath = public_path($foundItem->image_url);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }

            $foundItem->update($data);
            $foundItem->refresh(); // Refresh the model to get the updated data

            return response()->json([
                'success' => true,
                'message' => 'Found item updated successfully',
                'post' => $foundItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified found item from storage.
     */
    public function destroy($id)
    {
        try {
            $foundItem = FoundItem::findOrFail($id);
            
            // Delete the image file if it exists
            if ($foundItem->image_url) {
                $filePath = public_path($foundItem->image_url);
                if (file_exists($filePath)) {
                    unlink($filePath); 
                }
            }
        
            $foundItem->delete();
        
            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item: ' . $e->getMessage()
            ], 500);
        }
    }
}
