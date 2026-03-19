<script setup lang="ts">
import { LucideFileUp, LucideTrash2 } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';

defineProps<{
    readonly: boolean;
    clearFiles: () => void;
    openFileDialog: () => void;
}>();
</script>

<template>
    <div class="flex items-center justify-between gap-2">
        <div class="flex gap-2" v-if="!readonly">
            <Button
                size="sm"
                variant="outline"
                @click="openFileDialog"
                type="button"
            >
                <LucideFileUp
                    class="-ms-0.5 size-3.5 opacity-60"
                    aria-hidden="true"
                />
                Dateien hinzufügen
            </Button>
            <Button
                size="sm"
                variant="outline"
                @click="clearFiles"
                type="button"
            >
                <LucideTrash2
                    class="-ms-0.5 size-3.5 opacity-60"
                    aria-hidden="true"
                />
                Alle entfernen
            </Button>
        </div>
    </div>
</template>
