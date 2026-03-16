<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profession;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfessionController extends Controller
{
    /**
     * Takes name and description as parameters, returns success (always) and message (sometimes).
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('professions', 'name')],
            'description' => ['required', 'string', 'max:255'],
        ], [
            'name.unique' => 'Der Berufsbereich ":input" existiert bereits.',
        ]);

        try {
            Profession::create([
                'name' => trim($validated['name']),
                'description' => trim($validated['description']),
            ]);

            return back()->with('success', 'Profession created successfully.');
        } catch (Exception $exception) {
            Log::error($exception);

            return back()->with('error', 'Es ist ein Fehler beim erstellen des Berufsbereiches aufgetreten. Probiere es bitte später erneut.');
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('professions', 'name')->ignore($id)],
            'description' => ['sometimes', 'string', 'max:255'],
            'instructor_ids' => ['sometimes', 'array'],
            'instructor_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ], [
            'name.unique' => 'Der Berufsbereich ":input" existiert bereits.',
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

            return back()->with('success', 'Der Berufsbereich wurde aktualisiert.');
        } catch (\Throwable $exception) {
            Log::error($exception);

            return back()->with('error', 'Es ist ein Fehler beim aktualisieren des Berufsbereiches aufgetreten. Probiere es bitte später erneut.');
        }
    }

    /**
     * Deletes a profession by its id.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            Profession::destroy($id);

            return back()->with('success', 'Der Berufsbereich wurde gelöscht.');
        } catch (Exception $exception) {
            Log::error($exception);

            return back()->with('error', 'Es ist ein Fehler beim löschen des Berufsbereichs passiert. Probiere es bitte später erneut.');
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
                    ->orWhereHas('instructors', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
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
