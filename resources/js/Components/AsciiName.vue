<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import figlet from 'figlet';
import ansiShadow from 'figlet/fonts/ANSI Shadow';

figlet.parseFont('ANSI Shadow', ansiShadow);

const props = defineProps({
    text: {
        type: String,
        default: 'Portfolio',
    },
});

const container = ref(null);
const asciiArt = ref('');
const asciiFontSize = ref(10);
const fallbackText = ref(false);

const scrambleGlyphs = ['█', '▓', '▒', '░', '╔', '═', '╗', '║', '╚', '╝'];
let resizeObserver = null;
let animationFrame = null;
let animationTimer = null;
let resizeTimer = null;
let lastRenderedWidth = 0;

function renderAnsiShadow(text) {
    return figlet
        .textSync(text, {
            font: 'ANSI Shadow',
            horizontalLayout: 'fitted',
            verticalLayout: 'fitted',
        })
        .split('\n')
        .map((line) => line.trimEnd())
        .join('\n')
        .trimEnd();
}

function longestLineLength(art) {
    return Math.max(...art.split('\n').map((line) => [...line].length));
}

function createAscii({ animate = false, measuredWidth = null } = {}) {
    if (!container.value || typeof window === 'undefined') return;

    const width = Math.round(measuredWidth ?? container.value.clientWidth);
    if (!width) return;

    try {
        const label = (props.text || 'Portfolio').trim().toUpperCase();
        const words = label.split(/\s+/).filter(Boolean);
        let finalArt = renderAnsiShadow(label);
        let columns = longestLineLength(finalArt);
        let fittedSize = width / (columns * 0.61);

        if (width < 560 && words.length > 1) {
            finalArt = words.map(renderAnsiShadow).join('\n\n');
            columns = longestLineLength(finalArt);
            fittedSize = width / (columns * 0.61);
        }

        lastRenderedWidth = width;
        asciiFontSize.value = Math.max(7, Math.min(width < 640 ? 13 : 14, fittedSize));
        fallbackText.value = false;

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.clearInterval(animationTimer);

        if (!animate || reduceMotion) {
            asciiArt.value = finalArt;
            return;
        }

        let progress = 0;
        const totalSteps = 14;

        animationTimer = window.setInterval(() => {
            progress += 1;
            const threshold = progress / totalSteps;

            asciiArt.value = finalArt
                .split('')
                .map((character, index) => {
                    if (character === ' ' || character === '\n') return character;

                    const revealAt = ((index * 37) % 101) / 100;
                    return revealAt <= threshold
                        ? character
                        : scrambleGlyphs[(index + progress) % scrambleGlyphs.length];
                })
                .join('');

            if (progress >= totalSteps) {
                window.clearInterval(animationTimer);
                asciiArt.value = finalArt;
            }
        }, 44);
    } catch {
        fallbackText.value = true;
    }
}

function scheduleRender(animate = false, measuredWidth = null) {
    window.cancelAnimationFrame(animationFrame);
    animationFrame = window.requestAnimationFrame(() => createAscii({ animate, measuredWidth }));
}

function handleResize(entries) {
    const width = Math.round(entries[0]?.contentRect.width ?? 0);
    if (!width || Math.abs(width - lastRenderedWidth) < 6) return;

    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(() => {
        if (Math.abs(width - lastRenderedWidth) < 6) return;
        scheduleRender(false, width);
    }, 120);
}

onMounted(async () => {
    await nextTick();
    scheduleRender(true);
    resizeObserver = new ResizeObserver(handleResize);
    resizeObserver.observe(container.value);
});

watch(() => props.text, () => scheduleRender(true));

onBeforeUnmount(() => {
    resizeObserver?.disconnect();
    window.cancelAnimationFrame(animationFrame);
    window.clearInterval(animationTimer);
    window.clearTimeout(resizeTimer);
});
</script>

<template>
    <div
        ref="container"
        class="ascii-name"
        :style="{ '--ascii-font-size': `${asciiFontSize}px` }"
    >
        <h1 class="sr-only">{{ text }}</h1>
        <span v-if="fallbackText" aria-hidden="true" class="ascii-name__fallback">{{ text }}</span>
        <pre v-else aria-hidden="true" class="ascii-name__art">{{ asciiArt }}</pre>
    </div>
</template>
