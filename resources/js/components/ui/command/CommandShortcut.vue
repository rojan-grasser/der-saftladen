<script lang="ts" setup>
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <span
    :class="cn('text-muted-foreground ml-auto text-xs tracking-widest', props.class)"
    data-slot="command-shortcut"
  >
    <slot />
  </span>
</template>
