<?php

namespace App\Http\Controllers\Forum;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TopicController extends Controller
{
    private function getQueryForInstructor(string $instructorId)
    {
        return Topic::join('user_to_professional_area as utpa', function ($join) use ($instructorId) {
            $join->on('utpa.professional_area_id', '=', 'topics.professional_area_id')
                ->where('utpa.user_id', $instructorId);
        })
            ->select('topics.*')
            ->orderBy('topics.created_at', 'desc')
            ->orderBy('topics.id', 'desc');
    }

    private function getQueryForTeacher()
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
            $this->getQueryForInstructor(auth()->id()) :
            $this->getQueryForTeacher();

        $limit = $validated['limit'] ?? 15;

        return Inertia::render(
            'forum/Overview',
            [
                'topics' => $query->cursorPaginate($limit)->withQueryString(),
            ]
        ); // $query->cursorPaginate($limit)->withQueryString();
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
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['required', 'string'],
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
        $topic = Topic::with(['posts' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'posts.creator'])->findOrFail($id);

        if (
            $request->user()->hasRole(UserRole::INSTRUCTOR) &&
            !Instructor::find($request->user()->id)->hasAccess($topic->professional_area_id)
        ) {
            return back()->with('error', 'Du hast keinen zugriff auf dieses Thema');
        }

        // Rendering of single topic on /forum/topics/{id}
        // Todo: Filter out data which is not necessary for the single topic view
        $owner = $topic->user;

        return Inertia::render('forum/Topic', [
            'id' => $topic->id,
            'title' => $topic->title,
            'description' => $topic->description,
            'isOwnPost' => $topic->user_id === $request->user()->id,
            'owner' => [
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'role' => $owner->role,
            ],
            'posts' => $topic->posts->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                    'user' => [
                        'id' => $post->creator->id,
                        'name' => $post->creator->name,
                        'email' => $post->creator->email,
                        'role' => $post->creator->role,
                    ],
                ];
            }),
            'createdAt' => $topic->created_at,
        ]);
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
            'description' => ['required', 'string'],
        ]);

        $topic = Topic::findOrFail($id);

        if ($topic->user_id !== auth()->id()) {
            return back()->with('error', 'Du bist nicht der ersteller dieses Themas, daher darfst du es auch nicht bearbeiten.');
        }

        $topic->update($validated);

        return back()->with('success', 'Das thema wurde bearbeitet');
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
