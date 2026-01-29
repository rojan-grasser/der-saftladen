<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Topic extends Model
{
    protected $guarded = ['id'];

    protected $fillable = [
        'title',
        'description',
        'professional_area_id',
        'user_id',
    ];

    public function posts()
    {
        return $this->hasMany(ForumPost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'user_id');
    }
}
