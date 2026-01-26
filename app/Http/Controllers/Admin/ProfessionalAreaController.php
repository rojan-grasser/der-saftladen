<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalArea;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProfessionalAreaController extends Controller
{
    /**
     * Takes name and description as parameters, returns success (always) and message (sometimes).
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($validated['name']);

        try {
            ProfessionalArea::create([
                'name' => $name,
                'description' => trim($validated['description']),
            ]);

            return back()->with('success', 'Professional area created successfully.');
        } catch (Exception $exception) {
            // Error code 23000 -> Unique violation on the name
            if ($exception instanceof QueryException && $exception->errorInfo[0] === '23000') {
                throw ValidationException::withMessages([
                    'name' => 'The professional area "' . $name . '" already exists.',
                ]);
            }

            Log::error($exception);

            return back()->with('error', 'There was an unexpected error while creating the professional area. Please try again later.');
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

        $professionalArea = ProfessionalArea::query()->findOrFail($id);

        $updateData = [
            'name' => array_key_exists('name', $validated) ? trim($validated['name']) : $professionalArea->name,
            'description' => array_key_exists('description', $validated) ? trim($validated['description']) : $professionalArea->description,
        ];

        $instructorIds = $validated['instructor_ids'] ?? null;

        try {
            DB::transaction(function () use ($professionalArea, $updateData, $instructorIds) {
                $professionalArea->update($updateData);

                if (is_array($instructorIds)) {
                    $professionalArea->instructors()->sync($instructorIds);
                }
            });

            return back()->with('success', 'Professional area updated successfully.');
        } catch (Exception $exception) {
            // Error code 23000 -> Unique violation on the name (SQLite/MySQL etc.)
            if ($exception instanceof QueryException && ($exception->errorInfo[0] ?? null) === '23000') {
                throw ValidationException::withMessages([
                    'name' => 'The professional area "' . $updateData['name'] . '" already exists.',
                ]);
            }

            Log::error($exception);

            return back()->with('error', 'There was an unexpected error while updating the professional area. Please try again later.');
        }
    }

    /**
     * Deletes a professional area by its id.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            ProfessionalArea::destroy($id);

            return back()->with('success', 'Professional area deleted successfully.');
        } catch (Exception $exception) {
            Log::error($exception);

            return back()->with('error', 'There was a unexpected error while deleting the professional area, please try again later');
        }
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'string', 'max:255'],
        ]);

        $query = ProfessionalArea::query()
            ->select('id', 'name', 'description')
            ->with([
                'instructors',
            ]);

        if (isset($validated['query'])) {
            $query->where('name', 'like', '%' . $validated['query'] . '%');
        }

        return Inertia::render('admin/ProfessionalAreas', [
            'professionalAreas' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function getInstructors(Request $request, string $id)
    {
        return ProfessionalArea::findOrFail($id)->instructors;
    }
}
