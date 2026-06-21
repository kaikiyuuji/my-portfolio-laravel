<script setup>
import { Link } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';

const { isDark, toggle } = useDarkMode();
</script>

<template>
    <div class="auth-shell portfolio-canvas relative min-h-screen overflow-hidden text-[var(--ink)]">
        <div class="star-field star-field--far" aria-hidden="true"></div>

        <header class="relative z-10 flex h-[72px] items-center justify-between border-b border-[var(--line)] px-4 sm:px-6 lg:px-8">
            <Link href="/" class="flex items-center gap-3">
                <span class="blueprint-grid grid h-9 w-9 place-items-center border border-[var(--ink)] font-mono text-[10px] font-bold">KH</span>
                <span>
                    <span class="block text-sm font-bold tracking-[-0.03em]">Portfolio</span>
                    <span class="technical-label mt-1 block">Secure access</span>
                </span>
            </Link>

            <button
                type="button"
                class="grid h-10 w-10 place-items-center border border-[var(--line)] bg-[var(--paper-raised)] transition-colors hover:border-[var(--accent)] hover:text-[var(--accent)]"
                :aria-label="isDark ? 'Ativar tema claro' : 'Ativar tema escuro'"
                @click="toggle"
            >
                <svg v-if="isDark" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                </svg>
                <svg v-else class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                </svg>
            </button>
        </header>

        <main class="relative z-10 grid min-h-[calc(100vh-72px)] lg:grid-cols-12">
            <section class="blueprint-grid hidden border-r border-[var(--ink)] p-10 lg:col-span-5 lg:flex lg:flex-col lg:justify-between xl:p-14">
                <div>
                    <p class="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-white/70">Admin console / 01</p>
                    <h1 class="mt-12 text-6xl font-medium leading-[0.86] tracking-[-0.075em] xl:text-7xl">
                        Conteúdo sob controle.
                    </h1>
                    <p class="mt-8 max-w-md text-base leading-7 text-white/75">
                        Gerencie portfolio, projetos, experiências e publicações em um único espaço.
                    </p>
                </div>
                <div class="border-t border-white/35 pt-5 font-mono text-[10px] uppercase tracking-[0.18em] text-white/70">
                    Laravel · Vue · Inertia
                </div>
            </section>

            <section class="flex items-center justify-center px-4 py-10 sm:px-6 lg:col-span-7 lg:px-12">
                <div class="w-full max-w-md border border-[var(--ink)] bg-[var(--paper-raised)] p-6 shadow-[8px_8px_0_var(--accent)] sm:p-8">
                    <slot />
                </div>
            </section>
        </main>
    </div>
</template>
