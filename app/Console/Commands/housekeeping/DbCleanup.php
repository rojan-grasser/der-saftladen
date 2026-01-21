<?php

namespace App\Console\Commands\housekeeping;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;
use Throwable;

class DbCleanup
{
    /**
     * @throws Throwable
     */
    public static function dbCleanup(): void
    {
        DB::beginTransaction();

        try {
            Log::info('Starting db cleanup jobs');
            cleanupUserToProfessionalField();
            // Other DB cleanup jobs

            DB::commit();
        } catch (Exception $exception) {
            Log::error($exception);

            DB::rollBack();
        }
    }
}
