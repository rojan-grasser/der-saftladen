export type User = {
    id: string;
    first_name: string;
    last_name: string;
    name: string;
    email: string;
};

export type Post = {
    id: number;
    content: string;
    created_at: string;
    updated_at: string;
    reaction: 'like' | 'dislike' | null;
    likesCount: number;
    dislikesCount: number;
    user: User;
    isOwnPost: boolean;
    edited: boolean;
};

export type Topic = {
    id: number;
    title: string;
    description: string;
    isOwnPost: boolean;
    owner: User;
    posts: Array<Post>;
    createdAt: string;
};

export type MinimalTopic = {
    id: number;
    title: string;
    description: string;
    user: {
        id: number;
        first_name: string;
        last_name: string;
        name: string;
        email: string;
    }
};

export type ProfessionalArea = {
    id: number;
    name: string;
    description: string;
};
