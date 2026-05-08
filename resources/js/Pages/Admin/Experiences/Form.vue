<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    experience: {
        type: Object,
        default: null,
    },
});

const isEditing = computed(() => props.experience !== null);

const toDateInput = (value) => (value ? String(value).slice(0, 10) : '');

const isCurrent = ref(props.experience ? !props.experience.end_date : false);

const form = useForm({
    company: props.experience?.company ?? '',
    role: props.experience?.role ?? '',
    description: props.experience?.description ?? '',
    start_date: toDateInput(props.experience?.start_date),
    end_date: toDateInput(props.experience?.end_date),
    order: props.experience?.order ?? 0,
});

watch(isCurrent, (value) => {
    if (value) form.end_date = '';
});

const submit = () => {
    const payload = { ...form.data() };
    if (isCurrent.value) payload.end_date = null;

    if (isEditing.value) {
        form.transform(() => payload).put(route('admin.experiences.update', props.experience.id), {
            preserveScroll: true,
        });
    } else {
        form.transform(() => payload).post(route('admin.experiences.store'), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Experiência' : 'Nova Experiência'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('admin.experiences.index')"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white ring-1 ring-inset ring-slate-200 text-slate-600 hover:bg-slate-50"
                    aria-label="Voltar"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ isEditing ? 'Editar Experiência' : 'Nova Experiência' }}
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <form @submit.prevent="submit">
                <div class="shadow-sm sm:overflow-hidden sm:rounded-2xl border border-slate-100 bg-white">
                    <div class="space-y-6 px-4 py-6 sm:p-8">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Company -->
                            <div class="sm:col-span-1">
                                <InputLabel for="company" value="Empresa" />
                                <TextInput
                                    id="company"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.company"
                                    required
                                    placeholder="Ex: Acme Corp"
                                />
                                <InputError class="mt-2" :message="form.errors.company" />
                            </div>

                            <!-- Role -->
                            <div class="sm:col-span-1">
                                <InputLabel for="role" value="Cargo" />
                                <TextInput
                                    id="role"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.role"
                                    required
                                    placeholder="Ex: Backend Developer"
                                />
                                <InputError class="mt-2" :message="form.errors.role" />
                            </div>

                            <!-- Start Date -->
                            <div class="sm:col-span-1">
                                <InputLabel for="start_date" value="Data de Início" />
                                <TextInput
                                    id="start_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.start_date"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.start_date" />
                            </div>

                            <!-- End Date -->
                            <div class="sm:col-span-1">
                                <InputLabel for="end_date" value="Data de Término" />
                                <TextInput
                                    id="end_date"
                                    type="date"
                                    class="mt-1 block w-full disabled:bg-slate-50 disabled:text-slate-400"
                                    v-model="form.end_date"
                                    :disabled="isCurrent"
                                />
                                <div class="mt-2 flex items-center gap-2">
                                    <input
                                        id="is_current"
                                        type="checkbox"
                                        v-model="isCurrent"
                                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <label for="is_current" class="text-sm text-slate-600">
                                        Emprego atual
                                    </label>
                                </div>
                                <InputError class="mt-2" :message="form.errors.end_date" />
                            </div>

                            <!-- Order -->
                            <div class="sm:col-span-2">
                                <InputLabel for="order" value="Ordem (manual)" />
                                <TextInput
                                    id="order"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                    v-model="form.order"
                                />
                                <p class="mt-1 text-xs text-slate-500">
                                    Complementar à data. A timeline ordena automaticamente por data de início (mais recente primeiro).
                                </p>
                                <InputError class="mt-2" :message="form.errors.order" />
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <InputLabel for="description" value="Descrição (opcional)" />
                            <textarea
                                id="description"
                                rows="5"
                                class="mt-1 block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                v-model="form.description"
                                placeholder="Atividades, conquistas, tecnologias usadas..."
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-x-3 bg-slate-50 px-4 py-4 sm:px-8 border-t border-slate-100">
                        <Link
                            :href="route('admin.experiences.index')"
                            class="inline-flex items-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </Link>
                        <PrimaryButton
                            :disabled="form.processing"
                            class="!rounded-xl px-6 py-2.5 shadow-sm hover:shadow bg-indigo-600 hover:bg-indigo-500 transition-all"
                        >
                            {{ isEditing ? 'Salvar Alterações' : 'Criar Experiência' }}
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
