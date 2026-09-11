<script setup>
import { computed, nextTick, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
});

const page = usePage();
const formElement = ref(null);
const settings = props.settings ?? {};
const arrayOrEmpty = (value) => Array.isArray(value) ? value : [];
const form = useForm({
    location: settings.location ?? '',
    phone: settings.phone ?? '',
    linkedin_url: settings.linkedin_url ?? '',
    website_url: settings.website_url ?? '',
    education: arrayOrEmpty(settings.education).map((item) => ({
        institution: item.institution ?? '',
        location: item.location ?? '',
        course: item.course ?? '',
        period: item.period ?? '',
        details: item.details ?? '',
    })),
    certifications: [...arrayOrEmpty(settings.certifications)],
    languages: arrayOrEmpty(settings.languages).map((item) => ({
        name: item.name ?? '',
        level: item.level ?? '',
    })),
    interests: [...arrayOrEmpty(settings.interests)],
});

const contactFields = [
    { name: 'location', label: 'Localização', type: 'text', maxLength: 255, placeholder: 'Cidade, estado, país' },
    { name: 'phone', label: 'Telefone', type: 'tel', maxLength: 50, placeholder: '+55 (00) 00000-0000', autocomplete: 'tel' },
    { name: 'linkedin_url', label: 'LinkedIn', type: 'url', maxLength: 2048, placeholder: 'https://www.linkedin.com/in/seu-perfil' },
    { name: 'website_url', label: 'Site ou portfólio', type: 'url', maxLength: 2048, placeholder: 'https://seusite.com.br' },
];

const educationFields = [
    { name: 'institution', label: 'Instituição', maxLength: 255, required: true },
    { name: 'location', label: 'Localização', maxLength: 255, required: false },
    { name: 'course', label: 'Curso ou formação', maxLength: 255, required: true },
    { name: 'period', label: 'Período', maxLength: 255, required: false, placeholder: 'Ex.: 2022 — 2026' },
];

const languageFields = [
    { name: 'name', label: 'Idioma', maxLength: 100, placeholder: 'Ex.: Inglês' },
    { name: 'level', label: 'Nível', maxLength: 100, placeholder: 'Ex.: Avançado' },
];

const sections = [
    { name: 'education', number: '02', title: 'Formação acadêmica', item: 'Formação', action: 'Adicionar formação', empty: 'Nenhuma formação adicionada.', note: 'Inclua cursos e instituições na ordem em que devem aparecer no currículo.', limit: 20 },
    { name: 'certifications', number: '03', title: 'Certificações', item: 'Certificação', action: 'Adicionar certificação', empty: 'Nenhuma certificação adicionada.', note: 'Informe o nome da certificação. Você pode incluir a instituição e o ano na mesma linha.', limit: 50 },
    { name: 'languages', number: '04', title: 'Idiomas', item: 'Idioma', action: 'Adicionar idioma', empty: 'Nenhum idioma adicionado.', note: 'Indique cada idioma e seu nível de domínio.', limit: 20 },
    { name: 'interests', number: '05', title: 'Interesses', item: 'Interesse', action: 'Adicionar interesse', empty: 'Nenhum interesse adicionado.', note: 'Adicione os temas ou atividades que deseja apresentar no currículo.', limit: 30 },
];

let nextItemKey = 0;
const itemKeys = ref(Object.fromEntries(sections.map(({ name }) => [name, form[name].map(() => ++nextItemKey)])));
const successMessage = computed(() => form.recentlySuccessful
    ? (page.props.flash?.success || 'Currículo salvo com sucesso.')
    : null);

function focusItem(key) {
    formElement.value?.querySelector(`[data-resume-item="${key}"] input, [data-resume-item="${key}"] textarea`)?.focus();
}

async function addItem(section) {
    if (form.processing || form[section.name].length >= section.limit) return;

    const value = section.name === 'education'
        ? { institution: '', location: '', course: '', period: '', details: '' }
        : section.name === 'languages' ? { name: '', level: '' } : '';
    const key = ++nextItemKey;
    form[section.name].push(value);
    itemKeys.value[section.name].push(key);
    await nextTick();
    focusItem(key);
}

