<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Head, Link } from '@inertiajs/vue3';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/Composables/useScrollReveal';
import { useTranslatable } from '@/Composables/useTranslatable';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    related: {
        type: Array,
        default: () => [],
    },
});

const { t, tm } = useI18n();
const { tr } = useTranslatable();
useScrollReveal('.reveal');

const postImage = (post) => post?.image_url ?? null;

function formatDate(iso) {
    if (!iso) return '';
    const months = tm('months');
    const date = new Date(iso);
    return `${String(date.getDate()).padStart(2, '0')} ${months[date.getMonth()]} ${date.getFullYear()}`;
}

marked.setOptions({ gfm: true, breaks: false });

const sanitizedBody = computed(() => {
    const raw = tr(props.post.body) || '';
    const html = marked.parse(raw);

    return DOMPurify.sanitize(html, {
        USE_PROFILES: { html: true },
        FORBID_TAGS: ['style', 'iframe', 'form', 'object', 'embed'],
        FORBID_ATTR: ['style', 'onerror', 'onload', 'onclick'],
    });
});

const ogTitle = computed(() => tr(props.post.title));
const ogDescription = computed(() => {
    const excerpt = tr(props.post.excerpt);
    if (excerpt) return excerpt;

    return (tr(props.post.body) || '')
        .replace(/<[^>]+>/g, '')
        .replace(/\s+/g, ' ')
        .trim()
        .slice(0, 160);
});
const ogImage = computed(() => {
    const url = postImage(props.post);
    if (!url) return null;
    if (typeof window === 'undefined') return url;
    return url.startsWith('http') ? url : new URL(url, window.location.origin).toString();
});
const canonicalUrl = computed(() =>
    typeof window !== 'undefined' ? window.location.origin + window.location.pathname : null,
);
</script>

