<script lang="ts" setup>
import type { AlertDialogTitleProps } from 'reka-ui';
import { AlertDialogTitle } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { cn } from '@/lib/utils';

const props = defineProps<AlertDialogTitleProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")
</script>

<template>
  <AlertDialogTitle
    :class="cn('text-lg font-semibold', props.class)"
    data-slot="alert-dialog-title"
    v-bind="delegatedProps"
  >
    <slot />
  </AlertDialogTitle>
</template>
