<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'role' => ['sometimes', new Enum(UserRole::class)],
            'status' => ['sometimes', new Enum(UserStatus::class)],
        ]);

        $query = User::query();

        if (isset($validated['role'])) {
            $query->where('role', $validated['role']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return $query->select('users.name', 'users.email', 'users.role', 'users.status')->paginate(20);
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

    public function edit(Request $request, string $id)
    {
        $validated = $request->validate([
            'role' => ['sometimes', new Enum(UserRole::class)],
            'status' => ['sometimes', new Enum(UserStatus::class)],
            'name' => ['sometimes'],
            'email' => ['sometimes', 'email'],
        ]);

        DB::table('users')->where('id', '=', $id)->update($validated);

        return ['success' => true];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
