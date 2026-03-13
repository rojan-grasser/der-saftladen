<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import NoProfessionsFound from '@/pages/forum/components/NoProfessionsFound.vue';
import ProfessionMapper from '@/pages/forum/components/ProfessionMapper.vue';
import { Profession } from '@/pages/forum/types';
import { professions as professionsShow } from '@/routes/forum';
import { BreadcrumbItem } from '@/types';

defineProps<{ professions: Array<Profession> }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berufsbereiche',
        href: professionsShow().url,
    },
];
</script>

<template>
    <Head title="Berufsbereiche" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="professions.length" class="px-10 pt-4">
            <ProfessionMapper :professions="professions" />
        </div>

        <div
            v-if="!professions.length"
            class="flex min-h-[calc(100vh-12rem)] px-10 pt-4"
        >
            <NoProfessionsFound />
        </div>
    </AppLayout>
</template>

<style scoped></style>
