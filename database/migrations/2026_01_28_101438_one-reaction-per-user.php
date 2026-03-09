<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('post_reaction', 'post_reactions');

        Schema::table('post_reactions', function (Blueprint $table) {
            $table->unique(['user_id', 'forum_post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_reactions', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'forum_post_id']);
        });

        Schema::rename('post_reactions', 'post_reaction');
    }
};
