<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstructorToProfessionController extends Controller
{
    public function index(Request $request, string $instructorId, string $professionId)
    {
        $instructor = Instructor::findOrFail($instructorId);

        // Instructor already allowed to see profession
        if ($instructor->professions()->where('professions.id', $professionId)->exists()) {
            return [
                'success' => false,
                'message' => 'The Instructor already is allowed to see this profession',
            ];
        }

        DB::table('user_to_profession')->insert([
            'profession_id' => $professionId,
            'user_id' => $instructorId,
        ]);

        return ['success' => true];
    }

    public function destroy(Request $request, string $instructorId, string $professionId)
    {
        try {
            DB::table('user_to_profession')
                ->where('user_id', '=', $instructorId)
                ->where('profession_id', '=', $professionId)
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
