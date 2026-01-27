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
                ? 'Ihr Konto wird derzeit überprüft. Bitte warten Sie, bis ein Administrator es aktiviert.'
                : 'Ihr Konto wurde deaktiviert. Sie haben keinen Zugriff mehr auf diese Plattform.'
        "
        :title="isPending ? 'Account Pending' : 'Account Deactivated'"
    >
        <Head title="Account Status" />

        <div class="space-y-6 text-center">
            <p v-if="isPending" class="text-sm text-muted-foreground">
                Bitte schauen Sie sporadisch nach, ob Ihr Zugang genehmigt worden ist. 
                Sollten Sie der Meinung sein, dass dies zu lange dauert, kontaktieren Sie bitte unseren Support.
            </p>
            <p v-else class="text-sm text-destructive">
                Der Zugriff auf dieses Konto wurde eingeschränkt. 
                Bitte kontaktieren Sie einen Administrator, falls Sie der Meinung sind, dass dies ein Fehler ist.
            </p>

            <div class="flex flex-col gap-4">
                <Button as-child variant="outline">
                    <Link
                        as="button"
                        class="w-full"
                        href="/logout"
                        method="post"
                    >
                        Abmelden
                    </Link>
                </Button>
            </div>
        </div>
    </AuthLayout>
</template>
