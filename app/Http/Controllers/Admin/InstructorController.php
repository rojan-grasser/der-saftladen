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
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:255'],
            'after' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $queryString = trim(($validated['query'] ?? ''));

        $after = (int)($validated['after'] ?? 0);
        $limit = (int)($validated['limit'] ?? 25);

        $query = DB::table('users')
            ->select('id', 'email', 'name')
            ->where('role', '=', UserRole::INSTRUCTOR)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit($limit + 1);

        if ($queryString !== '') {
            $query->where(function ($q) use ($queryString) {
                $q->where('name', 'like', '%' . $queryString . '%')
                    ->orWhere('email', 'like', '%' . $queryString . '%');
            });
        }

        $rows = $query->get();

        $hasMore = $rows->count() > $limit;
        $items = $hasMore ? $rows->take($limit)->values() : $rows->values();

        $nextCursor = null;
        if ($hasMore && $items->count() > 0) {
            $nextCursor = (int)$items->last()->id;
        }

        return response()->json([
            'items' => $items,
            'next_cursor' => $nextCursor,
        ]);
    }
}
