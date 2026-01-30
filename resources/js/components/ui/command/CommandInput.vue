<script lang="ts" setup>
import type { ListboxFilterProps } from 'reka-ui';
import { ListboxFilter, useForwardProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { computed, watch } from 'vue';
import { reactiveOmit } from '@vueuse/core';
import { Search } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { useCommand } from '.';

defineOptions({
  inheritAttrs: false,
})

type CommandInputProps = ListboxFilterProps & {
  class?: HTMLAttributes["class"]
}

const props = withDefaults(defineProps<CommandInputProps>(), {
  autoFocus: true,
})

const emits = defineEmits<{
  (e: "update:modelValue", value: string): void
}>()

const delegatedProps = reactiveOmit(props, "class", "modelValue", "autoFocus")

const forwardedProps = useForwardProps(delegatedProps)

const { filterState } = useCommand()

const inputValue = computed({
  get: () => (props.modelValue ?? filterState.search),
  set: (value: string) => {
    filterState.search = value
    emits("update:modelValue", value)
  },
})

watch(
  () => props.modelValue,
  (value) => {
    if (value !== undefined) {
      filterState.search = value
    }
  },
)
</script>

<template>
  <div
    class="flex h-9 items-center gap-2 border-b px-3"
    data-slot="command-input-wrapper"
  >
    <Search class="size-4 shrink-0 opacity-50" />
    <ListboxFilter
      v-model="inputValue"
      :class="cn('placeholder:text-muted-foreground flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-hidden disabled:cursor-not-allowed disabled:opacity-50', props.class)"
      :auto-focus="props.autoFocus"
      data-slot="command-input"
      v-bind="{ ...forwardedProps, ...$attrs }"
    />
  </div>
</template>
