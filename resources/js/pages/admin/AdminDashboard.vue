<script lang="ts" setup>
import { Head, Link } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';

import { Card } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import admin from '@/routes/admin';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: admin.dashboard().url,
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Link :href="admin.users().url">
                    <Card
                        class="relative aspect-video items-center justify-center gap-2 overflow-hidden p-0 transition-colors hover:bg-muted/50"
                    >
                        <Users class="h-8 w-8 text-muted-foreground" />
                        <span class="font-medium">User Management</span>
                    </Card>
                </Link>
                <Card
                    class="relative aspect-video items-center justify-center gap-2 overflow-hidden p-0 transition-colors hover:bg-muted/50"
                >
                    <span class="font-medium">Demo Text</span>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
