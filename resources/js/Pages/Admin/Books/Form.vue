<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    book: { type: Object, default: null },
});

const isEditing = computed(() => props.book !== null);
const fileInput = ref(null);
const temporaryCover = ref(null);
const clientCoverError = ref('');
const form = useForm({
    _method: isEditing.value ? 'put' : 'post',
    title: props.book?.title ?? '',
    author: props.book?.author ?? '',
    category: props.book?.category ?? 'academic',
    description: props.book?.description ?? '',
    purchase_url: props.book?.purchase_url ?? '',
    cover: null,
    remove_cover: false,
    is_published: props.book?.is_published ?? true,
});

const coverPreview = computed(() => temporaryCover.value || (!form.remove_cover ? props.book?.cover_url : null));

function releaseTemporaryCover() {
    if (temporaryCover.value) URL.revokeObjectURL(temporaryCover.value);
    temporaryCover.value = null;
}

function handleCoverChange(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    releaseTemporaryCover();
    form.cover = null;
    clientCoverError.value = '';
    form.clearErrors('cover');
    event.target.value = '';
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        clientCoverError.value = 'Escolha uma imagem JPG, PNG ou WebP.';
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        clientCoverError.value = 'A imagem da capa deve ter no máximo 5 MB.';
        return;
    }

    form.cover = file;
    form.remove_cover = false;
    temporaryCover.value = URL.createObjectURL(file);
}

function discardSelection() {
    releaseTemporaryCover();
    form.cover = null;
    clientCoverError.value = '';
    form.clearErrors('cover');
    if (fileInput.value) fileInput.value.value = '';
}

function removeCover() {
    discardSelection();
    form.remove_cover = Boolean(props.book?.cover_url);
    form.clearErrors('remove_cover');
}

