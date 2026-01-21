<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import { dashboard, login, register } from '@/routes';

defineProps<{
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="Ausbilderportal">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    </Head>

    <div class="bg-[#f8f7f6] dark:bg-[#000000] text-slate-900 dark:text-slate-100 font-sans overflow-x-hidden min-h-screen flex flex-col relative selection:bg-[#ee9d2b] selection:text-white">
        <!-- Background Effects -->
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
            <div class="absolute inset-0 bg-dot-pattern opacity-[0.2] mask-corners"></div>
            <div class="absolute -top-[10%] -left-[10%] w-[500px] h-[500px] bg-[#ee9d2b]/10 blur-[120px] rounded-full opacity-40"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[500px] h-[500px] bg-[#ee9d2b]/10 blur-[120px] rounded-full opacity-40"></div>
            <div class="absolute top-[20%] right-[5%] w-[300px] h-[300px] bg-[#ee9d2b]/5 blur-[100px] rounded-full opacity-20"></div>
        </div>

        <!-- Header -->
        <header class="relative z-10 w-full pt-6 pb-4 px-6 md:px-12">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center size-10 rounded-lg bg-white/5 border border-white/10 text-[#ee9d2b]">
                        <span class="material-symbols-outlined" style="font-size: 24px;">school</span>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-black dark:text-white">IFA 12</span>
                </div>
                <div class="flex items-center gap-4">
                    <template v-if="$page.props.auth.user">
                        <Link :href="dashboard()" class="inline-flex h-9 items-center justify-center rounded-lg bg-[#ee9d2b] hover:bg-[#ffad3d] px-4 text-sm font-semibold text-black transition-colors shadow-[0_0_20px_rgba(238,157,43,0.15)]">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login()" class="hidden md:inline-block text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-black dark:hover:text-white transition-colors">
                            Log in
                        </Link>
                        <Link v-if="canRegister" :href="register()" class="inline-flex h-9 items-center justify-center rounded-lg bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 px-4 text-sm font-semibold text-black dark:text-white transition-colors border border-black/5 dark:border-white/5">
                            Register
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="relative z-10 flex-grow flex flex-col items-center justify-center px-6 py-16 md:py-24 text-center">
            <div class="max-w-5xl mx-auto flex flex-col items-center gap-10">
                <div class="space-y-6">
                    <h1 class="text-6xl md:text-8xl font-extrabold tracking-tight text-black dark:text-white pb-2">
                        Ausbilderportal
                    </h1>
                    <p class="text-lg md:text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">
                        Die zentrale Schnittstelle für Ausbildung, Projektmanagement und Kommunikation. Effizient, übersichtlich und sicher.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 w-full justify-center mt-6">
                    <template v-if="$page.props.auth.user">
                         <Link :href="dashboard()" class="inline-flex h-12 md:h-14 min-w-[180px] cursor-pointer items-center justify-center rounded-lg bg-[#ee9d2b] px-8 text-base font-bold text-black shadow-[0_0_20px_rgba(238,157,43,0.15)] hover:shadow-[0_0_30px_rgba(238,157,43,0.3)] hover:bg-[#ffad3d] transition-all transform hover:-translate-y-0.5">
                            Zum Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="login()" class="inline-flex h-12 md:h-14 min-w-[180px] cursor-pointer items-center justify-center rounded-lg bg-[#ee9d2b] px-8 text-base font-bold text-black shadow-[0_0_20px_rgba(238,157,43,0.15)] hover:shadow-[0_0_30px_rgba(238,157,43,0.3)] hover:bg-[#ffad3d] transition-all transform hover:-translate-y-0.5">
                            Einloggen
                        </Link>
                        <Link v-if="canRegister" :href="register()" class="inline-flex h-12 md:h-14 min-w-[180px] cursor-pointer items-center justify-center rounded-lg bg-transparent border border-black/20 dark:border-white/20 px-8 text-base font-bold text-black dark:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                            Jetzt Registrieren
                        </Link>
                    </template>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-20 w-full text-left">
                    <!-- Feature 1 -->
                    <div class="group relative rounded-xl border border-black/5 dark:border-white/5 bg-white/[0.5] dark:bg-white/[0.02] p-6 hover:bg-black/[0.05] dark:hover:bg-white/[0.05] hover:border-[#ee9d2b]/20 transition-all duration-300">
                        <div class="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 text-slate-500 dark:text-slate-300 group-hover:text-[#ee9d2b] transition-colors">
                            <span class="material-symbols-outlined" style="font-size: 24px;">lock</span>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-black dark:text-white">Authentifizierung</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Sichere Anmeldung mit modernen Standards und Datenschutz.</p>
                    </div>
                    <!-- Feature 2 -->
                    <div class="group relative rounded-xl border border-black/5 dark:border-white/5 bg-white/[0.5] dark:bg-white/[0.02] p-6 hover:bg-black/[0.05] dark:hover:bg-white/[0.05] hover:border-[#ee9d2b]/20 transition-all duration-300">
                        <div class="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 text-slate-500 dark:text-slate-300 group-hover:text-[#ee9d2b] transition-colors">
                            <span class="material-symbols-outlined" style="font-size: 24px;">forum</span>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-black dark:text-white">Forum</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Direkter Austausch und Diskussionen im Team.</p>
                    </div>
                    <!-- Feature 3 -->
                    <div class="group relative rounded-xl border border-black/5 dark:border-white/5 bg-white/[0.5] dark:bg-white/[0.02] p-6 hover:bg-black/[0.05] dark:hover:bg-white/[0.05] hover:border-[#ee9d2b]/20 transition-all duration-300">
                        <div class="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 text-slate-500 dark:text-slate-300 group-hover:text-[#ee9d2b] transition-colors">
                            <span class="material-symbols-outlined" style="font-size: 24px;">calendar_month</span>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-black dark:text-white">Termine</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Wichtige Deadlines und Events immer im Blick behalten.</p>
                    </div>
                    <!-- Feature 4 -->
                    <div class="group relative rounded-xl border border-black/5 dark:border-white/5 bg-white/[0.5] dark:bg-white/[0.02] p-6 hover:bg-black/[0.05] dark:hover:bg-white/[0.05] hover:border-[#ee9d2b]/20 transition-all duration-300">
                        <div class="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 text-slate-500 dark:text-slate-300 group-hover:text-[#ee9d2b] transition-colors">
                            <span class="material-symbols-outlined" style="font-size: 24px;">domain</span>
                        </div>
                        <h3 class="mb-2 text-base font-bold text-black dark:text-white">Berufsbereiche</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Verwaltung verschiedener Fachrichtungen und Ausbildungszweige.</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 w-full border-t border-black/5 dark:border-white/5 bg-white dark:bg-black py-8">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-600">© 2023 IFA 12. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2 text-slate-600 text-xs">
                        <span class="material-symbols-outlined text-[16px]">code</span>
                        <span>Laravel Powered</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.bg-dot-pattern {
    background-image: radial-gradient(#ee9d2b 1.5px, transparent 1.5px);
    background-size: 40px 40px;
}
.mask-corners {
    mask-image: radial-gradient(circle at center, transparent 30%, black 100%);
    -webkit-mask-image: radial-gradient(circle at center, transparent 30%, black 100%);
}
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
}
</style>
