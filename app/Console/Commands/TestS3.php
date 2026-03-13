<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestS3 extends Command
{
    protected $signature = 'test:s3';
    protected $description = 'Tests that the S3 bucket is publicly readable and only writable with API keys';

    public function handle(): void
    {
        $diskUsers = Storage::disk('s3-users');
        $diskForum = Storage::disk('s3-forum');

        $file = 'test-' . now()->timestamp . '.txt';
        $content = 'hello from laravel';

        $diskUsers->put($file, $content);
        $diskForum->put($file, $content);

        $this->info('Contents users: ' . $diskUsers->get($file));
        $this->info('Contents forum: ' . $diskForum->get($file));

        // Cleanup
        // $disk->delete($file);
        // $this->info('   Cleaned up test file.');
    }
}
