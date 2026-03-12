<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ProfessionalArea } from '@/pages/forum/types';
import { index } from '@/routes/topics';

defineProps<{ areas: Array<ProfessionalArea> }>();
</script>

<template>
    <div
        class="grid auto-rows-fr grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
    >
        <div v-for="area in areas" :key="`area-${area.id}`" class="h-full">
            <Link :href="index({ areaId: area.id }).url">
                <Card class="h-full">
                    <CardHeader class="gap-3">
                        <CardTitle class="flex justify-between">
                            {{ area.name }}
                        </CardTitle>
                        <CardDescription>
                            {{ area.description }}
                        </CardDescription>
                    </CardHeader>
                </Card>
            </Link>
        </div>
    </div>
</template>
