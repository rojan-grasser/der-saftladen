<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('appointment_reminder_sent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->integer('offset_minutes');
            $table->timestamp('sent_at');

            $table->unique(['appointment_id', 'offset_minutes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_reminder_sent');
    }
};
