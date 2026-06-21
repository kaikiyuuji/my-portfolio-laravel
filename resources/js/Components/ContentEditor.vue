<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, shallowRef, watch } from 'vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import { basicSetup } from 'codemirror';
import { Compartment, EditorState } from '@codemirror/state';
import { EditorView, keymap, placeholder as editorPlaceholder } from '@codemirror/view';
import { redo, redoDepth, undo, undoDepth } from '@codemirror/commands';
import { markdown } from '@codemirror/lang-markdown';
import { html } from '@codemirror/lang-html';
import { oneDark } from '@codemirror/theme-one-dark';

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    required: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: 'Comece a escrever...',
    },
});

const model = defineModel({
    type: String,
    default: '',
});

const editorHost = ref(null);
const mode = ref(detectMode(model.value));
const viewMode = ref('split');
const fullscreen = ref(false);
const canUndo = ref(false);
const canRedo = ref(false);
const editorView = shallowRef(null);

const languageCompartment = new Compartment();
const themeCompartment = new Compartment();
let themeObserver = null;
let updatingFromEditor = false;

marked.setOptions({ gfm: true, breaks: false });

function detectMode(value) {
    const source = (value || '').trim();
    return /^<(?:!doctype|html|article|section|div|h[1-6]|p|ul|ol|blockquote|pre)\b/i.test(source)
        ? 'html'
        : 'markdown';
}

const renderedPreview = computed(() => {
    const source = model.value || '';
    const output = mode.value === 'html' ? source : marked.parse(source);

    return DOMPurify.sanitize(output, {
        USE_PROFILES: { html: true },
        FORBID_TAGS: ['style', 'iframe', 'form', 'object', 'embed', 'script'],
        FORBID_ATTR: ['style', 'onerror', 'onload', 'onclick'],
    });
});

const words = computed(() =>
    (model.value || '').trim().split(/\s+/).filter(Boolean).length,
);
const characters = computed(() => (model.value || '').length);
const lines = computed(() => (model.value || '').split('\n').length);

const lightEditorTheme = EditorView.theme({
    '&': {
        height: '100%',
        minHeight: '520px',
        backgroundColor: 'var(--paper)',
        color: 'var(--ink)',
        fontSize: '14px',
    },
    '.cm-content': {
        padding: '18px 0',
        caretColor: 'var(--accent)',
        fontFamily: '"IBM Plex Mono", ui-monospace, monospace',
        lineHeight: '1.75',
    },
    '.cm-line': {
        padding: '0 18px',
    },
    '.cm-gutters': {
        backgroundColor: 'var(--paper-raised)',
        color: 'var(--muted)',
        borderRight: '1px solid var(--line)',
        fontFamily: '"IBM Plex Mono", ui-monospace, monospace',
        fontSize: '11px',
    },
    '.cm-activeLine, .cm-activeLineGutter': {
        backgroundColor: 'color-mix(in srgb, var(--accent) 7%, transparent)',
    },
    '.cm-selectionBackground, &.cm-focused .cm-selectionBackground': {
        backgroundColor: 'color-mix(in srgb, var(--accent) 22%, transparent) !important',
    },
    '.cm-cursor, .cm-dropCursor': {
        borderLeftColor: 'var(--accent)',
    },
    '.cm-panels': {
        backgroundColor: 'var(--paper-raised)',
        color: 'var(--ink)',
    },
    '.cm-searchMatch': {
        backgroundColor: 'color-mix(in srgb, #facc15 38%, transparent)',
        outline: '1px solid #ca8a04',
    },
    '.cm-tooltip': {
        border: '1px solid var(--line)',
        backgroundColor: 'var(--paper-raised)',
        color: 'var(--ink)',
    },
});

function languageExtension() {
    return mode.value === 'html' ? html() : markdown();
}

function themeExtension() {
    return document.documentElement.classList.contains('dark') ? oneDark : lightEditorTheme;
}

function refreshHistoryState(view) {
    canUndo.value = undoDepth(view.state) > 0;
    canRedo.value = redoDepth(view.state) > 0;
}

function buildEditor() {
    if (!editorHost.value) return;

    editorView.value = new EditorView({
        parent: editorHost.value,
        state: EditorState.create({
            doc: model.value || '',
            extensions: [
                basicSetup,
                languageCompartment.of(languageExtension()),
                themeCompartment.of(themeExtension()),
                EditorView.lineWrapping,
                editorPlaceholder(props.placeholder),
                EditorView.contentAttributes.of({
                    'aria-label': props.label,
                    'aria-required': String(props.required),
                    'data-placeholder': props.placeholder,
                }),
                keymap.of([
                    { key: 'Mod-b', run: () => applyFormat('bold') },
                    { key: 'Mod-i', run: () => applyFormat('italic') },
                    { key: 'Mod-Shift-k', run: () => applyFormat('link') },
                    { key: 'Mod-Shift-p', run: () => { viewMode.value = 'preview'; return true; } },
                ]),
                EditorView.updateListener.of((update) => {
                    refreshHistoryState(update.view);
                    if (!update.docChanged) return;

                    updatingFromEditor = true;
                    model.value = update.state.doc.toString();
                    nextTick(() => {
                        updatingFromEditor = false;
                    });
                }),
            ],
        }),
    });

    refreshHistoryState(editorView.value);
}

