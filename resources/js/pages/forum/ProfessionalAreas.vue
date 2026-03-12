<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import AreaMapper from '@/pages/forum/components/AreaMapper.vue';
import NoAreasFound from '@/pages/forum/components/NoAreasFound.vue';
import { ProfessionalArea } from '@/pages/forum/types';
import { areas as areasShow } from '@/routes/forum';
import { BreadcrumbItem } from '@/types';

defineProps<{ areas: Array<ProfessionalArea> }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berufsbereiche',
        href: areasShow().url,
    },
];
</script>

<template>
    <Head title="Berufsbereiche" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="areas.length" class="px-10 pt-4">
            <AreaMapper :areas="areas" />
        </div>

        <div
            v-if="!areas.length"
            class="flex min-h-[calc(100vh-12rem)] px-10 pt-4"
        >
            <NoAreasFound />
        </div>
    </AppLayout>
</template>

<style scoped></style>
