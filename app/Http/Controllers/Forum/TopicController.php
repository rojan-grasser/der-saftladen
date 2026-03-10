<?php

namespace App\Http\Controllers\Forum;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\ProfessionalArea;
use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $areaId)
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'query' => ['nullable', 'string', 'min:1', 'max:50']
        ]);

        $area = ProfessionalArea::findOrFail($areaId);

        $query = Topic::with('user')
            ->where('topics.professional_area_id', '=', $areaId)
            ->orderBy('topics.created_at', 'desc');

        $limit = $validated['limit'] ?? 15;

        if (isset($validated['query'])) {
            $query->where('title', 'like', $validated['query'] . '%');
        }

        return Inertia::render(
            'forum/Topics',
            [
                'topics' => $query->paginate($limit)->withQueryString()->through(function ($topic) {
                    return [
                        'id' => $topic->id,
                        'title' => $topic->title,
                        'description' => $topic->description,
                        'created_at' => $topic->created_at,
                        'user' => [
                            'id' => $topic->user->id,
                            'name' => $topic->user->name,
                            'email' => $topic->user->email,
                            'role' => $topic->user->role,
                        ],
                    ];
                }),
                'area' => [
                    'id' => $area->id,
                    'name' => $area->name,
                    'description' => $area->description,
                ],
                'query' => $validated['query'] ?? null,
            ]
        );
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
    public function store(Request $request, string $areaId)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        if (
            $request->user()->hasRole(Role::INSTRUCTOR) &&
            !Instructor::find($request->user()->id)->hasAccess($areaId)
        ) {
            return back()->with('error', 'Du hast keinen zugriff auf das ausgewählte professionelle Fachgebiet.');
        }

        // Check existence
        ProfessionalArea::findOrFail($areaId);

        $topic = Topic::create([
            ...$validated,
            'professional_area_id' => $areaId,
            'user_id' => auth()->id(),
        ]);

        return redirect("/forum/area/$areaId/topics/" . $topic->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $areaId, string $topicId)
    {
        $currentUserId = $request->user()->id;

        $area = ProfessionalArea::findOrFail($areaId);

        $topic = Topic::with([
            'posts' => function ($query) {
                $query->orderBy('created_at', 'desc')
                    ->withCount([
                        'reactions as likes_count' => function ($query) {
                            $query->where('type', 'like');
                        },
                        'reactions as dislikes_count' => function ($query) {
                            $query->where('type', 'dislike');
                        },
                    ]);
            },
            'posts.creator',
            'posts.reactions' => function ($query) use ($currentUserId) {
                $query->where('user_id', $currentUserId);
            },
        ])
            ->where('topics.professional_area_id', '=', $areaId)
            ->findOrFail($topicId);

        if (
            $request->user()->hasRole(Role::INSTRUCTOR) &&
            !Instructor::find($request->user()->id)->hasAccess($topic->professional_area_id)
        ) {
            return back()->with('error', 'Du hast keinen zugriff auf dieses Thema');
        }

        // Rendering of single topic on /forum/topics/{id}
        $owner = $topic->user;

        return Inertia::render('forum/Topic', [
            'topic' => [
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
                        'reaction' => $post->reactions->first()?->type,
                        'likesCount' => $post->likes_count,
                        'dislikesCount' => $post->dislikes_count,
                        'user' => [
                            'id' => $post->creator->id,
                            'name' => $post->creator->name,
                            'email' => $post->creator->email,
                            'role' => $post->creator->role,
                        ],
                    ];
                }),
                'createdAt' => $topic->created_at,
            ],
            'area' => [
                'id' => $area->id,
                'name' => $area->name,
                'description' => $area->description,
            ],
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
    public function update(Request $request, string $topicID, string $id)
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
    public function destroy(string $topicID, string $id)
    {
        $topic = Topic::findOrFail($id);

        if ($topic->user_id !== auth()->id()) {
            return back()->with('error', 'Du bist nicht der ersteller dieses Themas, daher darfst du es auch nicht löschen.');
        }

        $topic->delete();

        return redirect('/forum/topics');
    }
}
