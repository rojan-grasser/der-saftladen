<?php

namespace Database\Seeders;

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

        $this->call(TestUserSeeder::class);
        User::factory()->count(250)->create();
        $this->call(AppointmentSeeder::class);
        // Profession::factory()->count(250)->create();

        $this->call(TopicSeeder::class);
    }
}
