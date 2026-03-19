<?php

namespace App\Models;

use Database\Factories\TopicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Topic extends Model
{
    /** @use HasFactory<TopicFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected $fillable = [
        'title',
        'description',
        'profession_id',
        'user_id',
        'pinned',
        'draft',
    ];

    public function posts()
    {
        return $this->hasMany(ForumPost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'topic_subscriptions')
            ->using(TopicSubscription::class)
            ->withTimestamps();
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'user_id');
    }

    public function fileUploads(): BelongsToMany
    {
        return $this->belongsToMany(FileUpload::class, 'topic_to_file_upload', 'topic_id', 'file_upload_id');
    }
}
