<script lang="ts" setup>
import { Head, router } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit, update } from '@/routes/notifications';
import { type BreadcrumbItem } from '@/types';

interface NotificationType {
    type: string;
    label: string;
    is_enabled: boolean;
}

const props = defineProps<{
    notificationTypes: NotificationType[];
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Benachrichtigungseinstellungen',
        href: edit().url,
    },
];

const updateNotification = (type: string, isEnabled: boolean) => {
    router.put(update().url, {
        type,
        is_enabled: isEnabled,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Benachrichtigungseinstellungen" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    description="Verwalten Sie, wie Sie Benachrichtigungen erhalten möchten."
                    title="Benachrichtigungseinstellungen"
                />

                <div class="space-y-4">
                    <div
                        v-for="notif in props.notificationTypes"
                        :key="notif.type"
                        class="flex items-center space-x-2"
                    >
                        <Checkbox
                            :id="notif.type"
                            :model-value="notif.is_enabled"
                            @update:model-value="
                                updateNotification(notif.type, Boolean($event))
                            "
                        />
                        <Label
                            :for="notif.type"
                            class="cursor-pointer text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            {{ notif.label }}
                        </Label>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
