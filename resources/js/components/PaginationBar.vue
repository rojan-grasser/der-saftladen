<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';

const props = defineProps<{
    currentPage: number;
    perPage: number;
    total: number;
}>();

const emit = defineEmits<{
    (e: 'page-change', page: number): void;
}>();

function onUpdatePage(page: number) {
    emit('page-change', page);
}
</script>

<template>
    <div
        v-if="props.total > props.perPage"
        class="flex items-center justify-end px-2"
    >
        <Pagination
            v-slot="{ page }"
            :default-page="props.currentPage"
            :items-per-page="props.perPage"
            :total="props.total"
            @update:page="onUpdatePage"
        >
            <PaginationContent
                v-slot="{ items }"
                class="flex items-center gap-1"
            >
                <PaginationFirst />
                <PaginationPrevious />

                <template v-for="(item, index) in items">
                    <PaginationItem
                        v-if="item.type === 'page'"
                        :key="index"
                        :value="item.value"
                        as-child
                    >
                        <Button
                            :variant="
                                item.value === page ? 'default' : 'outline'
                            "
                        >
                            {{ item.value }}
                        </Button>
                    </PaginationItem>

                    <PaginationEllipsis
                        v-else
                        :key="`${item.type}-${index}`"
                        :index="index"
                    />
                </template>

                <PaginationNext />
                <PaginationLast />
            </PaginationContent>
        </Pagination>
    </div>
</template>
