<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:255'],
            'cursor' => ['nullable', 'string'], // cursorPaginate token
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $queryString = trim(($validated['query'] ?? ''));
        $limit = (int)($validated['limit'] ?? 25);

        $query = Instructor::query()
            ->select('id', 'email', 'name');


        if ($queryString !== '') {
            $query->where(function ($q) use ($queryString) {
                $q->where('name', 'like', $queryString . '%')
                    ->orWhere('email', 'like', $queryString . '%');
            });
        }

        $paginator = $query
            ->orderBy('name') // important: deterministic order for cursor pagination
            ->cursorPaginate($limit)
            ->withQueryString();

        return response()->json($paginator);
    }
}
