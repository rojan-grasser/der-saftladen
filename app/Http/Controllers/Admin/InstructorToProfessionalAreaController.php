<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstructorToProfessionalAreaController extends Controller
{
    public function store(Request $request, string $instructorId, string $areaId)
    {
        $instructor = Instructor::findOrFail($instructorId);

        // Instructor already allowed to see professional area
        if ($instructor->professionalAreas()->where('professional_areas.id', $areaId)->exists()) {
            return response()->setStatusCode(200);
        }

        DB::table('user_to_professional_area')->insert([
            'professional_area_id' => $areaId,
            'user_id' => $instructorId,
        ]);

        return response()->setStatusCode(200);
    }

    public function destroy(Request $request, string $instructorId, string $areaId)
    {
        try {
            DB::table('user_to_professional_area')
                ->where('user_id', '=', $instructorId)
                ->where('professional_area_id', '=', $areaId)
                ->delete();

            return response()->setStatusCode(200);
        } catch (Exception $exception) {
            Log::error($exception);

            return response()->setStatusCode(500);
        }
    }
}
