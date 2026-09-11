<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <GuestLayout>
        <Head title="Login" />

        <div class="mb-8 border-b border-[var(--line)] pb-6">
            <p class="technical-label mb-3 text-[var(--accent)]">Authentication / 01</p>
            <h2 class="text-4xl font-semibold leading-none tracking-[-0.055em]">Entrar</h2>
            <p class="mt-3 text-sm leading-6 text-[var(--muted)]">
                Acesse o painel administrativo do portfolio.
            </p>
        </div>

        <div v-if="status" class="mb-5 border border-emerald-600 bg-emerald-500/10 px-4 py-3 font-mono text-[10px] font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
            {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-2 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="admin@example.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Senha" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-2 block w-full"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between gap-4 border-y border-[var(--line)] py-4">
                <label class="flex items-center gap-2">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="font-mono text-[10px] font-semibold uppercase tracking-wider text-[var(--muted)]">
                        Manter conectado
                    </span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="font-mono text-[9px] font-semibold uppercase tracking-wider text-[var(--accent)] underline underline-offset-4"
                >
                    Recuperar senha
                </Link>
            </div>

            <PrimaryButton
                class="w-full justify-between"
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
                <span>{{ form.processing ? 'Autenticando...' : 'Entrar no painel' }}</span>
                <span aria-hidden="true">→</span>
            </PrimaryButton>
        </form>

        <p class="technical-label mt-7 text-center">Protected workspace · Authorized access only</p>
    </GuestLayout>
</template>
