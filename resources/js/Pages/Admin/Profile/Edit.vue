<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    _method: 'put',
    name: props.profile.name,
    headline: props.profile.headline,
    bio: props.profile.bio || '',
    email: props.profile.email,
    avatar: null,
    resume_url: props.profile.resume_url || '',
});

const avatarPreview = ref(props.profile.avatar_path ? '/storage/' + props.profile.avatar_path : null);
const fileInput = ref(null);

const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    form.avatar = file;
    if (file) {
        avatarPreview.value = URL.createObjectURL(file);
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const submit = () => {
    form.post(route('admin.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: trigger a success toast here
        },
    });
};
</script>

<template>
    <Head title="Gerenciar Portfolio" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                Informações do Portfolio
            </h2>
        </template>

        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-8">
                <!-- Left column: Info -->
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-semibold leading-6 text-slate-900">Perfil Público</h3>
                        <p class="mt-1 text-sm text-slate-600">
                            Estas informações serão exibidas na sua landing page. Mantenha seu título e resumo atualizados para atrair melhores oportunidades.
                        </p>
                    </div>
                </div>
                
                <!-- Right column: Form -->
                <div class="mt-5 md:col-span-2 md:mt-0">
                    <form @submit.prevent="submit">
                        <div class="shadow-sm sm:overflow-hidden sm:rounded-2xl border border-slate-100 bg-white">
                            <div class="space-y-6 px-4 py-6 sm:p-8">
                                
                                <!-- Avatar Upload (Premium style) -->
                                <div>
                                    <InputLabel value="Foto de Perfil" />
                                    <div class="mt-3 flex items-center gap-6">
                                        <div class="relative group cursor-pointer" @click="triggerFileInput">
                                            <div class="h-24 w-24 rounded-full overflow-hidden ring-4 ring-slate-50 flex items-center justify-center bg-slate-100 group-hover:ring-indigo-100 transition-all">
                                                <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar" class="h-full w-full object-cover" />
                                                <svg v-else class="h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" @click="triggerFileInput" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors">
                                                Alterar imagem
                                            </button>
                                            <p class="mt-2 text-xs leading-5 text-slate-500">JPG, PNG ou GIF. Máximo 2MB.</p>
                                        </div>
                                        <input ref="fileInput" type="file" class="hidden" @change="handleAvatarChange" accept="image/*" />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.avatar" />
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <!-- Name -->
                                    <div class="sm:col-span-1">
                                        <InputLabel for="name" value="Nome Completo" />
                                        <TextInput id="name" type="text" class="mt-1 block w-full transition-shadow hover:shadow-sm focus:ring-indigo-500" v-model="form.name" required />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>

                                    <!-- Headline -->
                                    <div class="sm:col-span-1">
                                        <InputLabel for="headline" value="Título Profissional (Ex: Full Stack Developer)" />
                                        <TextInput id="headline" type="text" class="mt-1 block w-full transition-shadow hover:shadow-sm" v-model="form.headline" required />
                                        <InputError class="mt-2" :message="form.errors.headline" />
                                    </div>

                                    <!-- Email -->
                                    <div class="sm:col-span-1">
                                        <InputLabel for="email" value="E-mail Público" />
                                        <TextInput id="email" type="email" class="mt-1 block w-full transition-shadow hover:shadow-sm" v-model="form.email" required />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <!-- Resume URL -->
                                    <div class="sm:col-span-1">
                                        <InputLabel for="resume_url" value="URL do Currículo (Opcional)" />
                                        <TextInput id="resume_url" type="url" class="mt-1 block w-full transition-shadow hover:shadow-sm" v-model="form.resume_url" placeholder="https://..." />
                                        <InputError class="mt-2" :message="form.errors.resume_url" />
                                    </div>
                                </div>

                                <!-- Bio -->
                                <div>
                                    <InputLabel for="bio" value="Resumo / Bio" />
                                    <div class="mt-1">
                                        <textarea id="bio" rows="5" class="block w-full rounded-xl border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-shadow hover:shadow-sm" v-model="form.bio" placeholder="Escreva um pouco sobre você..."></textarea>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.bio" />
                                </div>
                            </div>

                            <!-- Footer Actions -->
                            <div class="flex items-center justify-end gap-x-4 bg-slate-50 px-4 py-4 sm:px-8 border-t border-slate-100">
                                <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
                                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600 font-medium flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Salvo com sucesso
                                    </p>
                                </Transition>
                                <PrimaryButton :disabled="form.processing" class="!rounded-xl px-6 py-2.5 shadow-sm hover:shadow bg-indigo-600 hover:bg-indigo-500 transition-all">
                                    Salvar Alterações
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
