<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookCover3d from '@/Components/BookCover3d.vue';

const props = defineProps({
    books: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '', category: '' }) },
    counts: { type: Object, default: () => ({ total: 0, academic: 0, personal: 0 }) },
});

const { t } = useI18n();
const page = usePage();
const search = ref(props.filters.search ?? '');
const busy = ref(false);
const dialog = ref(null);
const selectedBook = ref(null);
const rotation = ref(-24);
let openingButton = null;
let previousOverflow = null;

const categories = ['academic', 'personal'];
const shelves = computed(() => categories.map((category, index) => ({
    category,
    number: String(index + 1).padStart(2, '0'),
    books: (props.books.data ?? []).filter((book) => book.category === category),
})).filter((shelf) => shelf.books.length));
const visibleBooks = computed(() => shelves.value.flatMap((shelf) => shelf.books));
const selectedIndex = computed(() => visibleBooks.value.findIndex((book) => book.id === selectedBook.value?.id));
const purchaseUrl = computed(() => {
    try {
        const url = new URL(selectedBook.value?.purchase_url);
        return ['http:', 'https:'].includes(url.protocol) ? url.href : null;
    } catch {
        return null;
    }
});
const hasFilters = computed(() => !!(props.filters.search || props.filters.category));
const canonicalUrl = computed(() => typeof window !== 'undefined' ? `${window.location.origin}/livros` : null);

watch(() => props.filters.search, (value) => { search.value = value ?? ''; });

function filter(category = props.filters.category ?? '') {
    busy.value = true;
    router.get(route('books.index'), {
        ...(search.value.trim() ? { search: search.value.trim() } : {}),
        ...(category ? { category } : {}),
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => { busy.value = false; },
    });
}

function clearFilters() {
    search.value = '';
    filter('');
}

async function openBook(book, event) {
    if (busy.value) return;
    openingButton = event.currentTarget;
    selectedBook.value = book;
    rotation.value = -24;
    await nextTick();
    previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    dialog.value.showModal();
}

function restoreScrolling() {
    if (previousOverflow !== null) {
        document.body.style.overflow = previousOverflow;
        previousOverflow = null;
    }
}

function closeBook() {
    dialog.value?.close();
    selectedBook.value = null;
    restoreScrolling();
    openingButton?.focus({ preventScroll: true });
}

function changeBook(direction) {
    const book = visibleBooks.value[selectedIndex.value + direction];
    if (book) {
        selectedBook.value = book;
        rotation.value = -24;
    }
}

function handleDialogKeys(event) {
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName) || event.altKey || event.ctrlKey || event.metaKey) return;
    if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
        event.preventDefault();
        changeBook(event.key === 'ArrowLeft' ? -1 : 1);
    }
}

onBeforeUnmount(restoreScrolling);
</script>

