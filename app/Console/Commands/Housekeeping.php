<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\housekeeping\DbCleanup;
use Illuminate\Support\Facades\Log;
use Throwable;

class Housekeeping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:housekeeping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Housekeeping like DB cleanups, etc.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            DbCleanup::dbCleanup();
        } catch (Throwable $exception) {
            Log::error($exception);
        }
    }
}
