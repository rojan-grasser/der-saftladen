<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalArea;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProfessionalAreaController extends Controller
{
    /**
     * Takes name and description as parameters, returns success (always) and message (sometimes).
     *
     * @param Request $request
     * @return array|false[]|true[]
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'description' => ['required'],
        ]);

        $name = trim($validated['name']);
        $description = trim($validated['description']);

        try {
            ProfessionalArea::create([
                'name' => $name,
                'description' => $description,
            ]);

            return ['success' => true];
        } catch (Exception $exception) {
            // Error code 23000 -> Unique violation on the name
            if ($exception instanceof QueryException && $exception->errorInfo[0] === '23000') {
                return [
                    'success' => false,
                    'message' => 'The professional area "'.$name.'" already exists',
                ];
            }

            return [
                'success' => false,
            ];
        }
    }
}
