<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    project: {
        type: Object,
        default: null,
    },
    stacks: {
        type: Array,
        required: true,
    },
});

const isEditing = computed(() => props.project !== null);

const initialStackIds = props.project?.stacks?.map((s) => s.id) ?? [];

const form = useForm({
    _method: isEditing.value ? 'put' : 'post',
    title: props.project?.title ?? '',
    description: props.project?.description ?? '',
    repository_url: props.project?.repository_url ?? '',
    demo_url: props.project?.demo_url ?? '',
    order: props.project?.order ?? 0,
    is_featured: props.project?.is_featured ?? false,
    image: null,
    stack_ids: initialStackIds,
});

const fileInput = ref(null);
const imagePreview = ref(props.project?.image_path ? '/storage/' + props.project.image_path : null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    form.image = file;
    if (file) imagePreview.value = URL.createObjectURL(file);
};

const triggerFileInput = () => fileInput.value.click();

const toggleStack = (id) => {
    const idx = form.stack_ids.indexOf(id);
    if (idx === -1) form.stack_ids.push(id);
    else form.stack_ids.splice(idx, 1);
};

const isStackSelected = (id) => form.stack_ids.includes(id);

const submit = () => {
    if (isEditing.value) {
        form.post(route('admin.projects.update', props.project.id), {
            preserveScroll: true,
            forceFormData: true,
        });
    } else {
        form.post(route('admin.projects.store'), {
            preserveScroll: true,
            forceFormData: true,
        });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Projeto' : 'Novo Projeto'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.projects.index')"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white ring-1 ring-inset ring-slate-200 text-slate-600 hover:bg-slate-50"
                    aria-label="Voltar"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ isEditing ? 'Editar Projeto' : 'Novo Projeto' }}
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <form @submit.prevent="submit">
                <div class="shadow-sm sm:overflow-hidden sm:rounded-2xl border border-slate-100 bg-white">
                    <div class="space-y-6 px-4 py-6 sm:p-8">
                        <!-- Image -->
                        <div>
                            <InputLabel value="Imagem do Projeto" />
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
                            <!-- Title -->
                            <div class="sm:col-span-2">
                                <InputLabel for="title" value="Título" />
                                <TextInput
                                    id="title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.title"
                                    required
                                    placeholder="Ex: Portfolio Pessoal"
                                />
                                <InputError class="mt-2" :message="form.errors.title" />
                            </div>

                            <!-- Description -->
                            <div class="sm:col-span-2">
                                <InputLabel for="description" value="Descrição" />
                                <textarea
                                    id="description"
                                    rows="4"
                                    class="mt-1 block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    v-model="form.description"
                                    required
                                    placeholder="Descreva o projeto..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <!-- Repository URL -->
                            <div class="sm:col-span-1">
                                <InputLabel for="repository_url" value="URL do Repositório" />
                                <TextInput
                                    id="repository_url"
                                    type="url"
                                    class="mt-1 block w-full"
                                    v-model="form.repository_url"
                                    placeholder="https://github.com/..."
                                />
                                <InputError class="mt-2" :message="form.errors.repository_url" />
                            </div>

                            <!-- Demo URL -->
                            <div class="sm:col-span-1">
                                <InputLabel for="demo_url" value="URL do Demo (opcional)" />
                                <TextInput
                                    id="demo_url"
                                    type="url"
                                    class="mt-1 block w-full"
                                    v-model="form.demo_url"
                                    placeholder="https://..."
                                />
                                <InputError class="mt-2" :message="form.errors.demo_url" />
                            </div>

                            <!-- Order -->
                            <div class="sm:col-span-1">
                                <InputLabel for="order" value="Ordem" />
                                <TextInput
                                    id="order"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                    v-model="form.order"
                                />
                                <InputError class="mt-2" :message="form.errors.order" />
                            </div>

                            <!-- Featured -->
                            <div class="sm:col-span-1 flex items-center gap-3 mt-7">
                                <input
                                    id="is_featured"
                                    type="checkbox"
                                    v-model="form.is_featured"
                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <InputLabel for="is_featured" value="Exibir em destaque na landing page" />
                            </div>
                        </div>

                        <!-- Stacks multi-select -->
                        <div>
                            <InputLabel value="Tecnologias usadas" />
                            <p class="text-xs text-slate-500 mt-1">Clique para adicionar/remover.</p>
                            <div v-if="stacks.length === 0" class="mt-3 text-sm text-slate-500">
                                Nenhuma tecnologia cadastrada.
                                <Link :href="route('admin.stacks.create')" class="text-indigo-600 hover:underline">Cadastre uma agora</Link>.
                            </div>
                            <div v-else class="mt-3 flex flex-wrap gap-2">
                                <button
                                    v-for="stack in stacks"
                                    :key="stack.id"
                                    type="button"
                                    @click="toggleStack(stack.id)"
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium ring-1 ring-inset transition-all',
                                        isStackSelected(stack.id)
                                            ? 'text-white shadow-sm'
                                            : 'bg-white text-slate-700 ring-slate-200 hover:bg-slate-50',
                                    ]"
                                    :style="isStackSelected(stack.id) ? { backgroundColor: stack.color || '#6366f1', borderColor: stack.color || '#6366f1' } : {}"
                                >
                                    <span>{{ stack.name }}</span>
                                </button>
                            </div>
                            <InputError class="mt-2" :message="form.errors.stack_ids" />
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-x-3 bg-slate-50 px-4 py-4 sm:px-8 border-t border-slate-100">
                        <Link
                            :href="route('admin.projects.index')"
                            class="inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton
                            :disabled="form.processing"
                            class="!rounded-xl px-6 py-2.5 shadow-sm hover:shadow bg-indigo-600 hover:bg-indigo-500 transition-all"
                        >
                            {{ isEditing ? 'Salvar Alterações' : 'Criar Projeto' }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
