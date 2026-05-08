<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                Dashboard
            </h2>
        </template>

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
            <!-- Welcome Card -->
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-2xl shadow-xl shadow-indigo-500/20 p-8 sm:p-10 text-white relative overflow-hidden border border-white/10">
                <div class="absolute -right-20 -top-20 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -bottom-10 w-64 h-64 bg-violet-400/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                    <div>
                        <h3 class="text-3xl font-extrabold mb-2 tracking-tight">Bem-vindo, {{ $page.props.auth.user.name }}!</h3>
                        <p class="text-indigo-100/90 max-w-xl text-lg font-medium leading-relaxed">Este é o painel de controle do seu portfolio. Utilize o menu acima para gerenciar seu perfil público.</p>
                    </div>
                    <Link :href="route('admin.profile.edit')" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-5 py-3 rounded-xl font-semibold backdrop-blur-md transition-all border border-white/20 hover:scale-105 active:scale-95 shadow-lg">
                        Editar Portfolio
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </Link>
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Profile -->
                <Link :href="route('admin.profile.edit')" class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all border border-slate-100 group">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-indigo-600 transition-colors">Perfil</h4>
                    <p class="text-slate-500 mt-1 text-sm">Atualize seu nome, título, foto e bio.</p>
                </Link>

                <!-- Stacks -->
                <Link :href="route('admin.stacks.index')" class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all border border-slate-100 group">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                    </div>
                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-emerald-600 transition-colors">Tecnologias</h4>
                    <p class="text-slate-500 mt-1 text-sm">Gerencie as stacks exibidas no portfolio.</p>
                </Link>

                <!-- Experiences -->
                <Link :href="route('admin.experiences.index')" class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all border border-slate-100 group">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-amber-600 group-hover:text-white transition-all shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-amber-600 transition-colors">Experiências</h4>
                    <p class="text-slate-500 mt-1 text-sm">Trajetória profissional na timeline.</p>
                </Link>

                <!-- Projects -->
                <Link :href="route('admin.projects.index')" class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all border border-slate-100 group">
                    <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-rose-600 group-hover:text-white transition-all shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-rose-600 transition-colors">Projetos</h4>
                    <p class="text-slate-500 mt-1 text-sm">Cards do portfolio com tecnologias e demos.</p>
                </Link>

                <!-- Social Links -->
                <Link :href="route('admin.social-links.index')" class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all border border-slate-100 group">
                    <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-sky-600 group-hover:text-white transition-all shadow-sm">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                    </div>
                    <h4 class="font-bold text-slate-800 text-lg group-hover:text-sky-600 transition-colors">Redes Sociais</h4>
                    <p class="text-slate-500 mt-1 text-sm">GitHub, LinkedIn e outros perfis.</p>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
