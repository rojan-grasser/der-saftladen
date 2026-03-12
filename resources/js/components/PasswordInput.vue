<script lang="ts" setup>
import { Eye, EyeOff } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';
import { ref } from 'vue';

import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps<{
    defaultValue?: string | number;
    modelValue?: string | number;
    class?: HTMLAttributes['class'];
    errorMessage?: string;
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void;
}>();

const showPassword = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};
</script>

<template>
    <div :class="cn('relative w-full', props.class)">
        <Input
            :model-value="modelValue"
            :type="showPassword ? 'text' : 'password'"
            class="pr-10"
            v-bind="$attrs"
            @update:model-value="emits('update:modelValue', $event)"
        />
        <button
            class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground"
            tabindex="-1"
            type="button"
            @click="togglePassword"
        >
            <Eye v-if="!showPassword" aria-hidden="true" class="h-4 w-4" />
            <EyeOff v-else aria-hidden="true" class="h-4 w-4" />
            <span class="sr-only">
                {{ showPassword ? 'Hide password' : 'Show password' }}
            </span>
        </button>
    </div>
</template>
