<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostReaction extends Model
{
    protected $guarded = ['id'];

    protected $fillable = ['user_id', 'forum_post_id', 'type'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    private static function baseFindByUserAndPost(string $userId, string $postId): Builder
    {
        return PostReaction::query()
            ->where('user_id', '=', $userId)
            ->where('forum_post_id', '=', $postId);
    }

    public static function findByUserAndPost(string $userId, string $postId): ?PostReaction
    {
        return PostReaction::baseFindByUserAndPost($userId, $postId)->first();
    }

    public static function findOrFailByUserAndPost(string $userId, string $postId): PostReaction
    {
        return PostReaction::baseFindByUserAndPost($userId, $postId)->firstOrFail();
    }
}
