<script lang="ts" setup>
import type { PrimitiveProps } from 'reka-ui';
import { Primitive } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { cn } from '@/lib/utils';
import { useCommand } from '.';

const props = defineProps<PrimitiveProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const { filterState } = useCommand()
const isRender = computed(() => !!filterState.search && filterState.filtered.count === 0,
)
</script>

<template>
  <Primitive
    v-if="isRender"
    :class="cn('py-6 text-center text-sm', props.class)"
    data-slot="command-empty" v-bind="delegatedProps"
  >
    <slot />
  </Primitive>
</template>
