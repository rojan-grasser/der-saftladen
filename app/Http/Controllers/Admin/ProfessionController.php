<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profession;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProfessionController extends Controller
{
    /**
     * Takes name and description as parameters, returns success (always) and message (sometimes).
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($validated['name']);

        try {
            Profession::create([
                'name' => $name,
                'description' => trim($validated['description']),
            ]);

            return back()->with('success', 'Profession created successfully.');
        } catch (Exception $exception) {
            // Error code 23000 -> Unique violation on the name
            if ($exception instanceof QueryException && $exception->errorInfo[0] === '23000') {
                throw ValidationException::withMessages([
                    'name' => 'The profession "' . $name . '" already exists.',
                ]);
            }

            Log::error($exception);

            return back()->with('error', 'There was an unexpected error while creating the profession. Please try again later.');
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'max:255'],
            'instructor_ids' => ['sometimes', 'array'],
            'instructor_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $profession = Profession::query()->findOrFail($id);

        $updateData = [
            'name' => array_key_exists('name', $validated) ? trim($validated['name']) : $profession->name,
            'description' => array_key_exists('description', $validated) ? trim($validated['description']) : $profession->description,
        ];

        $instructorIds = $validated['instructor_ids'] ?? null;

        try {
            DB::transaction(function () use ($profession, $updateData, $instructorIds) {
                $profession->update($updateData);

                if (is_array($instructorIds)) {
                    $profession->instructors()->sync($instructorIds);
                }
            });

            return back()->with('success', 'profession updated successfully.');
        } catch (Exception $exception) {
            // Error code 23000 -> Unique violation on the name (SQLite/MySQL etc.)
            if ($exception instanceof QueryException && ($exception->errorInfo[0] ?? null) === '23000') {
                throw ValidationException::withMessages([
                    'name' => 'The profession "' . $updateData['name'] . '" already exists.',
                ]);
            }

            Log::error($exception);

            return back()->with('error', 'There was an unexpected error while updating the profession. Please try again later.');
        }
    }

    /**
     * Deletes a profession by its id.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            Profession::destroy($id);

            return back()->with('success', 'profession deleted successfully.');
        } catch (Exception $exception) {
            Log::error($exception);

            return back()->with('error', 'There was a unexpected error while deleting the profession, please try again later');
        }
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'string', 'max:255'],
        ]);

        $queryBuilder = Profession::query()
            ->select('id', 'name', 'description')
            ->with(['instructors']);

        if (!empty($validated['query'])) {
            $search = $validated['query'];

            $queryBuilder->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    //->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('instructors', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return Inertia::render('admin/Professions', [
            'professions' => $queryBuilder
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'filters' => $request->only('query'),
        ]);
    }


    public function getInstructors(Request $request, string $id)
    {
        return Profession::findOrFail($id)->instructors;
    }
}
