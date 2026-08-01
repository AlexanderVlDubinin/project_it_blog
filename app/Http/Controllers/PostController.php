<?php

namespace App\Http\Controllers;

use App\Actions\LoadPostComments;
use App\Actions\PublishedPaginatedPosts;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly PostService $postService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PublishedPaginatedPosts $publishedPaginatedPosts)
    {
        $this->authorize('viewAny', Post::class);

        $search = trim((string) $request->input('q'));

        $posts = $publishedPaginatedPosts($search, 6);

        return view('posts.index', [
            'posts' => $posts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Post::class);

        return view('posts.edit', [
            'post' => new Post()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $this->authorize('create', Post::class);

        $this->postService->store($request->validated());

        return redirect()->route('posts.index')
            ->with('success', 'Post created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post, LoadPostComments $loadPostComments)
    {
        $this->authorize('view', $post);

        $comments = $loadPostComments($post);

        return view('posts.show', [
            'post' => $post,
            'comments' => $comments,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('posts.edit', [
            'post' => $post
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        $this->postService->update($post, $request->validated());

        return redirect()->route('posts.index')
            ->with('success', 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $this->postService->destroy($post);

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully');
    }
}
