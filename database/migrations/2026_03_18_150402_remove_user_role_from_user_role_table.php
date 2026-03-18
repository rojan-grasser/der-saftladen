<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_role')->where('role', 'user')->delete();
    }

    public function down(): void
    {
        // Intentionally empty – deleted rows cannot be restored.
        // Was Again created in this Commit but never used :/
    }
};
