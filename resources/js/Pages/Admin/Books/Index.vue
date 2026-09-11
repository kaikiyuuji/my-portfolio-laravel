<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    books: { type: Object, required: true },
});

const page = usePage();
const items = computed(() => props.books.data ?? []);
const selectedBook = ref(null);
const deleteForm = useForm({});
const categories = { academic: 'Acadêmico', personal: 'Pessoal' };

function confirmDelete(book) {
    deleteForm.clearErrors();
    selectedBook.value = book;
}

function closeDelete() {
    if (!deleteForm.processing) selectedBook.value = null;
}

function destroy() {
    if (!selectedBook.value || deleteForm.processing) return;

    deleteForm.delete(route('admin.books.destroy', selectedBook.value.id), {
        preserveScroll: true,
        onSuccess: () => { selectedBook.value = null; },
    });
}
</script>

<template>
    <Head title="Livros" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="technical-label mb-2 text-[var(--accent)]">08 / Biblioteca</p>
                    <h2 class="text-3xl font-semibold tracking-[-0.05em]">Livros</h2>
                </div>
                <Link
                    :href="route('admin.books.create')"
                    class="inline-flex min-h-11 items-center gap-4 border border-[var(--accent)] bg-[var(--accent)] px-5 font-mono text-[10px] font-bold uppercase tracking-wider text-white transition-colors hover:bg-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[var(--accent)]"
                >
                    <span aria-hidden="true" class="text-xl">+</span>
                    Adicionar livro
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div v-if="page.props.flash?.success" role="status" class="border border-[var(--accent)] bg-[var(--paper-raised)] px-5 py-4 text-sm text-[var(--ink)]">
                <span aria-hidden="true" class="mr-2 text-[var(--accent)]">✓</span>{{ page.props.flash.success }}
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="max-w-xl text-sm leading-6 text-[var(--muted)]">Organize suas referências acadêmicas e leituras pessoais em duas coleções.</p>
                <p class="technical-label">{{ books.total }} {{ books.total === 1 ? 'livro cadastrado' : 'livros cadastrados' }}</p>
            </div>

            <section class="border border-[var(--line)] bg-[var(--paper-raised)]" aria-label="Livros cadastrados">
                <div v-if="items.length === 0" class="px-6 py-16 text-center sm:py-24">
                    <div class="blueprint-grid mx-auto mb-7 grid h-20 w-16 place-items-center border border-[var(--ink)] text-white shadow-[5px_5px_0_var(--line)]" aria-hidden="true">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4">
                            <path d="M5 4h14v17H6a2 2 0 0 1-2-2V5a1 1 0 0 1 1-1Zm0 13h14M8 7h7M8 10h5" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold tracking-[-0.04em]">Sua biblioteca começa aqui.</h3>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-[var(--muted)]">Adicione o título, o autor e a capa do primeiro livro. Você escolhe em qual coleção ele aparece.</p>
                    <Link :href="route('admin.books.create')" class="admin-quiet-button mt-7 !min-h-11">Adicionar primeiro livro →</Link>
                </div>

                <div v-else>
                    <article
                        v-for="book in items"
                        :key="book.id"
                        class="grid grid-cols-[56px_minmax(0,1fr)] gap-4 border-b border-[var(--line)] p-4 last:border-b-0 sm:grid-cols-[64px_minmax(0,1fr)_auto] sm:items-center sm:gap-6 sm:p-6"
                    >
                        <div class="aspect-[2/3] w-14 overflow-hidden border border-[var(--line)] bg-[var(--paper)] sm:w-16">
                            <img v-if="book.cover_url" :src="book.cover_url" :alt="`Capa de ${book.title}`" class="h-full w-full object-cover" loading="lazy" />
                            <div v-else class="dot-field-muted flex h-full items-center justify-center font-mono text-xl text-[var(--accent)]" aria-label="Sem capa">
                                {{ book.title.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap items-center gap-x-3 gap-y-1 font-mono text-[9px] font-bold uppercase tracking-wider">
                                <span class="text-[var(--accent)]">{{ categories[book.category] }}</span>
                                <span class="inline-flex items-center gap-1.5 text-[var(--muted)]">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="book.is_published ? 'bg-[var(--accent)]' : 'bg-[var(--muted)]'" aria-hidden="true"></span>
                                    {{ book.is_published ? 'Publicado' : 'Rascunho' }}
                                </span>
                            </div>
                            <h3 class="break-words text-lg font-semibold leading-snug tracking-[-0.035em]">{{ book.title }}</h3>
                            <p class="mt-1 break-words text-sm text-[var(--muted)]">{{ book.author }}</p>
                        </div>
                        <div class="col-span-2 flex flex-wrap gap-2 sm:col-span-1 sm:justify-end">
                            <Link :href="route('admin.books.edit', book.id)" class="admin-quiet-button !min-h-11" :aria-label="`Editar ${book.title}`">Editar</Link>
                            <button type="button" class="admin-quiet-button !min-h-11 !text-red-600" :aria-label="`Excluir ${book.title}`" @click="confirmDelete(book)">Excluir</button>
                        </div>
                    </article>
                </div>

                <nav v-if="books.last_page > 1" aria-label="Paginação dos livros" class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--line)] p-4 sm:px-6">
                    <p class="font-mono text-[10px] text-[var(--muted)]">Página {{ books.current_page }} de {{ books.last_page }}</p>
                    <div class="flex gap-2">
                        <Link v-if="books.prev_page_url" :href="books.prev_page_url" class="admin-quiet-button !min-h-11" preserve-scroll>← Anterior</Link>
                        <span v-else class="admin-quiet-button !min-h-11 opacity-40" aria-disabled="true">← Anterior</span>
                        <Link v-if="books.next_page_url" :href="books.next_page_url" class="admin-quiet-button !min-h-11" preserve-scroll>Próxima →</Link>
                        <span v-else class="admin-quiet-button !min-h-11 opacity-40" aria-disabled="true">Próxima →</span>
                    </div>
                </nav>
            </section>
        </div>

        <Modal :show="selectedBook !== null" max-width="lg" :closeable="!deleteForm.processing" aria-labelledby="delete-book-title" aria-describedby="delete-book-description" @close="closeDelete">
            <div class="p-6 sm:p-8">
                <p class="technical-label mb-4 text-[var(--accent)]">Biblioteca / Excluir</p>
                <h2 id="delete-book-title" class="text-2xl font-semibold tracking-[-0.04em]">Excluir este livro?</h2>
                <p id="delete-book-description" class="mt-4 break-words text-sm leading-6 text-[var(--muted)]">“{{ selectedBook?.title }}” e sua capa serão removidos da biblioteca. Esta ação não pode ser desfeita.</p>
                <p v-if="deleteForm.hasErrors" role="alert" class="mt-4 text-sm text-red-600">{{ Object.values(deleteForm.errors)[0] }}</p>
                <div class="mt-7 flex flex-wrap justify-end gap-3">
                    <button type="button" class="admin-quiet-button !min-h-11 disabled:opacity-50" :disabled="deleteForm.processing" autofocus @click="closeDelete">Cancelar</button>
                    <button type="button" class="min-h-11 border border-red-600 bg-red-600 px-4 font-mono text-[10px] font-bold uppercase tracking-wider text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50" :disabled="deleteForm.processing" @click="destroy">{{ deleteForm.processing ? 'Excluindo…' : 'Excluir livro' }}</button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
