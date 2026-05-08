<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900 font-sans antialiased selection:bg-indigo-500 selection:text-white">
        <!-- Modern Glass Topnav -->
        <nav class="sticky top-0 z-50 w-full backdrop-blur-md bg-white/70 border-b border-slate-200/50 shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center gap-8">
                        <!-- Logo -->
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('admin.dashboard')" class="group flex items-center gap-2">
                                <span class="font-bold text-xl tracking-tight text-slate-800">Admin</span>
                            </Link>
                        </div>

                        <!-- Main Desktop Navigation -->
                        <div class="hidden sm:flex sm:items-center sm:space-x-4 h-16">
                            <NavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                                Dashboard
                            </NavLink>
                            <NavLink :href="route('admin.profile.edit')" :active="route().current('admin.profile.*')">
                                Portfolio Público
                            </NavLink>
                            <NavLink :href="route('admin.stacks.index')" :active="route().current('admin.stacks.*')">
                                Tecnologias
                            </NavLink>
                            <NavLink :href="route('admin.experiences.index')" :active="route().current('admin.experiences.*')">
                                Experiências
                            </NavLink>
                            <NavLink :href="route('admin.projects.index')" :active="route().current('admin.projects.*')">
                                Projetos
                            </NavLink>
                            <NavLink :href="route('admin.social-links.index')" :active="route().current('admin.social-links.*')">
                                Redes Sociais
                            </NavLink>
                            <NavLink :href="route('admin.posts.index')" :active="route().current('admin.posts.*')">
                                Blog
                            </NavLink>
                        </div>
                    </div>

                    <!-- User Menu Desktop -->
                    <div class="hidden sm:flex sm:items-center">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-full hover:bg-slate-50 hover:border-slate-300 transition focus:outline-none focus:ring-2 focus:ring-indigo-500/20 shadow-sm">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                                        {{ $page.props.auth.user.name.charAt(0) }}
                                    </div>
                                    <span>{{ $page.props.auth.user.name }}</span>
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $page.props.auth.user.email }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Conta Administrador</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')">
                                    Configurações da Conta
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600 hover:text-red-700 hover:bg-red-50">
                                    Sair
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex items-center sm:hidden">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="p-2 text-slate-500 rounded-lg hover:bg-slate-100 transition focus:outline-none">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden border-t border-slate-200 bg-white">
                <div class="pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink :href="route('admin.dashboard')" :active="route().current('admin.dashboard')">
                        Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.profile.edit')" :active="route().current('admin.profile.*')">
                        Portfolio Público
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.stacks.index')" :active="route().current('admin.stacks.*')">
                        Tecnologias
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.experiences.index')" :active="route().current('admin.experiences.*')">
                        Experiências
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.projects.index')" :active="route().current('admin.projects.*')">
                        Projetos
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.social-links.index')" :active="route().current('admin.social-links.*')">
                        Redes Sociais
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('admin.posts.index')" :active="route().current('admin.posts.*')">
                        Blog
                    </ResponsiveNavLink>
                </div>
                <div class="pt-4 pb-1 border-t border-slate-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-slate-800">{{ $page.props.auth.user.name }}</div>
                        <div class="font-medium text-sm text-slate-500">{{ $page.props.auth.user.email }}</div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">
                            Configurações da Conta
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button" class="text-red-600">
                            Sair
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Header -->
        <header v-if="$slots.header" class="bg-white/50 backdrop-blur border-b border-slate-200/60 shadow-[0_4px_20px_-15px_rgba(0,0,0,0.1)] relative z-40">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="relative z-30 pt-8 pb-16">
            <slot />
        </main>
    </div>
</template>
