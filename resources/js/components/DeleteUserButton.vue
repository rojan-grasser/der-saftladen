<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';

import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="destructive">Account Löschen</Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="ProfileController.destroy.form()"
                reset-on-success
                @error="() => passwordInput?.$el?.focus()"
                :options="{
                    preserveScroll: true,
                }"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle
                        >Möchten Sie Ihr Konto wirklich löschen?</DialogTitle
                    >
                    <DialogDescription>
                        Sobald Ihr Konto gelöscht ist, werden auch alle
                        zugehörigen Ressourcen und Daten endgültig gelöscht.
                        Bitte geben Sie Ihr Passwort ein, um zu bestätigen, dass
                        Sie Ihr Konto endgültig löschen möchten.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="password" class="sr-only">Passwort</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        ref="passwordInput"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button
                            variant="secondary"
                            @click="
                                () => {
                                    clearErrors();
                                    reset();
                                }
                            "
                        >
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                    >
                        Account Löschen
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
