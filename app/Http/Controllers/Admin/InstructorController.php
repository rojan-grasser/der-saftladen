<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryString = trim($request->validate(['query' => ['nullable', 'string', 'max:255']])['query'] ?? '');

        $query = DB::table('users')
            ->select('id', 'email', 'name')
            ->where('role', '=', UserRole::INSTRUCTOR);

        if ($queryString !== '') {
            $query->where(function ($q) use ($queryString) {
                $q->where('name', 'like', '%' . $queryString . '%')
                    ->orWhere('email', 'like', '%' . $queryString . '%');
            });
        }

        return response()->json($query->get());
    }
}
