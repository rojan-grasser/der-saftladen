<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profil Einstellungen',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profil Einstellungen" />

        <h1 class="sr-only">Profil Einstellungen</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Profil Information"
                    description="Aktualisieren Sie Ihren Namen und Ihre E-Mail-Adresse."
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="first_name">Vorname</Label>
                        <Input
                            id="first_name"
                            class="mt-1 block w-full"
                            name="first_name"
                            :default-value="user.first_name"
                            required
                            placeholder="Vorname"
                        />
                        <InputError class="mt-2" :message="errors.first_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="last_name">Nachname</Label>
                        <Input
                            id="last_name"
                            class="mt-1 block w-full"
                            name="last_name"
                            :default-value="user.last_name"
                            required
                            placeholder="Nachname"
                        />
                        <InputError class="mt-2" :message="errors.last_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email-Addresse</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Ihre E-Mail-Adresse ist nicht verifiziert.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Klicken Sie hier, um die Bestätigungs-E-Mail
                                erneut zu senden.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            Ein neuer Bestätigungslink wurde an Ihre
                            E-Mail-Adresse gesendet.
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="company">Unternehmen</Label>
                        <Input
                            id="company"
                            class="mt-1 block w-full"
                            name="company"
                            :default-value="user.company ?? ''"
                            placeholder="Unternehmen (optional)"
                        />
                        <InputError class="mt-2" :message="errors.company" />
                    </div>

                    <div class="flex items-center space-x-2">
                        <input
                            id="email_notifications"
                            hidden
                            name="email_notifications"
                            value="0"
                        />
                        <Checkbox
                            id="email_notifications"
                            :default-value="Boolean(user.email_notifications)"
                            name="email_notifications"
                            value="1"
                        />
                        <Label for="email_notifications"
                            >E-Mail-Benachrichtigungen erhalten</Label
                        >
                        <InputError
                            :message="errors.email_notifications"
                            class="mt-2"
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Speichern</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Gespeichert.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
