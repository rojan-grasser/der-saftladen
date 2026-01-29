<script lang="ts" setup>
import { Link } from '@inertiajs/vue3';

import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Topic } from '@/pages/forum/types';
import { show } from '@/routes/topics';
import { PaginatedResponse } from '@/types';

interface Props {
    topics: PaginatedResponse<Topic>;
}

defineProps<Props>();
</script>

<template>
    <div v-for="topic in topics.data" :key="`topic-${topic.id}`" class="p-5">
        <Link :href="show.url({ topic: topic.id })" class="block">
            <Card
                class="cursor-pointer gap-2 overflow-hidden transition-colors hover:bg-muted/70"
            >
                <CardHeader>
                    <CardTitle>{{ topic.title }}</CardTitle>
                    <CardDescription>
                        <!-- Todo: Make dynamic by using the content of the initial post -->
                        Enter your email below to login to your account
                    </CardDescription>
                </CardHeader>
            </Card>
        </Link>
    </div>
</template>
