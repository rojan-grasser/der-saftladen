<script lang="ts" setup>
import type { SeparatorProps } from 'reka-ui';
import { Separator } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { cn } from '@/lib/utils';

const props = defineProps<SeparatorProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")
</script>

<template>
  <Separator
    :class="cn('bg-border -mx-1 h-px', props.class)"
    data-slot="command-separator"
    v-bind="delegatedProps"
  >
    <slot />
  </Separator>
</template>
