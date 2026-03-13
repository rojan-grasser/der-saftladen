<script setup lang="ts">
import { Inbox } from 'lucide-vue-next';
</script>

<template>
    <div class="flex h-full w-full items-center justify-center">
        <div
            class="flex flex-col items-center gap-3 rounded-xl border border-border/60 bg-background/40 px-8 py-6 text-center shadow-sm backdrop-blur-sm"
        >
            <div
                class="flex h-12 w-12 items-center justify-center rounded-full bg-muted"
            >
                <Inbox class="h-6 w-6 text-muted-foreground" />
            </div>

            <h2 class="text-lg font-medium">Keine Berufsbereiche gefunden</h2>

            <p class="text-sm text-muted-foreground">
                Es sind aktuell keine Einträge vorhanden.
            </p>
        </div>
    </div>
</template>
