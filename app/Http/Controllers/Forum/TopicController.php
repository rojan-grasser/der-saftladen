<?php

namespace App\Http\Controllers\Forum;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use App\Models\Instructor;
use App\Models\Profession;
use App\Models\Topic;
use App\Models\TopicToFileUpload;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $professionId)
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'query' => ['nullable', 'string', 'min:1', 'max:50'],
        ]);

        $profession = Profession::findOrFail($professionId);

        $query = Topic::with('user')
            ->where('topics.profession_id', '=', $professionId)
            ->orderBy('pinned', 'desc')
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
                        'pinned' => $topic->pinned,
                        'created_at' => $topic->created_at,
                        'user' => [
                            'id' => $topic->user?->id ?? 0,
                            'name' => $topic->user?->name ?? User::$deletedUserName,
                            'email' => $topic->user?->email ?? '',
                        ],
                    ];
                }),
                'profession' => [
                    'id' => $profession->id,
                    'name' => $profession->name,
                    'description' => $profession->description,
                ],
                'query' => $validated['query'] ?? null,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $professionId)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['required', 'string'],
            'files' => ['sometimes', 'array'],
            'files.*' => ['integer'],
        ]);

        if (
            $request->user()->hasRole(Role::INSTRUCTOR) &&
            !Instructor::find($request->user()->id)->hasAccess($professionId)
        ) {
            return back()->with('error', 'Du hast keinen zugriff auf den Berufsbereich.');
        }

        // Check existence
        Profession::findOrFail($professionId);

        $topic = Topic::create([
            ...$validated,
            'profession_id' => $professionId,
            'user_id' => auth()->id(),
        ]);

        $fileUploads = FileUpload::findMany($validated['files']);

        foreach ($fileUploads as $file) {
            TopicToFileUpload::create([
                'topic_id' => $topic->id,
                'file_upload_id' => $file->id,
            ]);
        }

        return redirect("/forum/profession/$professionId/topics/" . $topic->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $professionId, string $topicId)
    {
        $currentUserId = $request->user()->id;

        $profession = Profession::findOrFail($professionId);

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
            ->where('topics.profession_id', '=', $professionId)
            ->findOrFail($topicId);

        $owner = $topic->user;

        return Inertia::render('forum/Topic', [
            'topic' => [
                'id' => $topic->id,
                'title' => $topic->title,
                'description' => $topic->description,
                'isOwnPost' => $topic->user_id === $request->user()->id,
                'pinned' => $topic->pinned,
                'files' => $topic->fileUploads->map(function ($upload) {
                    return [
                        'name' => $upload->filename(),
                        'size' => $upload->size,
                        'type' => $upload->type(),
                        'url' => $upload->downloadUrl(),
                        'id' => $upload->id,
                    ];
                }),
                'owner' => [
                    'id' => $owner?->id ?? 0,
                    'name' => $owner?->name ?? User::$deletedUserName,
                    'email' => $owner?->email ?? '',
                ],
                'posts' => $topic->posts->map(function ($post) use ($request) {
                    return [
                        'id' => $post->id,
                        'content' => $post->content,
                        'created_at' => $post->created_at,
                        'updated_at' => $post->updated_at,
                        'reaction' => $post->reactions->first()?->type,
                        'likesCount' => $post->likes_count,
                        'dislikesCount' => $post->dislikes_count,
                        'isOwnPost' => ($post->creator?->id ?? 0) === $request->user()->id,
                        'edited' => (bool)$post->edited,
                        'user' => [
                            'id' => $post->creator?->id ?? 0,
                            'name' => $post->creator?->name ?? User::$deletedUserName,
                            'email' => $post->creator?->email ?? '',
                        ],
                    ];
                }),
                'createdAt' => $topic->created_at,
            ],
            'profession' => [
                'id' => $profession->id,
                'name' => $profession->name,
                'description' => $profession->description,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $topicID, string $id)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'files' => ['sometimes', 'array'],
            'files.*' => ['integer'],
        ]);

        $topic = Topic::findOrFail($id);

        if ($topic->user_id !== auth()->id()) {
            return back()->with('error', 'Du bist nicht der ersteller dieses Themas, daher darfst du es auch nicht bearbeiten.');
        }

        try {
            \DB::transaction(function () use ($validated, $topic) {
                $topic->update($validated);

                TopicToFileUpload::where('topic_id', $topic->id)->delete();

                $fileUploads = FileUpload::findMany($validated['files']);

                foreach ($fileUploads as $file) {
                    TopicToFileUpload::create([
                        'topic_id' => $topic->id,
                        'file_upload_id' => $file->id,
                    ]);
                }
            });
        } catch (\Throwable) {
            return back()->with('error', 'Es ist ein Fehler beim bearbeiten aufgetreten');
        }

        return back()->with('success', 'Das Thema wurde bearbeitet');
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

        return redirect('/forum/professions');
    }

    public function togglePin(Request $request, string $professionId, string $id)
    {
        if (!$request->user()->hasRole(Role::ADMIN)) {
            return back()->with('error', 'Du darfst keine Themen anheften!');
        }

        $topic = Topic::findOrFail($id);

        $topic->update([
            'pinned' => !$topic->pinned,
        ]);

        return back()->with('success', 'Das Thema wurde ' . ($topic->pinned ? 'angeheftet' : 'abgeheftet'));
    }
}
