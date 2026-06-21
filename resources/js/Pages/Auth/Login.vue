<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Entrar" />

        <div class="pixel-login__heading">
            <span>AUTH_01</span>
            <h1>Acesso administrativo</h1>
            <p>Identifique-se para carregar o painel de controle.</p>
        </div>

        <div v-if="status" class="pixel-login__status">
            <span>●</span>
            {{ status }}
        </div>

        <form class="pixel-login__form" @submit.prevent="submit">
            <div class="pixel-login__field">
                <InputLabel for="email" value="E-mail" />
                <div class="pixel-login__input-wrap">
                    <span aria-hidden="true">@</span>
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@portfolio.com"
                    />
                </div>
                <InputError :message="form.errors.email" />
            </div>

            <div class="pixel-login__field">
                <InputLabel for="password" value="Senha" />
                <div class="pixel-login__input-wrap">
                    <span aria-hidden="true">◆</span>
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                </div>
                <InputError :message="form.errors.password" />
            </div>

            <div class="pixel-login__options">
                <label>
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span>Lembrar de mim</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="pixel-login__forgot"
                >
                    Esqueceu a senha?
                </Link>
            </div>

            <PrimaryButton
                class="pixel-login__submit"
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
                <span>{{ form.processing ? 'CARREGANDO...' : 'ENTRAR NO SISTEMA' }}</span>
                <b aria-hidden="true">▶</b>
            </PrimaryButton>
        </form>

        <p class="pixel-login__note">
            <span>!</span>
            Área restrita ao administrador do portfolio.
        </p>
    </GuestLayout>
</template>
