<script lang="ts" setup>
import type { ListboxFilterProps } from 'reka-ui';
import { ListboxFilter, useForwardProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { Search } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { useCommand } from '.';

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<ListboxFilterProps & {
  class?: HTMLAttributes["class"]
}>()

const delegatedProps = reactiveOmit(props, "class")

const forwardedProps = useForwardProps(delegatedProps)

const { filterState } = useCommand()
</script>

<template>
  <div
    class="flex h-9 items-center gap-2 border-b px-3"
    data-slot="command-input-wrapper"
  >
    <Search class="size-4 shrink-0 opacity-50" />
    <ListboxFilter
      v-model="filterState.search"
      :class="cn('placeholder:text-muted-foreground flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-hidden disabled:cursor-not-allowed disabled:opacity-50', props.class)"
      auto-focus
      data-slot="command-input"
      v-bind="{ ...forwardedProps, ...$attrs }"
    />
  </div>
</template>
