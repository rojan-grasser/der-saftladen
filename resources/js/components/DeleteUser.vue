<script setup lang="ts">
import DeleteUserButton from '@/components/DeleteUserButton.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall
            title="Account Löschen"
            description="Löschen Sie Ihr Konto und alle zugehörigen Ressourcen"
        />
        <div
            class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
        >
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Warnung</p>
                <p class="text-sm">
                    Bitte gehen Sie mit Vorsicht vor, dies kann nicht rückgängig
                    gemacht werden.
                </p>
            </div>
            <DeleteUserButton />
        </div>
    </div>
</template>
