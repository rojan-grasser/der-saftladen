<?php

namespace App\Http\Controllers\Forum;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Profession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfessionController extends Controller
{
    private function getQueryForInstructor(string $instructorId)
    {
        return Profession::join('user_to_profession as utp', function ($join) use ($instructorId) {
            $join->on('utp.profession_id', '=', 'professions.id')
                ->where('utp.user_id', $instructorId);
        });
    }

    private function getQueryForTeacher()
    {
        return Profession::query();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $areas = ($request->user()->hasRole(Role::INSTRUCTOR) ?
            $this->getQueryForInstructor($request->user()->id) :
            $this->getQueryForTeacher())
            ->get()
            ->map(function ($area) {
                return [
                    'id' => $area->id,
                    'name' => $area->name,
                    'description' => $area->description,
                ];
            });

        return Inertia::render('forum/Professions', [
            'areas' => $areas,
        ]);
    }
}
