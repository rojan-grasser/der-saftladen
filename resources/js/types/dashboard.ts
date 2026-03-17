export interface Appointment {
    id: number;
    title: string;
    description: string;
    location: string;
    start_time: string;
    end_time: string;
}

export interface Topic {
    id: number;
    profession_id: number;
    title: string;
    description: string;
    posts_count: number;
    user: {
        name: string;
    };
    created_at: string;
}

export interface AdminData {
    stats: {
        total_users: number;
        pending_users_count: number;
        recent_posts_count: number;
        upcoming_appointments_count: number;
    };
}
