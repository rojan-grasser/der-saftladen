<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->integer('offset_minutes');
            $table->timestamps();

            $table->unique(['appointment_id', 'offset_minutes']);
        });

        // Migrate existing JSON reminders to the new table
        $appointments = DB::table('appointments')
            ->whereNotNull('reminders')
            ->get(['id', 'reminders']);

        foreach ($appointments as $appointment) {
            $reminders = json_decode($appointment->reminders, true);

            if (!is_array($reminders)) {
                continue;
            }

            foreach ($reminders as $offsetMinutes) {
                DB::table('appointment_reminders')->insert([
                    'appointment_id' => $appointment->id,
                    'offset_minutes' => (int) $offsetMinutes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('reminders');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->json('reminders')->nullable()->after('end_time');
        });

        // Migrate data back to JSON column
        $reminders = DB::table('appointment_reminders')
            ->select('appointment_id', DB::raw('GROUP_CONCAT(offset_minutes) as offsets'))
            ->groupBy('appointment_id')
            ->get();

        foreach ($reminders as $reminder) {
            $offsets = array_map('intval', explode(',', $reminder->offsets));
            DB::table('appointments')
                ->where('id', $reminder->appointment_id)
                ->update(['reminders' => json_encode($offsets)]);
        }

        Schema::dropIfExists('appointment_reminders');
    }
};