<template>
    <Head :title="t('books.title')">
        <meta name="description" :content="t('books.subtitle')" />
        <link v-if="canonicalUrl" rel="canonical" :href="canonicalUrl" />
        <meta property="og:type" content="website" />
        <meta property="og:title" :content="t('books.title')" />
        <meta property="og:description" :content="t('books.subtitle')" />
        <meta v-if="canonicalUrl" property="og:url" :content="canonicalUrl" />
        <meta name="twitter:card" content="summary" />
    </Head>

    <PublicLayout :profile-name="t('books.title')">
        <section class="library-hero border-b border-[var(--line)]">
            <div class="mx-auto grid max-w-7xl border-x border-[var(--line)] md:grid-cols-[1fr_280px] lg:grid-cols-[1fr_360px]">
                <div class="px-5 py-12 sm:px-8 sm:py-16 lg:px-12 lg:py-20">
                    <p class="technical-label mb-6 !text-[var(--accent)]">07 / {{ t('books.label') }}</p>
                    <h1 class="text-[clamp(3.5rem,9vw,7.5rem)] font-medium leading-[0.9] tracking-[-0.075em]">{{ t('books.title') }}<span class="text-[var(--accent)]">.</span></h1>
                    <p class="mt-7 max-w-lg text-base leading-7 text-[var(--muted)] sm:text-lg">{{ t('books.subtitle') }}</p>
                    <a href="#acervo" class="mt-8 inline-flex min-h-11 items-center gap-5 border-b border-[var(--ink)] font-mono text-[10px] font-bold uppercase tracking-widest">
                        {{ t('books.explore') }} <span class="text-xl text-[var(--accent)]" aria-hidden="true">↓</span>
                    </a>
                </div>
                <div class="library-hero-art blueprint-grid relative flex min-h-40 flex-col justify-between overflow-hidden border-t border-[var(--line)] p-5 sm:p-8 md:border-l md:border-t-0">
                    <div class="flex items-start justify-between gap-4">
                        <span class="font-mono text-[10px] font-semibold uppercase tracking-widest text-white/80">{{ t('books.collection') }}</span>
                        <span class="font-mono text-[10px] text-white/80">{{ String(counts.total).padStart(2, '0') }}</span>
                    </div>
                    <div class="library-spines" aria-hidden="true">
                        <span class="library-spine library-spine--one"><span>01 / {{ t('books.academic') }}</span></span>
                        <span class="library-spine library-spine--two"><span>02 / {{ t('books.personal') }}</span></span>
                        <span class="library-spine library-spine--three"><span>∞</span></span>
                    </div>
                    <p class="relative z-10 self-end bg-[var(--paper-raised)] px-3 py-2 font-mono text-[10px] font-semibold uppercase tracking-wider text-[var(--ink)]">{{ t('books.volumes', counts.total) }}</p>
                </div>
            </div>
        </section>

        <section id="acervo" class="portfolio-section scroll-mt-24 pb-16 pt-8 sm:pb-24 sm:pt-12">
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="library-toolbar flex flex-col gap-5 border-b border-[var(--line)] pb-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="flex flex-wrap gap-2" role="group" :aria-label="t('books.filter')">
                        <button
                            v-for="category in ['', ...categories]" :key="category"
                            type="button" class="library-filter"
                            :aria-pressed="(filters.category ?? '') === category"
                            :disabled="busy" @click="filter(category)"
                        >
                            {{ t(`books.${category || 'all'}`) }}
                            <span class="ml-2 text-[9px] opacity-70">{{ String(category ? counts[category] : counts.total).padStart(2, '0') }}</span>
                        </button>
                    </div>
                    <form class="flex w-full gap-2 lg:max-w-sm" role="search" @submit.prevent="filter()">
                        <label for="book-search" class="sr-only">{{ t('books.search') }}</label>
                        <div class="relative min-w-0 flex-1">
                            <svg class="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-[var(--muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 5 5" /></svg>
                            <input id="book-search" v-model="search" type="search" maxlength="120" :placeholder="t('books.search')" class="h-11 w-full border-[var(--line)] bg-[var(--paper-raised)] pl-10 pr-3 text-xs focus:border-[var(--accent)] focus:ring-[var(--accent)]" />
                        </div>
                        <button type="submit" :disabled="busy" class="library-filter !border-[var(--ink)] !bg-[var(--ink)] !text-[var(--paper)]">{{ t('books.searchButton') }}</button>
                    </form>
                </div>

                <div class="flex min-h-16 flex-wrap items-center justify-between gap-3 py-4">
                    <p class="font-mono text-[10px] text-[var(--muted)]" role="status" aria-live="polite">
                        {{ busy ? t('books.loading') : hasFilters ? t('books.results', books.total) : t('books.hint') }}
                    </p>
                    <button v-if="hasFilters" type="button" class="min-h-11 text-xs text-[var(--accent)] underline underline-offset-4" :disabled="busy" @click="clearFilters">{{ t('books.clear') }} <span aria-hidden="true">×</span></button>
                </div>

                <div :aria-busy="busy" :class="{ 'opacity-60': busy }" class="transition-opacity">
                    <section v-for="shelf in shelves" :key="shelf.category" class="library-shelf mb-10 last:mb-0" :aria-labelledby="`shelf-${shelf.category}`">
                        <div class="flex items-center gap-4 border border-[var(--line)] bg-[var(--paper-raised)] px-4 py-5 sm:px-6">
                            <span class="grid h-10 w-10 shrink-0 place-items-center border border-[var(--line)] font-mono text-xs text-[var(--accent)]">{{ shelf.number }}</span>
                            <div class="min-w-0 flex-1">
                                <h2 :id="`shelf-${shelf.category}`" class="text-xl font-semibold tracking-[-0.04em] sm:text-2xl">{{ t(`books.${shelf.category}`) }}</h2>
                                <p class="mt-1 text-xs leading-5 text-[var(--muted)]">{{ t(`books.${shelf.category}Note`) }}</p>
                            </div>
                            <span class="hidden font-mono text-[10px] text-[var(--muted)] sm:block">{{ t('books.volumes', counts[shelf.category]) }}</span>
                        </div>
                        <div class="library-book-grid grid grid-cols-2 border-l border-[var(--line)] md:grid-cols-3 xl:grid-cols-4">
                            <button
                                v-for="(book, index) in shelf.books" :key="book.id"
                                type="button" class="library-book-card group min-w-0 text-left"
                                :disabled="busy"
                                :aria-label="t('books.open', { title: book.title, author: book.author })"
                                aria-haspopup="dialog" @click="openBook(book, $event)"
                            >
                                <span class="library-book-stage">
                                    <span class="absolute left-3 top-3 font-mono text-[9px] text-[var(--muted)] sm:left-5 sm:top-4" aria-hidden="true">{{ String(index + 1).padStart(2, '0') }}</span>
                                    <BookCover3d :book="book" />
                                </span>
                                <span class="flex gap-2 px-3 pb-5 pt-5 sm:px-5 sm:pb-6">
                                    <span class="min-w-0 flex-1">
                                        <span class="block break-words text-sm font-semibold leading-5 tracking-[-0.025em] transition-colors group-hover:text-[var(--accent)] sm:text-base">{{ book.title }}</span>
                                        <span class="mt-2 block break-words text-xs leading-5 text-[var(--muted)]">{{ book.author }}</span>
                                    </span>
                                    <span class="text-base text-[var(--accent)]" aria-hidden="true">↗</span>
                                </span>
                            </button>
                        </div>
                    </section>

                    <div v-if="!visibleBooks.length" class="border border-[var(--line)] bg-[var(--paper-raised)] px-5 py-16 text-center sm:py-24">
                        <div class="library-empty-spines mx-auto mb-8" aria-hidden="true"><span></span><span></span><span></span></div>
                        <h2 class="text-2xl font-medium tracking-[-0.04em]">{{ t(hasFilters ? 'books.noResults' : 'books.emptyTitle') }}</h2>
                        <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-[var(--muted)]">{{ t(hasFilters ? 'books.noResultsNote' : 'books.emptyNote') }}</p>
                        <button v-if="hasFilters" class="action-secondary mt-7" type="button" :disabled="busy" @click="clearFilters">{{ t('books.clear') }}</button>
                        <Link v-else-if="page.props.auth?.user" :href="route('admin.books.create')" class="action-primary mt-7">{{ t('books.addFirst') }} <span aria-hidden="true">↗</span></Link>
                    </div>
                </div>

                <nav v-if="books.last_page > 1" class="mt-10 flex flex-wrap items-center justify-center gap-4" :aria-label="t('books.pagination')">
                    <Link v-if="books.prev_page_url" :href="books.prev_page_url" class="library-filter">← {{ t('books.previousPage') }}</Link>
                    <span v-else class="library-filter opacity-40" aria-disabled="true">← {{ t('books.previousPage') }}</span>
                    <span class="font-mono text-[10px] text-[var(--muted)]" aria-current="page">{{ t('books.page', { current: books.current_page, total: books.last_page }) }}</span>
                    <Link v-if="books.next_page_url" :href="books.next_page_url" class="library-filter">{{ t('books.nextPage') }} →</Link>
                    <span v-else class="library-filter opacity-40" aria-disabled="true">{{ t('books.nextPage') }} →</span>
                </nav>
                <div class="mt-12 flex flex-wrap justify-between gap-3 border-t border-[var(--line)] pt-5 font-mono text-[9px] uppercase tracking-widest text-[var(--muted)]">
                    <span>{{ t('books.label') }} / {{ t('books.volumes', counts.total) }}</span>
                    <span>{{ t('books.footer') }}</span>
                </div>
            </div>
        </section>

        <dialog ref="dialog" class="library-dialog" aria-labelledby="book-detail-title" @cancel.prevent="closeBook" @click="(event) => { if (event.target === event.currentTarget) closeBook(); }" @keydown="handleDialogKeys">
            <div v-if="selectedBook" class="library-detail text-[var(--ink)]">
                <div class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-[var(--line)] bg-[var(--paper-raised)] px-5 py-3">
                    <p class="technical-label !text-[var(--accent)]">{{ t(`books.${selectedBook.category}`) }}</p>
                    <button type="button" autofocus :aria-label="t('books.close')" class="grid h-11 w-11 place-items-center border border-[var(--line)] text-xl transition-colors hover:border-[var(--ink)]" @click="closeBook">×</button>
                </div>
                <div class="grid md:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                    <div class="library-detail-preview border-b border-[var(--line)] px-7 pb-6 pt-1 md:border-b-0 md:border-r md:pt-6">
                        <BookCover3d :key="selectedBook.id" :book="selectedBook" large :rotation="rotation" :interactive="false" />
                        <div class="mx-auto max-w-56">
                            <div class="mb-2 flex items-center justify-between">
                                <label for="book-rotation" class="font-mono text-[9px] uppercase tracking-wider text-[var(--muted)]">{{ t('books.rotate') }}</label>
                                <button type="button" class="grid h-8 w-8 place-items-center text-lg text-[var(--accent)]" :aria-label="t('books.resetRotation')" @click="rotation = -24">↺</button>
                            </div>
                            <input id="book-rotation" v-model.number="rotation" type="range" min="-35" max="35" step="1" class="h-7 w-full cursor-pointer accent-[var(--accent)]" />
                        </div>
                    </div>
                    <div class="min-w-0 p-6 sm:p-8 md:py-12" aria-live="polite" aria-atomic="true">
                        <h2 id="book-detail-title" class="break-words text-3xl font-semibold leading-[1.08] tracking-[-0.05em] sm:text-4xl">{{ selectedBook.title }}</h2>
                        <p class="technical-label mt-7">{{ t('books.author') }}</p>
                        <p class="mt-2 break-words text-base">{{ selectedBook.author }}</p>
                        <a v-if="purchaseUrl" :href="purchaseUrl" target="_blank" rel="noopener noreferrer" class="action-primary mt-6 w-full sm:w-auto">
                            {{ t('books.buyBook') }}
                            <span aria-hidden="true">↗</span>
                            <span class="sr-only">{{ t('books.opensNewTab') }}</span>
                        </a>
                        <div class="mt-8 border-t border-[var(--line)] pt-5">
                            <h3 class="technical-label">{{ t('books.notes') }}</h3>
                            <p class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-[var(--muted)]">{{ selectedBook.description || t('books.noNotes') }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[var(--line)] px-4 py-3 sm:px-5">
                    <button type="button" class="library-detail-nav" :aria-label="t('books.previousBook')" :disabled="selectedIndex <= 0" @click="changeBook(-1)">← <span class="hidden sm:inline">{{ t('books.previousBook') }}</span></button>
                    <p class="font-mono text-[9px] text-[var(--muted)]">{{ t('books.position', { current: selectedIndex + 1, total: visibleBooks.length }) }}</p>
                    <button type="button" class="library-detail-nav" :aria-label="t('books.nextBook')" :disabled="selectedIndex >= visibleBooks.length - 1" @click="changeBook(1)"><span class="hidden sm:inline">{{ t('books.nextBook') }}</span> →</button>
                </div>
            </div>
        </dialog>
    </PublicLayout>
