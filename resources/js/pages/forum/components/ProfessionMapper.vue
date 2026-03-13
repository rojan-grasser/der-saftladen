<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Profession } from '@/pages/forum/types';
import { index } from '@/routes/topics';

defineProps<{ professions: Array<Profession> }>();
</script>

<template>
    <div
        class="grid auto-rows-fr grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
    >
        <div
            v-for="profession in professions"
            :key="`profession-${profession.id}`"
            class="h-full"
        >
            <Link :href="index({ professionId: profession.id }).url">
                <Card class="h-full">
                    <CardHeader class="gap-3">
                        <CardTitle class="flex justify-between">
                            {{ profession.name }}
                        </CardTitle>
                        <CardDescription>
                            {{ profession.description }}
                        </CardDescription>
                    </CardHeader>
                </Card>
            </Link>
        </div>
    </div>
</template>
