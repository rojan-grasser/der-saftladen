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
    public function index(Request $request, string $instructorId, string $areaId)
    {
        $instructor = Instructor::findOrFail($instructorId);

        // Instructor already allowed to see professional area
        if ($instructor->professionalAreas()->where('professional_areas.id', $areaId)->exists()) {
            return [
                'success' => false,
                'message' => 'The Instructor already is allowed to see this professional area',
            ];
        }

        DB::table('user_to_professional_area')->insert([
            'professional_area_id' => $areaId,
            'user_id' => $instructorId,
        ]);

        return ['success' => true];
    }

    public function destroy(Request $request, string $instructorId, string $areaId)
    {
        try {
            DB::table('user_to_professional_area')
                ->where('user_id', '=', $instructorId)
                ->where('professional_area_id', '=', $areaId)
                ->delete();

            return ['success' => true];
        } catch (Exception $exception) {
            Log::error($exception);

            return [
                'success' => false,
                'message' => 'An unexpected error occurred while removing the instructor',
            ];
        }
    }
}
