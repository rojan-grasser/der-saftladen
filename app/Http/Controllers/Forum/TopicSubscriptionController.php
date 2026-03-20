<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicSubscriptionController extends Controller
{
    public function toggle(Request $request, string $professionId, string $topicId)
    {
        $topic = Topic::findOrFail($topicId);
        $user = $request->user();

        if ($user->subscribedTopics()->where('topic_id', $topic->id)->exists()) {
            $user->subscribedTopics()->detach($topic->id);
            $message = 'Abonnement für das Thema wurde beendet.';
        } else {
            $user->subscribedTopics()->attach($topic->id);
            $message = 'Das Thema wurde abonniert.';
        }

        return back()->with('success', $message);
    }
}
