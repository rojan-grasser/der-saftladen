<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useActiveUrl } from '@/composables/useActiveUrl';
import { type NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { urlIsActive } = useActiveUrl();

function isItemActive(item: NavItem) {
    if (item.isActive) {
        return true;
    }
    if (item.children?.length) {
        return (
            urlIsActive(item.href) ||
            item.children.some((child) => urlIsActive(child.href))
        );
    }

    return urlIsActive(item.href);
}

function isSubItemActive(item: NavItem) {
    return item.isActive ?? urlIsActive(item.href);
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isItemActive(item)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
                <SidebarMenuSub v-if="item.children?.length">
                    <SidebarMenuSubItem
                        v-for="child in item.children"
                        :key="child.title"
                    >
                        <SidebarMenuSubButton
                            as-child
                            :is-active="isSubItemActive(child)"
                        >
                            <Link :href="child.href">
                                <span>{{ child.title }}</span>
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                </SidebarMenuSub>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