function replaceSelection(before, after = '', placeholder = 'texto') {
    const view = editorView.value;
    if (!view) return false;

    const selection = view.state.selection.main;
    const selected = view.state.sliceDoc(selection.from, selection.to) || placeholder;
    const inserted = `${before}${selected}${after}`;
    const anchor = selection.from + before.length;

    view.dispatch({
        changes: { from: selection.from, to: selection.to, insert: inserted },
        selection: { anchor, head: anchor + selected.length },
        scrollIntoView: true,
    });
    view.focus();
    return true;
}

function prefixLines(prefix) {
    const view = editorView.value;
    if (!view) return false;

    const selection = view.state.selection.main;
    const selected = view.state.sliceDoc(selection.from, selection.to) || 'texto';
    const inserted = selected
        .split('\n')
        .map((line) => `${prefix}${line}`)
        .join('\n');

    view.dispatch({
        changes: { from: selection.from, to: selection.to, insert: inserted },
        selection: { anchor: selection.from, head: selection.from + inserted.length },
        scrollIntoView: true,
    });
    view.focus();
    return true;
}

function applyFormat(action) {
    const isHtml = mode.value === 'html';

    const commands = {
        heading: () => isHtml ? replaceSelection('<h2>', '</h2>', 'Título') : prefixLines('## '),
        bold: () => isHtml ? replaceSelection('<strong>', '</strong>') : replaceSelection('**', '**'),
        italic: () => isHtml ? replaceSelection('<em>', '</em>') : replaceSelection('*', '*'),
        quote: () => isHtml ? replaceSelection('<blockquote>\n', '\n</blockquote>') : prefixLines('> '),
        list: () => isHtml ? replaceSelection('<ul>\n  <li>', '</li>\n</ul>', 'Item') : prefixLines('- '),
        orderedList: () => isHtml ? replaceSelection('<ol>\n  <li>', '</li>\n</ol>', 'Item') : prefixLines('1. '),
        link: () => isHtml ? replaceSelection('<a href="https://">', '</a>', 'Link') : replaceSelection('[', '](https://)', 'Link'),
        inlineCode: () => isHtml ? replaceSelection('<code>', '</code>', 'código') : replaceSelection('`', '`', 'código'),
        codeBlock: () => isHtml
            ? replaceSelection('<pre><code>\n', '\n</code></pre>', 'código')
            : replaceSelection('```\n', '\n```', 'código'),
        image: () => isHtml
            ? replaceSelection('<img src="https://" alt="', '" />', 'Descrição')
            : replaceSelection('![', '](https://)', 'Descrição'),
        divider: () => replaceSelection(isHtml ? '\n<hr />\n' : '\n\n---\n\n', '', ''),
    };

    return commands[action]?.() ?? false;
}

function runUndo() {
    if (editorView.value) undo(editorView.value);
}

function runRedo() {
    if (editorView.value) redo(editorView.value);
}

function focusEditor() {
    viewMode.value = viewMode.value === 'preview' ? 'split' : viewMode.value;
    nextTick(() => editorView.value?.focus());
}

function toggleFullscreen() {
    fullscreen.value = !fullscreen.value;
    document.body.style.overflow = fullscreen.value ? 'hidden' : '';
    nextTick(() => editorView.value?.requestMeasure());
}

watch(mode, () => {
    editorView.value?.dispatch({
        effects: languageCompartment.reconfigure(languageExtension()),
    });
});

watch(model, (value) => {
    const view = editorView.value;
    if (!view || updatingFromEditor) return;

    const current = view.state.doc.toString();
    if (current === value) return;

    view.dispatch({
        changes: { from: 0, to: current.length, insert: value || '' },
    });
});

onMounted(() => {
    buildEditor();

    themeObserver = new MutationObserver(() => {
        editorView.value?.dispatch({
            effects: themeCompartment.reconfigure(themeExtension()),
        });
    });
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
});

onBeforeUnmount(() => {
    themeObserver?.disconnect();
    editorView.value?.destroy();
    document.body.style.overflow = '';
});
</script>

