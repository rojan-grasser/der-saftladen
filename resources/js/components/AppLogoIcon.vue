<!-- AppLogoIcon.vue -->
<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        viewBox="0 0 24 24"
        fill="currentColor"
        xmlns="http://www.w3.org/2000/svg"
        :class="className"
        v-bind="$attrs"
        aria-hidden="true"
    >
        <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3Zm0 9.18L5.24 9 12 5.82 18.76 9 12 12.18ZM6 13.09V17c0 1.66 3.13 3 6 3s6-1.34 6-3v-3.91l-6 3.27-6-3.27Z"/>
    </svg>
</template>
