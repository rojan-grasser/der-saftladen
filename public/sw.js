self.addEventListener('push', function (event) {
    if (!event.data) return;

    let data;
    try {
        data = event.data.json();
    } catch {
        data = { title: 'Terminerinnerung', body: event.data.text() };
    }

    const options = {
        body: data.body || '',
        icon: '/favicon.ico',
        badge: '/favicon.ico',
        tag: 'appointment-' + (data.appointmentId || Date.now()),
        renotify: true,
        data: {
            appointmentId: data.appointmentId,
            url: '/appointments',
        },
    };

    event.waitUntil(self.registration.showNotification(data.title || 'Terminerinnerung', options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const url = event.notification.data?.url || '/appointments';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (const client of clientList) {
                if (client.url.includes('/appointments') && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        }),
    );
});
