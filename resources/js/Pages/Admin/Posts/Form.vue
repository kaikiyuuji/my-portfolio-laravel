<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    post: {
        type: Object,
        default: null,
    },
});

const isEditing = computed(() => props.post !== null);

const toTrans = (val) => {
    if (val && typeof val === 'object') return { pt: val.pt || '', en: val.en || '' };
    return { pt: val || '', en: '' };
};

const toDateTimeInput = (value) => {
    if (!value) return '';
    const d = new Date(value);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const form = useForm({
    _method: isEditing.value ? 'put' : 'post',
    slug: props.post?.slug ?? '',
    title: toTrans(props.post?.title),
    excerpt: toTrans(props.post?.excerpt),
    body: toTrans(props.post?.body),
    image: null,
    is_published: props.post?.is_published ?? false,
    published_at: toDateTimeInput(props.post?.published_at),
});

const fileInput = ref(null);
const imagePreview = ref(props.post?.image_path ? '/storage/' + props.post.image_path : null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    form.image = file;
    if (file) imagePreview.value = URL.createObjectURL(file);
};

const triggerFileInput = () => fileInput.value.click();

const submit = () => {
    if (isEditing.value) {
        form.post(route('admin.posts.update', props.post.slug), {
            preserveScroll: true,
            forceFormData: true,
        });
    } else {
        form.post(route('admin.posts.store'), {
            preserveScroll: true,
            forceFormData: true,
        });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Post' : 'Novo Post'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.posts.index')"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white ring-1 ring-inset ring-slate-200 text-slate-600 hover:bg-slate-50"
                    aria-label="Voltar"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ isEditing ? 'Editar Post' : 'Novo Post' }}
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <form @submit.prevent="submit">
                <div class="shadow-sm sm:overflow-hidden sm:rounded-2xl border border-slate-100 bg-white">
                    <div class="space-y-6 px-4 py-6 sm:p-8">
                        <!-- Cover image -->
                        <div>
                            <InputLabel value="Imagem de Capa" />
                            <div class="mt-3 flex items-start gap-6">
                                <div class="relative group cursor-pointer w-48 h-32 rounded-xl overflow-hidden ring-1 ring-slate-200 bg-slate-100" @click="triggerFileInput">
                                    <img v-if="imagePreview" :src="imagePreview" alt="Preview" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white text-sm font-medium">Alterar</span>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" @click="triggerFileInput" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors">
                                        Selecionar imagem
                                    </button>
                                    <p class="mt-2 text-xs leading-5 text-slate-500">JPG, PNG ou WebP. Máximo 4MB.</p>
                                </div>
                                <input ref="fileInput" type="file" class="hidden" @change="handleImageChange" accept="image/*" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.image" />
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Title PT -->
                            <div class="sm:col-span-1">
                                <InputLabel for="title_pt" value="Título (PT)" />
                                <TextInput id="title_pt" type="text" class="mt-1 block w-full" v-model="form.title.pt" required placeholder="Ex: Como construí meu portfolio" />
                                <InputError class="mt-2" :message="form.errors['title.pt']" />
                            </div>

                            <!-- Title EN -->
                            <div class="sm:col-span-1">
                                <InputLabel for="title_en" value="Título (EN)" />
                                <TextInput id="title_en" type="text" class="mt-1 block w-full" v-model="form.title.en" placeholder="Ex: How I built my portfolio" />
                                <InputError class="mt-2" :message="form.errors['title.en']" />
                            </div>

                            <!-- Slug -->
                            <div class="sm:col-span-2">
                                <InputLabel for="slug" value="Slug (URL)" />
                                <TextInput id="slug" type="text" class="mt-1 block w-full" v-model="form.slug" placeholder="deixe vazio para gerar automaticamente do título" />
                                <p class="mt-1 text-xs text-slate-500">Visível em /blog/<span class="font-mono text-slate-700">{{ form.slug || 'meu-post' }}</span></p>
                                <InputError class="mt-2" :message="form.errors.slug" />
                            </div>

                            <!-- Excerpt PT -->
                            <div class="sm:col-span-2">
                                <InputLabel for="excerpt_pt" value="Resumo (PT)" />
                                <textarea id="excerpt_pt" rows="2" class="mt-1 block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" v-model="form.excerpt.pt" placeholder="Resumo curto exibido na listagem..."></textarea>
                                <InputError class="mt-2" :message="form.errors['excerpt.pt']" />
                            </div>

                            <!-- Excerpt EN -->
                            <div class="sm:col-span-2">
                                <InputLabel for="excerpt_en" value="Resumo (EN)" />
                                <textarea id="excerpt_en" rows="2" class="mt-1 block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" v-model="form.excerpt.en" placeholder="Short summary shown on listing..."></textarea>
                                <InputError class="mt-2" :message="form.errors['excerpt.en']" />
                            </div>

                            <!-- Body PT -->
                            <div class="sm:col-span-2">
                                <InputLabel for="body_pt" value="Conteúdo (PT) — aceita HTML/Markdown" />
                                <textarea id="body_pt" rows="12" class="mt-1 block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 font-mono text-sm leading-6" v-model="form.body.pt" required placeholder="Conteúdo completo do post..."></textarea>
                                <InputError class="mt-2" :message="form.errors['body.pt']" />
                            </div>

                            <!-- Body EN -->
                            <div class="sm:col-span-2">
                                <InputLabel for="body_en" value="Conteúdo (EN)" />
                                <textarea id="body_en" rows="12" class="mt-1 block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 font-mono text-sm leading-6" v-model="form.body.en" placeholder="Full post content..."></textarea>
                                <InputError class="mt-2" :message="form.errors['body.en']" />
                            </div>

                            <!-- Published at -->
                            <div class="sm:col-span-1">
                                <InputLabel for="published_at" value="Publicar em" />
                                <TextInput id="published_at" type="datetime-local" class="mt-1 block w-full" v-model="form.published_at" />
                                <p class="mt-1 text-xs text-slate-500">Vazio + publicado = agora.</p>
                                <InputError class="mt-2" :message="form.errors.published_at" />
                            </div>

                            <!-- Published flag -->
                            <div class="sm:col-span-1 flex items-center gap-3 mt-7">
                                <input
                                    id="is_published"
                                    type="checkbox"
                                    v-model="form.is_published"
                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <InputLabel for="is_published" value="Publicar (rascunho fica oculto)" />
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-x-3 bg-slate-50 px-4 py-4 sm:px-8 border-t border-slate-100">
                        <Link
                            :href="route('admin.posts.index')"
                            class="inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton
                            :disabled="form.processing"
                            class="!rounded-xl px-6 py-2.5 shadow-sm hover:shadow bg-indigo-600 hover:bg-indigo-500 transition-all"
                        >
                            {{ isEditing ? 'Salvar Alterações' : 'Criar Post' }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
