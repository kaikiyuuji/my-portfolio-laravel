<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/Composables/useScrollReveal';

const props = defineProps({
    posts: {
        type: Object,
        required: true,
    },
});

const { t, tm, locale } = useI18n();
useScrollReveal('.reveal');

function tr(field) {
    if (field == null) return '';
    if (typeof field === 'object') {
        return field[locale.value] || field.pt || field.en || '';
    }
    return field;
}

const items = computed(() => props.posts.data ?? []);

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
</script>

<template>
    <Head :title="t('blog.title')" />

    <PublicLayout :profile-name="t('blog.title')">
        <!-- Header -->
        <section class="relative overflow-hidden border-b border-black/10 bg-neutral-50 dark:border-white/10 dark:bg-neutral-950">
            <div class="grid-bg absolute inset-0 opacity-50 [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]"></div>
            <div class="relative mx-auto max-w-5xl px-6 py-20 text-center md:py-28">
                <p class="reveal mb-3 text-xs font-bold uppercase tracking-[0.3em] text-black/50 dark:text-white/50">
                    {{ t('blog.label') }}
                </p>
                <h1 class="reveal reveal-delay-1 mb-4 text-5xl font-black tracking-tight sm:text-6xl">
                    <span class="text-gradient-mono">{{ t('blog.title') }}</span>
                </h1>
                <p class="reveal reveal-delay-2 mx-auto max-w-2xl text-base text-black/65 dark:text-white/65">
                    {{ t('blog.subtitle') }}
                </p>
            </div>
        </section>

        <!-- Listing -->
        <section class="py-20">
            <div class="mx-auto max-w-6xl px-6">
                <div v-if="items.length" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="(post, i) in items"
                        :key="post.id"
                        :href="route('blog.show', post.slug)"
                        class="reveal group flex flex-col overflow-hidden rounded-3xl border border-black/10 bg-white transition-all duration-500 hover:-translate-y-2 hover:border-black hover:shadow-2xl dark:border-white/10 dark:bg-black dark:hover:border-white"
                        :class="`reveal-delay-${(i % 4) + 1}`"
                    >
                        <div class="relative aspect-[16/10] overflow-hidden bg-neutral-100 dark:bg-neutral-900">
                            <img
                                v-if="projectImage(post.image_path)"
                                :src="projectImage(post.image_path)"
                                :alt="tr(post.title)"
                                class="h-full w-full object-cover object-center transition-transform duration-700 group-hover:scale-110"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-6xl font-black text-black/15 dark:text-white/15"
                            >
                                {{ tr(post.title).charAt(0) }}
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="mb-3 flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                                <span>{{ formatDate(post.published_at || post.created_at) }}</span>
                                <span class="text-black/20 dark:text-white/20">·</span>
                                <span>{{ readingTime(post.body) }} min</span>
                            </div>
                            <h2 class="mb-2 text-2xl font-black tracking-tight transition-colors group-hover:text-black dark:group-hover:text-white">
                                {{ tr(post.title) }}
                            </h2>
                            <p v-if="tr(post.excerpt)" class="flex-1 text-sm leading-relaxed text-black/65 dark:text-white/65">
                                {{ tr(post.excerpt) }}
                            </p>
                            <span class="mt-5 inline-flex items-center gap-1 text-sm font-bold transition-transform group-hover:translate-x-1">
                                {{ t('blog.readMore') }}
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </span>
                        </div>
                    </Link>
                </div>

                <div v-else class="reveal py-24 text-center">
                    <p class="text-lg font-semibold text-black/60 dark:text-white/60">{{ t('blog.empty') }}</p>
                </div>

                <!-- Pagination -->
                <nav v-if="items.length && posts.last_page > 1" class="reveal mt-16 flex items-center justify-center gap-2">
                    <Link
                        v-for="link in posts.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-bold transition-all"
                        :class="link.active
                            ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black'
                            : link.url
                                ? 'border-black/15 bg-white hover:border-black dark:border-white/15 dark:bg-black dark:hover:border-white'
                                : 'border-black/5 bg-white/50 text-black/30 cursor-not-allowed dark:border-white/5 dark:bg-black/50 dark:text-white/30'"
                        :preserve-scroll="true"
                    />
                </nav>
            </div>
        </section>
    </PublicLayout>
</template>
