<?php

namespace App\Http\Controllers\Forum;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\ProfessionalArea;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfessionalAreaController extends Controller
{
    private function getQueryForInstructor(string $instructorId)
    {
        return ProfessionalArea::join('user_to_professional_area as utpa', function ($join) use ($instructorId) {
            $join->on('utpa.professional_area_id', '=', 'professional_areas.id')
                ->where('utpa.user_id', $instructorId);
        });
    }

    private function getQueryForTeacher()
    {
        return ProfessionalArea::query();
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

        return Inertia::render('forum/ProfessionalAreas', [
            'areas' => $areas,
        ]);
    }
}
