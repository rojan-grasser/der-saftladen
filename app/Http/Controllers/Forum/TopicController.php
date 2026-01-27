<?php

namespace App\Http\Controllers\Forum;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    private function getManyForInstructor(string $instructorId)
    {
        return Topic::join('user_to_professional_area as utpa', function ($join) use ($instructorId) {
            $join->on('utpa.professional_area_id', '=', 'topics.professional_area_id')
                ->where('utpa.user_id', $instructorId);
        })
            ->select('topics.*')
            ->orderBy('topics.created_at', 'desc')
            ->orderBy('topics.id', 'desc');
    }

    private function getManyForTeacher()
    {
        return Topic::query()
            ->orderBy('topics.created_at', 'desc')
            ->orderBy('topics.id', 'desc');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'cursor' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $request->user()->hasRole(UserRole::INSTRUCTOR) ?
            $this->getManyForInstructor(auth()->id()) :
            $this->getManyForTeacher();

        $limit = $validated['limit'] ?? 15;

        return $query->cursorPaginate($limit)->withQueryString();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->setStatusCode(501);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'professional_area_id' => ['required', 'integer', 'exists:professional_areas,id'],
        ]);

        if (
            $request->user()->hasRole(UserRole::INSTRUCTOR) &&
            !Instructor::find($request->user()->id)->hasAccess($validated['professional_area_id'])
        ) {
            return back()->with('error', 'Du hast keinen zugriff auf das ausgewählte professionelle Fachgebiet.');
        }

        $topic = Topic::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return redirect('/forum/topics/' . $topic->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $topic = Topic::findOrFail($id);

        if (
            $request->user()->hasRole(UserRole::INSTRUCTOR) &&
            !Instructor::find($request->user()->id)->hasAccess($topic->professional_area_id)
        ) {
            return back()->with('error', 'Du hast keinen zugriff auf dieses Thema');
        }

        // Rendering of single topic on /forum/topics/{id}
        // Todo: Filter out data which is not necessary for the single topic view
        return $topic;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return response()->setStatusCode(501);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $topic = Topic::findOrFail($id);

        if ($topic->user_id !== auth()->id()) {
            return back()->with('error', 'Du bist nicht der ersteller dieses Themas, daher darfst du es auch nicht bearbeiten.');
        }

        $topic->update($validated);

        return redirect('/forum/topics/' . $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $topic = Topic::findOrFail($id);

        if ($topic->user_id !== auth()->id()) {
            return back()->with('error', 'Du bist nicht der ersteller dieses Themas, daher darfst du es auch nicht löschen.');
        }

        $topic->delete();

        return redirect('/forum/topics');
    }
}
