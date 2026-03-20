<script setup lang="ts">
import { ProgressIndicator, ProgressRoot, type ProgressRootProps } from 'reka-ui';
import { computed } from 'vue';

import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<ProgressRootProps & { class?: string }>(),
    {
        modelValue: 0,
    },
);

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;
    return delegated;
});
</script>

<template>
    <ProgressRoot
        v-bind="delegatedProps"
        :class="
            cn(
                'bg-primary/20 relative h-2 w-full overflow-hidden rounded-full',
                props.class,
            )
        "
    >
        <ProgressIndicator
            class="bg-primary h-full w-full flex-1 rounded-full transition-all"
            :style="`transform: translateX(-${100 - (props.modelValue ?? 0)}%)`"
        />
    </ProgressRoot>
</template>
