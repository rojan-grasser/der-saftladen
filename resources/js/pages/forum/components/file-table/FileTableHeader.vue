<script setup lang="ts">
import { Table, TableHead, TableHeader, TableRow } from '@/components/ui/table';
</script>

<template>
    <Table class="w-full table-fixed">
        <colgroup>
            <col class="w-1/4" />
            <col class="w-[15%]" />
            <col class="w-[20%]" />
            <col class="w-[15%]" />
        </colgroup>
        <TableHeader class="bg-background text-xs">
            <TableRow class="bg-muted/50">
                <TableHead class="h-9 py-2 text-left">Name</TableHead>
                <TableHead class="h-9 py-2 text-left">Typ</TableHead>
                <TableHead class="h-9 py-2 text-left">Große</TableHead>
                <TableHead class="h-9 py-2 text-right">Aktionen</TableHead>
            </TableRow>
        </TableHeader>
    </Table>
</template>
