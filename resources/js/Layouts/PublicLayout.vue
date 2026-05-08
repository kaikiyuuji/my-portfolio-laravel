<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
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
    { href: '/#sobre', label: t('nav.about') },
    { href: '/#stacks', label: t('nav.stacks') },
    { href: '/#experiencia', label: t('nav.experience') },
    { href: '/#projetos', label: t('nav.projects') },
    { href: '/blog', label: t('nav.blog') },
    { href: '/#contato', label: t('nav.contact') },
]);

function handleScroll() {
    scrolled.value = window.scrollY > 24;
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
    <div class="min-h-screen bg-white text-black transition-colors duration-500 dark:bg-black dark:text-white">
        <!-- Sticky Header -->
        <header
            class="fixed inset-x-0 top-0 z-50 transition-all duration-500"
            :class="scrolled
                ? 'border-b border-black/10 bg-white/80 backdrop-blur-xl dark:border-white/10 dark:bg-black/70'
                : 'border-b border-transparent bg-transparent'"
        >
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <a href="#topo" class="group flex items-center gap-2 font-black tracking-tight">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-black bg-black text-sm font-black text-white transition-transform duration-300 group-hover:rotate-12 dark:border-white dark:bg-white dark:text-black">
                        {{ profileName.charAt(0).toUpperCase() }}
                    </span>
                    <span class="hidden text-lg sm:inline">{{ profileName }}</span>
                </a>

                <nav class="hidden items-center gap-1 md:flex">
                    <a
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="relative rounded-full px-4 py-2 text-sm font-semibold text-black/70 transition-colors hover:text-black dark:text-white/70 dark:hover:text-white"
                    >
                        <span class="relative z-10">{{ item.label }}</span>
                        <span class="absolute inset-0 scale-0 rounded-full bg-black/5 transition-transform duration-300 hover:scale-100 dark:bg-white/10"></span>
                    </a>
                </nav>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="toggleLocale"
                        class="group flex h-10 items-center gap-1.5 rounded-full border border-black/15 bg-white px-3 text-xs font-bold uppercase tracking-wider text-black transition-all hover:scale-105 hover:border-black active:scale-95 dark:border-white/20 dark:bg-black dark:text-white dark:hover:border-white"
                        :aria-label="t('a11y.toggleLocale')"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m6.06 13H21M12 21l3.06-7L18 21M3 12h7m-3.5-2v2c0 3.5-2 5.5-3.5 6" />
                        </svg>
                        <span>{{ locale === 'pt' ? 'PT' : 'EN' }}</span>
                    </button>

                    <button
                        type="button"
                        @click="toggle"
                        class="group relative flex h-10 w-10 items-center justify-center rounded-full border border-black/15 bg-white text-black transition-all hover:scale-110 hover:border-black active:scale-95 dark:border-white/20 dark:bg-black dark:text-white dark:hover:border-white"
                        :aria-label="isDark ? t('a11y.toggleTheme.dark') : t('a11y.toggleTheme.light')"
                    >
                        <svg v-if="isDark" class="h-5 w-5 transition-transform group-hover:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                        </svg>
                        <svg v-else class="h-5 w-5 transition-transform group-hover:-rotate-12" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                        </svg>
                    </button>

                    <button
                        type="button"
                        @click="mobileOpen = !mobileOpen"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-black/15 bg-white text-black md:hidden dark:border-white/20 dark:bg-black dark:text-white"
                        :aria-label="t('a11y.menu')"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path v-if="!mobileOpen" stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div
                v-show="mobileOpen"
                class="border-t border-black/10 bg-white md:hidden dark:border-white/10 dark:bg-black"
            >
                <nav class="mx-auto flex max-w-6xl flex-col px-6 py-4">
                    <a
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        @click="closeMobile"
                        class="border-b border-black/5 py-3 text-sm font-semibold text-black/80 last:border-none dark:border-white/5 dark:text-white/80"
                    >
                        {{ item.label }}
                    </a>
                </nav>
            </div>
        </header>

        <main id="topo" class="pt-20">
            <slot />
        </main>

        <!-- Back to top -->
        <a
            v-show="scrolled"
            href="#topo"
            class="fixed bottom-6 right-6 z-40 flex h-12 w-12 items-center justify-center rounded-full border border-black bg-black text-white shadow-2xl transition-transform hover:-translate-y-1 active:scale-95 dark:border-white dark:bg-white dark:text-black"
            :aria-label="t('a11y.backTop')"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
            </svg>
        </a>
    </div>
</template>
