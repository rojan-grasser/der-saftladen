<script lang="ts" setup>
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import debounce from 'debounce';
import { ref, watch } from 'vue';

import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import CreateTopic from '@/pages/forum/components/CreateTopic.vue';
import TopicsMapper from '@/pages/forum/components/TopicsMapper.vue';
import { MinimalTopic, ProfessionalArea } from '@/pages/forum/types';
import { areas } from '@/routes/forum';
import { index as topicIndex } from '@/routes/topics';
import { BreadcrumbItem, PaginatedResponse } from '@/types';

type Props = {
    topics: PaginatedResponse<MinimalTopic>;
    area: ProfessionalArea;
    query?: string;
};

const { area, topics, query: initialQuery } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berufsbereiche',
        href: areas().url,
    },
    {
        title: area.name,
        href: topicIndex({ areaId: area.id }).url,
    },
];

const search = ref(initialQuery ?? '');

const debouncedSearch = debounce((value: string) => {
    router.get(
        topicIndex({ areaId: area.id }, { query: value ? { query: value } : undefined }).url,
        {},
        { preserveState: true, replace: true },
    );
}, 300);

watch(search, debouncedSearch);
</script>

<template>
    <Head title="Forum Themen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-3 p-5">
            <div class="flex justify-between gap-2">
                <CreateTopic :area-id="area.id" />
                <div class="w-96">
                    <Input v-model="search" placeholder="Suche" />
                </div>
            </div>
            <TopicsMapper :topics="topics" :area="area" />
        </div>
    </AppLayout>
</template>
