<?php

namespace App\Console\Commands\housekeeping;
use Illuminate\Support\Facades\DB;

function cleanupUserToProfessionalField(): void
{
    DB::table('user_to_professional_field')
        ->whereIn('user_id', function ($query) {
            $query->select('id')
                ->from('users')
                ->where('role', '<>', 'teacher');
        })
        ->delete();
}
