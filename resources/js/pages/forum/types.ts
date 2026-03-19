export type User = {
    id: string;
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
    isSubscribed: boolean;
    pinned: boolean;
    owner: User;
    posts: Array<Post>;
    createdAt: string;
};

export type MinimalTopic = {
    id: number;
    title: string;
    description: string;
    pinned: boolean;
    isSubscribed: boolean;
    user: {
        id: number;
        name: string;
        email: string;
    };
};

export type Profession = {
    id: number;
    name: string;
    description: string;
};
