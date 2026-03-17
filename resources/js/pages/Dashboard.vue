<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Clock, MapPin, MessageSquare } from 'lucide-vue-next';

import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate } from '@/lib/utils';
import AdminDashboard from '@/pages/admin/AdminDashboard.vue';
import { dashboard } from '@/routes';
import { show as showTopic } from '@/routes/topics';
import { type BreadcrumbItem } from '@/types';
import { type AdminData, type Appointment, type Topic } from '@/types/dashboard';

defineProps<{
    appointments: Appointment[];
    recentTopics: Topic[];
    admin: AdminData | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-6">
            <!-- Admin Section -->
            <AdminDashboard v-if="admin" :admin="admin" />

            <!-- Dashboard Content Grid -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Upcoming Appointments -->
                <Card>
                    <CardHeader>
                        <CardTitle
                            class="flex items-center text-base font-semibold"
                        >
                            <Clock class="h-4 w-4" />
                            Anstehende Termine
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="appointments.length === 0"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Keine anstehenden Termine gefunden.
                        </div>
                        <ul v-else class="space-y-4">
                            <li
                                v-for="appointment in appointments"
                                :key="appointment.id"
                                class="flex flex-col gap-1 rounded-lg border border-sidebar-border/50 p-3"
                            >
                                <span class="font-medium">
                                    {{ appointment.title }}
                                </span>
                                <div
                                    class="flex items-center gap-4 text-sm text-muted-foreground"
                                >
                                    <span class="flex items-center gap-1">
                                        <Calendar class="h-3 w-3" />
                                        {{ formatDate(appointment.start_time) }}
                                    </span>
                                    <span
                                        v-if="appointment.location"
                                        class="flex items-center gap-1"
                                    >
                                        <MapPin class="h-3 w-3" />
                                        {{ appointment.location }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </CardContent>
                </Card>

                <!-- Recent Forum Topics -->
                <Card>
                    <CardHeader>
                        <CardTitle
                            class="flex items-center text-base font-semibold"
                        >
                            <MessageSquare class="h-4 w-4" />
                            Aktuelle Forum-Diskussionen
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="recentTopics.length === 0"
                            class="py-8 text-center text-muted-foreground"
                        >
                            Noch keine Diskussionen im Forum.
                        </div>
                        <ul v-else class="divide-y divide-sidebar-border/50">
                            <li
                                v-for="topic in recentTopics"
                                :key="topic.id"
                                class="py-3 first:pt-0 last:pb-0"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div>
                                        <Link
                                            :href="
                                                showTopic({
                                                    professionId:
                                                        topic.profession_id,
                                                    topicId: topic.id,
                                                }).url
                                            "
                                        >
                                            <h3
                                                class="cursor-pointer font-medium hover:underline"
                                            >
                                                {{ topic.title }}
                                            </h3>
                                        </Link>
                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            Erstellt von {{ topic.user.name }} •
                                            {{ formatDate(topic.created_at) }}
                                        </p>
                                    </div>
                                    <Badge variant="secondary">
                                        {{ topic.posts_count }} Antworten
                                    </Badge>
                                </div>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