function submit() {
    if (form.processing || clientCoverError.value) return;
    form.post(isEditing.value ? route('admin.books.update', props.book.id) : route('admin.books.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
}

onBeforeUnmount(releaseTemporaryCover);
</script>

<template>
    <Head :title="isEditing ? 'Editar livro' : 'Adicionar livro'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.books.index')" class="admin-quiet-button !h-11 !w-11 !p-0 text-lg" aria-label="Voltar para os livros">←</Link>
                <div>
                    <p class="technical-label mb-2 text-[var(--accent)]">08 / Biblioteca</p>
                    <h2 class="text-3xl font-semibold tracking-[-0.05em]">{{ isEditing ? 'Editar livro' : 'Adicionar livro' }}</h2>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <form class="border border-[var(--line)] bg-[var(--paper-raised)]" :aria-busy="form.processing" @submit.prevent="submit">
                <fieldset :disabled="form.processing" class="grid min-w-0 lg:grid-cols-[300px_minmax(0,1fr)]">
                    <legend class="sr-only">Dados do livro</legend>

                    <div class="border-b border-[var(--line)] bg-[var(--paper)] p-5 sm:p-8 lg:border-b-0 lg:border-r">
                        <InputLabel for="cover" value="Capa do livro" />
                        <div class="mx-auto my-7 aspect-[2/3] w-44 overflow-hidden border border-[var(--line)] bg-[var(--paper-raised)] shadow-[6px_6px_0_var(--line)]">
                            <img v-if="coverPreview" :src="coverPreview" alt="Prévia da capa do livro" class="h-full w-full object-contain" />
                            <div v-else class="dot-field-muted flex h-full flex-col justify-between border-l-8 border-[var(--accent)] p-5">
                                <span class="font-mono text-[9px] uppercase tracking-widest text-[var(--accent)]">{{ form.category === 'academic' ? 'Acadêmico' : 'Pessoal' }}</span>
                                <span class="line-clamp-5 break-words text-xl font-semibold leading-tight tracking-[-0.04em]">{{ form.title || 'Seu próximo livro' }}</span>
                                <span class="line-clamp-2 break-words text-xs text-[var(--muted)]">{{ form.author || 'Nome do autor' }}</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap justify-center gap-2">
                            <button type="button" class="admin-quiet-button !min-h-11" @click="fileInput?.click()">{{ coverPreview ? 'Trocar capa' : 'Escolher capa' }}</button>
                            <button v-if="coverPreview" type="button" class="admin-quiet-button !min-h-11 !text-red-600" @click="removeCover">Remover</button>
                            <button v-else-if="form.remove_cover" type="button" class="admin-quiet-button !min-h-11" @click="form.remove_cover = false">Restaurar capa</button>
                            <button v-if="clientCoverError" type="button" class="admin-quiet-button !min-h-11" @click="discardSelection">Descartar seleção</button>
                        </div>
                        <input id="cover" ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" tabindex="-1" aria-describedby="cover-hint cover-error" :aria-invalid="Boolean(clientCoverError || form.errors.cover)" @change="handleCoverChange" />
                        <p id="cover-hint" class="mt-4 text-center text-xs leading-5 text-[var(--muted)]">JPG, PNG ou WebP, até 5 MB.<br />Uma imagem vertical funciona melhor.</p>
                        <p v-if="form.cover" class="mt-3 break-all text-center text-xs text-[var(--muted)]">{{ form.cover.name }}</p>
                        <p v-if="form.remove_cover" class="mt-3 text-center text-xs leading-5 text-[var(--muted)]">A capa será removida ao salvar.</p>
                        <InputError id="cover-error" class="mt-3" :message="clientCoverError || form.errors.cover" />
                        <InputError class="mt-2" :message="form.errors.remove_cover" />
                        <p class="mt-7 border-t border-[var(--line)] pt-5 text-xs leading-6 text-[var(--muted)]">A capa é opcional. Sem imagem, o livro recebe uma capa com o título e o autor.</p>
                    </div>

                    <div class="min-w-0 space-y-6 p-5 sm:p-8">
                        <div>
                            <InputLabel for="title" value="Nome do livro *" />
                            <TextInput id="title" v-model="form.title" class="mt-2 block w-full" maxlength="255" required placeholder="Ex.: Engenharia de Software" :aria-invalid="Boolean(form.errors.title)" aria-describedby="title-error" />
                            <InputError id="title-error" class="mt-2" :message="form.errors.title" />
                        </div>
                        <div>
                            <InputLabel for="author" value="Autor *" />
                            <TextInput id="author" v-model="form.author" class="mt-2 block w-full" maxlength="255" required placeholder="Nome do autor ou dos autores" :aria-invalid="Boolean(form.errors.author)" aria-describedby="author-error" />
                            <InputError id="author-error" class="mt-2" :message="form.errors.author" />
                        </div>
                        <div>
                            <InputLabel for="category" value="Coleção *" />
                            <select id="category" v-model="form.category" required class="mt-2 block min-h-11 w-full border border-[var(--line)] bg-[var(--paper)] text-sm text-[var(--ink)]" :aria-invalid="Boolean(form.errors.category)" aria-describedby="category-hint category-error">
                                <option value="academic">Acadêmicos — estudo e referências</option>
                                <option value="personal">Pessoais — leituras por prazer</option>
                            </select>
                            <p id="category-hint" class="mt-2 text-xs leading-5 text-[var(--muted)]">Define em qual estante o livro aparece na biblioteca.</p>
                            <InputError id="category-error" class="mt-2" :message="form.errors.category" />
                        </div>
                        <div>
                            <InputLabel for="purchase_url" value="Link de compra (opcional)" />
                            <TextInput id="purchase_url" v-model="form.purchase_url" type="url" inputmode="url" class="mt-2 block w-full" maxlength="2048" placeholder="https://loja.com/livro" :aria-invalid="Boolean(form.errors.purchase_url)" aria-describedby="purchase-url-hint purchase-url-error" />
                            <p id="purchase-url-hint" class="mt-2 text-xs leading-5 text-[var(--muted)]">Use um endereço com https:// ou http://. Deixe em branco para ocultar o botão de compra.</p>
                            <InputError id="purchase-url-error" class="mt-2" :message="form.errors.purchase_url" />
                        </div>
                        <div>
                            <InputLabel for="description" value="Sobre esta leitura (opcional)" />
                            <textarea id="description" v-model="form.description" rows="5" maxlength="5000" class="mt-2 block w-full resize-y border border-[var(--line)] bg-[var(--paper)] text-sm leading-6 text-[var(--ink)]" placeholder="O que torna este livro especial para você?" :aria-invalid="Boolean(form.errors.description)" aria-describedby="description-hint description-error"></textarea>
                            <p id="description-hint" class="mt-2 text-xs leading-5 text-[var(--muted)]">Seu comentário aparece nos detalhes do livro. Até 5.000 caracteres.</p>
                            <InputError id="description-error" class="mt-2" :message="form.errors.description" />
                        </div>
                        <div class="border-t border-[var(--line)] pt-6">
                            <label for="is_published" class="flex cursor-pointer items-start gap-3 py-1">
                                <input id="is_published" v-model="form.is_published" type="checkbox" class="mt-0.5 h-5 w-5 shrink-0 border-[var(--line)] text-[var(--accent)] focus:ring-[var(--accent)]" aria-describedby="publish-hint publish-error" />
                                <span>
                                    <span class="block text-sm font-semibold">Publicar na biblioteca</span>
                                    <span id="publish-hint" class="mt-1 block text-xs leading-5 text-[var(--muted)]">Quando marcado, o livro fica visível no site. Desmarque para salvá-lo como rascunho.</span>
                                </span>
                            </label>
                            <InputError id="publish-error" class="mt-2" :message="form.errors.is_published" />
                        </div>
                    </div>
                </fieldset>

                <div v-if="form.progress" class="border-t border-[var(--line)] px-5 py-4 sm:px-8" role="status">
                    <div class="mb-2 flex justify-between gap-3 font-mono text-[10px] text-[var(--muted)]"><span>Enviando livro…</span><span>{{ form.progress.percentage }}%</span></div>
                    <progress :value="form.progress.percentage" max="100" class="h-1.5 w-full accent-[var(--accent)]" aria-label="Progresso do envio">{{ form.progress.percentage }}%</progress>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-[var(--line)] bg-[var(--paper)] p-5 sm:px-8">
                    <p class="mr-auto text-xs text-[var(--muted)]">* Campos obrigatórios</p>
                    <Link v-if="!form.processing" :href="route('admin.books.index')" class="admin-quiet-button !min-h-11">Cancelar</Link>
                    <PrimaryButton type="submit" :disabled="form.processing || Boolean(clientCoverError)">{{ form.processing ? 'Salvando…' : isEditing ? 'Salvar alterações' : 'Adicionar livro' }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
