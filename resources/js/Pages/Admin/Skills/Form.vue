<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    skill: { type: Object, default: null },
});

const isEditing = computed(() => props.skill !== null);
const languages = [{ code: 'pt', label: 'Português' }, { code: 'en', label: 'English' }];
const toTranslations = (value) => typeof value === 'object' && value !== null
    ? { pt: value.pt ?? '', en: value.en ?? '' }
    : { pt: value ?? '', en: '' };

const form = useForm({
    title: toTranslations(props.skill?.title),
    description: toTranslations(props.skill?.description),
    category: props.skill?.category ?? 'technical',
    order: props.skill?.order ?? 0,
    is_visible: props.skill?.is_visible ?? true,
});

function submit() {
    if (form.processing) return;

    if (isEditing.value) {
        form.put(route('admin.skills.update', props.skill.id), { preserveScroll: true });
    } else {
        form.post(route('admin.skills.store'), { preserveScroll: true });
    }
}
</script>

<template>
    <Head :title="isEditing ? 'Editar habilidade' : 'Adicionar habilidade'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.skills.index')" class="admin-quiet-button !h-11 !w-11 shrink-0 !p-0 text-lg" aria-label="Voltar para as habilidades">←</Link>
                <div>
                    <p class="technical-label mb-2 text-[var(--accent)]">09 / Competências</p>
                    <h2 class="text-3xl font-semibold tracking-[-0.05em]">{{ isEditing ? 'Editar habilidade' : 'Adicionar habilidade' }}</h2>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <form class="border border-[var(--line)] bg-[var(--paper-raised)]" :aria-busy="form.processing" @submit.prevent="submit">
                <fieldset :disabled="form.processing" class="min-w-0 space-y-8 p-5 sm:p-8">
                    <legend class="sr-only">Dados da habilidade</legend>
                    <div>
                        <p class="text-sm leading-6 text-[var(--muted)]">Descreva sua habilidade em português. A tradução em inglês é opcional.</p>
                        <div class="mt-6 grid gap-7 md:grid-cols-2">
                            <section v-for="language in languages" :key="language.code" class="min-w-0 space-y-5" :aria-labelledby="`language-${language.code}`">
                                <h3 :id="`language-${language.code}`" class="technical-label border-b border-[var(--line)] pb-3 !text-[var(--accent)]">{{ language.label }} / {{ language.code.toUpperCase() }}</h3>
                                <div>
                                    <InputLabel :for="`title-${language.code}`" :value="language.code === 'pt' ? 'Nome da habilidade *' : 'Nome da habilidade (EN)'" />
                                    <TextInput :id="`title-${language.code}`" v-model="form.title[language.code]" class="mt-2 block w-full" maxlength="255" :required="language.code === 'pt'" :placeholder="language.code === 'pt' ? 'Ex.: Resolução de problemas' : 'Ex.: Problem solving'" :lang="language.code === 'pt' ? 'pt-BR' : 'en'" :aria-invalid="Boolean(form.errors[`title.${language.code}`])" :aria-describedby="`title-${language.code}-error`" />
                                    <InputError :id="`title-${language.code}-error`" class="mt-2" :message="form.errors[`title.${language.code}`]" />
                                </div>
                                <div>
                                    <InputLabel :for="`description-${language.code}`" :value="language.code === 'pt' ? 'Descrição (opcional)' : 'Descrição (EN, opcional)'" />
                                    <textarea :id="`description-${language.code}`" v-model="form.description[language.code]" rows="5" maxlength="1000" class="mt-2 block w-full resize-y border border-[var(--line)] bg-[var(--paper)] text-sm leading-6 text-[var(--ink)]" :placeholder="language.code === 'pt' ? 'Como você aplica esta habilidade no trabalho?' : 'How do you apply this skill in your work?'" :lang="language.code === 'pt' ? 'pt-BR' : 'en'" :aria-invalid="Boolean(form.errors[`description.${language.code}`])" :aria-describedby="`description-${language.code}-hint description-${language.code}-error`"></textarea>
                                    <p :id="`description-${language.code}-hint`" class="mt-2 text-xs text-[var(--muted)]">Até 1.000 caracteres.</p>
                                    <InputError :id="`description-${language.code}-error`" class="mt-2" :message="form.errors[`description.${language.code}`]" />
                                </div>
                            </section>
                        </div>
                        <InputError class="mt-3" :message="form.errors.title" />
                        <InputError class="mt-3" :message="form.errors.description" />
                    </div>

                    <div class="grid gap-6 border-t border-[var(--line)] pt-7 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <div>
                            <InputLabel for="category" value="Categoria *" />
                            <select id="category" v-model="form.category" required class="mt-2 block min-h-11 w-full border border-[var(--line)] bg-[var(--paper)] text-sm text-[var(--ink)]" :aria-invalid="Boolean(form.errors.category)" aria-describedby="category-hint category-error">
                                <option value="technical">Técnica</option>
                                <option value="interpersonal">Interpessoal</option>
                            </select>
                            <p id="category-hint" class="mt-2 text-xs leading-5 text-[var(--muted)]">Escolha o grupo em que a competência aparece.</p>
                            <InputError id="category-error" class="mt-2" :message="form.errors.category" />
                        </div>
                        <div>
                            <InputLabel for="order" value="Ordem de exibição *" />
                            <input id="order" v-model.number="form.order" type="number" min="0" max="9999" step="1" required inputmode="numeric" class="mt-2 block min-h-11 w-full border border-[var(--line)] bg-[var(--paper)] px-3 text-sm text-[var(--ink)]" :aria-invalid="Boolean(form.errors.order)" aria-describedby="order-hint order-error" />
                            <p id="order-hint" class="mt-2 text-xs leading-5 text-[var(--muted)]">De 0 a 9.999. Números menores aparecem primeiro.</p>
                            <InputError id="order-error" class="mt-2" :message="form.errors.order" />
                        </div>
                    </div>

                    <div class="border-t border-[var(--line)] pt-6">
                        <label for="is_visible" class="flex cursor-pointer items-start gap-3 py-1">
                            <input id="is_visible" v-model="form.is_visible" type="checkbox" class="mt-0.5 h-5 w-5 shrink-0 border-[var(--line)] text-[var(--accent)] focus:ring-[var(--accent)]" aria-describedby="visibility-hint visibility-error" />
                            <span>
                                <span class="block text-sm font-semibold">Exibir no portfolio</span>
                                <span id="visibility-hint" class="mt-1 block text-xs leading-5 text-[var(--muted)]">Desmarque para manter a habilidade cadastrada sem exibi-la no site.</span>
                            </span>
                        </label>
                        <InputError id="visibility-error" class="mt-2" :message="form.errors.is_visible" />
                    </div>
                </fieldset>

                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-[var(--line)] bg-[var(--paper)] p-5 sm:px-8">
                    <p class="mr-auto text-xs text-[var(--muted)]">* Campos obrigatórios</p>
                    <Link v-if="!form.processing" :href="route('admin.skills.index')" class="admin-quiet-button !min-h-11">Cancelar</Link>
                    <PrimaryButton type="submit" :disabled="form.processing">{{ form.processing ? 'Salvando…' : isEditing ? 'Salvar alterações' : 'Adicionar habilidade' }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
