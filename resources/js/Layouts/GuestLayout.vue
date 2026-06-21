<script setup>
import { useArcadeSound } from '@/Composables/useArcadeSound';
import { useDarkMode } from '@/Composables/useDarkMode';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const authRoot = ref(null);
const { isDark, toggle } = useDarkMode();
const { soundEnabled, toggleSound } = useArcadeSound(authRoot, {
    storageKey: 'portfolio-arcade-sound',
});
</script>

<template>
    <div ref="authRoot" class="pixel-auth">
        <div class="pixel-auth__stars" aria-hidden="true"></div>

        <header class="pixel-auth__topbar">
            <Link :href="route('home')" class="pixel-auth__brand">
                <span class="pixel-auth__logo">
                    <img src="/favicon.ico" alt="" width="30" height="30" />
                </span>
                <span>
                    <strong>PORTFOLIO.EXE</strong>
                    <small>SECURE ACCESS</small>
                </span>
            </Link>

            <div class="pixel-auth__controls">
                <button
                    type="button"
                    :title="isDark ? 'Modo claro' : 'Modo escuro'"
                    :aria-label="isDark ? 'Ativar modo claro' : 'Ativar modo escuro'"
                    @click="toggle"
                >
                    {{ isDark ? '☀' : '◐' }}
                </button>
                <button
                    type="button"
                    data-sound-toggle
                    :aria-pressed="soundEnabled"
                    :title="soundEnabled ? 'Desativar sons' : 'Ativar sons'"
                    @click.stop="toggleSound"
                >
                    {{ soundEnabled ? '♪' : '×' }}
                </button>
            </div>
        </header>

        <main class="pixel-auth__main">
            <section class="pixel-auth__intro" aria-hidden="true">
                <p>// ADMIN TERMINAL</p>
                <div class="pixel-auth__terminal">
                    <span>&gt; INICIANDO SISTEMA...</span>
                    <span>&gt; PORTFOLIO ONLINE</span>
                    <span>&gt; CANAL SEGURO: ATIVO</span>
                    <strong>&gt; AGUARDANDO LOGIN_</strong>
                </div>
                <div class="pixel-auth__shield">
                    <span></span>
                </div>
                <div class="pixel-auth__levels">
                    <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                </div>
            </section>

            <section class="pixel-auth__card">
                <slot />
            </section>
        </main>

        <footer class="pixel-auth__footer">
            <span>DEV.OS // AUTH MODULE</span>
            <Link :href="route('home')">← VOLTAR AO PORTFOLIO</Link>
        </footer>
    </div>
</template>
