import { ref } from 'vue';

const isSupported = typeof window !== 'undefined' && 'serviceWorker' in navigator && 'PushManager' in window;

const isSubscribed = ref(false);
const isLoading = ref(false);

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

async function apiPost(url: string, data: object): Promise<void> {
    await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-XSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify(data),
    });
}

async function apiGet<T>(url: string): Promise<T | null> {
    const response = await fetch(url, {
        headers: { Accept: 'application/json' },
    });
    if (!response.ok) return null;
    return response.json() as Promise<T>;
}

async function urlBase64ToUint8Array(base64String: string): Promise<Uint8Array> {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

async function getVapidPublicKey(): Promise<string | null> {
    const data = await apiGet<{ key: string }>('/push/vapid-key');
    return data?.key ?? null;
}

async function registerServiceWorker(): Promise<ServiceWorkerRegistration | null> {
    if (!isSupported) return null;
    try {
        return await navigator.serviceWorker.register('/sw.js');
    } catch {
        return null;
    }
}

async function checkSubscriptionStatus(): Promise<void> {
    if (!isSupported) return;

    const registration = await navigator.serviceWorker.getRegistration('/sw.js');
    if (!registration) {
        isSubscribed.value = false;
        return;
    }

    const subscription = await registration.pushManager.getSubscription();
    isSubscribed.value = !!subscription;
}

async function subscribe(): Promise<boolean> {
    if (!isSupported) return false;

    isLoading.value = true;
    try {
        const vapidKey = await getVapidPublicKey();
        if (!vapidKey) return false;

        let registration = await navigator.serviceWorker.getRegistration('/sw.js');
        if (!registration) {
            registration = await registerServiceWorker();
        }
        if (!registration) return false;

        const applicationServerKey = await urlBase64ToUint8Array(vapidKey);

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey,
        });

        const subJson = subscription.toJSON();

        await apiPost('/push/subscribe', {
            endpoint: subscription.endpoint,
            public_key: subJson.keys?.p256dh ?? null,
            auth_token: subJson.keys?.auth ?? null,
        });

        isSubscribed.value = true;
        return true;
    } catch {
        isSubscribed.value = false;
        return false;
    } finally {
        isLoading.value = false;
    }
}

async function unsubscribe(): Promise<void> {
    if (!isSupported) return;

    isLoading.value = true;
    try {
        const registration = await navigator.serviceWorker.getRegistration('/sw.js');
        if (!registration) return;

        const subscription = await registration.pushManager.getSubscription();
        if (!subscription) return;

        await apiPost('/push/unsubscribe', { endpoint: subscription.endpoint });
        await subscription.unsubscribe();
        isSubscribed.value = false;
    } finally {
        isLoading.value = false;
    }
}

async function requestPermissionAndSubscribe(): Promise<'granted' | 'denied' | 'unsupported'> {
    if (!isSupported) return 'unsupported';

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') return 'denied';

    await subscribe();
    return 'granted';
}

export function usePushNotifications() {
    return {
        isSupported,
        isSubscribed,
        isLoading,
        checkSubscriptionStatus,
        requestPermissionAndSubscribe,
        unsubscribe,
    };
}
