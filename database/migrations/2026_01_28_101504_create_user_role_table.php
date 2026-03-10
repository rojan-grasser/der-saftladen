<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

        if (Schema::hasColumn('users', 'role')) {
            $now = now();

            $roles = DB::table('users')
                ->whereNotNull('role')
                ->select('id as user_id', 'role')
                ->get()
                ->map(fn($user) => [
                    'user_id' => $user->user_id,
                    'role' => $user->role,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            if ($roles !== []) {
                DB::table('user_role')->upsert(
                    $roles,
                    ['user_id', 'role'],
                    ['updated_at']
                );
            }

            $this->dropLegacyRoleIndex();

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['user', 'teacher', 'instructor', 'admin'])->default(Role::USER);
            });
        }

        if (Schema::hasTable('user_role')) {
            $roles = DB::table('user_role')
                ->select('user_id', 'role')
                ->orderBy('id')
                ->get();

            foreach ($roles as $role) {
                DB::table('users')
                    ->where('id', $role->user_id)
                    ->whereNull('role')
                    ->update(['role' => $role->role]);
            }
        }

        Schema::dropIfExists('user_role');
    }

    private function dropLegacyRoleIndex(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite' || $driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS users_role_status_index');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('DROP INDEX users_role_status_index ON users');
        }
    }
};
