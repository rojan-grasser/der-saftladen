// resources/js/composables/avatar.ts
export type AllowedImageType = 'image/png' | 'image/jpeg' | 'image/webp';

const BASE_MINIO = (import.meta as any).env?.VITE_MINIO_PUBLIC_BASE_URL ?? 'http://localhost:9000';
const USERS_BUCKET = (import.meta as any).env?.VITE_MINIO_USERS_BUCKET ?? 'users';

export function getAvatarUrl(folder: number | string): string {
    const base = BASE_MINIO.replace(/\/$/, '');
    return `${base}/${USERS_BUCKET}/${folder}/avatar.png`;
}

export async function getPresignedPutUrl(contentType: AllowedImageType): Promise<any> {
    const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';

    const res = await fetch('/profile/avatar/presign', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ contentType }),
        credentials: 'same-origin',
    });

    if (!res.ok) throw new Error('Presign fehlgeschlagen');
    return res.json();
}

export async function uploadToPresignedUrl(url: string, file: File): Promise<void> {
    const res = await fetch(url, {
        method: 'PUT',
        body: file,
        headers: { 'Content-Type': file.type },
    });

    if (!res.ok) throw new Error('Upload fehlgeschlagen');
}

export async function deleteAvatar(): Promise<any> {
    const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '';

    const res = await fetch('/profile/avatar', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    if (!res.ok) throw new Error('Löschen fehlgeschlagen');

    return res.json();
}
