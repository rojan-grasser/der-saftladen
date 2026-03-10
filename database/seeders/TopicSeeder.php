<?php

namespace Database\Seeders;

use App\Models\ForumPost;
use App\Models\ProfessionalArea;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there are users to assign as authors
        if (User::count() === 0) {
            User::factory(10)->create();
        }

        $areas = ProfessionalArea::factory(5)->create();

        foreach ($areas as $area) {
            $topics = Topic::factory(100)->create([
                'professional_area_id' => $area->id,
            ]);

            foreach ($topics as $topic) {
                ForumPost::factory(100)->create([
                    'topic_id' => $topic->id,
                ]);
            }
        }
    }
}
