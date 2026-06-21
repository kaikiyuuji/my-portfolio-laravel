<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';

const page = usePage();
const { isDark, toggle } = useDarkMode();
const mobileOpen = ref(false);

const navItems = computed(() => [
    { label: 'Dashboard', index: '01', route: 'admin.dashboard', pattern: 'admin.dashboard' },
    { label: 'Portfolio', index: '02', route: 'admin.profile.edit', pattern: 'admin.profile.*' },
    { label: 'Tecnologias', index: '03', route: 'admin.stacks.index', pattern: 'admin.stacks.*' },
    { label: 'Experiências', index: '04', route: 'admin.experiences.index', pattern: 'admin.experiences.*' },
    { label: 'Projetos', index: '05', route: 'admin.projects.index', pattern: 'admin.projects.*' },
    { label: 'Redes sociais', index: '06', route: 'admin.social-links.index', pattern: 'admin.social-links.*' },
    { label: 'Blog', index: '07', route: 'admin.posts.index', pattern: 'admin.posts.*' },
]);

function closeMobile() {
    mobileOpen.value = false;
}
</script>

<template>
    <div class="admin-shell portfolio-canvas min-h-screen text-[var(--ink)]">
        <aside class="fixed inset-y-0 left-0 z-50 hidden w-72 border-r border-[var(--line)] bg-[var(--paper-raised)] lg:flex lg:flex-col">
            <div class="flex h-[88px] items-center gap-3 border-b border-[var(--line)] px-6">
                <Link :href="route('admin.dashboard')" class="blueprint-grid grid h-11 w-11 place-items-center border border-[var(--ink)] font-mono text-xs font-bold">
                    AD
                </Link>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold tracking-[-0.03em]">Portfolio Admin</p>
                    <p class="technical-label mt-1">Control system / 2026</p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto p-4">
                <p class="technical-label mb-3 px-3">Navigation index</p>
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="group flex items-center gap-3 border-b border-[var(--line)] px-3 py-3.5 font-mono text-[10px] font-semibold uppercase tracking-[0.1em] transition-all"
                    :class="route().current(item.pattern)
                        ? 'border-l-4 border-l-[var(--accent)] bg-[var(--paper)] text-[var(--ink)]'
                        : 'text-[var(--muted)] hover:bg-[var(--paper)] hover:text-[var(--ink)]'"
                >
                    <span class="text-[var(--accent)]">{{ item.index }}</span>
                    <span>{{ item.label }}</span>
                    <span class="ml-auto transition-transform group-hover:translate-x-1">→</span>
                </Link>
            </nav>

            <div class="border-t border-[var(--line)] p-4">
                <div class="mb-3 border border-[var(--line)] p-4">
                    <div class="flex items-center gap-3">
                        <span class="blueprint-grid grid h-9 w-9 shrink-0 place-items-center border border-[var(--ink)] font-mono text-xs font-bold">
                            {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ page.props.auth.user.name }}</p>
                            <p class="truncate font-mono text-[9px] text-[var(--muted)]">{{ page.props.auth.user.email }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <Link
                        :href="route('profile.edit')"
                        class="admin-quiet-button"
                    >
                        Conta
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="admin-quiet-button text-red-600"
                    >
                        Sair
                    </Link>
                </div>
            </div>
        </aside>

        <div class="min-h-screen lg:pl-72">
            <header class="sticky top-0 z-40 border-b border-[var(--line)] bg-[color:var(--paper)]/95 backdrop-blur-xl">
                <div class="flex h-[72px] items-center justify-between px-4 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        class="grid h-10 w-10 place-items-center border border-[var(--line)] bg-[var(--paper-raised)] lg:hidden"
                        :aria-expanded="mobileOpen"
                        aria-label="Abrir navegação"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path v-if="!mobileOpen" d="M4 7h16M4 12h16M4 17h16" />
                            <path v-else d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>

                    <div class="hidden lg:block">
                        <p class="technical-label">Authenticated workspace</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a
                            href="/"
                            target="_blank"
                            rel="noopener"
                            class="admin-quiet-button hidden sm:inline-flex"
                        >
                            Ver site ↗
                        </a>
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
                    </div>
                </div>

                <div v-show="mobileOpen" class="border-t border-[var(--line)] bg-[var(--paper-raised)] lg:hidden">
                    <nav class="grid grid-cols-2 p-3">
                        <Link
                            v-for="item in navItems"
                            :key="item.route"
                            :href="route(item.route)"
                            class="flex items-center gap-2 border-b border-r border-[var(--line)] px-3 py-3 font-mono text-[9px] font-semibold uppercase tracking-wider"
                            :class="{ 'bg-[var(--accent)] text-white': route().current(item.pattern) }"
                            @click="closeMobile"
                        >
                            <span>{{ item.index }}</span>
                            {{ item.label }}
                        </Link>
                    </nav>
                    <div class="border-t border-[var(--line)] p-3">
                        <p class="truncate px-2 pb-3 font-mono text-[9px] uppercase tracking-wider text-[var(--muted)]">
                            {{ page.props.auth.user.email }}
                        </p>
                        <div class="grid grid-cols-3 gap-2">
                            <Link :href="route('profile.edit')" class="admin-quiet-button" @click="closeMobile">Conta</Link>
                            <a href="/" target="_blank" rel="noopener" class="admin-quiet-button">Site ↗</a>
                            <Link :href="route('logout')" method="post" as="button" class="admin-quiet-button text-red-600">Sair</Link>
                        </div>
                    </div>
                </div>
            </header>

            <header v-if="$slots.header" class="admin-page-header border-b border-[var(--line)] bg-[var(--paper-raised)]">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="admin-content relative py-8 sm:py-10">
                <slot />
            </main>
        </div>
    </div>
</template>
