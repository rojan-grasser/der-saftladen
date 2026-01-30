<?php

namespace App\Http\Controllers\Forum;

use App\Enums\PostReactionType;
use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\PostReaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostReactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $topicId, string $postId)
    {
        $post = ForumPost::findOrFail($postId);

        $likeCount = $post->reactions()->where('type', PostReactionType::LIKE->value)->count();
        $dislikeCount = $post->reactions()->where('type', PostReactionType::DISLIKE->value)->count();

        return response()->json([
            'likes' => $likeCount,
            'dislikes' => $dislikeCount,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $topicId, string $postId)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::enum(PostReactionType::class)],
        ]);

        // Kinda an upsert, but since a user can only have one reaction per post i dont see the need for the frontend to know the id of the reaction
        $postReaction = PostReaction::findByUserAndPost($request->user()->id, $postId);

        if (!$postReaction) {
            PostReaction::create([
                'user_id' => $request->user()->id,
                'forum_post_id' => $postId,
                'type' => $validated['type'],
            ]);

            return;
        }

        $postReaction->update([
            'type' => $validated['type'],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $topicId, string $postId)
    {
        PostReaction::findOrFailByUserAndPost($request->user()->id, $postId)->delete();

        return response(null, 200);
    }
}
