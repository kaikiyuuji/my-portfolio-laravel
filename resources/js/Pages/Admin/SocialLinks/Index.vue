<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';

const props = defineProps({
    socialLinks: {
        type: Array,
        required: true,
    },
});

const deleteForm = useForm({});
const confirmingDeleteId = ref(null);
const DEFAULT_BG = '#1e293b'; // slate-800

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

const iconSources = (slug, color) => {
    const c = iconColor(color || DEFAULT_BG);
    return [
        `https://cdn.simpleicons.org/${slug}/${c}`,
        `https://api.iconify.design/mdi:${slug}.svg?color=%23${c}`,
        `https://api.iconify.design/fa6-brands:${slug}.svg?color=%23${c}`,
        `https://api.iconify.design/logos:${slug}.svg`,
    ];
};

const attemptIndex = ref({});
const failedIcons = ref(new Set());

const currentIconUrl = (link) => {
    const sources = iconSources(link.icon_slug, link.color);
    const idx = attemptIndex.value[link.id] ?? 0;
    return sources[idx];
};

const onIconError = (link) => {
    const sources = iconSources(link.icon_slug, link.color);
    const idx = (attemptIndex.value[link.id] ?? 0) + 1;
    if (idx >= sources.length) {
        failedIcons.value.add(link.id);
        return;
    }
    attemptIndex.value = { ...attemptIndex.value, [link.id]: idx };
};

const confirmDelete = (id) => {
    confirmingDeleteId.value = id;
};

const cancelDelete = () => {
    confirmingDeleteId.value = null;
};

const destroy = (id) => {
    deleteForm.delete(route('admin.social-links.destroy', id), {
        preserveScroll: true,
        onFinish: () => (confirmingDeleteId.value = null),
    });
};

const moveUp = (index) => {
    if (index === 0) return;
    const ordered = [...props.socialLinks];
    [ordered[index - 1], ordered[index]] = [ordered[index], ordered[index - 1]];
    persistOrder(ordered.map((l) => l.id));
};

const moveDown = (index) => {
    if (index === props.socialLinks.length - 1) return;
    const ordered = [...props.socialLinks];
    [ordered[index + 1], ordered[index]] = [ordered[index], ordered[index + 1]];
    persistOrder(ordered.map((l) => l.id));
};

const persistOrder = async (orderedIds) => {
    await axios.put(route('admin.social-links.reorder'), { ordered_ids: orderedIds });
    router.reload({ only: ['socialLinks'], preserveScroll: true });
};
</script>

<template>
    <Head title="Redes Sociais" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    Redes Sociais
                </h2>
                <Link
                    :href="route('admin.social-links.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nova Rede Social
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 overflow-hidden">
                <div v-if="socialLinks.length === 0" class="px-6 py-16 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Nenhuma rede social cadastrada</h3>
                    <p class="mt-1 text-sm text-slate-500">Adicione links para suas redes sociais.</p>
                    <Link
                        :href="route('admin.social-links.create')"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Adicionar Primeira Rede Social
                    </Link>
                </div>

                <ul v-else class="divide-y divide-slate-100">
                    <li
                        v-for="(link, index) in socialLinks"
                        :key="link.id"
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
                                :disabled="index === socialLinks.length - 1"
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
                            class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs shadow-sm overflow-hidden"
                            :style="{
                                backgroundColor: link.color || DEFAULT_BG,
                                color: iconColor(link.color || DEFAULT_BG) === '000000' ? '#000' : '#fff',
                            }"
                        >
                            <img
                                v-if="!failedIcons.has(link.id)"
                                :src="currentIconUrl(link)"
                                :alt="link.platform"
                                class="w-6 h-6 object-contain"
                                @error="onIconError(link)"
                            />
                            <span v-else>{{ link.platform.charAt(0).toUpperCase() }}</span>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">{{ link.platform }}</p>
                            <a :href="link.url" target="_blank" rel="noopener" class="text-xs text-indigo-600 hover:underline truncate block max-w-md">
                                {{ link.url }}
                            </a>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('admin.social-links.edit', link.id)"
                                class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50 transition-colors"
                            >
                                Editar
                            </Link>

                            <template v-if="confirmingDeleteId === link.id">
                                <button
                                    type="button"
                                    @click="cancelDelete"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    @click="destroy(link.id)"
                                    :disabled="deleteForm.processing"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 disabled:opacity-60"
                                >
                                    Confirmar
                                </button>
                            </template>
                            <button
                                v-else
                                type="button"
                                @click="confirmDelete(link.id)"
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
