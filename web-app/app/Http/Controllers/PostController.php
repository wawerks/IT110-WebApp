<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the posts.
     */
    public function index()
    {
        $posts = Post::latest()->get();
        return response()->json($posts);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'required|string',
            'contact_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post = Post::create($request->all());
        return response()->json($post, 201);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'required|string',
            'contact_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post->update($request->all());
        return response()->json($post);
    }

    /**
     * Remove the specified post.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json(null, 204);
    }
}
