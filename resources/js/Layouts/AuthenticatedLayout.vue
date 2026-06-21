<script setup>
import AdminNavIcon from '@/Components/AdminNavIcon.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { useArcadeSound } from '@/Composables/useArcadeSound';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const showingNavigationDropdown = ref(false);
const adminRoot = ref(null);
const { soundEnabled, toggleSound } = useArcadeSound(adminRoot);

const navItems = [
    { label: 'Dashboard', shortLabel: 'Home', route: 'admin.dashboard', pattern: 'admin.dashboard', icon: 'dashboard', color: '#64d8ff' },
    { label: 'Portfolio', shortLabel: 'Perfil', route: 'admin.profile.edit', pattern: 'admin.profile.*', icon: 'profile', color: '#ff8eb3' },
    { label: 'Tecnologias', shortLabel: 'Stacks', route: 'admin.stacks.index', pattern: 'admin.stacks.*', icon: 'code', color: '#70f49b' },
    { label: 'Experiências', shortLabel: 'XP', route: 'admin.experiences.index', pattern: 'admin.experiences.*', icon: 'experience', color: '#ffe66d' },
    { label: 'Projetos', shortLabel: 'Projetos', route: 'admin.projects.index', pattern: 'admin.projects.*', icon: 'projects', color: '#ff9f5a' },
    { label: 'Redes sociais', shortLabel: 'Links', route: 'admin.social-links.index', pattern: 'admin.social-links.*', icon: 'social', color: '#8da2ff' },
    { label: 'Blog', shortLabel: 'Blog', route: 'admin.posts.index', pattern: 'admin.posts.*', icon: 'blog', color: '#c084fc' },
];

const activeSection = computed(
    () => navItems.find((item) => route().current(item.pattern))?.label ?? 'Admin',
);
</script>

<template>
    <div ref="adminRoot" class="pixel-admin min-h-screen">
        <div class="pixel-scanlines" aria-hidden="true"></div>

        <aside class="pixel-sidebar">
            <Link :href="route('admin.dashboard')" class="pixel-brand" aria-label="Ir para o dashboard">
                <span class="pixel-brand__mark" aria-hidden="true">
                    <i></i><i></i><i></i><i></i>
                </span>
                <span>
                    <strong>DEV.OS</strong>
                    <small>ADMIN CONSOLE</small>
                </span>
            </Link>

            <div class="pixel-status">
                <span class="pixel-status__light"></span>
                <span>SISTEMA ONLINE</span>
                <b>v8.0</b>
            </div>

            <nav class="pixel-nav" aria-label="Navegação principal">
                <p class="pixel-nav__eyebrow">// MAIN MENU</p>
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route)"
                    class="pixel-nav__link"
                    :class="{ 'is-active': route().current(item.pattern) }"
                    :style="{ '--nav-accent': item.color }"
                >
                    <span class="pixel-nav__icon" aria-hidden="true">
                        <AdminNavIcon v-if="item.icon" :name="item.icon" />
                        <span v-else class="pixel-nav__glyph">{{ item.glyph }}</span>
                    </span>
                    <span>{{ item.label }}</span>
                    <span class="pixel-nav__cursor" aria-hidden="true">▶</span>
                </Link>
            </nav>

            <div class="pixel-sidebar__footer">
                <div class="pixel-player">
                    <div class="pixel-avatar">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</div>
                    <div class="min-w-0">
                        <small>PLAYER 01</small>
                        <strong>{{ $page.props.auth.user.name }}</strong>
                    </div>
                </div>

                <a
                    :href="route('home')"
                    target="_blank"
                    rel="noopener"
                    class="pixel-mini-link pixel-mini-link--site"
                >
                    <AdminNavIcon name="external" /> VER PORTFOLIO
                </a>
                <Link :href="route('profile.edit')" class="pixel-mini-link">
                    <AdminNavIcon name="settings" /> CONTA
                </Link>
                <Link :href="route('logout')" method="post" as="button" class="pixel-mini-link pixel-mini-link--danger">
                    <AdminNavIcon name="logout" /> SAIR
                </Link>
            </div>
        </aside>

        <section class="pixel-shell">
            <header class="pixel-topbar">
                <div>
                    <p class="pixel-kicker">C:\PORTFOLIO\{{ activeSection.toUpperCase() }}</p>
                    <div class="pixel-page-title">
                        <slot name="header" />
                    </div>
                </div>

                <div class="pixel-topbar__actions">
                    <div class="pixel-coins" title="Sessão ativa">
                        <span aria-hidden="true">●</span>
                        <div>
                            <small>PLAYER</small>
                            <strong>01</strong>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="pixel-sound"
                        data-sound-toggle
                        :aria-pressed="soundEnabled"
                        :title="soundEnabled ? 'Desativar sons' : 'Ativar sons'"
                        @click.stop="toggleSound"
                    >
                        <span class="pixel-sound__icon" aria-hidden="true">{{ soundEnabled ? '♪' : '×' }}</span>
                        <span>{{ soundEnabled ? 'SOM ON' : 'SOM OFF' }}</span>
                    </button>

                    <button
                        type="button"
                        class="pixel-mobile-toggle"
                        :aria-expanded="showingNavigationDropdown"
                        aria-controls="mobile-menu"
                        aria-label="Alternar menu"
                        @click="showingNavigationDropdown = !showingNavigationDropdown"
                    >
                        {{ showingNavigationDropdown ? '×' : '☰' }}
                    </button>
                </div>

                <div
                    id="mobile-menu"
                    class="pixel-mobile-menu"
                    :class="{ 'is-open': showingNavigationDropdown }"
                >
                    <div class="pixel-mobile-menu__grid">
                        <ResponsiveNavLink
                            v-for="item in navItems"
                            :key="item.route"
                            :href="route(item.route)"
                            :active="route().current(item.pattern)"
                            :style="{ '--nav-accent': item.color }"
                            @click="showingNavigationDropdown = false"
                        >
                            <span class="pixel-mobile-nav-icon" aria-hidden="true">
                                <AdminNavIcon :name="item.icon" />
                            </span>
                            {{ item.shortLabel }}
                        </ResponsiveNavLink>
                    </div>
                    <div class="pixel-mobile-menu__account">
                        <a :href="route('home')" target="_blank" rel="noopener">
                            <AdminNavIcon name="external" /> Ver portfolio
                        </a>
                        <ResponsiveNavLink :href="route('profile.edit')">
                            <AdminNavIcon name="settings" /> Conta
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                            <AdminNavIcon name="logout" /> Sair
                        </ResponsiveNavLink>
                    </div>
                </div>
            </header>

            <main class="pixel-content">
                <slot />
            </main>

            <footer class="pixel-footer">
                <span>DEV.OS // READY</span>
                <span>USE ↑ ↓ ← → TO NAVIGATE</span>
                <span>© {{ new Date().getFullYear() }}</span>
            </footer>
        </section>
    </div>
</template>
