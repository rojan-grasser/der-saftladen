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
        Schema::create('user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('role');
            $table->timestamps();

            $table->unique(['user_id', 'role']);
        });

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
            DB::table('user_role')->insert($roles);
 }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_status_index');
            $table->dropColumn('role');
        });
         }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {  Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'teacher', 'instructor', 'admin'])->default(Role::USER);
            $table->index(['role', 'status']);
        });

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

        Schema::dropIfExists('user_role');
    }
};