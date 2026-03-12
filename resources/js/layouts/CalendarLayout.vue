<script setup lang="ts">
import 'vue-sonner/style.css';

import { Link, usePage } from '@inertiajs/vue3';
import { Calendar, LogOut, Menu, User, X } from 'lucide-vue-next';
import { ref } from 'vue';

import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import FlashToaster from '@/components/FlashToaster.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const user = page.props.auth?.user;

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const mobileMenuOpen = ref(false);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <header class="sticky top-0 z-50 border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="flex h-16 items-center justify-between px-4 lg:px-6">
                <div class="flex items-center gap-4">
                    <Link href="/appointments" class="flex items-center gap-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary">
                            <Calendar class="h-5 w-5 text-primary-foreground" />
                        </div>
                        <span class="text-lg font-semibold hidden sm:inline-block">
                            Terminkalender
                        </span>
                    </Link>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="lg:hidden"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" class="relative h-9 w-9 rounded-full">
                                <Avatar class="h-9 w-9">
                                    <AvatarFallback class="bg-primary/10 text-primary">
                                        {{ user ? getInitials(user.name) : '?' }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <div class="flex items-center gap-2 p-2">
                                <Avatar class="h-8 w-8">
                                    <AvatarFallback class="bg-primary/10 text-primary text-xs">
                                        {{ user ? getInitials(user.name) : '?' }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="flex flex-col space-y-0.5">
                                    <p class="text-sm font-medium">{{ user?.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ user?.email }}</p>
                                </div>
                            </div>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/settings/profile" class="flex items-center gap-2">
                                    <User class="h-4 w-4" />
                                    Profil
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    class="flex w-full items-center gap-2 text-destructive"
                                >
                                    <LogOut class="h-4 w-4" />
                                    Abmelden
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>
    </div>
    <FlashToaster />
</template>