<template>
    <Head :title="ogTitle">
        <meta name="description" :content="ogDescription" />
        <link v-if="canonicalUrl" rel="canonical" :href="canonicalUrl" />
        <meta property="og:type" content="article" />
        <meta property="og:title" :content="ogTitle" />
        <meta property="og:description" :content="ogDescription" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />
        <meta v-if="canonicalUrl" property="og:url" :content="canonicalUrl" />
        <meta v-if="post.published_at" property="article:published_time" :content="post.published_at" />
        <meta name="twitter:card" :content="ogImage ? 'summary_large_image' : 'summary'" />
        <meta name="twitter:title" :content="ogTitle" />
        <meta name="twitter:description" :content="ogDescription" />
        <meta v-if="ogImage" name="twitter:image" :content="ogImage" />
    </Head>

    <PublicLayout :profile-name="t('blog.title')">
        <article>
            <header class="relative overflow-hidden border-b border-[var(--line)]">
                <div class="absolute inset-y-0 right-0 hidden w-[24%] border-l border-[var(--line)] lg:block">
                    <div class="dot-field h-full w-full"></div>
                </div>

                <div class="relative mx-auto grid max-w-7xl border-x border-[var(--line)] lg:grid-cols-12">
                    <div class="px-5 py-12 sm:px-8 sm:py-16 lg:col-span-9 lg:px-12 lg:py-20">
                        <Link
                            :href="route('blog.index')"
                            class="technical-label reveal inline-flex items-center gap-2 border border-[var(--line)] bg-[var(--paper-raised)] px-3 py-2 text-[var(--ink)] transition-all hover:border-[var(--accent)] hover:text-[var(--accent)]"
                        >
                            <span aria-hidden="true">←</span>
                            {{ t('blog.backToList') }}
                        </Link>

                        <div class="reveal reveal-delay-1 mt-8 flex flex-wrap items-center gap-3 font-mono text-[9px] font-semibold uppercase tracking-wider text-[var(--muted)]">
                            <span class="text-[var(--accent)]">■</span>
                            <span class="border border-[var(--ink)] bg-[var(--ink)] px-3 py-2 text-[var(--paper)]">
                                {{ formatDate(post.published_at || post.created_at) }}
                            </span>
                        </div>

                        <h1 class="reveal reveal-delay-2 mt-7 max-w-5xl text-4xl font-semibold leading-[0.96] tracking-[-0.06em] sm:text-6xl lg:text-7xl">
                            {{ tr(post.title) }}
                        </h1>

                        <p
                            v-if="tr(post.excerpt)"
                            class="reveal reveal-delay-3 mt-7 max-w-3xl text-base leading-8 text-[var(--muted)] sm:text-lg"
                        >
                            {{ tr(post.excerpt) }}
                        </p>
                    </div>

                    <aside class="blueprint-grid relative hidden border-l border-[var(--ink)] lg:col-span-3 lg:block">
                        <span class="absolute left-7 top-7 font-mono text-6xl font-light">A</span>
                        <div class="absolute bottom-7 right-7 text-right">
                            <p class="font-mono text-[10px] font-bold uppercase tracking-[0.18em]">{{ t('blog.articleFile') }}</p>
                            <p class="mt-1 font-mono text-[10px] uppercase tracking-[0.18em] text-white/70">01 / {{ t('blog.read') }}</p>
                        </div>
                    </aside>
                </div>
            </header>

            <div class="portfolio-section py-8 sm:py-12">
                <div class="relative mx-auto max-w-5xl px-4 sm:px-6">
                    <figure
                        v-if="postImage(post)"
                        class="reveal mb-6 border border-[var(--ink)] bg-[var(--paper-raised)] p-2 sm:mb-8 sm:p-3"
                    >
                        <img
                            :src="postImage(post)"
                            :alt="tr(post.title)"
                            class="aspect-[16/9] w-full object-cover object-center"
                        />
                        <figcaption class="flex items-center justify-between px-1 pb-1 pt-3">
                            <span class="technical-label">{{ t('blog.coverImage') }}</span>
                            <span class="technical-label text-[var(--accent)]">Fig. 01</span>
                        </figcaption>
                    </figure>

                    <div class="blog-paper reveal border border-[var(--line)] bg-[var(--paper-raised)] px-5 py-9 shadow-[8px_8px_0_color-mix(in_srgb,var(--accent)_16%,transparent)] sm:px-10 sm:py-12 lg:px-16">
                        <div
                            class="prose-blog text-base leading-8 text-[var(--ink)]"
                            v-html="sanitizedBody"
                        ></div>
                    </div>
                </div>
            </div>
        </article>

        <section v-if="related.length" class="portfolio-section py-16 sm:py-20">
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-end justify-between border-b border-[var(--line)] pb-5">
                    <div>
                        <p class="technical-label mb-3 text-[var(--accent)]">{{ t('blog.related') }} / 03</p>
                        <h2 class="text-3xl font-semibold tracking-[-0.045em] sm:text-4xl">
                            {{ t('blog.related') }}
                        </h2>
                    </div>
                    <span class="hidden font-mono text-3xl font-light text-[var(--accent)] sm:block">↘</span>
                </div>

                <div class="grid border-l border-t border-[var(--line)] md:grid-cols-3">
                    <Link
                        v-for="(relatedPost, index) in related"
                        :key="relatedPost.id"
                        :href="route('blog.show', relatedPost.slug)"
                        class="reveal group flex flex-col border-b border-r border-[var(--line)] bg-[var(--paper-raised)] transition-all duration-300 hover:z-10 hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-[6px_6px_0_var(--accent)]"
                        :class="`reveal-delay-${(index % 3) + 1}`"
                    >
                        <div class="aspect-[16/10] overflow-hidden border-b border-[var(--line)] bg-[var(--paper)]">
                            <img
                                v-if="postImage(relatedPost)"
                                :src="postImage(relatedPost)"
                                :alt="tr(relatedPost.title)"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-[1.025]"
                                loading="lazy"
                            />
                            <div v-else class="dot-field grid h-full place-items-center">
                                <span class="text-5xl font-medium text-[var(--accent)]">
                                    {{ tr(relatedPost.title).charAt(0) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <p class="technical-label mb-3">{{ formatDate(relatedPost.published_at || relatedPost.created_at) }}</p>
                            <h3 class="text-lg font-semibold leading-tight tracking-[-0.035em] transition-colors group-hover:text-[var(--accent)]">
                                {{ tr(relatedPost.title) }}
                            </h3>
                        </div>
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
.blog-paper {
    color-scheme: light dark;
}

.prose-blog :deep(h1) {
    margin: 2.5rem 0 1rem;
    font-size: 2.15rem;
    font-weight: 650;
    line-height: 1;
    letter-spacing: -0.045em;
}

.prose-blog :deep(h2) {
    margin: 2.25rem 0 0.85rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--line);
    font-size: 1.65rem;
    font-weight: 650;
    line-height: 1.1;
    letter-spacing: -0.035em;
}

.prose-blog :deep(h3) {
    margin: 1.75rem 0 0.65rem;
    font-size: 1.3rem;
    font-weight: 650;
}

.prose-blog :deep(p) {
    margin-bottom: 1.25rem;
}

.prose-blog :deep(a) {
    color: var(--accent);
    font-weight: 600;
    text-decoration: underline;
    text-decoration-thickness: 1px;
    text-underline-offset: 4px;
}

.prose-blog :deep(ul),
.prose-blog :deep(ol) {
    margin: 1.25rem 0;
    padding-left: 1.5rem;
}

.prose-blog :deep(ul) { list-style: square; }
.prose-blog :deep(ol) { list-style: decimal-leading-zero; }
.prose-blog :deep(li) { margin-bottom: 0.45rem; padding-left: 0.25rem; }
.prose-blog :deep(li::marker) { color: var(--accent); font-family: monospace; }

.prose-blog :deep(blockquote) {
    margin: 1.75rem 0;
    border-left: 4px solid var(--accent);
    background: color-mix(in srgb, var(--accent) 7%, var(--paper-raised));
    padding: 1rem 1.25rem;
    font-style: normal;
}

.prose-blog :deep(code) {
    border: 1px solid var(--line);
    background: color-mix(in srgb, var(--ink) 7%, var(--paper-raised));
    padding: 0.15rem 0.4rem;
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 0.88em;
}

.prose-blog :deep(pre) {
    margin: 1.75rem 0;
    overflow-x: auto;
    border: 1px solid var(--line);
    background: #0d0e12;
    color: #f4f2ea;
    padding: 1.25rem;
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 0.85rem;
    line-height: 1.7;
}

.prose-blog :deep(pre code) {
    border: 0;
    background: transparent;
    padding: 0;
    color: inherit;
}

.prose-blog :deep(img) {
    margin: 1.75rem 0;
    width: 100%;
    border: 1px solid var(--line);
}

.prose-blog :deep(hr) {
    margin: 2.5rem 0;
    border: 0;
    border-top: 1px solid var(--line);
}

.prose-blog :deep(table) {
    display: block;
    width: 100%;
    overflow-x: auto;
    border-collapse: collapse;
    margin: 1.75rem 0;
}

.prose-blog :deep(th),
.prose-blog :deep(td) {
    border: 1px solid var(--line);
    padding: 0.65rem 0.8rem;
    text-align: left;
}

.prose-blog :deep(th) {
    background: color-mix(in srgb, var(--ink) 7%, var(--paper-raised));
    font-family: 'IBM Plex Mono', ui-monospace, monospace;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
</style>
