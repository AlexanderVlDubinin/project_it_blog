<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));

        $posts = Post::query()
            ->where('posts.is_published', true)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->where('posts.id', (int) $search);
                    }
                    $q->orWhere('posts.title', 'like', "%{$search}%")
                        ->orWhere('posts.content', 'like', "%{$search}%");
                });

            })
            ->with('user')
            ->orderByDesc('created_at')
            ->orderBy('id')
            ->paginate(6)
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts.edit', [
            'post' => new Post()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();

        $image = $validated['image'] ?? null;
        unset($validated['image'], $validated['remove_image']);

        $validated['is_published'] = (bool)($validated['is_published'] ?? false);

        $post = Post::create($validated);

        if ($image) {
            $path = $image->store('posts', 'public');
            $post->image = $path;
            $post->save();
        }

        return redirect()->route('posts.index')
            ->with('success', 'Post created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('posts.show', [
            'post' => $post
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return view('posts.edit', [
            'post' => $post
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $validated = $request->validated();

        $newImage = $validated['image'] ?? null;
        $removeImage = (bool)($validated['remove_image'] ?? false);
        unset($validated['image'], $validated['remove_image']);

        $validated['is_published'] = (bool)($validated['is_published'] ?? false);

        $post->update($validated);

        if ($removeImage && $post->image) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->image = null;
        }

        if ($newImage) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $path = $newImage->store('posts', 'public');
            $post->image = $path;
        }

        $post->save();

        return redirect()->route('posts.index')
            ->with('success', 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully');
    }
}
