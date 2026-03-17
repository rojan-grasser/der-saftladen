<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserToProfession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'role'     => ['sometimes', new Enum(Role::class)],
            'status'   => ['sometimes', new Enum(UserStatus::class)],
            'search'   => ['sometimes', 'string', 'max:255'],
            'priority' => ['sometimes', 'in:firstname,lastname,email'],
        ]);

        $query = User::query()->with('roles');

        // Filter by role
        if (isset($validated['role'])) {
            $query->whereHas('roles', function ($q) use ($validated) {
                $q->where('role', $validated['role']);
            });
        }

        // Filter by status
        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $appliedPrioritySort = false;

        // Suche / Priority
        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            if (!empty($validated['priority'])) {
                $column = match ($validated['priority']) {
                    'firstname' => 'first_name',
                    'lastname'  => 'last_name',
                    'email'     => 'email',
                };

                if ($column === 'email') {
                    // Email: ILIKE (case-insensitive)
                    $query->where('email', 'ilike', "%{$search}%");

                    $query->orderByRaw("
                        CASE
                            WHEN email ILIKE ? THEN 1
                            ELSE 2
                        END
                    ", ["{$search}%"]);

                    $appliedPrioritySort = true;
                } else {
                    // Vor-/Nachname: NULL-safe + ILIKE
                    $query->whereRaw("COALESCE({$column}, '') ILIKE ?", ["%{$search}%"]);

                    $query->orderByRaw("
                        CASE
                            WHEN COALESCE({$column}, '') ILIKE ? THEN 1
                            WHEN COALESCE({$column}, '') ILIKE ? THEN 2
                            ELSE 3
                        END
                    ", ["{$search}%", "% {$search}%"]);

                    $appliedPrioritySort = true;
                }
            } else {
                // Default-Suche (ohne Priority): in allen Feldern, NULL-safe für Namen
                $query->where(function ($q) use ($search) {
                    $q->whereRaw("COALESCE(first_name, '') ILIKE ?", ["%{$search}%"])
                      ->orWhereRaw("COALESCE(last_name, '') ILIKE ?", ["%{$search}%"])
                      ->orWhere('email', 'ilike', "%{$search}%");
                });
            }
        }

        // Standard-Sortierung nur, wenn keine Priority-Sortierung aktiv ist
        if (!$appliedPrioritySort) {
            $query->orderBy('first_name')->orderBy('last_name');
        }

        $users = $query
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Users', [
            'users'   => $users,
            'filters' => $request->only(['role', 'status', 'search', 'priority']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Throwable
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'company'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', new Enum(UserStatus::class)],
            'roles' => ['sometimes', 'array'],
            'roles.*' => [new Enum(Role::class)],
        ]);

        DB::transaction(function () use ($validated, $id) {
            $user = User::findOrFail($id);

            $user->update(collect($validated)->except('roles')->toArray());

            if (($validated['status'] ?? null) != UserStatus::ACTIVE->value) {
                UserToProfession::where([
                    'user_id' => $user->id,
                ])->delete();
            }

            if (isset($validated['roles'])) {
                $user->roles()->delete();

                foreach ($validated['roles'] as $role) {
                    $user->assignRole(Role::from($role));
                }
            }
        });

        return back()->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        User::findOrFail($id)->delete();

        return back()->with('success', 'Der benutzer wurde gelöscht');
    }
}