</template>

<style scoped>
.library-hero { background: var(--paper-raised); }
.library-hero-art { isolation: isolate; }
.library-spines { display: flex; align-items: end; justify-content: center; gap: 8px; height: 205px; margin: 15px 0; perspective: 800px; transform: rotate(-8deg); }
.library-spine { display: flex; justify-content: center; width: 49px; border: 1px solid #171717; box-shadow: inset -6px 0 0 #0002, 9px 8px 0 #0002; transform: rotateY(-15deg); }
.library-spine > span { padding: 18px 0; writing-mode: vertical-rl; font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 600; letter-spacing: 0.13em; text-transform: uppercase; }
.library-spine--one { height: 188px; background: #ebe7dc; color: #242b44; }
.library-spine--two { height: 165px; background: #222d48; color: #f4e8d6; }
.library-spine--three { height: 199px; width: 36px; background: #bd6248; color: #fff5df; transform: rotateY(-15deg) rotate(10deg); transform-origin: bottom; }
.library-filter { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; padding: 0 14px; border: 1px solid var(--line); background: var(--paper-raised); color: var(--muted); font-family: 'IBM Plex Mono', monospace; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; transition: color .2s, background .2s, border-color .2s; }
.library-filter:hover { border-color: var(--ink); color: var(--ink); }
.library-filter[aria-pressed='true'] { background: var(--ink); color: var(--paper); border-color: var(--ink); }
.library-filter:disabled { cursor: wait; opacity: .65; }
.library-book-card { display: flex; flex-direction: column; border-right: 1px solid var(--line); border-bottom: 1px solid var(--line); background: var(--paper-raised); }
.library-book-stage { position: relative; display: block; width: 100%; padding: 16px 14px 0; background: radial-gradient(ellipse at 50% 85%, color-mix(in srgb, var(--ink) 5%, transparent), transparent 65%), var(--paper); }
.library-book-stage::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 7px; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); background: var(--paper-raised); box-shadow: 0 5px 7px #0000000b; }
.library-book-card:focus-visible { position: relative; outline: 2px solid var(--accent); outline-offset: -3px; z-index: 1; }
.library-empty-spines { display: flex; justify-content: center; align-items: end; gap: 5px; width: 110px; height: 78px; border-bottom: 2px solid var(--line); }
.library-empty-spines span { height: 62px; width: 19px; border: 1px solid var(--accent); background: color-mix(in srgb, var(--accent) 6%, var(--paper)); }
.library-empty-spines span:nth-child(2) { height: 72px; }
.library-empty-spines span:nth-child(3) { height: 56px; transform: rotate(-14deg); transform-origin: bottom left; }
.library-dialog { width: min(850px, calc(100% - 32px)); max-width: none; max-height: calc(100dvh - 32px); margin: auto; padding: 0; overflow-y: auto; overscroll-behavior: contain; border: 1px solid var(--ink); background: var(--paper-raised); color: var(--ink); box-shadow: 8px 8px 0 var(--accent); }
.library-dialog::backdrop { background: #080b17bd; backdrop-filter: blur(5px); }
.library-detail { min-width: 0; }
.library-detail-preview { background: radial-gradient(ellipse at center, color-mix(in srgb, var(--accent) 8%, transparent), transparent 65%), var(--paper); }
.library-detail-nav { display: inline-flex; min-height: 44px; min-width: 44px; align-items: center; justify-content: center; gap: 10px; font-family: 'IBM Plex Mono', monospace; font-size: 10px; color: var(--accent); }
.library-detail-nav:disabled { color: var(--muted); opacity: .35; cursor: not-allowed; }
button:focus-visible, a:focus-visible, input[type='range']:focus-visible { outline: 2px solid var(--accent); outline-offset: 4px; }
@media (max-width: 767px) {
    .library-hero-art { min-height: 134px; flex-direction: row; align-items: end; }
    .library-hero-art > div:first-child { align-self: start; flex-direction: column; gap: 10px; }
    .library-spines { position: absolute; height: 150px; margin: 0; top: 14px; right: 38px; transform: rotate(-12deg); }
    .library-spine--one { height: 134px; }
    .library-spine--two { height: 113px; }
    .library-spine--three { height: 145px; }
    .library-spine > span { font-size: 8px; padding: 12px 0; }
    .library-spine { width: 34px; }
    .library-spine--three { width: 26px; }
    .library-book-stage { padding: 10px 10px 0; }
    .library-dialog { width: calc(100% - 24px); max-height: calc(100dvh - 24px); box-shadow: 4px 4px 0 var(--accent); }
}
@media (prefers-reduced-motion: reduce) { .library-filter { transition: none; } }
</style>
