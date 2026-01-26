<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalArea;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Illuminate\Http\Response;

class ProfessionalAreaController extends Controller
{
    /**
     * Takes name and description as parameters, returns success (always) and message (sometimes).
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'description' => ['required'],
        ]);

        $name = trim($validated['name']);

        try {
            ProfessionalArea::create([
                'name' => $name,
                'description' => trim($validated['description']),
            ]);

            return response()->setStatusCode(200);
        } catch (Exception $exception) {
            // Error code 23000 -> Unique violation on the name
            if ($exception instanceof QueryException && $exception->errorInfo[0] === '23000') {
                return response()->setStatusCode(409);
            }

            return response()->setStatusCode(500);
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string'],
            'description' => ['sometimes', 'string'],
        ]);

        $professionalArea = ProfessionalArea::find($id);

        $updateData = [
            'name' => array_key_exists('name', $validated) ? trim($validated['name']) : $professionalArea->name,
            'description' => array_key_exists('description', $validated) ? trim($validated['description']) : $professionalArea->description,
        ];

        try {
            $professionalArea->update($updateData);

            return response()->setStatusCode(200);
        } catch (Exception $exception) {
            // Error code 23000 -> Unique violation on the name
            if ($exception instanceof QueryException && $exception->errorInfo[0] === '23000') {
                return response()->setStatusCode(409);
            }

            return response()->setStatusCode(500);
        }
    }

    /**
     * Deletes a professional area by its id.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            ProfessionalArea::destroy($id);

            return ['success' => true];
        } catch (Exception $exception) {
            Log::error($exception);

            return [
                'success' => false,
                'message' => 'There was a unexpected error while deleting the professional area, please try again later',
            ];
        }
    }

    public function get(Request $request)
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'string', 'max:255'],
        ]);

        $query = DB::table('professional_areas');

        if (isset($validated['query'])) {
            $query->where('name', 'like', '%' . $validated['query'] . '%');
        }

        return $query->paginate(20, ['id', 'name', 'description'])->withQueryString();
    }

    public function getInstructors(Request $request, string $id)
    {
        return ProfessionalArea::findOrFail($id)->instructors;
    }
}
