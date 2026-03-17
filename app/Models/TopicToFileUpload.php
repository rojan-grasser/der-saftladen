<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicToFileUpload extends Model
{
    protected $table = 'topic_to_file_upload';

    protected $guarded = ['id'];

    protected $fillable = ['topic_id', 'file_upload_id'];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(FileUpload::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
