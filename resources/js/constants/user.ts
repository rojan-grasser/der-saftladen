import { UserRoleString, UserStatus } from '@/types';

export const statusVariants: Record<
    UserStatus,
    'default' | 'destructive' | 'outline'
> = {
    active: 'default',
    inactive: 'destructive',
    pending: 'outline',
};

export const statusLabels: Record<UserStatus, string> = {
    active: 'Aktiv',
    inactive: 'Inaktiv',
    pending: 'Ausstehend',
};

export const roleLabels: Record<UserRoleString, string> = {
    admin: 'Admin',
    instructor: 'Ausbilder',
    teacher: 'Lehrer',
};
