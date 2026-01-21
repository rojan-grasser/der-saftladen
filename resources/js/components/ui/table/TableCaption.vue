<script lang="ts" setup>
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <caption
    :class="cn('text-muted-foreground mt-4 text-sm', props.class)"
    data-slot="table-caption"
  >
    <slot />
  </caption>
</template>
