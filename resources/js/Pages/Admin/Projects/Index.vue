<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import { useTranslatable } from '@/Composables/useTranslatable';

const { tr } = useTranslatable();

const props = defineProps({
    projects: {
        type: Array,
        required: true,
    },
});

const deleteForm = useForm({});
const confirmingDeleteId = ref(null);

const confirmDelete = (id) => {
    confirmingDeleteId.value = id;
};

const cancelDelete = () => {
    confirmingDeleteId.value = null;
};

const destroy = (id) => {
    deleteForm.delete(route('admin.projects.destroy', id), {
        preserveScroll: true,
        onFinish: () => (confirmingDeleteId.value = null),
    });
};

const moveUp = (index) => {
    if (index === 0) return;
    const ordered = [...props.projects];
    [ordered[index - 1], ordered[index]] = [ordered[index], ordered[index - 1]];
    persistOrder(ordered.map((p) => p.id));
};

const moveDown = (index) => {
    if (index === props.projects.length - 1) return;
    const ordered = [...props.projects];
    [ordered[index + 1], ordered[index]] = [ordered[index], ordered[index + 1]];
    persistOrder(ordered.map((p) => p.id));
};

const persistOrder = async (orderedIds) => {
    await axios.put(route('admin.projects.reorder'), { ordered_ids: orderedIds });
    router.reload({ only: ['projects'], preserveScroll: true });
};

const imageSrc = (path) => (path ? '/storage/' + path : null);

</script>

<template>
    <Head title="Projetos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    Projetos
                </h2>
                <Link
                    :href="route('admin.projects.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo Projeto
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 overflow-hidden">
                <div v-if="projects.length === 0" class="px-6 py-16 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Nenhum projeto cadastrado</h3>
                    <p class="mt-1 text-sm text-slate-500">Adicione seu primeiro projeto ao portfolio.</p>
                    <Link
                        :href="route('admin.projects.create')"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Adicionar Primeiro Projeto
                    </Link>
                </div>

                <ul v-else class="divide-y divide-slate-100">
                    <li
                        v-for="(project, index) in projects"
                        :key="project.id"
                        class="flex items-start gap-4 px-6 py-5 hover:bg-slate-50 transition-colors"
                    >
                        <!-- Reorder controls -->
                        <div class="flex flex-col gap-1 pt-2">
                            <button
                                type="button"
                                :disabled="index === 0"
                                @click="moveUp(index)"
                                class="p-1 text-slate-400 hover:text-indigo-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                aria-label="Mover para cima"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                :disabled="index === projects.length - 1"
                                @click="moveDown(index)"
                                class="p-1 text-slate-400 hover:text-indigo-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                aria-label="Mover para baixo"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Image preview -->
                        <div class="w-20 h-20 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 ring-1 ring-slate-200">
                            <img v-if="project.image_path" :src="imageSrc(project.image_path)" :alt="tr(project.title)" class="w-full h-full object-cover" />
                            <svg v-else class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ tr(project.title) }}</p>
                                <span
                                    v-if="project.is_featured"
                                    class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200"
                                >
                                    Destaque
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ tr(project.description) }}</p>
                            <div v-if="project.stacks && project.stacks.length" class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    v-for="stack in project.stacks"
                                    :key="stack.id"
                                    class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium text-white"
                                    :style="{ backgroundColor: stack.color || '#64748b' }"
                                >
                                    {{ stack.name }}
                                </span>
                            </div>
                            <div class="mt-2 flex items-center gap-3 text-xs text-slate-500">
                                <a v-if="project.repository_url" :href="project.repository_url" target="_blank" rel="noopener" class="hover:text-indigo-600 inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.4 3-.405 1.02.005 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                                    Repo
                                </a>
                                <a v-if="project.demo_url" :href="project.demo_url" target="_blank" rel="noopener" class="hover:text-indigo-600 inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    Demo
                                </a>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="route('admin.projects.edit', project.id)"
                                class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50 transition-colors"
                            >
                                Editar
                            </Link>

                            <template v-if="confirmingDeleteId === project.id">
                                <button
                                    type="button"
                                    @click="cancelDelete"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    @click="destroy(project.id)"
                                    :disabled="deleteForm.processing"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 disabled:opacity-60"
                                >
                                    Confirmar
                                </button>
                            </template>
                            <button
                                v-else
                                type="button"
                                @click="confirmDelete(project.id)"
                                class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-600 bg-white ring-1 ring-inset ring-red-200 hover:bg-red-50 transition-colors"
                            >
                                Excluir
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
