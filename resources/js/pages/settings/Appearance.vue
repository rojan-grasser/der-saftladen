<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Darstellungs Einstellungen',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Darstellungseinstellungen" />

        <h1 class="sr-only">Darstellungs Einstellungen</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Darstellungs Einstellungen"
                    description="Aktualisieren Sie die Darstellungs Einstellungen Ihres Kontos."
                />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
