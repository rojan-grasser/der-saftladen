<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';
</script>

<template>
    <AuthLayout
        title="Bestätigen Sie Ihr Passwort"
        description="Dies ist ein geschützter Bereich der Anwendung. Bitte bestätigen Sie Ihr Passwort, bevor Sie fortfahren."
    >
        <Head title="Bestätigen Sie Ihr Passwort" />

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
        >
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label htmlFor="password">Passwort</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        class="mt-1 block w-full"
                        required
                        autofocus
                    />

                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center">
                    <Button :disabled="processing" class="w-full">
                        <Spinner v-if="processing" />
                        Passwort bestätigen
                    </Button>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>
