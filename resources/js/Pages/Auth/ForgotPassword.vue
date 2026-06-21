<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar senha" />

        <div class="pixel-login__heading">
            <span>RECOVERY_01</span>
            <h1>Recuperar senha</h1>
            <p>
                Informe seu e-mail e enviaremos um link seguro para cadastrar
                uma nova senha.
            </p>
        </div>

        <div
            v-if="status"
            class="pixel-login__status"
        >
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

            <PrimaryButton
                class="pixel-login__submit"
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
                <span>{{ form.processing ? 'ENVIANDO...' : 'ENVIAR LINK DE ACESSO' }}</span>
                <b aria-hidden="true">▶</b>
            </PrimaryButton>
        </form>

        <p class="pixel-login__note">
            <span>!</span>
            Verifique também a caixa de spam após solicitar o link.
        </p>
    </GuestLayout>
</template>
