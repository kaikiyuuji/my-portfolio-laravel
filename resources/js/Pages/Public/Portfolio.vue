<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Head } from '@inertiajs/vue3';
import AsciiName from '@/Components/AsciiName.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/Composables/useScrollReveal';
import { useTranslatable } from '@/Composables/useTranslatable';

const { t, tm } = useI18n();
const { tr } = useTranslatable();
const linkLibraryUrl = 'https://klink-hub.vercel.app/';

const props = defineProps({
    profile: { type: Object, required: true },
    stacks: { type: Array, default: () => [] },
    experiences: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    socialLinks: { type: Array, default: () => [] },
});

useScrollReveal('.reveal');

const hero = ref(null);
const failedIcons = reactive(new Set());
const attemptIndex = reactive({});
let heroScrollFrame = null;

function updateHeroScroll() {
    if (!hero.value) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        hero.value.style.setProperty('--hero-scroll-far', '0px');
        hero.value.style.setProperty('--hero-scroll-near', '0px');
        return;
    }

    const rect = hero.value.getBoundingClientRect();
    const progress = Math.max(-1, Math.min(1.5, -rect.top / Math.max(rect.height, 1)));
    hero.value.style.setProperty('--hero-scroll-far', `${progress * 15}px`);
    hero.value.style.setProperty('--hero-scroll-near', `${progress * 32}px`);
}

function handleHeroScroll() {
    if (heroScrollFrame) return;

    heroScrollFrame = window.requestAnimationFrame(() => {
        updateHeroScroll();
        heroScrollFrame = null;
    });
}

onMounted(() => {
    window.addEventListener('scroll', handleHeroScroll, { passive: true });
    updateHeroScroll();
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleHeroScroll);
    window.cancelAnimationFrame(heroScrollFrame);
});


function iconKey(prefix, id) {
    return `${prefix}-${id}`;
}

function iconSources(slug) {
    return [
        `https://cdn.simpleicons.org/${slug}/000000`,
        `https://api.iconify.design/mdi:${slug}.svg?color=%23000000`,
        `https://api.iconify.design/fa6-brands:${slug}.svg?color=%23000000`,
        `https://api.iconify.design/logos:${slug}.svg`,
    ];
}

function currentIconUrl(prefix, id, slug) {
    if (!slug) return null;
    const sources = iconSources(slug);
    const idx = attemptIndex[iconKey(prefix, id)] ?? 0;
    return sources[idx];
}

function onIconError(prefix, id, slug) {
    if (!slug) return;
    const sources = iconSources(slug);
    const key = iconKey(prefix, id);
    const idx = (attemptIndex[key] ?? 0) + 1;

    if (idx >= sources.length) {
        failedIcons.add(key);
        return;
    }

    attemptIndex[key] = idx;
}

function iconHasFailed(prefix, id) {
    return failedIcons.has(iconKey(prefix, id));
}

function shouldInvertIcon(prefix, id) {
    return (attemptIndex[iconKey(prefix, id)] ?? 0) < 3;
}

const avatarUrl = computed(() => props.profile?.avatar_url ?? null);

const ogTitle = computed(() => props.profile?.name || 'Portfolio');
const ogDescription = computed(() =>
    tr(props.profile?.bio) || tr(props.profile?.headline) || t('meta.defaultDescription'),
);
const ogImage = computed(() => {
    if (!avatarUrl.value) return null;
    if (typeof window === 'undefined') return avatarUrl.value;
    return new URL(avatarUrl.value, window.location.origin).toString();
});
const canonicalUrl = computed(() =>
    typeof window !== 'undefined' ? window.location.origin + window.location.pathname : null,
);

const projectImage = (project) => project.image_url ?? null;

const initials = computed(() => {
    const name = props.profile?.name || 'P';
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});

function formatPeriod(start, end) {
    if (!start) return '';
    const months = tm('months');
    const format = (iso) => {
        const date = new Date(iso);
        return `${months[date.getMonth()]} ${date.getFullYear()}`;
    };

    return `${format(start)} — ${end ? format(end) : t('experience.present')}`;
}
</script>

