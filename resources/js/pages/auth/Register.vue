<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <AuthBase
        title="Ein Konto erstellen"
        description="Geben Sie unten Ihre Daten ein, um Ihr Konto zu erstellen."
    >
        <Head title="Registrieren" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        name="name"
                        placeholder="Voller Name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email-Addresse</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="role">Role</Label>
                    <Select name="role">
                        <SelectTrigger id="role" :tabindex="3" class="w-full">
                            <SelectValue placeholder="Wählen Sie eine Rolle aus" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="teacher"> Lehrer </SelectItem>
                            <SelectItem value="instructor"
                                >Ausbilder
                            </SelectItem>
                            <SelectItem value="admin"> Admin </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.role" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Passwort</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        name="password"
                        placeholder="Passwort"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Passwort bestätigen</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        name="password_confirmation"
                        placeholder="Passwort bestätigen"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="5"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    Account erstellen
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Sie haben bereits ein Konto?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="6"
                    >Anmelden</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
