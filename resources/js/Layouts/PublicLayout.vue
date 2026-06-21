<script setup>
import { useArcadeSound } from '@/Composables/useArcadeSound';
import { useDarkMode } from '@/Composables/useDarkMode';
import { toggleLocale } from '@/i18n';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineProps({
    profileName: {
        type: String,
        default: 'Portfolio',
    },
});

const { t, locale } = useI18n();
const { isDark, toggle } = useDarkMode();
const publicRoot = ref(null);
const { soundEnabled, toggleSound } = useArcadeSound(publicRoot, {
    storageKey: 'portfolio-arcade-sound',
});
const scrolled = ref(false);
const mobileOpen = ref(false);

const navItems = computed(() => [
    { href: '/#sobre', label: t('nav.about'), code: '01' },
    { href: '/#stacks', label: t('nav.stacks'), code: '02' },
    { href: '/#experiencia', label: t('nav.experience'), code: '03' },
    { href: '/#projetos', label: t('nav.projects'), code: '04' },
    { href: '/#contato', label: t('nav.contact'), code: '05' },
    { href: '/blog', label: t('nav.blog'), code: '06' },
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
    <div
        ref="publicRoot"
        class="pixel-public min-h-screen"
        :class="{ 'pixel-public--scrolled': scrolled }"
    >
        <div class="pixel-public__scanlines" aria-hidden="true"></div>
        <div class="pixel-public__ambient" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <header class="pixel-public-nav">
            <div class="pixel-public-nav__inner">
                <a href="/#topo" class="pixel-public-brand" aria-label="Ir para o início">
                    <span class="pixel-public-brand__sprite" aria-hidden="true">
                        <img src="/favicon.ico" alt="" width="28" height="28" />
                    </span>
                    <span class="pixel-public-brand__copy">
                        <strong>{{ profileName }}</strong>
                        <small>PORTFOLIO.EXE</small>
                    </span>
                </a>

                <nav class="pixel-public-links" aria-label="Navegação principal">
                    <a
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                    >
                        <small>{{ item.code }}</small>
                        <span>{{ item.label }}</span>
                    </a>
                </nav>

                <div class="pixel-public-actions">
                    <button
                        type="button"
                        class="pixel-public-control pixel-public-control--locale"
                        :aria-label="t('a11y.toggleLocale')"
                        @click="toggleLocale"
                    >
                        <span>文</span>
                        <b>{{ locale === 'pt' ? 'PT' : 'EN' }}</b>
                    </button>

                    <button
                        type="button"
                        class="pixel-public-control"
                        :aria-label="isDark ? t('a11y.toggleTheme.dark') : t('a11y.toggleTheme.light')"
                        :title="isDark ? 'Modo claro' : 'Modo escuro'"
                        @click="toggle"
                    >
                        <span aria-hidden="true">{{ isDark ? '☀' : '◐' }}</span>
                    </button>

                    <button
                        type="button"
                        class="pixel-public-control"
                        data-sound-toggle
                        :aria-pressed="soundEnabled"
                        :title="soundEnabled ? 'Desativar sons' : 'Ativar sons'"
                        @click.stop="toggleSound"
                    >
                        <span aria-hidden="true">{{ soundEnabled ? '♪' : '×' }}</span>
                    </button>

                    <button
                        type="button"
                        class="pixel-public-control pixel-public-menu-button"
                        :aria-expanded="mobileOpen"
                        aria-controls="public-mobile-menu"
                        :aria-label="t('a11y.menu')"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <span aria-hidden="true">{{ mobileOpen ? '×' : '☰' }}</span>
                    </button>
                </div>
            </div>

            <div
                id="public-mobile-menu"
                class="pixel-public-mobile-menu"
                :class="{ 'is-open': mobileOpen }"
            >
                <nav>
                    <a
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        @click="closeMobile"
                    >
                        <small>{{ item.code }}</small>
                        <span>{{ item.label }}</span>
                        <b>▶</b>
                    </a>
                </nav>
            </div>
        </header>

        <main id="topo" class="pixel-public-main">
            <slot />
        </main>

        <a
            v-show="scrolled"
            href="#topo"
            class="pixel-public-top"
            :aria-label="t('a11y.backTop')"
        >
            <span>↑</span>
            <small>TOP</small>
        </a>
    </div>
</template>