async function removeItem(section, index) {
    if (form.processing) return;

    form[section.name].splice(index, 1);
    itemKeys.value[section.name].splice(index, 1);
    const collectionErrors = Object.keys(form.errors).filter((key) => key === section.name || key.startsWith(`${section.name}.`));
    if (collectionErrors.length) form.clearErrors(...collectionErrors);
    await nextTick();
    const nextKey = itemKeys.value[section.name][index] ?? itemKeys.value[section.name][index - 1];
    if (nextKey !== undefined) focusItem(nextKey);
    else formElement.value?.querySelector(`#resume-add-${section.name}`)?.focus();
}

function submit() {
    form.put(route('admin.resume.update'), {
        preserveScroll: true,
        onError: async () => {
            await nextTick();
            formElement.value?.querySelector('[aria-invalid="true"]')?.focus();
        },
    });
}
</script>

<template>
    <Head title="Currículo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="technical-label mb-2 !text-[var(--accent)]">Documento / PDF</p>
                    <h1 class="text-3xl font-semibold tracking-[-0.05em] text-[var(--ink)]">Currículo</h1>
                </div>
                <a :href="route('resume.download')" class="resume-secondary w-full sm:w-auto">
                    Exportar currículo em PDF <span aria-hidden="true">↓</span>
                </a>
            </div>
        </template>

        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 border-l-2 border-[var(--accent)] bg-[var(--paper-raised)] px-5 py-4">
                <p class="max-w-3xl text-sm leading-7 text-[var(--muted)]">Nome e e-mail vêm do perfil. Experiências e habilidades acompanham os respectivos cadastros. O PDF segue sempre o modelo enviado.</p>
                <p class="mt-2 font-mono text-[10px] text-[var(--muted)]">Salve as alterações antes de exportar o PDF.</p>
            </div>

            <form ref="formElement" @submit.prevent="submit">
                <fieldset :disabled="form.processing" class="min-w-0 space-y-7" :aria-busy="form.processing">
                    <legend class="sr-only">Informações complementares do currículo</legend>

                    <section class="resume-section" aria-labelledby="resume-contact-title">
                        <div class="resume-section-header">
                            <span class="resume-number" aria-hidden="true">01</span>
                            <div class="min-w-0">
                                <h2 id="resume-contact-title" class="resume-section-title">Contato</h2>
                                <p class="resume-section-note">Dados opcionais para o cabeçalho do currículo.</p>
                            </div>
                        </div>
                        <div class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
                            <div v-for="field in contactFields" :key="field.name" class="min-w-0">
                                <InputLabel :for="`resume-${field.name}`" :value="field.label" />
                                <TextInput
                                    :id="`resume-${field.name}`" v-model="form[field.name]" :name="field.name"
                                    :type="field.type" :maxlength="field.maxLength" :placeholder="field.placeholder"
                                    :autocomplete="field.autocomplete" class="mt-2 block w-full"
                                    :aria-invalid="Boolean(form.errors[field.name])"
                                    :aria-describedby="form.errors[field.name] ? `resume-${field.name}-error` : undefined"
                                />
                                <InputError :id="`resume-${field.name}-error`" class="mt-2" :message="form.errors[field.name]" />
                            </div>
                        </div>
                    </section>

                    <section v-for="section in sections" :key="section.name" class="resume-section" :aria-labelledby="`resume-${section.name}-title`">
                        <div class="resume-section-header flex-wrap">
                            <span class="resume-number" aria-hidden="true">{{ section.number }}</span>
                            <div class="min-w-0 flex-1">
                                <h2 :id="`resume-${section.name}-title`" class="resume-section-title">{{ section.title }}</h2>
                                <p class="resume-section-note">{{ section.note }}</p>
                            </div>
                            <span class="ml-auto font-mono text-[10px] text-[var(--muted)]" :aria-label="`${form[section.name].length} de ${section.limit} itens`">{{ form[section.name].length }} / {{ section.limit }}</span>
                        </div>

                        <div class="space-y-4 p-5 sm:p-6">
                            <InputError :message="form.errors[section.name]" />
                            <p v-if="!form[section.name].length" class="border border-dashed border-[var(--line)] px-4 py-7 text-sm text-[var(--muted)]">{{ section.empty }}</p>

                            <fieldset
                                v-for="(item, index) in form[section.name]" :key="itemKeys[section.name][index]"
                                :data-resume-item="itemKeys[section.name][index]"
                                class="min-w-0 border border-[var(--line)] bg-[var(--paper)] p-4 sm:p-5"
                            >
                                <legend class="sr-only">{{ section.item }} {{ index + 1 }}</legend>
                                <div class="mb-5 flex items-center justify-between gap-3 border-b border-[var(--line)] pb-3">
                                    <p class="technical-label !text-[var(--ink)]">{{ section.item }} {{ String(index + 1).padStart(2, '0') }}</p>
                                    <button type="button" class="resume-remove" :aria-label="`Remover ${section.item.toLowerCase()} ${index + 1}`" @click="removeItem(section, index)">Remover <span aria-hidden="true">×</span></button>
                                </div>

                                <template v-if="section.name === 'education'">
                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <div v-for="field in educationFields" :key="field.name" class="min-w-0">
                                            <InputLabel :for="`resume-education-${itemKeys.education[index]}-${field.name}`" :value="`${field.label}${field.required ? ' *' : ''}`" />
                                            <TextInput
                                                :id="`resume-education-${itemKeys.education[index]}-${field.name}`"
                                                v-model="form.education[index][field.name]" :name="`education[${index}][${field.name}]`"
                                                type="text" :maxlength="field.maxLength" :required="field.required" :placeholder="field.placeholder" class="mt-2 block w-full"
                                                :aria-invalid="Boolean(form.errors[`education.${index}.${field.name}`])"
                                                :aria-describedby="form.errors[`education.${index}.${field.name}`] ? `resume-education-${itemKeys.education[index]}-${field.name}-error` : undefined"
                                            />
                                            <InputError :id="`resume-education-${itemKeys.education[index]}-${field.name}-error`" class="mt-2" :message="form.errors[`education.${index}.${field.name}`]" />
                                        </div>
                                    </div>
                                    <div class="mt-5">
                                        <InputLabel :for="`resume-education-${itemKeys.education[index]}-details`" value="Detalhes" />
                                        <textarea
                                            :id="`resume-education-${itemKeys.education[index]}-details`" v-model="form.education[index].details"
                                            :name="`education[${index}][details]`" rows="3" maxlength="3000" class="resume-textarea mt-2"
                                            placeholder="Disciplinas, projetos ou outras informações relevantes."
                                            :aria-invalid="Boolean(form.errors[`education.${index}.details`])"
                                            :aria-describedby="form.errors[`education.${index}.details`] ? `resume-education-${itemKeys.education[index]}-details-error` : undefined"
                                        ></textarea>
                                        <InputError :id="`resume-education-${itemKeys.education[index]}-details-error`" class="mt-2" :message="form.errors[`education.${index}.details`]" />
                                    </div>
                                </template>

                                <div v-else-if="section.name === 'languages'" class="grid gap-5 sm:grid-cols-2">
                                    <div v-for="field in languageFields" :key="field.name" class="min-w-0">
                                        <InputLabel :for="`resume-language-${itemKeys.languages[index]}-${field.name}`" :value="`${field.label} *`" />
                                        <TextInput
                                            :id="`resume-language-${itemKeys.languages[index]}-${field.name}`"
                                            v-model="form.languages[index][field.name]" :name="`languages[${index}][${field.name}]`"
                                            type="text" :maxlength="field.maxLength" required :placeholder="field.placeholder" class="mt-2 block w-full"
                                            :aria-invalid="Boolean(form.errors[`languages.${index}.${field.name}`])"
                                            :aria-describedby="form.errors[`languages.${index}.${field.name}`] ? `resume-language-${itemKeys.languages[index]}-${field.name}-error` : undefined"
                                        />
                                        <InputError :id="`resume-language-${itemKeys.languages[index]}-${field.name}-error`" class="mt-2" :message="form.errors[`languages.${index}.${field.name}`]" />
                                    </div>
                                </div>

                                <div v-else>
                                    <InputLabel :for="`resume-${section.name}-${itemKeys[section.name][index]}`" :value="section.name === 'certifications' ? 'Nome da certificação *' : 'Descrição do interesse *'" />
                                    <TextInput
                                        v-if="section.name === 'certifications'" :id="`resume-${section.name}-${itemKeys[section.name][index]}`"
                                        v-model="form.certifications[index]" :name="`certifications[${index}]`" type="text" maxlength="255" required class="mt-2 block w-full"
                                        :aria-invalid="Boolean(form.errors[`certifications.${index}`])"
                                        :aria-describedby="form.errors[`certifications.${index}`] ? `resume-${section.name}-${itemKeys[section.name][index]}-error` : undefined"
                                    />
                                    <textarea
                                        v-else :id="`resume-${section.name}-${itemKeys[section.name][index]}`"
                                        v-model="form.interests[index]" :name="`interests[${index}]`" rows="2" maxlength="1000" required class="resume-textarea mt-2"
                                        :aria-invalid="Boolean(form.errors[`interests.${index}`])"
                                        :aria-describedby="form.errors[`interests.${index}`] ? `resume-${section.name}-${itemKeys[section.name][index]}-error` : undefined"
                                    ></textarea>
                                    <InputError :id="`resume-${section.name}-${itemKeys[section.name][index]}-error`" class="mt-2" :message="form.errors[`${section.name}.${index}`]" />
                                </div>
                            </fieldset>

                            <div class="flex flex-wrap items-center gap-3">
                                <button :id="`resume-add-${section.name}`" type="button" class="resume-secondary w-full sm:w-auto" :disabled="form[section.name].length >= section.limit" @click="addItem(section)">
                                    <span aria-hidden="true">+</span> {{ section.action }}
                                </button>
                                <p v-if="form[section.name].length >= section.limit" class="text-xs text-[var(--muted)]" role="status">Limite de {{ section.limit }} itens atingido.</p>
                            </div>
                        </div>
                    </section>
                </fieldset>

                <div class="mt-7 flex flex-col gap-5 border-t border-[var(--line)] py-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-mono text-[10px] text-[var(--muted)]">* Campos obrigatórios nos itens adicionados.</p>
                        <p v-if="successMessage" class="mt-3 text-sm font-medium text-[var(--accent)]" role="status">{{ successMessage }}</p>
                        <p v-if="form.hasErrors" class="mt-3 text-sm text-red-600" role="alert">Revise os campos indicados antes de salvar.</p>
                    </div>
                    <PrimaryButton type="submit" :disabled="form.processing" class="w-full shrink-0 sm:w-auto">{{ form.processing ? 'Salvando…' : 'Salvar currículo' }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.resume-section { border: 1px solid var(--line); background: var(--paper-raised); }
.resume-section-header { display: flex; align-items: center; gap: 16px; padding: 20px; border-bottom: 1px solid var(--line); }
.resume-number { display: grid; place-items: center; flex-shrink: 0; width: 38px; height: 38px; border: 1px solid var(--line); font-family: ui-monospace, monospace; font-size: 11px; color: var(--accent); }
.resume-section-title { font-size: 18px; font-weight: 600; line-height: 1.4; letter-spacing: -.035em; }
.resume-section-note { margin-top: 5px; font-size: 12px; line-height: 1.7; color: var(--muted); }
.resume-secondary { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; gap: 10px; border: 1px solid var(--line); padding: 10px 15px; background: var(--paper-raised); font-family: ui-monospace, monospace; font-size: 10px; font-weight: 600; line-height: 1.6; text-align: center; text-transform: uppercase; letter-spacing: .05em; color: var(--ink); }
.resume-secondary:hover { border-color: var(--accent); color: var(--accent); }
.resume-secondary:disabled { opacity: .45; cursor: not-allowed; }
.resume-remove { display: inline-flex; min-height: 44px; align-items: center; gap: 10px; padding: 8px 0 8px 12px; font-family: ui-monospace, monospace; font-size: 10px; color: var(--muted); }
.resume-remove:hover { color: #dc2626; }
.resume-remove span { font-size: 18px; }
.resume-textarea { display: block; width: 100%; min-height: 80px; resize: vertical; border: 1px solid var(--line); background: var(--paper); padding: 10px 12px; color: var(--ink); font-size: 14px; line-height: 1.6; }
.resume-textarea::placeholder { color: var(--muted); opacity: .6; }
.resume-textarea:focus { border-color: var(--accent); --tw-ring-color: var(--accent); }
.resume-textarea:disabled { opacity: .5; }
button:focus-visible, a:focus-visible { outline: 2px solid var(--accent); outline-offset: 4px; }
@media (min-width: 640px) { .resume-section-header { padding: 24px; } }
</style>
