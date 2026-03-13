<script lang="ts" setup>
import { Link, router } from '@inertiajs/vue3';

import PaginationBar from '@/components/PaginationBar.vue';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { MinimalTopic, Profession } from '@/pages/forum/types';
import topicsApi, { show } from '@/routes/topics';
import { PaginatedResponse } from '@/types';

interface Props {
    topics: PaginatedResponse<MinimalTopic>;
    profession: Profession;
}

const { topics, profession } = defineProps<Props>();

const handlePageChange = (page: number) => {
    router.get(
        topicsApi.index({ professionId: profession.id }).url,
        {
            page: page,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};
</script>

<template>
    <div v-for="topic in topics.data" :key="`topic-${topic.id}`">
        <Link
            :href="show.url({ topicId: topic.id, professionId: profession.id })"
            class="block"
        >
            <Card
                class="cursor-pointer gap-2 overflow-hidden transition-colors hover:bg-muted/70"
            >
                <CardHeader>
                    <CardTitle>
                        <div class="flex flex-col justify-between gap-2">
                            <span>{{ topic.title }}</span>
                            <span class="text-xs italic">
                                {{ topic.user.first_name }} {{ topic.user.last_name }}
                                <span
                                    class="text-sm font-normal text-foreground/70"
                                >
                                    {{ topic.user.email }}
                                </span>
                            </span>
                        </div>
                    </CardTitle>
                    <CardDescription class="truncate">
                        {{ topic.description }}
                    </CardDescription>
                </CardHeader>
            </Card>
        </Link>
    </div>
    <PaginationBar
        :current-page="topics.current_page"
        :per-page="topics.per_page"
        :total="topics.total"
        @page-change="handlePageChange"
    />
</template>