<template>
    <section
        class="content-editor border border-[var(--ink)] bg-[var(--paper-raised)]"
        :class="{ 'content-editor--fullscreen': fullscreen }"
    >
        <header class="flex flex-col border-b border-[var(--line)]">
            <div class="flex flex-wrap items-center justify-between gap-3 px-3 py-3 sm:px-4">
                <div>
                    <label :for="id" class="font-mono text-[10px] font-bold uppercase tracking-[0.12em] text-[var(--ink)]">
                        {{ label }}
                        <span v-if="required" class="text-red-600">*</span>
                    </label>
                    <p class="mt-1 font-mono text-[9px] uppercase tracking-wider text-[var(--muted)]">
                        Markdown ou HTML · preview sanitizado
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex border border-[var(--line)]">
                        <button
                            v-for="format in ['markdown', 'html']"
                            :key="format"
                            type="button"
                            class="editor-tab"
                            :class="{ 'editor-tab--active': mode === format }"
                            @click="mode = format"
                        >
                            {{ format === 'markdown' ? 'MD' : 'HTML' }}
                        </button>
                    </div>

                    <div class="flex border border-[var(--line)]">
                        <button
                            v-for="layout in ['edit', 'split', 'preview']"
                            :key="layout"
                            type="button"
                            class="editor-tab"
                            :class="{ 'editor-tab--active': viewMode === layout }"
                            @click="viewMode = layout"
                        >
                            {{ { edit: 'Editar', split: 'Dividido', preview: 'Preview' }[layout] }}
                        </button>
                    </div>

                    <button
                        type="button"
                        class="editor-icon-button"
                        :title="fullscreen ? 'Sair da tela cheia' : 'Tela cheia'"
                        @click="toggleFullscreen"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path v-if="!fullscreen" d="M8 3H3v5M16 3h5v5M8 21H3v-5M16 21h5v-5" />
                            <path v-else d="M3 8h5V3M21 8h-5V3M3 16h5v5M21 16h-5v5" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-1 border-t border-[var(--line)] bg-[var(--paper)] px-2 py-2">
                <button type="button" class="editor-icon-button" title="Desfazer" :disabled="!canUndo" @click="runUndo">↶</button>
                <button type="button" class="editor-icon-button" title="Refazer" :disabled="!canRedo" @click="runRedo">↷</button>
                <span class="mx-1 h-6 w-px bg-[var(--line)]"></span>
                <button type="button" class="editor-tool-button" title="Título" @click="applyFormat('heading')">H2</button>
                <button type="button" class="editor-tool-button font-bold" title="Negrito (Ctrl/Cmd+B)" @click="applyFormat('bold')">B</button>
                <button type="button" class="editor-tool-button italic" title="Itálico (Ctrl/Cmd+I)" @click="applyFormat('italic')">I</button>
                <button type="button" class="editor-tool-button" title="Citação" @click="applyFormat('quote')">❝</button>
                <button type="button" class="editor-tool-button" title="Lista" @click="applyFormat('list')">• List</button>
                <button type="button" class="editor-tool-button" title="Lista numerada" @click="applyFormat('orderedList')">1. List</button>
                <button type="button" class="editor-tool-button" title="Link (Ctrl/Cmd+Shift+K)" @click="applyFormat('link')">Link</button>
                <button type="button" class="editor-tool-button" title="Código inline" @click="applyFormat('inlineCode')">&lt;/&gt;</button>
                <button type="button" class="editor-tool-button" title="Bloco de código" @click="applyFormat('codeBlock')">{ }</button>
                <button type="button" class="editor-tool-button" title="Imagem por URL" @click="applyFormat('image')">Img</button>
                <button type="button" class="editor-tool-button" title="Divisor" @click="applyFormat('divider')">—</button>
            </div>
        </header>

        <div
            class="editor-workspace"
            :class="{
                'editor-workspace--edit': viewMode === 'edit',
                'editor-workspace--preview': viewMode === 'preview',
            }"
        >
            <div
                v-show="viewMode !== 'preview'"
                class="relative min-w-0 overflow-hidden"
                @click="focusEditor"
            >
                <div ref="editorHost" :id="id" class="h-full"></div>
            </div>

            <div
                v-show="viewMode !== 'edit'"
                class="editor-preview min-w-0 overflow-y-auto border-l border-[var(--line)] bg-[var(--paper-raised)] p-5 sm:p-7"
            >
                <div v-if="model.trim()" class="editor-prose" v-html="renderedPreview"></div>
                <div v-else class="grid min-h-80 place-items-center">
                    <p class="max-w-xs text-center font-mono text-[10px] uppercase tracking-wider text-[var(--muted)]">
                        O preview aparecerá aqui enquanto você escreve.
                    </p>
                </div>
            </div>
        </div>

        <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--line)] bg-[var(--paper)] px-3 py-2 font-mono text-[9px] uppercase tracking-wider text-[var(--muted)] sm:px-4">
            <span>{{ lines }} linhas · {{ words }} palavras · {{ characters }} caracteres</span>
            <span>Ctrl/Cmd + F para buscar · Ctrl/Cmd + Z para desfazer</span>
        </footer>
    </section>
