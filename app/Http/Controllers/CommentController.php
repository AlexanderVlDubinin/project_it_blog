<?php

namespace App\Http\Controllers;

use App\Actions\CreateNewComment;
use App\Actions\ForceDeleteComment;
use App\Actions\RestoreComment;
use App\Actions\SoftDeleteComment;
use App\Actions\UpdateComment;
use App\Http\Requests\SoftDeleteCommentRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request, Post $post, CreateNewComment $createNewComment)
    {
        // Forbid commenting on mildly deleted posts.
        if ($post->trashed()) {
            abort(404, 'You cannot comment on a deleted post.');
        }

        $createNewComment($post, $request->validated());

        return back()->with('success', 'The comment was added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, Comment $comment, UpdateComment $updateComment)
    {
        $this->authorize('update', $comment);

        $updateComment($comment, $request->validated());

        return back()->with('success', 'The comment has been successfully updated!');
    }

    public function delete(SoftDeleteCommentRequest $request, Comment $comment, SoftDeleteComment $softDeleteComment)
    {
        $this->authorize('delete', $comment);

        $softDeleteComment($request, $comment, $request->validated());

        return back()->with('success', 'The comment was hidden by the moderator.');
    }

    public function restore(Comment $comment, RestoreComment $restoreComment)
    {
        $this->authorize('restore', $comment);

        $restoreComment($comment);

        return back()->with('success', 'The comment was restored by the moderator.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function forceDelete(Comment $comment, ForceDeleteComment $forceDeleteComment)
    {
        $this->authorize('forceDelete', $comment);

        $forceDeleteComment($comment);

        return back()->with('success', 'The comment was deleted by the moderator.');
    }
}
