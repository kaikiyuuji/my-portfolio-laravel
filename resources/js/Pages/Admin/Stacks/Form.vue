<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    stack: {
        type: Object,
        default: null,
    },
});

const isEditing = computed(() => props.stack !== null);
const iconFailed = ref(false);

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

const iconUrl = computed(() =>
    form.icon_slug ? `https://cdn.simpleicons.org/${form.icon_slug}/${iconColor(form.color)}` : null
);

const form = useForm({
    name: props.stack?.name ?? '',
    icon_slug: props.stack?.icon_slug ?? '',
    color: props.stack?.color ?? '#6366f1',
    order: props.stack?.order ?? 0,
    is_featured: props.stack?.is_featured ?? false,
});

watch([() => form.icon_slug, () => form.color], () => {
    iconFailed.value = false;
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.stacks.update', props.stack.id), { preserveScroll: true });
    } else {
        form.post(route('admin.stacks.store'), { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Tecnologia' : 'Nova Tecnologia'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.stacks.index')"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white ring-1 ring-inset ring-slate-200 text-slate-600 hover:bg-slate-50"
                    aria-label="Voltar"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ isEditing ? 'Editar Tecnologia' : 'Nova Tecnologia' }}
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form @submit.prevent="submit">
                <div class="shadow-sm sm:overflow-hidden sm:rounded-2xl border border-slate-100 bg-white">
                    <div class="space-y-6 px-4 py-6 sm:p-8">
                        <!-- Preview -->
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <div
                                class="w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold shadow-sm overflow-hidden"
                                :style="{ backgroundColor: form.color || '#64748b' }"
                            >
                                <img
                                    v-if="iconUrl && !iconFailed"
                                    :src="iconUrl"
                                    :alt="form.name"
                                    class="w-8 h-8 object-contain"
                                    @error="iconFailed = true"
                                />
                                <span v-else>{{ (form.name || '?').charAt(0).toUpperCase() }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ form.name || 'Pré-visualização' }}</p>
                                <p class="text-xs text-slate-500 font-mono">{{ form.icon_slug || 'icon-slug' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Name -->
                            <div class="sm:col-span-1">
                                <InputLabel for="name" value="Nome" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    placeholder="Ex: Laravel"
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <!-- Icon Slug -->
                            <div class="sm:col-span-1">
                                <InputLabel for="icon_slug" value="Icon Slug (Simple Icons)" />
                                <TextInput
                                    id="icon_slug"
                                    type="text"
                                    class="mt-1 block w-full font-mono"
                                    v-model="form.icon_slug"
                                    required
                                    placeholder="laravel"
                                />
                                <p class="mt-1 text-xs text-slate-500">
                                    Slug do
                                    <a href="https://simpleicons.org/" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">Simple Icons</a>.
                                </p>
                                <InputError class="mt-2" :message="form.errors.icon_slug" />
                            </div>

                            <!-- Color -->
                            <div class="sm:col-span-1">
                                <InputLabel for="color" value="Cor (hex)" />
                                <div class="mt-1 flex items-center gap-2">
                                    <input
                                        id="color-picker"
                                        type="color"
                                        v-model="form.color"
                                        class="h-10 w-12 rounded-lg border border-slate-300 cursor-pointer"
                                    />
                                    <TextInput
                                        id="color"
                                        type="text"
                                        class="block w-full font-mono"
                                        v-model="form.color"
                                        placeholder="#FF2D20"
                                    />
                                </div>
                                <InputError class="mt-2" :message="form.errors.color" />
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
                        </div>

                        <!-- Featured -->
                        <div class="flex items-center gap-3">
                            <input
                                id="is_featured"
                                type="checkbox"
                                v-model="form.is_featured"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <InputLabel for="is_featured" value="Exibir em destaque na landing page" />
                            <InputError class="ml-2" :message="form.errors.is_featured" />
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-x-3 bg-slate-50 px-4 py-4 sm:px-8 border-t border-slate-100">
                        <Link
                            :href="route('admin.stacks.index')"
                            class="inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton
                            :disabled="form.processing"
                            class="!rounded-xl px-6 py-2.5 shadow-sm hover:shadow bg-indigo-600 hover:bg-indigo-500 transition-all"
                        >
                            {{ isEditing ? 'Salvar Alterações' : 'Criar Tecnologia' }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
