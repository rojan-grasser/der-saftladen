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
        Schema::rename('professional_areas', 'professions');
        Schema::rename('user_to_professional_area', 'user_to_profession');

        Schema::table('user_to_profession', function (Blueprint $table) {
            $table->renameColumn('professional_area_id', 'profession_id');
        });

        Schema::table('topics', function (Blueprint $table) {
            $table->renameColumn('professional_area_id', 'profession_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->renameColumn('profession_id', 'professional_area_id');
        });

        Schema::table('user_to_profession', function (Blueprint $table) {
            $table->renameColumn('profession_id', 'professional_area_id');
        });

        Schema::rename('user_to_profession', 'user_to_professional_area');
        Schema::rename('professions', 'professional_areas');
    }
};
