import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    children?: NavItem[];
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
};

export type UserRoleString = 'admin' | 'instructor' | 'teacher';
export type UserStatus = 'active' | 'inactive' | 'pending';

export interface UserRole {
    id: number;
    user_id: number;
    role: UserRoleString;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    status: UserStatus;
    roles: UserRole[] | null;
    [key: string]: unknown; // This allows for additional properties...
}

export interface Instructor {
    id: number;
    name: string;
    email: string;
}

export interface ProfessionalArea {
    id: number;
    name: string;
    description: string;
    instructors: User[] | null;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface PaginatedResponse<T> {
    data: T[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    current_page_url: string;
    first_page_url: string;
    last_page_url: string;
    next_page_url: string | null;
    prev_page_url: string | null;
    path: string;
    from: number | null;
    to: number | null;
}

export interface PaginatedCursorResponse<T> {
    data: T[];
    path: string;
    per_page: number;
    next_cursor: string | null;
    next_page_url: string | null;
    prev_cursor: string | null;
    prev_page_url: string | null;
}
