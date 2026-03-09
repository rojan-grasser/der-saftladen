<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
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
            'role' => ['sometimes', new Enum(Role::class)],
            'status' => ['sometimes', new Enum(UserStatus::class)],
            'search' => ['sometimes', 'string', 'max:255'],
        ]);

        $query = User::query()->with('roles');

        if (isset($validated['role'])) {
            $query->whereHas('roles', function ($q) use ($validated) {
                $q->where('role', $validated['role']);
            });
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Users', [
            'users' => $users,
            'filters' => $request->only(['role', 'status', 'search']),
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @throws Throwable
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'status' => ['sometimes', new Enum(UserStatus::class)],
            'roles' => ['sometimes', 'array'],
            'roles.*' => [new Enum(Role::class)],
        ]);

        DB::transaction(function () use ($validated, $id) {
            $user = User::findOrFail($id);

            $user->update(collect($validated)->except('roles')->toArray());

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
        //
    }
}

