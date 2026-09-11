<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/Composables/useScrollReveal';
import { useTranslatable } from '@/Composables/useTranslatable';

const props = defineProps({
    posts: {
        type: Object,
        required: true,
    },
});

const { t, tm } = useI18n();
const { tr } = useTranslatable();
useScrollReveal('.reveal');

const items = computed(() => props.posts.data ?? []);
const ogTitle = computed(() => t('blog.title'));
const ogDescription = computed(() => t('meta.blogIndexDescription'));
const canonicalUrl = computed(() =>
    typeof window !== 'undefined' ? window.location.origin + window.location.pathname : null,
);

const postImage = (post) => post?.image_url ?? null;

function formatDate(iso) {
    if (!iso) return '';
    const months = tm('months');
    const date = new Date(iso);
    return `${String(date.getDate()).padStart(2, '0')} ${months[date.getMonth()]} ${date.getFullYear()}`;
}

</script>

<template>
    <Head :title="ogTitle">
        <meta name="description" :content="ogDescription" />
        <link v-if="canonicalUrl" rel="canonical" :href="canonicalUrl" />
        <meta property="og:type" content="website" />
        <meta property="og:title" :content="ogTitle" />
        <meta property="og:description" :content="ogDescription" />
        <meta v-if="canonicalUrl" property="og:url" :content="canonicalUrl" />
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" :content="ogTitle" />
        <meta name="twitter:description" :content="ogDescription" />
    </Head>

    <PublicLayout :profile-name="t('blog.title')">
        <section class="relative overflow-hidden border-b border-[var(--line)]">
            <div class="blueprint-grid absolute inset-y-0 right-0 hidden w-[30%] border-l border-[var(--ink)] lg:block"></div>
            <div class="relative mx-auto grid max-w-7xl border-x border-[var(--line)] lg:grid-cols-12">
                <div class="px-5 py-16 sm:px-8 sm:py-20 lg:col-span-8 lg:px-12 lg:py-28">
                    <p class="technical-label reveal mb-5 text-[var(--accent)]">
                        01 / {{ t('blog.label') }}
                    </p>
                    <h1 class="reveal reveal-delay-1 text-6xl font-medium leading-[0.84] tracking-[-0.075em] sm:text-8xl lg:text-9xl">
                        {{ t('blog.title') }}
                    </h1>
                    <p class="reveal reveal-delay-2 mt-8 max-w-2xl text-base leading-7 text-[var(--muted)] sm:text-lg">
                        {{ t('blog.subtitle') }}
                    </p>
                </div>

                <div class="dot-field-muted relative min-h-36 border-t border-[var(--line)] sm:min-h-44 lg:col-span-4 lg:min-h-56 lg:border-l lg:border-t-0">
                    <span class="absolute left-5 top-5 border border-[var(--line)] bg-[var(--paper-raised)] px-2 py-1 font-mono text-lg font-semibold text-[var(--accent)] sm:text-2xl lg:left-6 lg:top-6 lg:border-0 lg:bg-transparent lg:p-0 lg:text-5xl lg:font-light">B</span>
                    <div class="absolute bottom-4 right-4 border border-[var(--ink)] bg-[var(--paper-raised)] px-3 py-2 text-right shadow-[3px_3px_0_var(--accent)] sm:bottom-5 sm:right-5 lg:bottom-6 lg:right-6 lg:px-4 lg:py-3 lg:shadow-[5px_5px_0_var(--accent)]">
                        <p class="technical-label text-[var(--ink)]">{{ t('blog.journalIndex') }}</p>
                        <p class="technical-label mt-1">{{ String(items.length).padStart(2, '0') }} {{ t('blog.entries') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="portfolio-section py-16 sm:py-24">
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div v-if="items.length" class="grid border-l border-t border-[var(--line)] md:grid-cols-2 lg:grid-cols-12">
                    <Link
                        v-for="(post, index) in items"
                        :key="post.id"
                        :href="route('blog.show', post.slug)"
                        class="reveal group flex min-h-full flex-col border-b border-r border-[var(--line)] bg-[var(--paper-raised)] transition-all duration-300 hover:z-10 hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-[7px_7px_0_var(--accent)]"
                        :class="[
                            `reveal-delay-${(index % 4) + 1}`,
                            index % 3 === 0 ? 'lg:col-span-7' : 'lg:col-span-5',
                        ]"
                    >
                        <div class="relative aspect-[16/9] overflow-hidden border-b border-[var(--line)] bg-[var(--paper)]">
                            <img
                                v-if="postImage(post)"
                                :src="postImage(post)"
                                :alt="tr(post.title)"
                                class="h-full w-full object-cover object-center transition-transform duration-700 group-hover:scale-[1.025]"
                                loading="lazy"
                            />
                            <div v-else class="dot-field grid h-full place-items-center">
                                <span class="text-7xl font-medium tracking-[-0.08em] text-[var(--accent)]">
                                    {{ tr(post.title).charAt(0) }}
                                </span>
                            </div>
                            <span class="absolute left-3 top-3 border border-[var(--ink)] bg-[var(--paper-raised)] px-2 py-1 font-mono text-[9px] font-bold uppercase tracking-widest">
                                {{ t('blog.entry') }} {{ String(index + 1).padStart(2, '0') }}
                            </span>
                        </div>

                        <div class="flex flex-1 flex-col p-5 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-center gap-3 font-mono text-[9px] font-semibold uppercase tracking-wider text-[var(--muted)]">
                                <span class="text-[var(--accent)]">■</span>
                                <span>{{ formatDate(post.published_at || post.created_at) }}</span>
                            </div>
                            <h2 class="text-2xl font-semibold leading-[1.05] tracking-[-0.045em] transition-colors group-hover:text-[var(--accent)] sm:text-3xl">
                                {{ tr(post.title) }}
                            </h2>
                            <p v-if="tr(post.excerpt)" class="mt-5 flex-1 text-sm leading-7 text-[var(--muted)]">
                                {{ tr(post.excerpt) }}
                            </p>
                            <span class="mt-7 flex items-center justify-between border-t border-[var(--line)] pt-4 font-mono text-[10px] font-bold uppercase tracking-wider">
                                {{ t('blog.readMore') }}
                                <span class="text-lg text-[var(--accent)] transition-transform group-hover:translate-x-1">→</span>
                            </span>
                        </div>
                    </Link>
                </div>

                <div v-else class="reveal border border-[var(--line)] bg-[var(--paper-raised)] px-6 py-24 text-center">
                    <p class="technical-label text-[var(--ink)]">{{ t('blog.empty') }}</p>
                </div>

                <nav
                    v-if="items.length && posts.last_page > 1"
                    class="reveal mt-12 flex flex-wrap items-center justify-center gap-2"
                    aria-label="Paginação"
                >
                    <template v-for="link in posts.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="grid min-h-10 min-w-10 place-items-center border px-3 font-mono text-[10px] font-bold uppercase transition-all"
                            :class="link.active
                                ? 'border-[var(--accent)] bg-[var(--accent)] text-white'
                                : 'border-[var(--line)] bg-[var(--paper-raised)] hover:border-[var(--ink)]'"
                            :preserve-scroll="true"
                            :aria-current="link.active ? 'page' : undefined"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="grid min-h-10 min-w-10 cursor-not-allowed place-items-center border border-[var(--line)] bg-[var(--paper)] px-3 font-mono text-[10px] font-bold text-[var(--muted)] opacity-45"
                            aria-disabled="true"
                        />
                    </template>
                </nav>
            </div>
        </section>
    </PublicLayout>
</template>
