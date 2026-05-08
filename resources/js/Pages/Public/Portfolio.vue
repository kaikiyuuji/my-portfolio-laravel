<script setup>
import { computed, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/Composables/useScrollReveal';

const { t, tm, locale } = useI18n();

function tr(field) {
    if (field == null) return '';
    if (typeof field === 'object') {
        return field[locale.value] || field.pt || field.en || '';
    }
    return field;
}

const props = defineProps({
    profile: { type: Object, required: true },
    stacks: { type: Array, default: () => [] },
    experiences: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    socialLinks: { type: Array, default: () => [] },
});

useScrollReveal('.reveal');

const failedIcons = reactive(new Set());
const attemptIndex = reactive({});

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
    const idx = attemptIndex[iconKey(prefix, id)] ?? 0;
    return idx < 3;
}

const avatarUrl = computed(() =>
    props.profile?.avatar_path ? '/storage/' + props.profile.avatar_path : null,
);

const projectImage = (path) => (path ? '/storage/' + path : null);

const initials = computed(() => {
    const name = props.profile?.name || 'P';
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((p) => p.charAt(0).toUpperCase())
        .join('');
});

function formatPeriod(start, end) {
    if (!start) return '';
    const months = tm('months');
    const fmt = (iso) => {
        const d = new Date(iso);
        return `${months[d.getMonth()]} ${d.getFullYear()}`;
    };
    return `${fmt(start)} — ${end ? fmt(end) : t('experience.present')}`;
}

</script>

