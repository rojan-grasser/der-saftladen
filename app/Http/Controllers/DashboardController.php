<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Appointment;
use App\Models\ForumPost;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Upcoming Appointments (Next 5)
        $appointments = Appointment::where('user_id', $user->id)
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        // 2. Recent Forum Activity (Latest 5 Topics by the latest post)
        $recentTopics = Topic::with(['user', 'posts' => fn($q) => $q->latest()->limit(1)])
            ->leftJoin('forum_posts', 'topics.id', '=', 'forum_posts.topic_id')
            ->select('topics.*')
            ->selectRaw('COALESCE(MAX(forum_posts.created_at), topics.created_at) as last_post_at')
            ->withCount('posts')
            ->groupBy('topics.id')
            ->orderBy('last_post_at', 'desc')
            ->limit(5)
            ->get();

        // 3. Admin Stats (Visible for Admins)
        $adminData = null;
        if ($user->hasRole(Role::ADMIN)) {
            $adminData = [
                'stats' => [
                    'total_users' => User::count(),
                    'pending_users_count' => User::where('status', UserStatus::PENDING)->count(),
                    'recent_posts_count' => ForumPost::where('created_at', '>=', now()->subDays(7))->count(),
                    'upcoming_appointments_count' => Appointment::where('start_time', '>=', now())->count(),
                ],
            ];
        }

        return Inertia::render('Dashboard', [
            'appointments' => $appointments,
            'recentTopics' => $recentTopics,
            'admin' => $adminData,
        ]);
    }
}
