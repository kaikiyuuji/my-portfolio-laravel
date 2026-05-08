<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stacks: {
        type: Array,
        required: true,
    },
});

const deleteForm = useForm({});
const confirmingDeleteId = ref(null);
const failedIcons = ref(new Set());

const iconColor = (hex) => {
    if (!hex) return 'ffffff';
    const c = hex.replace('#', '');
    if (c.length !== 6) return 'ffffff';
    const r = parseInt(c.slice(0, 2), 16);
    const g = parseInt(c.slice(2, 4), 16);
    const b = parseInt(c.slice(4, 6), 16);
    const yiq = (r * 299 + g * 587 + b * 114) / 1000;
    return yiq >= 160 ? '000000' : 'ffffff';
};

const iconUrl = (slug, color) => `https://cdn.simpleicons.org/${slug}/${iconColor(color)}`;
const onIconError = (id) => {
    failedIcons.value.add(id);
};

const confirmDelete = (id) => {
    confirmingDeleteId.value = id;
};

const cancelDelete = () => {
    confirmingDeleteId.value = null;
};

const destroy = (id) => {
    deleteForm.delete(route('admin.stacks.destroy', id), {
        preserveScroll: true,
        onFinish: () => (confirmingDeleteId.value = null),
    });
};

const moveUp = (index) => {
    if (index === 0) return;
    const ordered = [...props.stacks];
    [ordered[index - 1], ordered[index]] = [ordered[index], ordered[index - 1]];
    persistOrder(ordered.map((s) => s.id));
};

const moveDown = (index) => {
    if (index === props.stacks.length - 1) return;
    const ordered = [...props.stacks];
    [ordered[index + 1], ordered[index]] = [ordered[index], ordered[index + 1]];
    persistOrder(ordered.map((s) => s.id));
};

const persistOrder = (orderedIds) => {
    router.put(
        route('admin.stacks.reorder'),
        { ordered_ids: orderedIds },
        { preserveScroll: true }
    );
};
</script>

<template>
    <Head title="Tecnologias" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    Tecnologias
                </h2>
                <Link
                    :href="route('admin.stacks.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nova Tecnologia
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 overflow-hidden">
                <div v-if="stacks.length === 0" class="px-6 py-16 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Nenhuma tecnologia cadastrada</h3>
                    <p class="mt-1 text-sm text-slate-500">Comece adicionando as tecnologias do seu portfolio.</p>
                    <Link
                        :href="route('admin.stacks.create')"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Adicionar Primeira Tecnologia
                    </Link>
                </div>

                <ul v-else class="divide-y divide-slate-100">
                    <li
                        v-for="(stack, index) in stacks"
                        :key="stack.id"
                        class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors"
                    >
                        <!-- Reorder controls -->
                        <div class="flex flex-col gap-1">
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
                                :disabled="index === stacks.length - 1"
                                @click="moveDown(index)"
                                class="p-1 text-slate-400 hover:text-indigo-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                aria-label="Mover para baixo"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Icon swatch -->
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xs shadow-sm overflow-hidden"
                            :style="{ backgroundColor: stack.color || '#64748b' }"
                        >
                            <img
                                v-if="!failedIcons.has(stack.id)"
                                :src="iconUrl(stack.icon_slug, stack.color)"
                                :alt="stack.name"
                                class="w-6 h-6 object-contain"
                                @error="onIconError(stack.id)"
                            />
                            <span v-else>{{ stack.name.charAt(0).toUpperCase() }}</span>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ stack.name }}</p>
                                <span
                                    v-if="stack.is_featured"
                                    class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200"
                                >
                                    Destaque
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                <code class="font-mono">{{ stack.icon_slug }}</code>
                                <span v-if="stack.color" class="ml-2">{{ stack.color }}</span>
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('admin.stacks.edit', stack.id)"
                                class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50 transition-colors"
                            >
                                Editar
                            </Link>

                            <template v-if="confirmingDeleteId === stack.id">
                                <button
                                    type="button"
                                    @click="cancelDelete"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    @click="destroy(stack.id)"
                                    :disabled="deleteForm.processing"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 disabled:opacity-60"
                                >
                                    Confirmar
                                </button>
                            </template>
                            <button
                                v-else
                                type="button"
                                @click="confirmDelete(stack.id)"
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
