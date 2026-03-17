<script lang="ts" setup>
import { Calendar, MessageSquare, Users } from 'lucide-vue-next';

import { Card, CardContent } from '@/components/ui/card';
import type { AdminData } from '@/types/dashboard';

defineProps<{
    admin: AdminData;
}>();
</script>

<template>
    <div class="flex flex-col gap-6">
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <Card>
                <CardContent>
                    <div class="flex items-start gap-4">
                        <div
                            class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30"
                        >
                            <Users
                                class="h-6 w-6 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-sm font-medium wrap-break-word text-muted-foreground"
                            >
                                Benutzer insgesamt
                            </p>
                            <h3 class="text-2xl font-bold wrap-break-word">
                                {{ admin.stats.total_users }}
                            </h3>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent>
                    <div class="flex items-start gap-4">
                        <div
                            class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/30"
                        >
                            <Users
                                class="h-6 w-6 text-orange-600 dark:text-orange-400"
                            />
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-sm font-medium wrap-break-word text-muted-foreground"
                            >
                                Ausstehende Benutzer
                            </p>
                            <h3 class="text-2xl font-bold wrap-break-word">
                                {{ admin.stats.pending_users_count }}
                            </h3>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent>
                    <div class="flex items-start gap-4">
                        <div
                            class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30"
                        >
                            <MessageSquare
                                class="h-6 w-6 text-green-600 dark:text-green-400"
                            />
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-sm font-medium wrap-break-word text-muted-foreground"
                            >
                                Aktuelle Foren-Aktivität
                            </p>
                            <h3 class="text-2xl font-bold wrap-break-word">
                                {{ admin.stats.recent_posts_count }} Beiträge
                            </h3>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent>
                    <div class="flex items-start gap-4">
                        <div
                            class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30"
                        >
                            <Calendar
                                class="h-6 w-6 text-purple-600 dark:text-purple-400"
                            />
                        </div>
                        <div class="min-w-0">
                            <p
                                class="text-sm font-medium wrap-break-word text-muted-foreground"
                            >
                                Anstehende Termine
                            </p>
                            <h3 class="text-2xl font-bold wrap-break-word">
                                {{ admin.stats.upcoming_appointments_count }}
                            </h3>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
