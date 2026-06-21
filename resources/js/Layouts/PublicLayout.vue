<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDarkMode } from '@/Composables/useDarkMode';
import { toggleLocale } from '@/i18n';

defineProps({
    profileName: {
        type: String,
        default: 'Portfolio',
    },
});

const { t, locale } = useI18n();
const { isDark, toggle } = useDarkMode();
const scrolled = ref(false);
const mobileOpen = ref(false);

const navItems = computed(() => [
    { href: '/#sobre', label: t('nav.about'), index: '01' },
    { href: '/#stacks', label: t('nav.stacks'), index: '02' },
    { href: '/#experiencia', label: t('nav.experience'), index: '03' },
    { href: '/#projetos', label: t('nav.projects'), index: '04' },
    { href: '/#contato', label: t('nav.contact'), index: '05' },
    { href: '/blog', label: t('nav.blog'), index: '06' },
]);

function handleScroll() {
    scrolled.value = window.scrollY > 20;
}

function closeMobile() {
    mobileOpen.value = false;
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="portfolio-canvas min-h-screen text-[var(--ink)] transition-colors duration-500">
        <header
            class="fixed inset-x-0 top-0 z-50 border-b transition-all duration-300"
            :class="scrolled
                ? 'border-technical bg-[color:var(--paper)]/95 shadow-[0_8px_30px_rgba(0,0,0,0.05)] backdrop-blur-xl'
                : 'border-transparent bg-[color:var(--paper)]/80 backdrop-blur-md'"
        >
            <div class="mx-auto grid h-[72px] max-w-7xl grid-cols-[1fr_auto] items-center px-4 sm:px-6 lg:grid-cols-[240px_1fr_auto] lg:px-8">
                <a href="/#topo" class="group flex min-w-0 items-center gap-3">
                    <span class="blueprint-grid grid h-9 w-9 shrink-0 place-items-center border border-[var(--ink)] font-mono text-xs font-bold transition-transform duration-300 group-hover:-rotate-3">
                        {{ profileName.slice(0, 2).toUpperCase() }}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold leading-none tracking-[-0.03em]">{{ profileName }}</span>
                        <span class="technical-label mt-1 block">Portfolio / 2026</span>
                    </span>
                </a>

                <nav class="hidden h-full items-center justify-center lg:flex">
                    <a
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="group flex h-full items-center gap-1.5 border-l border-transparent px-3 font-mono text-[10px] font-semibold uppercase tracking-[0.1em] text-[var(--muted)] transition-colors last:border-r hover:border-[var(--line)] hover:bg-[var(--paper-raised)] hover:text-[var(--ink)]"
                    >
                        <span class="text-[var(--accent)]">{{ item.index }}</span>
                        <span>{{ item.label }}</span>
                    </a>
                </nav>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        @click="toggleLocale"
                        class="grid h-9 min-w-12 place-items-center border border-[var(--line)] bg-[var(--paper-raised)] px-2 font-mono text-[10px] font-bold uppercase tracking-wider transition-all hover:border-[var(--ink)] hover:text-[var(--accent)]"
                        :aria-label="t('a11y.toggleLocale')"
                    >
                        {{ locale === 'pt' ? 'PT' : 'EN' }}
                    </button>

                    <button
                        type="button"
                        @click="toggle"
                        class="grid h-9 w-9 place-items-center border border-[var(--line)] bg-[var(--paper-raised)] transition-all hover:border-[var(--ink)] hover:text-[var(--accent)]"
                        :aria-label="isDark ? t('a11y.toggleTheme.dark') : t('a11y.toggleTheme.light')"
                    >
                        <svg v-if="isDark" class="h-4 w-4 transition-transform duration-300 hover:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                        </svg>
                    </button>

                    <button
                        type="button"
                        @click="mobileOpen = !mobileOpen"
                        class="grid h-9 w-9 place-items-center border border-[var(--line)] bg-[var(--paper-raised)] lg:hidden"
                        :aria-expanded="mobileOpen"
                        :aria-label="t('a11y.menu')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path v-if="!mobileOpen" d="M4 7h16M4 12h16M4 17h16" />
                            <path v-else d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div
                v-show="mobileOpen"
                class="border-t border-[var(--line)] bg-[var(--paper)] lg:hidden"
            >
                <nav class="mx-auto grid max-w-7xl grid-cols-2 px-4 py-3 sm:px-6">
                    <a
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        @click="closeMobile"
                        class="flex items-center gap-2 border-b border-[var(--line)] py-3 font-mono text-[11px] font-semibold uppercase tracking-wider odd:border-r odd:pr-3 even:pl-3"
                    >
                        <span class="text-[var(--accent)]">{{ item.index }}</span>
                        {{ item.label }}
                    </a>
                </nav>
            </div>
        </header>

        <main id="topo" class="pt-[72px]">
            <slot />
        </main>

        <a
            v-show="scrolled"
            href="#topo"
            class="fixed bottom-4 right-4 z-40 grid h-11 w-11 place-items-center border border-[var(--ink)] bg-[var(--ink)] text-[var(--paper)] shadow-[4px_4px_0_var(--accent)] transition-transform hover:-translate-y-1 sm:bottom-6 sm:right-6"
            :aria-label="t('a11y.backTop')"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="m6 14 6-6 6 6" />
            </svg>
        </a>
    </div>
</template>
