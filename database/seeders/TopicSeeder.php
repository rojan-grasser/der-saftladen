<?php

namespace Database\Seeders;

use App\Models\ForumPost;
use App\Models\Profession;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $professions = Profession::factory(5)->create();

        foreach ($professions as $profession) {
            $topics = Topic::factory(100)->create([
                'profession_id' => $profession->id,
            ]);

            foreach ($topics as $topic) {
                ForumPost::factory(100)->create([
                    'topic_id' => $topic->id,
                ]);
            }
        }
    }
}
