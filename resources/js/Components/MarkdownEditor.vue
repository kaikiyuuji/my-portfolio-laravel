<script setup>
import DOMPurify from 'dompurify';
import { marked } from 'marked';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';

const model = defineModel({
    type: String,
    default: '',
});

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    language: {
        type: String,
        default: 'PT',
    },
    placeholder: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const textarea = ref(null);
const gutter = ref(null);
const mode = ref('write');
const isFullscreen = ref(false);

marked.setOptions({
    breaks: true,
    gfm: true,
});

const preview = computed(() =>
    DOMPurify.sanitize(marked.parse(model.value || '')),
);

const words = computed(() => {
    const content = model.value.trim();
    return content ? content.split(/\s+/).length : 0;
});

const characters = computed(() => model.value.length);
const readingTime = computed(() => Math.max(1, Math.ceil(words.value / 220)));

const toolbar = [
    { label: 'Título', icon: 'H2', prefix: '## ', suffix: '', lineStart: true },
    { label: 'Negrito', icon: 'B', prefix: '**', suffix: '**', fallback: 'texto em negrito' },
    { label: 'Itálico', icon: 'I', prefix: '_', suffix: '_', fallback: 'texto em itálico' },
    { label: 'Citação', icon: '❞', prefix: '> ', suffix: '', lineStart: true },
    { label: 'Lista', icon: '•', prefix: '- ', suffix: '', lineStart: true },
    { label: 'Código', icon: '</>', prefix: '```\n', suffix: '\n```', fallback: 'código' },
    { label: 'Link', icon: '↗', prefix: '[', suffix: '](https://)', fallback: 'texto do link' },
    { label: 'Linha', icon: '—', prefix: '\n---\n', suffix: '', lineStart: true },
];

const applyFormat = async (tool) => {
    mode.value = 'write';
    await nextTick();

    const input = textarea.value;
    if (!input) return;

    const start = input.selectionStart;
    const end = input.selectionEnd;
    const selected = model.value.slice(start, end);
    const content = selected || tool.fallback || '';
    const needsLineBreak = tool.lineStart && start > 0 && model.value[start - 1] !== '\n';
    const before = needsLineBreak ? `\n${tool.prefix}` : tool.prefix;
    const insertion = `${before}${content}${tool.suffix}`;

    model.value = `${model.value.slice(0, start)}${insertion}${model.value.slice(end)}`;

    await nextTick();
    input.focus();

    const selectionStart = start + before.length;
    input.setSelectionRange(selectionStart, selectionStart + content.length);
};

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
};

const syncScroll = (event) => {
    if (gutter.value) {
        gutter.value.scrollTop = event.target.scrollTop;
    }
};

const handleEscape = (event) => {
    if (event.key === 'Escape' && isFullscreen.value) {
        isFullscreen.value = false;
    }
};

watch(isFullscreen, (expanded) => {
    document.body.style.overflow = expanded ? 'hidden' : '';
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <section
        class="pixel-editor"
        :class="{ 'pixel-editor--fullscreen': isFullscreen }"
        @keydown="handleEscape"
    >
        <header class="pixel-editor__header">
            <div class="pixel-editor__identity">
                <span class="pixel-editor__language">{{ language }}</span>
                <div>
                    <label :for="id">{{ label }}</label>
                    <p>Markdown + HTML habilitados</p>
                </div>
            </div>

            <div class="pixel-editor__tabs" role="tablist" aria-label="Modo do editor">
                <button
                    type="button"
                    role="tab"
                    :aria-selected="mode === 'write'"
                    :class="{ 'is-active': mode === 'write' }"
                    @click="mode = 'write'"
                >
                    ESCREVER
                </button>
                <button
                    type="button"
                    role="tab"
                    :aria-selected="mode === 'split'"
                    :class="{ 'is-active': mode === 'split' }"
                    @click="mode = 'split'"
                >
                    DIVIDIDO
                </button>
                <button
                    type="button"
                    role="tab"
                    :aria-selected="mode === 'preview'"
                    :class="{ 'is-active': mode === 'preview' }"
                    @click="mode = 'preview'"
                >
                    PREVIEW
                </button>
                <button
                    type="button"
                    class="pixel-editor__expand"
                    :title="isFullscreen ? 'Sair da tela cheia (Esc)' : 'Abrir em tela cheia'"
                    :aria-label="isFullscreen ? 'Sair da tela cheia' : 'Abrir em tela cheia'"
                    @click="toggleFullscreen"
                >
                    {{ isFullscreen ? '↙' : '↗' }}
                </button>
            </div>
        </header>

        <div v-show="mode !== 'preview'" class="pixel-editor__toolbar" aria-label="Formatação">
            <button
                v-for="tool in toolbar"
                :key="tool.label"
                type="button"
                :title="tool.label"
                :aria-label="tool.label"
                @click="applyFormat(tool)"
            >
                <span :class="{ 'is-italic': tool.label === 'Itálico' }">{{ tool.icon }}</span>
            </button>
            <span class="pixel-editor__hint">SELECIONE O TEXTO + COMANDO</span>
        </div>

        <div
            class="pixel-editor__workspace"
            :class="{
                'is-preview': mode === 'preview',
                'is-split': mode === 'split',
            }"
        >
            <div v-show="mode !== 'preview'" class="pixel-editor__input">
                <div ref="gutter" class="pixel-editor__gutter" aria-hidden="true">
                    <span v-for="line in Math.max(12, model.split('\n').length)" :key="line">{{ line }}</span>
                </div>
                <textarea
                    :id="id"
                    ref="textarea"
                    v-model="model"
                    :required="required"
                    :placeholder="placeholder"
                    spellcheck="true"
                    @scroll="syncScroll"
                ></textarea>
            </div>

            <article v-show="mode !== 'write'" class="pixel-editor__preview">
                <div v-if="model.trim()" class="pixel-editor__prose" v-html="preview"></div>
                <div v-else class="pixel-editor__empty">
                    <span>▤</span>
                    <strong>PREVIEW VAZIO</strong>
                    <p>Comece a escrever para visualizar o conteúdo formatado.</p>
                </div>
            </article>
        </div>

        <footer class="pixel-editor__footer">
            <span><b>{{ words }}</b> PALAVRAS</span>
            <span><b>{{ characters }}</b> CARACTERES</span>
            <span><b>~{{ readingTime }}</b> MIN DE LEITURA</span>
            <span class="pixel-editor__saved">● CONTEÚDO LOCAL</span>
        </footer>
    </section>
</template>
