<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    posts: {
        type: Array,
        required: true,
    },
});

const deleteForm = useForm({});
const confirmingDeleteId = ref(null);

const confirmDelete = (id) => (confirmingDeleteId.value = id);
const cancelDelete = () => (confirmingDeleteId.value = null);

const destroy = (slug) => {
    deleteForm.delete(route('admin.posts.destroy', slug), {
        preserveScroll: true,
        onFinish: () => (confirmingDeleteId.value = null),
    });
};

const tr = (val) => {
    if (val && typeof val === 'object') return val.pt || val.en || '';
    return val || '';
};

const imageSrc = (path) => (path ? '/storage/' + path : null);

const formatDate = (iso) => {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Blog" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Blog</h2>
                <Link
                    :href="route('admin.posts.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Novo Post
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl border border-slate-100 overflow-hidden">
                <div v-if="posts.length === 0" class="px-6 py-16 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-fuchsia-50 text-fuchsia-600 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Nenhum post cadastrado</h3>
                    <p class="mt-1 text-sm text-slate-500">Comece a escrever o primeiro post do seu blog.</p>
                    <Link :href="route('admin.posts.create')" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        Criar Primeiro Post
                    </Link>
                </div>

                <ul v-else class="divide-y divide-slate-100">
                    <li v-for="post in posts" :key="post.id" class="flex items-start gap-4 px-6 py-5 hover:bg-slate-50 transition-colors">
                        <!-- Cover -->
                        <div class="w-24 h-20 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 ring-1 ring-slate-200">
                            <img v-if="post.image_path" :src="imageSrc(post.image_path)" :alt="tr(post.title)" class="w-full h-full object-cover" />
                            <svg v-else class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-slate-900 truncate">{{ tr(post.title) }}</p>
                                <span
                                    v-if="post.is_published"
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Publicado
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200"
                                >
                                    Rascunho
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 font-medium">
                                /{{ post.slug }} · {{ formatDate(post.published_at || post.created_at) }}
                            </p>
                            <p v-if="tr(post.excerpt)" class="text-sm text-slate-600 mt-2 line-clamp-2">{{ tr(post.excerpt) }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="route('admin.posts.edit', post.slug)"
                                class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50 transition-colors"
                            >
                                Editar
                            </Link>

                            <template v-if="confirmingDeleteId === post.id">
                                <button
                                    type="button"
                                    @click="cancelDelete"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white ring-1 ring-inset ring-slate-200 hover:bg-slate-50"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    @click="destroy(post.slug)"
                                    :disabled="deleteForm.processing"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 disabled:opacity-60"
                                >
                                    Confirmar
                                </button>
                            </template>
                            <button
                                v-else
                                type="button"
                                @click="confirmDelete(post.id)"
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
