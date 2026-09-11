<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useTranslatable } from '@/Composables/useTranslatable';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    skills: { type: Array, required: true },
});

const { tr } = useTranslatable();
const page = usePage();
const selectedSkill = ref(null);
const deleteForm = useForm({});
const categories = { technical: 'Técnica', interpersonal: 'Interpessoal' };

function confirmDelete(skill) {
    deleteForm.clearErrors();
    selectedSkill.value = skill;
}

function closeDelete() {
    if (!deleteForm.processing) selectedSkill.value = null;
}

function destroy() {
    if (!selectedSkill.value || deleteForm.processing) return;

    deleteForm.delete(route('admin.skills.destroy', selectedSkill.value.id), {
        preserveScroll: true,
        onSuccess: () => { selectedSkill.value = null; },
    });
}
</script>

<template>
    <Head title="Habilidades" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="technical-label mb-2 text-[var(--accent)]">09 / Competências</p>
                    <h2 class="text-3xl font-semibold tracking-[-0.05em]">Habilidades</h2>
                </div>
                <Link
                    :href="route('admin.skills.create')"
                    class="inline-flex min-h-11 items-center gap-4 border border-[var(--accent)] bg-[var(--accent)] px-5 font-mono text-[10px] font-bold uppercase tracking-wider text-white transition-colors hover:bg-[var(--ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[var(--accent)]"
                >
                    <span aria-hidden="true" class="text-xl">+</span>
                    Adicionar habilidade
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div v-if="page.props.flash?.success" role="status" class="border border-[var(--accent)] bg-[var(--paper-raised)] px-5 py-4 text-sm text-[var(--ink)]">
                <span aria-hidden="true" class="mr-2 text-[var(--accent)]">✓</span>{{ page.props.flash.success }}
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="max-w-xl text-sm leading-6 text-[var(--muted)]">Apresente suas competências técnicas e interpessoais. A ordem de exibição começa pelos menores números.</p>
                <p class="technical-label">{{ skills.length }} {{ skills.length === 1 ? 'habilidade cadastrada' : 'habilidades cadastradas' }}</p>
            </div>

            <section class="border border-[var(--line)] bg-[var(--paper-raised)]" aria-label="Habilidades cadastradas">
                <div v-if="skills.length === 0" class="px-6 py-16 text-center sm:py-24">
                    <div class="blueprint-grid mx-auto mb-7 grid h-16 w-16 place-items-center border border-[var(--ink)] font-mono text-2xl text-white shadow-[5px_5px_0_var(--line)]" aria-hidden="true">H</div>
                    <h3 class="text-2xl font-semibold tracking-[-0.04em]">Quais habilidades fazem parte do seu trabalho?</h3>
                    <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-[var(--muted)]">Adicione uma competência e conte como você a aplica.</p>
                    <Link :href="route('admin.skills.create')" class="admin-quiet-button mt-7 !min-h-11">Adicionar primeira habilidade →</Link>
                </div>

                <div v-else>
                    <article
                        v-for="skill in skills"
                        :key="skill.id"
                        class="grid grid-cols-[44px_minmax(0,1fr)] gap-4 border-b border-[var(--line)] p-4 last:border-b-0 sm:grid-cols-[56px_minmax(0,1fr)_auto] sm:items-center sm:gap-6 sm:p-6"
                    >
                        <div class="grid min-h-11 place-items-center border border-[var(--line)] bg-[var(--paper)] px-1 py-3 font-mono text-[10px] text-[var(--accent)]" :aria-label="`Ordem de exibição: ${skill.order}`">{{ String(skill.order).padStart(2, '0') }}</div>
                        <div class="min-w-0">
                            <div class="mb-2 flex flex-wrap items-center gap-x-3 gap-y-1 font-mono text-[9px] font-bold uppercase tracking-wider">
                                <span class="text-[var(--accent)]">{{ categories[skill.category] }}</span>
                                <span class="inline-flex items-center gap-1.5 text-[var(--muted)]">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="skill.is_visible ? 'bg-[var(--accent)]' : 'bg-[var(--muted)]'" aria-hidden="true"></span>
                                    {{ skill.is_visible ? 'Visível no site' : 'Oculta' }}
                                </span>
                            </div>
                            <h3 class="break-words text-lg font-semibold leading-snug tracking-[-0.035em]">{{ tr(skill.title) }}</h3>
                            <p v-if="tr(skill.description)" class="mt-2 line-clamp-2 break-words text-sm leading-6 text-[var(--muted)]">{{ tr(skill.description) }}</p>
                        </div>
                        <div class="col-span-2 flex flex-wrap gap-2 sm:col-span-1 sm:justify-end">
                            <Link :href="route('admin.skills.edit', skill.id)" class="admin-quiet-button !min-h-11" :aria-label="`Editar ${tr(skill.title)}`">Editar</Link>
                            <button type="button" class="admin-quiet-button !min-h-11 !text-red-600" :aria-label="`Excluir ${tr(skill.title)}`" @click="confirmDelete(skill)">Excluir</button>
                        </div>
                    </article>
                </div>
            </section>
        </div>

        <Modal :show="selectedSkill !== null" max-width="lg" :closeable="!deleteForm.processing" aria-labelledby="delete-skill-title" aria-describedby="delete-skill-description" @close="closeDelete">
            <div class="p-6 sm:p-8">
                <p class="technical-label mb-4 text-[var(--accent)]">Habilidades / Excluir</p>
                <h2 id="delete-skill-title" class="text-2xl font-semibold tracking-[-0.04em]">Excluir esta habilidade?</h2>
                <p id="delete-skill-description" class="mt-4 break-words text-sm leading-6 text-[var(--muted)]">“{{ tr(selectedSkill?.title) }}” será removida do portfolio. Esta ação não pode ser desfeita.</p>
                <p v-if="deleteForm.hasErrors" role="alert" class="mt-4 text-sm text-red-600">{{ Object.values(deleteForm.errors)[0] }}</p>
                <div class="mt-7 flex flex-wrap justify-end gap-3">
                    <button type="button" class="admin-quiet-button !min-h-11 disabled:opacity-50" :disabled="deleteForm.processing" autofocus @click="closeDelete">Cancelar</button>
                    <button type="button" class="min-h-11 border border-red-600 bg-red-600 px-4 font-mono text-[10px] font-bold uppercase tracking-wider text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50" :disabled="deleteForm.processing" @click="destroy">{{ deleteForm.processing ? 'Excluindo…' : 'Excluir habilidade' }}</button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
