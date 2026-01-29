export type User = {
    id: string;
    name: string;
    email: string;
    role: string;
};

export type Topic = {
    id: number;
    title: string;
    description: string;
    isOwnPost: boolean;
    owner: User;
    posts: Array<{
        id: number;
        content: string;
        created_at: string;
        updated_at: string;
        user: User;
    }>;
    createdAt: string;
};
