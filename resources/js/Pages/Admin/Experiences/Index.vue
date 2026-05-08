<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    experiences: {
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
    deleteForm.delete(route('admin.experiences.destroy', id), {
        preserveScroll: true,
        onFinish: () => (confirmingDeleteId.value = null),
    });
};

const formatPeriod = (start, end) => {
    const startDate = new Date(start);
    const startStr = startDate.toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
    if (!end) return `${startStr} — Atual`;
    const endStr = new Date(end).toLocaleDateString('pt-BR', { month: 'short', year: 'numeric' });
    return `${startStr} — ${endStr}`;
};
</script>

<template>
    <Head title="Experiências" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    Experiências
                </h2>
                <Link
                    :href="route('admin.experiences.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nova Experiência
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 overflow-hidden">
                <div v-if="experiences.length === 0" class="px-6 py-16 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Nenhuma experiência cadastrada</h3>
                    <p class="mt-1 text-sm text-slate-500">Adicione sua trajetória profissional para exibir na timeline.</p>
                    <Link
                        :href="route('admin.experiences.create')"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Adicionar Primeira Experiência
                    </Link>
                </div>

                <ul v-else class="divide-y divide-slate-100">
                    <li
                        v-for="experience in experiences"
                        :key="experience.id"
                        class="flex items-start gap-4 px-6 py-5 hover:bg-slate-50 transition-colors"
                    >
                        <!-- Timeline marker -->
                        <div class="flex flex-col items-center pt-1 shrink-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-slate-900">{{ experience.role }}</p>
                                <span class="text-slate-400">·</span>
                                <p class="text-sm font-medium text-indigo-600">{{ experience.company }}</p>
                                <span
                                    v-if="!experience.end_date"
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200"
                                >
                                    Atual
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 font-medium">
                                {{ formatPeriod(experience.start_date, experience.end_date) }}
                            </p>
                            <p v-if="experience.description" class="text-sm text-slate-600 mt-2 line-clamp-2">
                                {{ experience.description }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="route('admin.experiences.edit', experience.id)"
                                class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50 transition-colors"
                            >
                                Editar
                            </Link>

                            <template v-if="confirmingDeleteId === experience.id">
                                <button
                                    type="button"
                                    @click="cancelDelete"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    @click="destroy(experience.id)"
                                    :disabled="deleteForm.processing"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 disabled:opacity-60"
                                >
                                    Confirmar
                                </button>
                            </template>
                            <button
                                v-else
                                type="button"
                                @click="confirmDelete(experience.id)"
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
