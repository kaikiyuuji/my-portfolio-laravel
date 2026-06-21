<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const modules = [
    { index: '01', title: 'Perfil', description: 'Identidade, avatar, bio e dados de contato.', route: 'admin.profile.edit', mark: 'P' },
    { index: '02', title: 'Tecnologias', description: 'Stacks e ferramentas exibidas no portfolio.', route: 'admin.stacks.index', mark: 'T' },
    { index: '03', title: 'Experiências', description: 'Histórico profissional e trajetória.', route: 'admin.experiences.index', mark: 'E' },
    { index: '04', title: 'Projetos', description: 'Cases, imagens, links e tecnologias.', route: 'admin.projects.index', mark: 'W' },
    { index: '05', title: 'Redes sociais', description: 'Canais públicos e perfis externos.', route: 'admin.social-links.index', mark: 'S' },
    { index: '06', title: 'Blog', description: 'Artigos, capas e publicação editorial.', route: 'admin.posts.index', mark: 'B' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="technical-label mb-2 text-[var(--accent)]">00 / Overview</p>
                    <h2 class="text-3xl font-semibold tracking-[-0.05em] text-[var(--ink)]">Dashboard</h2>
                </div>
                <p class="technical-label">Management console</p>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="blueprint-grid relative overflow-hidden border border-[var(--ink)] p-6 sm:p-9 lg:p-12">
                <div class="relative z-10 grid gap-10 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <p class="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-white/70">
                            Workspace active / {{ new Date().getFullYear() }}
                        </p>
                        <h3 class="mt-8 max-w-3xl text-4xl font-medium leading-[0.9] tracking-[-0.065em] text-white sm:text-6xl">
                            Olá, {{ $page.props.auth.user.name }}.
                        </h3>
                        <p class="mt-6 max-w-xl text-sm leading-7 text-white/75 sm:text-base">
                            Gerencie o conteúdo público do portfolio por módulos independentes e mantenha tudo atualizado.
                        </p>
                    </div>

                    <Link
                        :href="route('admin.profile.edit')"
                        class="inline-flex min-h-12 min-w-52 items-center justify-between border border-white bg-white px-5 font-mono text-[10px] font-bold uppercase tracking-wider text-[var(--accent)] transition-transform hover:-translate-y-1"
                    >
                        Editar portfolio
                        <span>↗</span>
                    </Link>
                </div>
            </section>

            <section class="grid border-l border-t border-[var(--line)] sm:grid-cols-2 xl:grid-cols-3">
                <Link
                    v-for="module in modules"
                    :key="module.route"
                    :href="route(module.route)"
                    class="group relative min-h-56 overflow-hidden border-b border-r border-[var(--line)] bg-[var(--paper-raised)] p-5 transition-all duration-300 hover:z-10 hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-[7px_7px_0_var(--accent)] sm:p-6"
                >
                    <div class="flex items-start justify-between">
                        <span class="font-mono text-[10px] font-bold tracking-widest text-[var(--accent)]">{{ module.index }}</span>
                        <span class="font-mono text-xl text-[var(--accent)] transition-transform group-hover:rotate-45">×</span>
                    </div>

                    <div class="mt-8 flex items-end gap-5">
                        <span class="dot-field-muted grid h-16 w-16 shrink-0 place-items-center border border-[var(--line)] text-3xl font-medium text-[var(--accent)]">
                            {{ module.mark }}
                        </span>
                        <div>
                            <h4 class="text-xl font-semibold tracking-[-0.04em] transition-colors group-hover:text-[var(--accent)]">
                                {{ module.title }}
                            </h4>
                            <p class="mt-2 text-sm leading-6 text-[var(--muted)]">{{ module.description }}</p>
                        </div>
                    </div>

                    <span class="absolute bottom-4 right-5 font-mono text-lg text-[var(--accent)] transition-transform group-hover:translate-x-1">→</span>
                </Link>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
