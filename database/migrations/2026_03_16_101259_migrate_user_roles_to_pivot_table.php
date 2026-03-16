<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void
    {
        // 1. Create the pivot table if it doesn't exist yet.
        if (! Schema::hasTable('user_role')) {
            Schema::create('user_role', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->string('role');
                $table->timestamps();

                $table->unique(['user_id', 'role']);
            });
        }

        // 2. Copy existing roles from `users.role` into the pivot table
        //    (only if the old column still exists and has data to migrate).
        if (Schema::hasColumn('users', 'role')) {
            $now = now();

            $existing = DB::table('user_role')
                ->select('user_id', 'role')
                ->get()
                ->map(fn ($row) => $row->user_id . ':' . $row->role)
                ->toArray();

            $roles = DB::table('users')
                ->whereNotNull('role')
                ->where('role', '!=', '')
                ->select('id as user_id', 'role')
                ->get()
                ->filter(fn ($user) => ! in_array($user->user_id . ':' . $user->role, $existing, true))
                ->map(fn ($user) => [
                    'user_id'    => $user->user_id,
                    'role'       => $user->role,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            if ($roles !== []) {
                DB::table('user_role')->insert($roles);
            }

            // 3. Drop the old column (and its index if present).
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasIndex('users', 'users_role_status_index')) {
                    $table->dropIndex('users_role_status_index');
                }
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    /**
     * Reverse: restore the `users.role` column and copy the first role
     * per user back.  Note that users with multiple roles will only keep
     * one – this is unavoidable when going back to a single column.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')
                    ->default(Role::USER->value)
                    ->after('email');
            });

            // Restore one role per user (the earliest assigned one).
            $roles = DB::table('user_role')
                ->select('user_id', 'role')
                ->orderBy('id')
                ->get()
                ->unique('user_id');

            foreach ($roles as $role) {
                DB::table('users')
                    ->where('id', $role->user_id)
                    ->update(['role' => $role->role]);
            }

            Schema::table('users', function (Blueprint $table) {
                $table->index(['role', 'status'], 'users_role_status_index');
            });
        }

        Schema::dropIfExists('user_role');
    }
};