</template>

<style scoped>
.content-editor {
    overflow: hidden;
}

.content-editor--fullscreen {
    position: fixed;
    inset: 0;
    z-index: 100;
    display: flex;
    flex-direction: column;
    width: 100vw;
    height: 100dvh;
}

.content-editor--fullscreen .editor-workspace {
    flex: 1;
    min-height: 0;
}

.editor-tab,
.editor-icon-button,
.editor-tool-button {
    display: inline-flex;
    min-height: 34px;
    align-items: center;
    justify-content: center;
    border: 0;
    background: var(--paper-raised);
    color: var(--muted);
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    transition: color 150ms ease, background-color 150ms ease;
}

.editor-tab {
    padding: 0 10px;
    border-right: 1px solid var(--line);
}

.editor-tab:last-child {
    border-right: 0;
}

.editor-tab--active,
.editor-tab:hover,
.editor-icon-button:hover,
.editor-tool-button:hover {
    background: var(--accent);
    color: white;
}

.editor-icon-button {
    min-width: 34px;
}

.editor-tool-button {
    min-width: 34px;
    padding: 0 8px;
    border: 1px solid transparent;
}

.editor-icon-button:disabled {
    cursor: not-allowed;
    opacity: 0.3;
}

.editor-workspace {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    min-height: 520px;
    max-height: 68vh;
}

.editor-workspace--edit,
.editor-workspace--preview {
    grid-template-columns: minmax(0, 1fr);
}

.editor-workspace--preview .editor-preview {
    border-left: 0;
}

.editor-preview {
    min-height: 520px;
}

.editor-prose {
    color: var(--ink);
    font-size: 0.95rem;
    line-height: 1.8;
}

.editor-prose :deep(h1) {
    margin: 0 0 1rem;
    font-size: 2.15rem;
    font-weight: 650;
    line-height: 1;
    letter-spacing: -0.05em;
}

.editor-prose :deep(h2) {
    margin: 2rem 0 0.8rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--line);
    font-size: 1.6rem;
    font-weight: 650;
    line-height: 1.1;
    letter-spacing: -0.04em;
}

.editor-prose :deep(h3) {
    margin: 1.5rem 0 0.6rem;
    font-size: 1.25rem;
    font-weight: 650;
}

.editor-prose :deep(p) {
    margin-bottom: 1rem;
}

.editor-prose :deep(a) {
    color: var(--accent);
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 4px;
}

.editor-prose :deep(ul),
.editor-prose :deep(ol) {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.editor-prose :deep(ul) {
    list-style: square;
}

.editor-prose :deep(ol) {
    list-style: decimal;
}

.editor-prose :deep(li::marker) {
    color: var(--accent);
}

.editor-prose :deep(blockquote) {
    margin: 1.5rem 0;
    border-left: 4px solid var(--accent);
    background: color-mix(in srgb, var(--accent) 7%, var(--paper-raised));
    padding: 0.9rem 1.1rem;
}

.editor-prose :deep(code) {
    border: 1px solid var(--line);
    background: color-mix(in srgb, var(--ink) 7%, var(--paper-raised));
    padding: 0.1rem 0.35rem;
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 0.88em;
}

.editor-prose :deep(pre) {
    margin: 1.5rem 0;
    overflow-x: auto;
    border: 1px solid var(--line);
    background: #0d0e12;
    color: #f4f2ea;
    padding: 1rem;
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 0.82rem;
}

.editor-prose :deep(pre code) {
    border: 0;
    background: transparent;
    padding: 0;
    color: inherit;
}

.editor-prose :deep(img) {
    max-width: 100%;
    border: 1px solid var(--line);
}

.editor-prose :deep(table) {
    display: block;
    width: 100%;
    overflow-x: auto;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.editor-prose :deep(th),
.editor-prose :deep(td) {
    border: 1px solid var(--line);
    padding: 0.55rem 0.7rem;
    text-align: left;
}

@media (max-width: 899px) {
    .editor-workspace {
        grid-template-columns: minmax(0, 1fr);
        max-height: none;
    }

    .editor-workspace:not(.editor-workspace--edit):not(.editor-workspace--preview) {
        grid-template-rows: minmax(420px, 1fr) minmax(420px, 1fr);
    }

    .editor-preview {
        min-height: 420px;
        border-top: 1px solid var(--line);
        border-left: 0;
    }
}
</style>
