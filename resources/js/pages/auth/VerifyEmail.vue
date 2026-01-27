<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="E-Mail-Bestätigung"
        description="Bitte bestätigen Sie Ihre E-Mail-Adresse, indem Sie auf den Link klicken, den wir Ihnen soeben per E-Mail zugesendet haben."
    >
        <Head title="E-Mail-Bestätigung" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >         
            Ein neuer Bestätigungslink wurde an die E-Mail-Adresse gesendet, die Sie
            bei der Registrierung angegeben haben.
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" variant="secondary">
                <Spinner v-if="processing" />
                Bestätigungs-E-Mail erneut senden
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                Abmelden
            </TextLink>
        </Form>
    </AuthLayout>
</template>
