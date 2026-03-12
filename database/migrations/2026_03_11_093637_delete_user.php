<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function topics(): void {
        Schema::create('topics_new', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('professional_area_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });

        DB::table('topics_new')->insert(
            DB::table('topics')->get()->map(function ($row) {
                return (array) $row;
            })->toArray()
        );

        Schema::drop('topics');
        Schema::rename('topics_new', 'topics');
    }

    private function posts(): void {
        Schema::create('forum_posts_new', function (Blueprint $table) {
            $table->id();
            $table->string('content');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('topic_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });

        DB::table('forum_posts_new')->insert(
            DB::table('forum_posts')->get()->map(function ($row) {
                return (array) $row;
            })->toArray()
        );

        Schema::drop('forum_posts');
        Schema::rename('forum_posts_new', 'forum_posts');
    }

    private function appointments(): void {
        Schema::create('appointments_new', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->longText('description')->nullable();
            $table->text('location')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestamps();
        });

        DB::table('appointments_new')->insert(
            DB::table('appointments')->get()->map(function ($row) {
                return (array) $row;
            })->toArray()
        );

        Schema::drop('appointments');
        Schema::rename('appointments_new', 'appointments');
    }

    public function up(): void
    {
        // SQLITE cannot edit the constraint of foreign keys, new table -> insert old tables data -> drop old table -> rename new table
        $this->topics();
        $this->posts();
        $this->appointments();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
