<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

import AppLogo from '@/components/AppLogo.vue';
import { dashboard, login, register } from '@/routes';

defineProps<{
    canRegister?: boolean;
}>();

const featuresVisible = ref(false);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    featuresVisible.value = true;
                }
            });
        },
        { threshold: 0.2 },
    );

    const target = document.getElementById('features-section');
    if (target) observer.observe(target);
});
</script>

<template>
    <Head title="Ausbilderportal">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            crossorigin=""
            href="https://fonts.gstatic.com"
            rel="preconnect"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        class="relative min-h-screen overflow-x-auto bg-background font-sans text-foreground selection:bg-primary selection:text-primary-foreground"
    >
        <!-- Background Effects -->
        <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
            <div
                class="bg-dot-pattern mask-corners absolute inset-0 opacity-[0.2]"
            ></div>
            <div
                class="absolute -top-[10%] -left-[10%] h-[500px] w-[500px] rounded-full bg-primary/10 opacity-40 blur-[120px]"
            ></div>
            <div
                class="absolute -right-[10%] -bottom-[10%] h-[500px] w-[500px] rounded-full bg-primary/10 opacity-40 blur-[120px]"
            ></div>
            <div
                class="absolute top-[20%] right-[5%] h-[300px] w-[300px] rounded-full bg-primary/5 opacity-20 blur-[100px]"
            ></div>
        </div>

        <!-- Header -->
        <header
            class="relative top-0 left-0 z-50 w-full px-4 pt-6 pb-4 backdrop-blur-sm transition-all duration-300 sm:px-6 md:px-12"
        >
            <div
                class="mx-auto flex max-w-7xl min-w-[320px] items-center justify-between"
            >
                <div class="flex items-center gap-3">
                    <AppLogo></AppLogo>
                </div>
                <div class="flex items-center gap-4">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="dashboard()"
                            class="inline-flex h-9 items-center justify-center rounded-lg bg-primary px-4 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-block text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Anmelden
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex h-9 items-center justify-center rounded-lg border border-border bg-secondary px-4 text-sm font-semibold text-secondary-foreground transition-colors hover:bg-secondary/80"
                        >
                            Registrieren
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="relative z-10 flex min-w-[320px] flex-col">
            <!-- Hero Section -->
            <section
                class="flex flex-col items-center justify-center px-4 pt-40 text-center sm:px-6"
            >
                <div
                    class="mx-auto flex max-w-5xl flex-col items-center gap-10"
                >
                    <div class="space-y-6">
                        <h1
                            class="text-4xl leading-tight font-extrabold tracking-tight break-words text-foreground sm:text-5xl md:text-7xl lg:text-8xl"
                        >
                            Ausbilderportal
                        </h1>
                        <p
                            class="mx-auto max-w-xl text-base leading-relaxed text-muted-foreground sm:max-w-2xl sm:text-lg md:text-xl"
                        >
                            Die zentrale Schnittstelle für Ausbildung,
                            Projektmanagement und Kommunikation. Effizient,
                            übersichtlich und sicher.
                        </p>
                    </div>

                    <div
                        class="mt-6 flex w-full max-w-sm flex-col justify-center gap-4 sm:max-w-none sm:flex-row"
                    >
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="dashboard()"
                                class="inline-flex h-12 min-w-[180px] transform cursor-pointer items-center justify-center rounded-lg bg-primary px-8 text-base font-bold text-primary-foreground shadow-lg transition-all hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-xl md:h-14"
                            >
                                Zum Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="inline-flex h-12 min-w-[180px] transform cursor-pointer items-center justify-center rounded-lg bg-primary px-8 text-base font-bold text-primary-foreground shadow-lg transition-all hover:-translate-y-0.5 hover:bg-primary/90 hover:shadow-xl md:h-14"
                            >
                                Einloggen
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="inline-flex h-12 min-w-[180px] cursor-pointer items-center justify-center rounded-lg border border-input bg-transparent px-8 text-base font-bold text-foreground transition-colors hover:bg-accent hover:text-accent-foreground md:h-14"
                            >
                                Jetzt Registrieren
                            </Link>
                        </template>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <!--section id="features-section" class="min-h-[80vh] flex flex-col items-center justify-center px-6 py-24 bg-card/30 transition-all duration-1000 transform" :class="{'opacity-0 translate-y-20': !featuresVisible, 'opacity-100 translate-y-0': featuresVisible}">
                <div class="max-w-7xl mx-auto w-full">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">Alles was du brauchst</h2>
                        <p class="text-muted-foreground max-w-2xl mx-auto">Unsere Plattform bietet alle Werkzeuge für eine erfolgreiche Ausbildung.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 w-full text-left">
                        < Feature 1
                        <div class="group relative rounded-xl border border-border bg-card p-6 hover:bg-accent/50 hover:border-primary/50 transition-all duration-300">
                            <div class="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-secondary text-muted-foreground group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined" style="font-size: 24px;">lock</span>
                            </div>
                            <h3 class="mb-2 text-base font-bold text-foreground">Authentifizierung</h3>
                            <p class="text-xs text-muted-foreground leading-relaxed">Sichere Anmeldung mit modernen Standards und Datenschutz.</p>
                        </div>
                        Feature 2
                        <div class="group relative rounded-xl border border-border bg-card p-6 hover:bg-accent/50 hover:border-primary/50 transition-all duration-300">
                            <div class="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-secondary text-muted-foreground group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined" style="font-size: 24px;">forum</span>
                            </div>
                            <h3 class="mb-2 text-base font-bold text-foreground">Forum</h3>
                            <p class="text-xs text-muted-foreground leading-relaxed">Direkter Austausch und Diskussionen im Team.</p>
                        </div>
                        Feature 3
                        <div class="group relative rounded-xl border border-border bg-card p-6 hover:bg-accent/50 hover:border-primary/50 transition-all duration-300">
                            <div class="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-secondary text-muted-foreground group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined" style="font-size: 24px;">calendar_month</span>
                            </div>
                            <h3 class="mb-2 text-base font-bold text-foreground">Termine</h3>
                            <p class="text-xs text-muted-foreground leading-relaxed">Wichtige Deadlines und Events immer im Blick behalten.</p>
                        </div>
                        Feature 4
                        <div class="group relative rounded-xl border border-border bg-card p-6 hover:bg-accent/50 hover:border-primary/50 transition-all duration-300">
                            <div class="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-secondary text-muted-foreground group-hover:text-primary transition-colors">
                                <span class="material-symbols-outlined" style="font-size: 24px;">domain</span>
                            </div>
                            <h3 class="mb-2 text-base font-bold text-foreground">Berufsbereiche</h3>
                            <p class="text-xs text-muted-foreground leading-relaxed">Verwaltung verschiedener Fachrichtungen und Ausbildungszweige.</p>
                        </div>
                    </div>
                </div>
            </section-->
        </main>

        <!-- Footer
        <footer class="relative z-10 w-full border-t border-border bg-background py-8">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs text-muted-foreground">© 2026 IFA 12. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2 text-muted-foreground text-xs">
                        <span class="material-symbols-outlined text-[16px]">code</span>
                        <span>Laravel Powered</span>
                    </div>
                </div>
            </div>
        </footer-->
    </div>
</template>

<style>
.bg-dot-pattern {
    background-image: radial-gradient(var(--primary) 1.5px, transparent 1.5px);
    background-size: 40px 40px;
}
.mask-corners {
    mask-image: radial-gradient(circle at center, transparent 30%, black 100%);
    -webkit-mask-image: radial-gradient(
        circle at center,
        transparent 30%,
        black 100%
    );
}
.material-symbols-outlined {
    font-variation-settings:
        'FILL' 0,
        'wght' 300,
        'GRAD' 0,
        'opsz' 24;
}
</style>
