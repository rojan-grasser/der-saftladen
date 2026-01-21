<script lang="ts" setup>
import { Head, Link, usePage } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';

const page = usePage();
const user = page.props.auth.user;

const isPending = user.status === 'pending';
</script>

<template>
    <AuthLayout
        :description="
            isPending
                ? 'Your account is currently under review. Please wait for an administrator to activate it.'
                : 'Your account has been deactivated. You no longer have access to this platform.'
        "
        :title="isPending ? 'Account Pending' : 'Account Deactivated'"
    >
        <Head title="Account Status" />

        <div class="space-y-6 text-center">
            <p v-if="isPending" class="text-sm text-muted-foreground">
                We'll notify you via email once your account is ready. If you
                believe this is taking too long, please contact support.
            </p>
            <p v-else class="text-sm text-destructive">
                Access to this account has been restricted. Please contact an
                administrator if you believe this is an error.
            </p>

            <div class="flex flex-col gap-4">
                <Button as-child variant="outline">
                    <Link
                        as="button"
                        class="w-full"
                        href="/logout"
                        method="post"
                    >
                        Log out
                    </Link>
                </Button>
            </div>
        </div>
    </AuthLayout>
</template>
