<script lang="ts" setup>
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast, Toaster } from 'vue-sonner';

import { AppPageProps } from '@/types';

type Flash = {
    success?: string | null;
    error?: string | null;
};

type FlashPageProps = AppPageProps & {
    flash?: Flash;
};

const page = usePage<FlashPageProps>();

let lastSuccess: string | null = null;
let lastError: string | null = null;

watch(
    () =>
        [
            page.props.flash?.success ?? null,
            page.props.flash?.error ?? null,
        ] as const,
    ([success, error]) => {
        if (success && success !== lastSuccess) {
            toast.success(success);
            lastSuccess = success;
        }

        if (error && error !== lastError) {
            toast.error(error);
            lastError = error;
        }
    },
    { immediate: false },
);
</script>

<template>
    <Toaster position="top-center" rich-colors />
</template>
