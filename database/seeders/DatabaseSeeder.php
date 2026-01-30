<?php

namespace Database\Seeders;

use App\Models\ProfessionalArea;
use App\Models\User;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->count(250)->create();
        ProfessionalArea::factory()->count(250)->create();
    }
}
