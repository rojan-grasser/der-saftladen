<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use Illuminate\Http\Request;

class PostController extends Controller
{
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

        return back()->with('success', 'Der Kommentar wurde aktualisiert');
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

        return back();
    }
}
