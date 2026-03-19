<?php

namespace App\Models;

use Database\Factories\TopicSubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TopicSubscription extends Pivot
{
    /** @use HasFactory<TopicSubscriptionFactory> */
    use HasFactory;

    protected $table = 'topic_subscriptions';

    protected $fillable = [
        'user_id',
        'topic_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
