<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\UserToProfession;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstructorToProfessionController extends Controller
{
    public function store(Request $request, string $instructorId, string $professionId)
    {
        $instructor = Instructor::findOrFail($instructorId);

        // Instructor already allowed to see profession
        if ($instructor->professions()->where('professions.id', $professionId)->exists()) {
            return back()->with('error', 'Dieser Ausbilder darf den Berufsbereich bereits sehen');
        }

        UserToProfession::create([
            'profession_id' => $professionId,
            'user_id' => $instructorId,
        ]);

        return back();
    }

    public function destroy(Request $request, string $instructorId, string $professionId)
    {
        try {
            UserToProfession::where([
                'user_id' => $instructorId,
                'profession_id' => $professionId,
            ])->delete();

            return back();
        } catch (Exception $exception) {
            Log::error($exception);

            return back()->with('error', 'Es ist ein fehler aufgetreten');
        }
    }
}
