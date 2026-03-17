<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileUpload extends Model
{
    protected $guarded = ['id'];

    protected $fillable = ['user_id', 'file_path', 'bucket', 'size'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uuid(): string
    {
        $parts = explode('/', $this->file_path, 2);

        return $parts[0];
    }

    public function filename(): string
    {
        return explode('/', $this->file_path, 2)[1];
    }

    public function type(): string
    {
        $exploded = explode('.', explode('/', $this->file_path, 2)[1]);
        return $exploded[count($exploded) - 1];
    }

    public function downloadUrl(): string
    {
        $bucket = $this->bucket;
        $path = $this->file_path;
        $host = app()->isProduction() ? 'https://der-saftladen.kubeberry.com/assets' : 'http://localhost:9000';

        return "$host/$bucket/$path";
    }
}
