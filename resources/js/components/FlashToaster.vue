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

watch(
    () =>
        [
            page.props.flash?.success ?? null,
            page.props.flash?.error ?? null,
        ] as const,
    ([success, error]) => {
        if (success) {
            toast.success(success);
        }

        if (error) {
            toast.error(error);
        }
    },
    { immediate: false },
);
</script>

<template>
    <Toaster position="top-center" rich-colors />
</template>
