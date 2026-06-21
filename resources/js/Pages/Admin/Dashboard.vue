<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const shortcuts = [
    {
        label: 'Perfil',
        description: 'Nome, título, avatar e bio.',
        route: 'admin.profile.edit',
        code: 'USR',
        icon: '☺',
        color: 'cyan',
        key: 'A',
    },
    {
        label: 'Tecnologias',
        description: 'Organize sua árvore de skills.',
        route: 'admin.stacks.index',
        code: 'TEC',
        icon: '◆',
        color: 'green',
        key: 'B',
    },
    {
        label: 'Experiências',
        description: 'Atualize sua jornada profissional.',
        route: 'admin.experiences.index',
        code: 'EXP',
        icon: '♜',
        color: 'yellow',
        key: 'X',
    },
    {
        label: 'Projetos',
        description: 'Cadastre seus melhores trabalhos.',
        route: 'admin.projects.index',
        code: 'PRJ',
        icon: '▣',
        color: 'pink',
        key: 'Y',
    },
    {
        label: 'Redes',
        description: 'Conecte todos os seus canais.',
        route: 'admin.social-links.index',
        code: 'NET',
        icon: '∞',
        color: 'blue',
        key: 'L',
    },
    {
        label: 'Blog',
        description: 'Escreva e publique novos posts.',
        route: 'admin.posts.index',
        code: 'TXT',
        icon: '✎',
        color: 'purple',
        key: 'R',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <span class="pixel-title-prefix">01.</span>
                <h1>Dashboard</h1>
            </div>
        </template>

        <div class="pixel-dashboard">
            <section class="pixel-hero">
                <div class="pixel-hero__copy">
                    <div class="pixel-chip">NOVO JOGO</div>
                    <p class="pixel-hero__overline">MISSÃO ATUAL</p>
                    <h2>
                        Olá, <span>{{ $page.props.auth.user.name }}</span>!
                    </h2>
                    <p>
                        Seu portfolio está carregado e pronto para receber upgrades.
                        Escolha uma fase abaixo e continue construindo sua história.
                    </p>
                    <div class="pixel-hero__actions">
                        <Link :href="route('admin.profile.edit')" class="pixel-cta">
                            <span class="pixel-cta__play">▶</span>
                            EDITAR PORTFOLIO
                        </Link>
                        <a
                            :href="route('home')"
                            target="_blank"
                            rel="noopener"
                            class="pixel-cta pixel-cta--secondary"
                        >
                            <span class="pixel-cta__play">↗</span>
                            VER PORTFOLIO
                        </a>
                    </div>
                </div>

                <div class="pixel-hero__scene" aria-hidden="true">
                    <div class="pixel-moon"></div>
                    <div class="pixel-stars">✦ · ✧ · ✦</div>
                    <div class="pixel-computer">
                        <div class="pixel-computer__screen">
                            <span>&gt; PORTFOLIO</span>
                            <span>&gt; STATUS: ON</span>
                            <b class="pixel-caret">_</b>
                        </div>
                        <div class="pixel-computer__base"></div>
                    </div>
                    <div class="pixel-floor"></div>
                </div>
            </section>

            <div class="pixel-section-heading">
                <div>
                    <span>SELECT STAGE</span>
                    <h2>Acesso rápido</h2>
                </div>
                <p>6 FASES DISPONÍVEIS</p>
            </div>

            <section class="pixel-shortcut-grid">
                <Link
                    v-for="shortcut in shortcuts"
                    :key="shortcut.route"
                    :href="route(shortcut.route)"
                    class="pixel-shortcut"
                    :class="`pixel-shortcut--${shortcut.color}`"
                >
                    <div class="pixel-shortcut__top">
                        <span class="pixel-shortcut__code">{{ shortcut.code }}</span>
                        <span class="pixel-shortcut__key">{{ shortcut.key }}</span>
                    </div>
                    <div class="pixel-shortcut__icon" aria-hidden="true">{{ shortcut.icon }}</div>
                    <h3>{{ shortcut.label }}</h3>
                    <p>{{ shortcut.description }}</p>
                    <span class="pixel-shortcut__action">ENTRAR <b>▶</b></span>
                </Link>
            </section>

            <section class="pixel-tips">
                <div class="pixel-tips__icon" aria-hidden="true">!</div>
                <div>
                    <span>DICA DO SISTEMA</span>
                    <p>Mantenha projetos e experiências atualizados para aumentar o nível do seu portfolio.</p>
                </div>
                <div class="pixel-tips__progress">
                    <i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
