<script setup lang="ts">
import { computed, ref, toRef, watch } from 'vue';

import MarkdownContent from '@/components/MarkdownContent.vue';
import { Button } from '@/components/ui/button';

const props = defineProps<{ description: string }>();

const description = toRef(props, 'description');

const size = 1000;

const splitDesc = computed<string[]>(() => {
    const parts: string[] = [];
    for (let i = 0; i < description.value.length; i += size) {
        parts.push(description.value.slice(i, i + size));
    }
    return parts;
});

const currentShowAllIdx = ref<number>(1);

watch(splitDesc, () => {
    currentShowAllIdx.value = 1;
});
</script>

<template>
    <div v-if="splitDesc.length === 1">
        <MarkdownContent :content="splitDesc[0]" />
    </div>
    <div v-if="splitDesc.length > 1">
        <MarkdownContent
            :content="Array.from({ length: currentShowAllIdx })
                .map((_, index) => splitDesc[index])
                .join('')"
        />
        <span v-if="currentShowAllIdx !== splitDesc.length" class="text-foreground/50">
            ...
        </span>

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
