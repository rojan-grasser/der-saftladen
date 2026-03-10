    <script setup lang="ts">
import { ref } from 'vue';

import { Button } from '@/components/ui/button';

const { description } = defineProps<{ description: string }>();

const size = 1000;

const splitDesc: string[] = [];
for (let i = 0; i < description.length; i += size) {
    splitDesc.push(description.slice(i, i + size));
}

const currentShowAllIdx = ref<number>(1);

</script>

<template>
    <div v-if="splitDesc.length === 1">
        {{ splitDesc[0] }}
    </div>
    <div v-if="splitDesc.length > 1">
        <div>
            {{
                Array.from({ length: currentShowAllIdx })
                    .map((_, index) => splitDesc[index])
                    .join('')
            }}
            <span class="text-foreground/50">
                {{ currentShowAllIdx !== splitDesc.length ? '...' : '' }}
            </span>
        </div>

        <Button
            v-if="currentShowAllIdx !== splitDesc.length"
            variant="link"
            class="p-0 text-foreground/70 italic"
            @click="() => (currentShowAllIdx += 1)"
        >
            Mehr anzeigen
        </Button>
    </div>
</template>

<style scoped></style>