<template>
    <Head :title="profile.name || 'Portfolio'" />

    <PublicLayout :profile-name="profile.name || 'Portfolio'">
        <!-- ───────── Hero / Apresentação ───────── -->
        <section id="sobre" class="relative overflow-hidden">
            <div class="grid-bg absolute inset-0 opacity-60 [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]"></div>
            <div class="absolute -left-32 top-20 h-96 w-96 rounded-full bg-black/5 blur-3xl dark:bg-white/5"></div>
            <div class="absolute -right-32 bottom-0 h-96 w-96 rounded-full bg-black/5 blur-3xl dark:bg-white/5"></div>

            <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-6 py-20 md:grid-cols-[auto_1fr] md:gap-16 md:py-32">
                <!-- Avatar -->
                <div class="reveal mx-auto md:mx-0">
                    <div class="group relative">
                        <div class="absolute -inset-3 rounded-full bg-gradient-to-tr from-black via-neutral-500 to-black opacity-30 blur-2xl transition-opacity duration-500 group-hover:opacity-60 dark:from-white dark:via-neutral-300 dark:to-white"></div>
                        <div class="relative h-64 w-64 overflow-hidden rounded-full border-4 border-black bg-white shadow-2xl ring-1 ring-black/10 transition-transform duration-500 group-hover:scale-105 sm:h-80 sm:w-80 md:h-96 md:w-96 dark:border-white dark:bg-black dark:ring-white/10">
                            <img
                                v-if="avatarUrl"
                                :src="avatarUrl"
                                :alt="profile.name"
                                class="h-full w-full object-cover object-center"
                                loading="eager"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-neutral-100 to-neutral-300 text-7xl font-black text-black dark:from-neutral-800 dark:to-neutral-950 dark:text-white"
                            >
                                {{ initials }}
                            </div>
                        </div>
                        <div class="absolute -right-1 bottom-4 flex h-12 w-12 animate-float items-center justify-center rounded-full border-2 border-black bg-white text-xs font-black shadow-xl dark:border-white dark:bg-black">
                            <span class="h-3.5 w-3.5 rounded-full bg-emerald-500"></span>
                        </div>
                    </div>
                </div>

                <!-- Texto -->
                <div class="text-center md:text-left">
                    <p class="reveal reveal-delay-1 mb-4 inline-flex items-center gap-2 rounded-full border border-black/15 bg-white/60 px-4 py-1.5 text-xs font-bold uppercase tracking-widest backdrop-blur-md dark:border-white/15 dark:bg-black/40">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                        {{ t('hero.available') }}
                    </p>
                    <h1 class="reveal reveal-delay-2 mb-4 text-5xl font-black leading-tight tracking-tight sm:text-6xl md:text-7xl">
                        <span class="text-gradient-mono">{{ profile.name }}</span>
                    </h1>
                    <h2 class="reveal reveal-delay-3 mb-6 text-xl font-semibold text-black/70 sm:text-2xl dark:text-white/70">
                        {{ tr(profile.headline) }}
                    </h2>
                    <p
                        v-if="tr(profile.bio)"
                        class="reveal reveal-delay-4 mx-auto mb-8 max-w-xl text-base leading-relaxed text-black/60 md:mx-0 md:text-lg dark:text-white/60"
                    >
                        {{ tr(profile.bio) }}
                    </p>

                    <div class="reveal reveal-delay-5 flex flex-wrap items-center justify-center gap-3 md:justify-start">
                        <a
                            href="#projetos"
                            class="group inline-flex items-center gap-2 rounded-full bg-black px-6 py-3 text-sm font-bold text-white transition-all hover:-translate-y-1 hover:shadow-2xl active:translate-y-0 dark:bg-white dark:text-black"
                        >
                            {{ t('hero.viewProjects') }}
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                        <a
                            v-if="profile.email"
                            :href="`mailto:${profile.email}`"
                            class="inline-flex items-center gap-2 rounded-full border-2 border-black px-6 py-3 text-sm font-bold transition-all hover:-translate-y-1 hover:bg-black hover:text-white dark:border-white dark:hover:bg-white dark:hover:text-black"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ t('hero.contactMe') }}
                        </a>
                        <a
                            v-if="profile.resume_url"
                            :href="profile.resume_url"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center gap-2 rounded-full px-6 py-3 text-sm font-bold text-black/70 underline-offset-4 transition-colors hover:text-black hover:underline dark:text-white/70 dark:hover:text-white"
                        >
                            {{ t('hero.resume') }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Scroll indicator -->
            <div class="pointer-events-none absolute bottom-8 left-1/2 hidden -translate-x-1/2 md:block">
                <div class="flex h-10 w-6 items-start justify-center rounded-full border-2 border-black/30 p-1 dark:border-white/30">
                    <span class="h-2 w-1 animate-bounce rounded-full bg-black/60 dark:bg-white/60"></span>
                </div>
            </div>
        </section>

        <!-- ───────── Stacks ───────── -->
        <section id="stacks" class="border-y border-black/10 bg-neutral-50 py-24 dark:border-white/10 dark:bg-neutral-950">
            <div class="mx-auto max-w-6xl px-6">
                <div class="reveal mb-12 text-center">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.3em] text-black/50 dark:text-white/50">{{ t('stacks.label') }}</p>
                    <h2 class="text-4xl font-black tracking-tight sm:text-5xl">{{ t('stacks.title') }}</h2>
                </div>

                <div v-if="stacks.length" class="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-6">
                    <div
                        v-for="(stack, i) in stacks"
                        :key="stack.id"
                        class="reveal group relative flex aspect-square flex-col items-center justify-center gap-2 overflow-hidden rounded-2xl border border-black/10 bg-white p-4 transition-all duration-500 hover:-translate-y-2 hover:border-black hover:shadow-2xl dark:border-white/10 dark:bg-black dark:hover:border-white"
                        :class="`reveal-delay-${(i % 5) + 1}`"
                    >
                        <div class="absolute inset-0 -translate-y-full bg-gradient-to-b from-black/5 to-transparent transition-transform duration-500 group-hover:translate-y-0 dark:from-white/5"></div>
                        <img
                            v-if="stack.icon_slug && !iconHasFailed('stack', stack.id)"
                            :src="currentIconUrl('stack', stack.id, stack.icon_slug)"
                            :alt="stack.name"
                            class="relative z-10 h-10 w-10 transition-transform duration-500 group-hover:scale-125 sm:h-12 sm:w-12"
                            :class="{ 'dark:invert': shouldInvertIcon('stack', stack.id) }"
                            loading="lazy"
                            @error="onIconError('stack', stack.id, stack.icon_slug)"
                        />
                        <div v-else class="relative z-10 flex h-10 w-10 items-center justify-center rounded-lg bg-black text-sm font-black text-white sm:h-12 sm:w-12 dark:bg-white dark:text-black">
                            {{ stack.name.charAt(0) }}
                        </div>
                        <span class="relative z-10 text-center text-xs font-semibold text-black/80 dark:text-white/80">
                            {{ stack.name }}
                        </span>
                    </div>
                </div>

                <p v-else class="reveal text-center text-black/50 dark:text-white/50">
                    {{ t('stacks.empty') }}
                </p>
            </div>
        </section>

        <!-- ───────── Experiências ───────── -->
        <section id="experiencia" class="py-24">
            <div class="mx-auto max-w-3xl px-6">
                <div class="reveal mb-12 text-center">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.3em] text-black/50 dark:text-white/50">{{ t('experience.label') }}</p>
                    <h2 class="text-4xl font-black tracking-tight sm:text-5xl">{{ t('experience.title') }}</h2>
                </div>

                <ol v-if="experiences.length" class="relative space-y-6 border-l-2 border-black/10 pl-6 dark:border-white/10 sm:pl-8">
                    <li
                        v-for="exp in experiences"
                        :key="exp.id"
                        class="reveal relative"
                    >
                        <span class="absolute -left-[33px] top-3 flex h-4 w-4 items-center justify-center rounded-full border-4 border-white bg-black ring-2 ring-black/10 dark:border-black dark:bg-white dark:ring-white/20 sm:-left-[41px] sm:h-5 sm:w-5"></span>

                        <article class="group rounded-2xl border border-black/10 bg-white p-5 shadow-sm transition-all duration-500 hover:-translate-y-1 hover:border-black hover:shadow-2xl sm:p-6 dark:border-white/10 dark:bg-neutral-950 dark:hover:border-white">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-black px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white dark:bg-white dark:text-black">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ formatPeriod(exp.start_date, exp.end_date) }}
                                </span>
                            </div>
                            <h3 class="mb-1 text-lg font-black tracking-tight sm:text-xl">{{ tr(exp.role) }}</h3>
                            <p class="mb-3 text-sm font-semibold text-black/60 dark:text-white/60">
                                {{ tr(exp.company) }}
                            </p>
                            <p v-if="tr(exp.description)" class="text-sm leading-relaxed text-black/70 dark:text-white/70">
                                {{ tr(exp.description) }}
                            </p>
                        </article>
                    </li>
                </ol>

                <p v-else class="reveal text-center text-black/50 dark:text-white/50">
                    {{ t('experience.empty') }}
                </p>
            </div>
        </section>

        <!-- ───────── Projetos ───────── -->
        <section id="projetos" class="border-y border-black/10 bg-neutral-50 py-24 dark:border-white/10 dark:bg-neutral-950">
            <div class="mx-auto max-w-6xl px-6">
                <div class="reveal mb-12 text-center">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.3em] text-black/50 dark:text-white/50">{{ t('projects.label') }}</p>
                    <h2 class="text-4xl font-black tracking-tight sm:text-5xl">{{ t('projects.title') }}</h2>
                </div>

                <div v-if="projects.length" class="grid gap-8 md:grid-cols-2">
                    <article
                        v-for="(project, i) in projects"
                        :key="project.id"
                        class="reveal group relative flex flex-col overflow-hidden rounded-3xl border border-black/10 bg-white transition-all duration-500 hover:-translate-y-2 hover:border-black hover:shadow-2xl dark:border-white/10 dark:bg-black dark:hover:border-white"
                        :class="`reveal-delay-${(i % 4) + 1}`"
                    >
                        <div class="relative aspect-[16/10] overflow-hidden bg-neutral-100 dark:bg-neutral-900">
                            <img
                                v-if="projectImage(project.image_path)"
                                :src="projectImage(project.image_path)"
                                :alt="tr(project.title)"
                                class="h-full w-full object-cover object-center transition-transform duration-700 group-hover:scale-110"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center text-6xl font-black text-black/20 dark:text-white/20"
                            >
                                {{ tr(project.title).charAt(0) }}
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                        </div>

                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="mb-2 text-2xl font-black tracking-tight">{{ tr(project.title) }}</h3>
                            <p v-if="tr(project.description)" class="mb-4 flex-1 text-sm leading-relaxed text-black/65 dark:text-white/65">
                                {{ tr(project.description) }}
                            </p>

                            <div v-if="project.stacks?.length" class="mb-5 flex flex-wrap gap-2">
                                <span
                                    v-for="stack in project.stacks"
                                    :key="stack.id"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-black/15 bg-white px-3 py-1 text-xs font-semibold dark:border-white/20 dark:bg-black"
                                >
                                    <img
                                        v-if="stack.icon_slug && !iconHasFailed('pstack', stack.id)"
                                        :src="currentIconUrl('pstack', stack.id, stack.icon_slug)"
                                        :alt="stack.name"
                                        class="h-3.5 w-3.5"
                                        :class="{ 'dark:invert': shouldInvertIcon('pstack', stack.id) }"
                                        loading="lazy"
                                        @error="onIconError('pstack', stack.id, stack.icon_slug)"
                                    />
                                    {{ stack.name }}
                                </span>
                            </div>

                            <div class="mt-auto flex flex-wrap gap-3">
                                <a
                                    v-if="project.demo_url"
                                    :href="project.demo_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-black px-4 py-2 text-xs font-bold text-white transition-transform hover:-translate-y-0.5 dark:bg-white dark:text-black"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    {{ t('projects.demo') }}
                                </a>
                                <a
                                    v-if="project.repository_url"
                                    :href="project.repository_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1.5 rounded-full border-2 border-black px-4 py-2 text-xs font-bold transition-colors hover:bg-black hover:text-white dark:border-white dark:hover:bg-white dark:hover:text-black"
                                >
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 .5C5.65.5.5 5.65.5 12c0 5.08 3.29 9.39 7.86 10.91.57.1.78-.25.78-.55v-2.06c-3.2.7-3.87-1.36-3.87-1.36-.52-1.32-1.27-1.67-1.27-1.67-1.04-.71.08-.7.08-.7 1.15.08 1.76 1.18 1.76 1.18 1.02 1.75 2.68 1.24 3.34.95.1-.74.4-1.24.73-1.53-2.55-.29-5.23-1.27-5.23-5.65 0-1.25.45-2.27 1.18-3.07-.12-.29-.51-1.46.11-3.04 0 0 .96-.31 3.15 1.18a10.94 10.94 0 0 1 5.74 0c2.19-1.49 3.14-1.18 3.14-1.18.63 1.58.23 2.75.11 3.04.74.8 1.18 1.82 1.18 3.07 0 4.39-2.69 5.36-5.25 5.64.41.36.78 1.07.78 2.16v3.2c0 .31.21.66.79.55C20.22 21.39 23.5 17.08 23.5 12 23.5 5.65 18.35.5 12 .5Z" />
                                    </svg>
                                    {{ t('projects.code') }}
                                </a>
                            </div>
                        </div>
                    </article>
                </div>

                <p v-else class="reveal text-center text-black/50 dark:text-white/50">
                    {{ t('projects.empty') }}
                </p>
            </div>
        </section>

        <!-- ───────── Contato / Redes Sociais ───────── -->
        <section id="contato" class="relative overflow-hidden py-24">
            <div class="grid-bg absolute inset-0 opacity-40 [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_75%)]"></div>

            <div class="relative mx-auto max-w-3xl px-6 text-center">
                <div class="reveal">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[0.3em] text-black/50 dark:text-white/50">{{ t('contact.label') }}</p>
                    <h2 class="mb-4 text-4xl font-black tracking-tight sm:text-5xl">
                        {{ t('contact.titlePart1') }} <span class="text-gradient-mono">{{ t('contact.titlePart2') }}</span>{{ t('contact.titleSuffix') }}
                    </h2>
                    <p class="mb-10 text-base text-black/65 dark:text-white/65">
                        {{ t('contact.subtitle') }}
                    </p>
                </div>

                <a
                    v-if="profile.email"
                    :href="`mailto:${profile.email}`"
                    class="reveal reveal-delay-1 group mx-auto mb-12 inline-flex items-center gap-3 rounded-full bg-black px-8 py-4 text-base font-bold text-white shadow-2xl transition-all hover:-translate-y-1 dark:bg-white dark:text-black"
                >
                    {{ profile.email }}
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>

                <div v-if="socialLinks.length" class="reveal reveal-delay-2 flex flex-wrap items-center justify-center gap-3">
                    <a
                        v-for="link in socialLinks"
                        :key="link.id"
                        :href="link.url"
                        target="_blank"
                        rel="noopener"
                        class="group flex h-12 w-12 items-center justify-center rounded-full border-2 border-black/15 bg-white transition-all hover:-translate-y-1 hover:scale-110 hover:border-black hover:shadow-xl dark:border-white/15 dark:bg-black dark:hover:border-white"
                        :aria-label="link.platform"
                        :title="link.platform"
                    >
                        <img
                            v-if="link.icon_slug && !iconHasFailed('social', link.id)"
                            :src="currentIconUrl('social', link.id, link.icon_slug)"
                            :alt="link.platform"
                            class="h-5 w-5 transition-transform duration-500 group-hover:scale-110"
                            :class="{ 'dark:invert': shouldInvertIcon('social', link.id) }"
                            loading="lazy"
                            @error="onIconError('social', link.id, link.icon_slug)"
                        />
                        <span v-else class="text-sm font-black">
                            {{ link.platform.charAt(0).toUpperCase() }}
                        </span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-black/10 py-8 dark:border-white/10">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 text-xs text-black/50 sm:flex-row dark:text-white/50">
                <p>© {{ new Date().getFullYear() }} {{ profile.name }}. {{ t('footer.rights') }}</p>
                <p>{{ t('footer.built') }}</p>
            </div>
        </footer>
    </PublicLayout>
</template>