<template>
    <Head :title="ogTitle">
        <meta name="description" :content="ogDescription" />
        <link v-if="canonicalUrl" rel="canonical" :href="canonicalUrl" />
        <meta property="og:type" content="website" />
        <meta property="og:title" :content="ogTitle" />
        <meta property="og:description" :content="ogDescription" />
        <meta v-if="ogImage" property="og:image" :content="ogImage" />
        <meta v-if="canonicalUrl" property="og:url" :content="canonicalUrl" />
        <meta name="twitter:card" :content="ogImage ? 'summary_large_image' : 'summary'" />
        <meta name="twitter:title" :content="ogTitle" />
        <meta name="twitter:description" :content="ogDescription" />
        <meta v-if="ogImage" name="twitter:image" :content="ogImage" />
    </Head>

    <PublicLayout :profile-name="profile.name || 'Portfolio'">
        <section id="sobre" ref="hero" class="hero-stars relative overflow-hidden">
            <div class="star-field star-field--far" aria-hidden="true"></div>
            <div class="star-field star-field--near" aria-hidden="true"></div>
            <div class="absolute inset-y-0 right-0 hidden w-[38%] border-l border-[var(--line)] lg:block"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                <div class="grid min-h-[calc(100vh-130px)] items-stretch border-x border-[var(--line)] lg:grid-cols-12">
                    <div class="flex flex-col border-b border-[var(--line)] p-5 sm:p-8 lg:col-span-7 lg:border-b-0 lg:border-r lg:p-10 xl:p-14">
                        <div>
                            <div class="reveal reveal-delay-1 mb-6 flex flex-wrap items-center gap-x-5 gap-y-2">
                                <p class="technical-label flex items-center gap-2 text-[var(--ink)]">
                                    <span class="h-2 w-2 bg-emerald-500"></span>
                                    {{ t('hero.available') }}
                                </p>
                            </div>

                            <AsciiName
                                :text="profile.name"
                                class="reveal reveal-delay-2 mb-6 border-y border-[var(--line)] py-4 sm:py-6"
                            />

                            <h2 class="reveal reveal-delay-3 max-w-3xl text-3xl font-medium leading-[1.02] tracking-[-0.055em] sm:text-4xl xl:text-5xl">
                                {{ tr(profile.headline) }}
                            </h2>
                        </div>

                        <div class="mt-6 border-t border-[var(--line)] pt-6">
                            <p
                                v-if="tr(profile.bio)"
                                class="reveal reveal-delay-4 max-w-xl text-sm leading-7 text-[var(--muted)] sm:text-base"
                            >
                                {{ tr(profile.bio) }}
                            </p>

                            <div class="reveal reveal-delay-5 mt-6 flex max-w-xl flex-col gap-2 sm:flex-row">
                                <a href="#projetos" class="action-primary justify-between sm:min-w-48">
                                    {{ t('hero.viewProjects') }}
                                    <span aria-hidden="true">↘</span>
                                </a>
                                <a
                                    v-if="profile.email"
                                    :href="`mailto:${profile.email}`"
                                    class="action-secondary justify-between sm:min-w-48"
                                >
                                    {{ t('hero.contactMe') }}
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="relative min-h-[440px] overflow-hidden bg-[var(--paper-raised)] lg:col-span-5 lg:min-h-full">
                        <div class="absolute left-4 top-4 z-20 font-mono text-4xl font-light text-[var(--accent)]">A</div>
                        <div class="absolute right-4 top-4 z-20 text-right">
                            <p class="technical-label text-[var(--ink)]">{{ t('design.profileStudy') }}</p>
                            <p class="technical-label">01 / 01</p>
                        </div>

                        <div class="halftone-wrap absolute inset-x-8 bottom-16 top-16 overflow-hidden border border-[var(--line)] sm:inset-x-12 sm:bottom-20 sm:top-20 lg:inset-x-8 lg:top-20 xl:inset-x-12">
                            <img
                                v-if="avatarUrl"
                                :src="avatarUrl"
                                :alt="profile.name"
                                class="halftone-image h-full w-full object-cover object-center"
                                loading="eager"
                            />
                            <div
                                v-else
                                class="dot-field grid h-full w-full place-items-center text-7xl font-medium tracking-[-0.08em] text-[var(--accent)] sm:text-8xl"
                            >
                                {{ initials }}
                            </div>
                        </div>

                        <div class="blueprint-grid absolute bottom-0 left-0 grid h-20 w-20 place-items-center border-r border-t border-[var(--ink)] font-mono text-xs font-bold sm:h-24 sm:w-24">
                            0/45
                        </div>
                        <div class="absolute bottom-0 right-0 z-20 flex h-16 max-w-[calc(100%-5rem)] items-center gap-3 border-l border-t border-[var(--line)] bg-[var(--paper-raised)] px-4 sm:h-20 sm:max-w-[calc(100%-6rem)] sm:px-5">
                            <span class="technical-label">{{ t('design.scroll') }}</span>
                            <span class="animate-bounce text-lg text-[var(--accent)]">↓</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="stacks" class="portfolio-section py-20 sm:py-28">
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-12 grid gap-6 lg:grid-cols-12 lg:items-end">
                    <div class="reveal lg:col-span-7">
                        <p class="technical-label mb-4 text-[var(--accent)]">02 / {{ t('stacks.label') }}</p>
                        <h2 class="section-heading">{{ t('stacks.title') }}</h2>
                    </div>
                    <p class="reveal reveal-delay-1 max-w-md text-sm leading-6 text-[var(--muted)] lg:col-span-4 lg:col-start-9">
                        {{ t('design.stackNote') }}
                    </p>
                </div>

                <div v-if="stacks.length" class="grid grid-cols-2 border-l border-t border-[var(--line)] sm:grid-cols-3 lg:grid-cols-6">
                    <div
                        v-for="(stack, index) in stacks"
                        :key="stack.id"
                        class="reveal group relative min-h-40 overflow-hidden border-b border-r border-[var(--line)] bg-[var(--paper-raised)] p-4 transition-colors duration-300 hover:bg-[var(--accent)] hover:text-white sm:min-h-48 sm:p-5"
                        :class="`reveal-delay-${(index % 5) + 1}`"
                    >
                        <div class="flex items-start justify-between">
                            <span class="index-number transition-colors group-hover:text-white/70">{{ String(index + 1).padStart(2, '0') }}</span>
                            <span class="font-mono text-xs text-[var(--accent)] transition-all group-hover:rotate-45 group-hover:text-white">×</span>
                        </div>

                        <div class="absolute inset-x-4 bottom-4 sm:inset-x-5 sm:bottom-5">
                            <img
                                v-if="stack.icon_slug && !iconHasFailed('stack', stack.id)"
                                :src="currentIconUrl('stack', stack.id, stack.icon_slug)"
                                :alt="stack.name"
                                class="mb-5 h-11 w-11 transition-all duration-300 group-hover:scale-110 group-hover:invert sm:h-11 sm:w-11"
                                :class="{ 'dark:invert dark:group-hover:invert-0': shouldInvertIcon('stack', stack.id) }"
                                loading="lazy"
                                @error="onIconError('stack', stack.id, stack.icon_slug)"
                            />
                            <div v-else class="mb-5 font-mono text-4xl font-bold sm:text-3xl">
                                {{ stack.name.charAt(0) }}
                            </div>
                            <h3 class="text-xs font-semibold leading-tight sm:text-base">{{ stack.name }}</h3>
                        </div>
                    </div>
                </div>

                <p v-else class="reveal border border-[var(--line)] p-8 text-center text-[var(--muted)]">
                    {{ t('stacks.empty') }}
                </p>
            </div>
        </section>

        <section id="experiencia" class="portfolio-section py-20 sm:py-28">
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-12">
                    <div class="reveal lg:col-span-4">
                        <p class="technical-label mb-4 text-[var(--accent)]">03 / {{ t('experience.label') }}</p>
                        <h2 class="section-heading">{{ t('experience.title') }}</h2>
                        <div class="dot-field mt-10 hidden aspect-square max-w-64 border border-[var(--line)] lg:block">
                            <div class="blueprint-grid m-8 h-[calc(100%-4rem)] border border-[var(--ink)]"></div>
                        </div>
                    </div>

                    <ol v-if="experiences.length" class="border-t border-[var(--line)] lg:col-span-8">
                        <li
                            v-for="(experience, index) in experiences"
                            :key="experience.id"
                            class="experience-row reveal group grid gap-4 border-b border-[var(--line)] px-4 py-7 sm:grid-cols-[72px_1fr] sm:px-5"
                        >
                            <div>
                                <span class="font-mono text-sm font-semibold text-[var(--accent)]">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                            </div>
                            <article class="grid gap-4 md:grid-cols-[1fr_210px]">
                                <div>
                                    <h3 class="text-xl font-semibold tracking-[-0.035em] sm:text-2xl">
                                        {{ tr(experience.role) }}
                                    </h3>
                                    <p class="mt-1 font-mono text-[11px] font-semibold uppercase tracking-wider text-[var(--muted)]">
                                        {{ tr(experience.company) }}
                                    </p>
                                    <p v-if="tr(experience.description)" class="mt-5 max-w-2xl text-sm leading-7 text-[var(--muted)]">
                                        {{ tr(experience.description) }}
                                    </p>
                                </div>
                                <div class="md:text-right">
                                    <span class="inline-block border border-[var(--line)] px-3 py-2 font-mono text-[10px] font-semibold uppercase tracking-wider">
                                        {{ formatPeriod(experience.start_date, experience.end_date) }}
                                    </span>
                                </div>
                            </article>
                        </li>
                    </ol>

                    <p v-else class="reveal border border-[var(--line)] p-8 text-center text-[var(--muted)] lg:col-span-8">
                        {{ t('experience.empty') }}
                    </p>
                </div>
            </div>
        </section>

        <section id="projetos" class="portfolio-section py-20 sm:py-28">
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-12 flex flex-col gap-6 border-b border-[var(--line)] pb-8 sm:flex-row sm:items-end sm:justify-between">
                    <div class="reveal">
                        <p class="technical-label mb-4 text-[var(--accent)]">04 / {{ t('projects.label') }}</p>
                        <h2 class="section-heading">{{ t('projects.title') }}</h2>
                    </div>
                    <p class="technical-label reveal reveal-delay-1">{{ t('design.archive') }}</p>
                </div>

                <div v-if="projects.length" class="grid gap-5 lg:grid-cols-12">
                    <article
                        v-for="(project, index) in projects"
                        :key="project.id"
                        class="editorial-card reveal group flex min-h-full flex-col overflow-hidden"
                        :class="[
                            `reveal-delay-${(index % 4) + 1}`,
                            index % 3 === 0 ? 'lg:col-span-7' : 'lg:col-span-5',
                        ]"
                    >
                        <div class="relative aspect-[16/10] overflow-hidden border-b border-[var(--line)] bg-[var(--paper)]">
                            <img
                                v-if="projectImage(project)"
                                :src="projectImage(project)"
                                :alt="tr(project.title)"
                                class="halftone-image h-full w-full object-cover object-center"
                                loading="lazy"
                            />
                            <div v-else class="dot-field grid h-full w-full place-items-center">
                                <span class="text-7xl font-medium tracking-[-0.08em] text-[var(--accent)]">
                                    {{ tr(project.title).charAt(0) }}
                                </span>
                            </div>

                            <span class="absolute left-3 top-3 border border-[var(--ink)] bg-[var(--paper-raised)] px-2 py-1 font-mono text-[9px] font-bold uppercase tracking-widest">
                                {{ t('design.figure') }} {{ String(index + 1).padStart(2, '0') }}
                            </span>
                        </div>

                        <div class="flex flex-1 flex-col p-5 sm:p-7">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="text-2xl font-semibold leading-none tracking-[-0.045em] sm:text-3xl">
                                    {{ tr(project.title) }}
                                </h3>
                                <span class="font-mono text-lg text-[var(--accent)]">↗</span>
                            </div>

                            <p v-if="tr(project.description)" class="mt-5 flex-1 text-sm leading-7 text-[var(--muted)]">
                                {{ tr(project.description) }}
                            </p>

                            <div v-if="project.stacks?.length" class="mt-6 flex flex-wrap gap-x-4 gap-y-2 border-t border-[var(--line)] pt-4">
                                <span
                                    v-for="stack in project.stacks"
                                    :key="stack.id"
                                    class="inline-flex items-center gap-1.5 font-mono text-[9px] font-semibold uppercase tracking-wider text-[var(--muted)]"
                                >
                                    <span class="text-[var(--accent)]">■</span>
                                    {{ stack.name }}
                                </span>
                            </div>

                            <div class="mt-6 grid gap-2 sm:grid-cols-2">
                                <a
                                    v-if="project.demo_url"
                                    :href="project.demo_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="action-primary"
                                >
                                    {{ t('projects.demo') }}
                                    <span aria-hidden="true">↗</span>
                                </a>
                                <a
                                    v-if="project.repository_url"
                                    :href="project.repository_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="action-secondary"
                                >
                                    {{ t('projects.code') }}
                                    <span aria-hidden="true">⌘</span>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>

                <p v-else class="reveal border border-[var(--line)] p-8 text-center text-[var(--muted)]">
                    {{ t('projects.empty') }}
                </p>
            </div>
        </section>

        <section id="contato" class="portfolio-section py-5">
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid overflow-hidden border border-[var(--ink)] lg:grid-cols-[1fr_280px]">
                    <div class="blueprint-grid reveal p-6 sm:p-10 lg:p-14">
                        <p class="mb-10 font-mono text-[10px] font-semibold uppercase tracking-[0.2em] text-white/70">
                            05 / {{ t('contact.label') }}
                        </p>
                        <h2 class="max-w-4xl text-5xl font-medium leading-[0.86] tracking-[-0.07em] sm:text-7xl lg:text-8xl">
                            {{ t('contact.titlePart1') }} {{ t('contact.titlePart2') }}{{ t('contact.titleSuffix') }}
                        </h2>
                        <p class="mt-8 max-w-xl text-sm leading-7 text-white/75 sm:text-base">
                            {{ t('contact.subtitle') }}
                        </p>

                        <a
                            v-if="profile.email"
                            :href="`mailto:${profile.email}`"
                            class="mt-10 inline-flex max-w-full items-center gap-3 border border-white bg-white px-5 py-3 font-mono text-[10px] font-bold uppercase tracking-wider text-[var(--accent)] transition-transform hover:-translate-y-1"
                        >
                            <span class="truncate">{{ profile.email }}</span>
                            <span aria-hidden="true">↗</span>
                        </a>
                    </div>

                    <div class="dot-field reveal reveal-delay-1 flex flex-col justify-between p-6 sm:p-8">
                        <div class="w-fit border border-[var(--ink)] bg-[var(--paper-raised)] px-4 py-3 shadow-[4px_4px_0_var(--accent)]">
                            <p class="technical-label text-[var(--ink)]">{{ t('design.network') }}</p>
                            <p class="technical-label mt-1 text-[var(--ink)]">{{ String(socialLinks.length + 1).padStart(2, '0') }} {{ t('design.activeLinks') }}</p>
                        </div>

                        <div class="my-8">
                            <div v-if="socialLinks.length" class="mb-3 grid grid-cols-2 border-l border-t border-[var(--line)]">
                                <a
                                    v-for="link in socialLinks"
                                    :key="link.id"
                                    :href="link.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="group grid aspect-square place-items-center border-b border-r border-[var(--line)] bg-[var(--paper-raised)] transition-colors hover:bg-[var(--ink)] hover:text-[var(--paper)]"
                                    :aria-label="link.platform"
                                    :title="link.platform"
                                >
                                    <img
                                        v-if="link.icon_slug && !iconHasFailed('social', link.id)"
                                        :src="currentIconUrl('social', link.id, link.icon_slug)"
                                        :alt="link.platform"
                                        class="h-8 w-8 transition-all duration-300 group-hover:scale-110 group-hover:invert"
                                        :class="{ 'dark:invert dark:group-hover:invert-0': shouldInvertIcon('social', link.id) }"
                                        loading="lazy"
                                        @error="onIconError('social', link.id, link.icon_slug)"
                                    />
                                    <span v-else class="font-mono text-xl font-bold">
                                        {{ link.platform.charAt(0).toUpperCase() }}
                                    </span>
                                </a>
                            </div>

                            <a
                                :href="linkLibraryUrl"
                                target="_blank"
                                rel="noopener"
                                class="group flex min-h-16 items-center justify-between gap-3 border border-[var(--ink)] bg-[var(--paper-raised)] px-3 py-2.5 transition-all duration-300 hover:-translate-y-1 hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:shadow-[4px_4px_0_var(--accent)]"
                                :aria-label="t('linkLibrary.ariaLabel')"
                            >
                                <span class="flex min-w-0 items-center gap-3">
                                    <span class="blueprint-grid grid h-9 w-9 shrink-0 place-items-center border border-[var(--ink)] font-mono text-[9px] font-bold">
                                        KH
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block font-mono text-[10px] font-bold uppercase tracking-[0.12em]">
                                            Klink Hub
                                        </span>
                                        <span class="mt-0.5 block text-xs text-[var(--muted)] transition-colors group-hover:text-[var(--paper)]/70">
                                            {{ t('linkLibrary.label') }}
                                        </span>
                                    </span>
                                </span>
                                <span class="shrink-0 font-mono text-lg text-[var(--accent)] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" aria-hidden="true">
                                    ↗
                                </span>
                            </a>
                        </div>

                        <span class="font-mono text-5xl font-light text-[var(--accent)]">M</span>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-[var(--line)] py-7">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 font-mono text-[9px] font-semibold uppercase tracking-[0.14em] text-[var(--muted)] sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>© {{ new Date().getFullYear() }} {{ profile.name }}. {{ t('footer.rights') }}</p>
                <p>{{ t('footer.built') }}</p>
            </div>
        </footer>
    </PublicLayout>
</template>
