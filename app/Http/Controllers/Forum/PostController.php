<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\Topic;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $professionId, string $topicId)
    {
        return Topic::findOrFail($topicId)->posts()->paginate(25);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, string $professionId, string $topicId)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $professionId, string $topicId)
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        ForumPost::create([
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
            'topic_id' => $topicId,
        ]);

        return back()->with('success', 'Der kommentar wurde erstellt');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $professionId, string $topicId, string $postId)
    {
        return ForumPost::findOrFail($postId);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $professionId, string $topicId, string $postId)
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $post = ForumPost::findOrFail($postId);

        if ($post->user_id !== $request->user()->id) {
            return back()->with('error', 'Du kannst nur deine eigenen posts bearbeiten!');
        }

        $post->update([
            ...$validated,
            'edited' => true,
        ]);

        return redirect("/forum/profession/$professionId/topics/$topicId")->with('success', 'Der Kommentar wurde aktualisiert');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $professionId, string $topicId, $postId)
    {
        $post = ForumPost::findOrFail($postId);

        if ($post->user_id !== $request->user()->id) {
            return back()->with('error', 'Du bist nicht berechtigt diesen post zu löschen');
        }

        $post->delete();

        return redirect("/forum/profession/$professionId/topics/$topicId");
    }
}
