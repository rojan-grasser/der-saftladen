<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('professional_area_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('topics');
    }
};
