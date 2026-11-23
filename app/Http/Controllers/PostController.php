<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    private const POSTS_CACHE_KEY = 'posts:all:v2';

    public function get()
    {
        $posts = Cache::remember(self::POSTS_CACHE_KEY, 1000, function () {
            return Post::with('user')->get()->toArray();
        });

        return response()->json([
            'msg' => 'Posts Get Successfully',
            'posts' => $posts,
        ], 200);
    }

    public function post(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:256',
            'description' => 'required|string',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $post = Post::create($data);

        Cache::forget(self::POSTS_CACHE_KEY);
        Cache::forget("user:{$post->user_id}:posts");

        return response()->json([
            'msg' => 'Post Created Successfully',
            'post' => $post,
        ], 201);
    }

    public function delete($id)
    {
        $deletedPost = Post::findOrFail($id);

        $userId = $deletedPost->user_id;

        $deletedPost->delete();

        Cache::forget(self::POSTS_CACHE_KEY);
        Cache::forget("user:{$userId}:posts");

        return response()->json([
            'msg' => 'Post Deleted Successfully',
            'post' => $deletedPost,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $findById = Post::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:256',
            'description' => 'sometimes|required|string',
        ]);

        $userId = $findById->user_id;

        $findById->update($data);

        Cache::forget(self::POSTS_CACHE_KEY);
        Cache::forget("user:{$userId}:posts");

        return response()->json([
            'msg' => 'Post Updated Successfully',
            'post' => $findById->fresh('user'),
        ], 200);
    }
}
