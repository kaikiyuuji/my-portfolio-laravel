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

const projectImage = (path) => (path ? '/storage/' + path : null);

function formatDate(iso) {
    if (!iso) return '';
    const months = tm('months');
    const d = new Date(iso);
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

function readingTime(body) {
    const text = tr(body) || '';
    const words = text.replace(/<[^>]+>/g, '').split(/\s+/).filter(Boolean).length;
    return Math.max(1, Math.ceil(words / 220));
}

// Render pipeline: tr() picks locale → marked() converts Markdown to HTML
// (raw HTML in source passes through) → DOMPurify strips scripts/event handlers
// before injection via v-html. Defense-in-depth against compromised admin account.
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
    const plain = (tr(props.post.body) || '').replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim();
    return plain.slice(0, 160);
});
const ogImage = computed(() => {
    const path = projectImage(props.post.image_path);
    if (!path) return null;
    if (typeof window === 'undefined') return path;
    return new URL(path, window.location.origin).toString();
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
            <!-- Hero -->
            <header class="relative overflow-hidden border-b border-black/10 dark:border-white/10">
                <div class="grid-bg absolute inset-0 opacity-40 [mask-image:radial-gradient(ellipse_at_top,black_20%,transparent_70%)]"></div>
                <div class="relative mx-auto max-w-3xl px-6 py-16 md:py-24">
                    <Link
                        :href="route('blog.index')"
                        class="reveal mb-6 inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-black/50 transition-colors hover:text-black dark:text-white/50 dark:hover:text-white"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        {{ t('blog.backToList') }}
                    </Link>

                    <div class="reveal reveal-delay-1 mb-4 flex flex-wrap items-center gap-3 text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-black px-3 py-1 text-white dark:bg-white dark:text-black">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ formatDate(post.published_at || post.created_at) }}
                        </span>
                        <span>{{ readingTime(post.body) }} min</span>
                    </div>

                    <h1 class="reveal reveal-delay-2 mb-6 text-4xl font-black leading-tight tracking-tight sm:text-5xl md:text-6xl">
                        <span class="text-gradient-mono">{{ tr(post.title) }}</span>
                    </h1>

                    <p
                        v-if="tr(post.excerpt)"
                        class="reveal reveal-delay-3 text-lg leading-relaxed text-black/70 dark:text-white/70"
                    >
                        {{ tr(post.excerpt) }}
                    </p>
                </div>
            </header>

            <!-- Cover -->
            <div v-if="projectImage(post.image_path)" class="reveal mx-auto max-w-4xl px-6 py-8">
                <div class="overflow-hidden rounded-3xl border border-black/10 dark:border-white/10">
                    <img
                        :src="projectImage(post.image_path)"
                        :alt="tr(post.title)"
                        class="aspect-[16/9] w-full object-cover object-center"
                    />
                </div>
            </div>

            <!-- Body -->
            <div class="mx-auto max-w-3xl px-6 py-12">
                <div
                    class="reveal prose-blog text-base leading-7 text-black/85 dark:text-white/85"
                    v-html="sanitizedBody"
                ></div>
            </div>
        </article>

        <!-- Related -->
        <section v-if="related.length" class="border-t border-black/10 bg-neutral-50 py-20 dark:border-white/10 dark:bg-neutral-950">
            <div class="mx-auto max-w-6xl px-6">
                <h2 class="reveal mb-10 text-center text-2xl font-black tracking-tight sm:text-3xl">
                    {{ t('blog.related') }}
                </h2>
                <div class="grid gap-6 md:grid-cols-3">
                    <Link
                        v-for="(rel, i) in related"
                        :key="rel.id"
                        :href="route('blog.show', rel.slug)"
                        class="reveal group flex flex-col overflow-hidden rounded-2xl border border-black/10 bg-white transition-all duration-500 hover:-translate-y-1 hover:border-black hover:shadow-xl dark:border-white/10 dark:bg-black dark:hover:border-white"
                        :class="`reveal-delay-${(i % 3) + 1}`"
                    >
                        <div class="aspect-[16/10] overflow-hidden bg-neutral-100 dark:bg-neutral-900">
                            <img
                                v-if="projectImage(rel.image_path)"
                                :src="projectImage(rel.image_path)"
                                :alt="tr(rel.title)"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                loading="lazy"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-5xl font-black text-black/15 dark:text-white/15">
                                {{ tr(rel.title).charAt(0) }}
                            </div>
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-black/50 dark:text-white/50">
                                {{ formatDate(rel.published_at || rel.created_at) }}
                            </p>
                            <h3 class="text-lg font-black tracking-tight">{{ tr(rel.title) }}</h3>
                        </div>
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
.prose-blog :deep(h1) { font-size: 2rem; font-weight: 800; margin-top: 2rem; margin-bottom: 1rem; letter-spacing: -0.02em; }
.prose-blog :deep(h2) { font-size: 1.6rem; font-weight: 800; margin-top: 1.75rem; margin-bottom: 0.75rem; letter-spacing: -0.02em; }
.prose-blog :deep(h3) { font-size: 1.3rem; font-weight: 700; margin-top: 1.5rem; margin-bottom: 0.5rem; }
.prose-blog :deep(p) { margin-bottom: 1rem; }
.prose-blog :deep(a) { color: inherit; text-decoration: underline; text-underline-offset: 3px; font-weight: 600; }
.prose-blog :deep(ul), .prose-blog :deep(ol) { margin: 1rem 0; padding-left: 1.5rem; }
.prose-blog :deep(ul) { list-style: disc; }
.prose-blog :deep(ol) { list-style: decimal; }
.prose-blog :deep(li) { margin-bottom: 0.25rem; }
.prose-blog :deep(blockquote) { border-left: 3px solid currentColor; padding-left: 1rem; margin: 1.25rem 0; font-style: italic; opacity: 0.8; }
.prose-blog :deep(code) { background: rgba(0,0,0,0.06); padding: 0.15rem 0.4rem; border-radius: 0.375rem; font-size: 0.9em; }
:global(.dark) .prose-blog :deep(code) { background: rgba(255,255,255,0.1); }
.prose-blog :deep(pre) { background: #000; color: #fff; padding: 1rem 1.25rem; border-radius: 0.75rem; overflow-x: auto; margin: 1.25rem 0; font-size: 0.875rem; }
:global(.dark) .prose-blog :deep(pre) { background: #fff; color: #000; }
.prose-blog :deep(pre code) { background: transparent; padding: 0; color: inherit; }
.prose-blog :deep(img) { border-radius: 1rem; margin: 1.5rem 0; }
.prose-blog :deep(hr) { border: none; border-top: 1px solid currentColor; opacity: 0.15; margin: 2rem 0; }
</style>
