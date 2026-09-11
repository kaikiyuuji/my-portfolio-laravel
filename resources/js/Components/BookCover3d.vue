<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    book: { type: Object, required: true },
    large: { type: Boolean, default: false },
    rotation: { type: Number, default: null },
    interactive: { type: Boolean, default: true },
});

const scene = ref(null);
const coverFailed = ref(false);
const canTilt = ref(false);
let pointerMedia = null;
let motionMedia = null;
let frame = null;
let pendingTilt = { x: 0, y: 0 };

const palettes = {
    academic: [
        ['#203c68', '#152944', '#dbe7ff'],
        ['#303e72', '#212b51', '#e1e6ff'],
    ],
    personal: [
        ['#884735', '#603024', '#f8e4ca'],
        ['#4c5a3b', '#333f27', '#edf0d2'],
    ],
};

const coverStyle = computed(() => {
    const seed = String(props.book.id ?? props.book.title ?? '')
        .split('')
        .reduce((sum, character) => sum + character.charCodeAt(0), 0);
    const palette = palettes[props.book.category === 'academic' ? 'academic' : 'personal'];
    const [cover, spine, detail] = palette[seed % palette.length];

    return {
        '--library-book-cover': cover,
        '--library-book-spine': spine,
        '--library-book-detail': detail,
        '--library-book-rotation': `${Number.isFinite(props.rotation) ? Math.max(-35, Math.min(35, props.rotation)) : -24}deg`,
    };
});

const showCover = computed(() => Boolean(props.book.cover_url) && !coverFailed.value);

function resetTilt() {
    if (frame !== null) cancelAnimationFrame(frame);
    frame = null;
    pendingTilt = { x: 0, y: 0 };
    scene.value?.style.setProperty('--library-book-tilt-x', '0deg');
    scene.value?.style.setProperty('--library-book-tilt-y', '0deg');
}

function updateInteraction() {
    canTilt.value = props.interactive && props.rotation === null
        && Boolean(pointerMedia?.matches) && !motionMedia?.matches;
    if (!canTilt.value) resetTilt();
}

function tilt(event) {
    if (!canTilt.value || event.pointerType !== 'mouse' || !scene.value) return;

    const bounds = scene.value.getBoundingClientRect();
    if (!bounds.width || !bounds.height) return;

    const horizontal = Math.max(-1, Math.min(1, ((event.clientX - bounds.left) / bounds.width - 0.5) * 2));
    const vertical = Math.max(-1, Math.min(1, ((event.clientY - bounds.top) / bounds.height - 0.5) * 2));
    pendingTilt = { x: -vertical * 5, y: horizontal * 12 };

    if (frame !== null) return;
    frame = requestAnimationFrame(() => {
        scene.value?.style.setProperty('--library-book-tilt-x', `${pendingTilt.x.toFixed(2)}deg`);
        scene.value?.style.setProperty('--library-book-tilt-y', `${pendingTilt.y.toFixed(2)}deg`);
        frame = null;
    });
}

watch(() => props.book.cover_url, () => { coverFailed.value = false; });
watch(() => [props.interactive, props.rotation], updateInteraction);

onMounted(() => {
    pointerMedia = window.matchMedia('(hover: hover) and (pointer: fine)');
    motionMedia = window.matchMedia('(prefers-reduced-motion: reduce)');
    pointerMedia.addEventListener('change', updateInteraction);
    motionMedia.addEventListener('change', updateInteraction);
    updateInteraction();
});

onBeforeUnmount(() => {
    pointerMedia?.removeEventListener('change', updateInteraction);
    motionMedia?.removeEventListener('change', updateInteraction);
    resetTilt();
});
</script>

<template>
    <span
        ref="scene"
        class="library-book-scene"
        :class="{ 'library-book-scene--large': large, 'library-book-scene--interactive': canTilt }"
        :style="coverStyle"
        aria-hidden="true"
        @pointermove="tilt"
        @pointerleave="resetTilt"
        @pointercancel="resetTilt"
    >
        <span class="library-book-shadow"></span>
        <span class="library-book-volume">
            <span class="library-book-back"></span>
            <span class="library-book-pages library-book-pages--right"></span>
            <span class="library-book-pages library-book-pages--top"></span>
            <span class="library-book-pages library-book-pages--bottom"></span>
            <span class="library-book-spine">
                <span class="library-book-spine-title">{{ book.title }}</span>
            </span>
            <span class="library-book-front">
                <span class="library-book-fallback">
                    <span class="library-book-ornament"><span></span><span></span><span></span></span>
                    <span class="library-book-fallback-title">{{ book.title }}</span>
                    <span class="library-book-fallback-author">{{ book.author }}</span>
                    <span class="library-book-fallback-rule"></span>
                </span>
                <img
                    v-if="showCover"
                    class="library-book-cover-image"
                    :src="book.cover_url"
                    alt=""
                    loading="lazy"
                    decoding="async"
                    draggable="false"
                    @error="coverFailed = true"
                />
                <span class="library-book-cover-light"></span>
            </span>
        </span>
    </span>
</template>

<style scoped>
.library-book-scene {
    --library-book-width: 140px;
    --library-book-depth: 22px;
    --library-book-tilt-x: 0deg;
    --library-book-tilt-y: 0deg;
    display: block;
    position: relative;
    width: 100%;
    height: 230px;
    perspective: 950px;
    user-select: none;
    -webkit-user-select: none;
}

.library-book-volume {
    display: block;
    position: absolute;
    top: auto;
    bottom: 12px;
    left: 50%;
    width: min(var(--library-book-width), calc(100% - 20px));
    aspect-ratio: 2 / 3;
    transform: translateX(-50%) rotateX(calc(5deg + var(--library-book-tilt-x))) rotateY(calc(var(--library-book-rotation) + var(--library-book-tilt-y)));
    transform-style: preserve-3d;
    transition: transform 220ms cubic-bezier(0.2, 0.7, 0.2, 1);
}

.library-book-front,
.library-book-back {
    display: block;
    position: absolute;
    inset: 0;
    border-radius: 1px 3px 3px 1px;
    background: var(--library-book-cover);
    backface-visibility: hidden;
}

.library-book-front {
    overflow: hidden;
    transform: translateZ(calc(var(--library-book-depth) / 2));
    box-shadow: inset 0 0 0 1px rgb(255 255 255 / 13%);
}

.library-book-back {
    transform: rotateY(180deg) translateZ(calc(var(--library-book-depth) / 2));
    background: var(--library-book-spine);
}

.library-book-pages {
    display: block;
    position: absolute;
    backface-visibility: hidden;
    background-color: #e9e3d5;
}

.library-book-pages--right {
    top: 3px;
    left: calc(100% - var(--library-book-depth) / 2);
    width: var(--library-book-depth);
    height: calc(100% - 6px);
    transform: rotateY(90deg);
    background-image: linear-gradient(to right, rgb(69 53 29 / 12%), transparent 30%, rgb(255 255 255 / 50%) 80%, rgb(69 53 29 / 12%)), repeating-linear-gradient(to right, #ddd6c6 0, #f4efe4 1px, #e9e3d5 2px);
    box-shadow: inset 0 2px 3px rgb(61 42 16 / 12%);
}

.library-book-pages--top,
.library-book-pages--bottom {
    left: 1px;
    width: calc(100% - 2px);
    height: var(--library-book-depth);
    background-image: repeating-linear-gradient(to bottom, #d8d0c0 0, #f4efe4 1px, #e9e3d5 2px);
}

.library-book-pages--top {
    top: calc(0px - var(--library-book-depth) / 2 + 2px);
    transform: rotateX(90deg);
}

.library-book-pages--bottom {
    bottom: calc(0px - var(--library-book-depth) / 2 + 2px);
    transform: rotateX(-90deg);
}

.library-book-spine {
    display: flex;
    position: absolute;
    top: 0;
    left: calc(0px - var(--library-book-depth) / 2);
    align-items: center;
    justify-content: center;
    width: var(--library-book-depth);
    height: 100%;
    overflow: hidden;
    transform: rotateY(-90deg);
    backface-visibility: hidden;
    background: linear-gradient(to right, rgb(0 0 0 / 15%), transparent 35%, rgb(255 255 255 / 7%) 65%, rgb(0 0 0 / 15%)), var(--library-book-spine);
    color: var(--library-book-detail);
    border-block: 1px solid rgb(255 255 255 / 15%);
}

.library-book-spine-title {
    display: block;
    max-height: 85%;
    overflow: hidden;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    text-overflow: ellipsis;
    white-space: nowrap;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 10px;
    letter-spacing: 0.03em;
}

.library-book-fallback {
    display: flex;
    position: absolute;
    inset: 0;
    flex-direction: column;
    padding: 19% 12% 12% 15%;
    color: #fff9ed;
    text-align: left;
    background-image: linear-gradient(135deg, rgb(255 255 255 / 4%), transparent 65%);
}

.library-book-ornament {
    display: flex;
    align-items: end;
    gap: 5px;
    height: 22px;
    margin-bottom: 16%;
    color: var(--library-book-detail);
    opacity: 0.75;
}

.library-book-ornament span {
    display: block;
    width: 15px;
    height: 22px;
    border: 1px solid currentColor;
}

.library-book-ornament span:nth-child(2) { height: 16px; }
.library-book-ornament span:nth-child(3) { height: 10px; }

.library-book-fallback-title {
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 4;
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 18px;
    font-weight: 400;
    line-height: 1.13;
    letter-spacing: -0.03em;
    overflow-wrap: anywhere;
}

.library-book-fallback-author {
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    margin-top: auto;
    padding-top: 10px;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 8px;
    line-height: 1.5;
    letter-spacing: 0.06em;
    color: var(--library-book-detail);
}

.library-book-fallback-rule {
    display: block;
    margin-top: 9px;
    width: 100%;
    height: 1px;
    flex-shrink: 0;
    background: var(--library-book-detail);
    opacity: 0.6;
}

.library-book-cover-image {
    display: block;
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.library-book-cover-light {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: linear-gradient(to right, rgb(0 0 0 / 24%), rgb(255 255 255 / 15%) 2%, rgb(0 0 0 / 10%) 4%, transparent 6%, transparent 94%, rgb(255 255 255 / 12%) 100%), linear-gradient(135deg, rgb(255 255 255 / 10%), transparent 48%, rgb(0 0 0 / 7%));
    box-shadow: inset 0 0 0 1px rgb(255 255 255 / 12%);
}

.library-book-shadow {
    display: block;
    position: absolute;
    left: 50%;
    bottom: 5px;
    width: min(155px, 85%);
    height: 20px;
    transform: translateX(-43%) rotate(-5deg);
    border-radius: 50%;
    background: rgb(0 0 0 / 24%);
    filter: blur(9px);
    opacity: 0.75;
    pointer-events: none;
}

.library-book-scene--large {
    --library-book-width: 200px;
    --library-book-depth: 28px;
    height: 330px;
}

.library-book-scene--large .library-book-fallback-title { font-size: 27px; }
.library-book-scene--large .library-book-fallback-author { font-size: 10px; }
.library-book-scene--large .library-book-shadow { width: min(215px, 85%); }

@media (max-width: 639px) {
    .library-book-fallback-title { font-size: clamp(12px, 3.65vw, 16px); }

    .library-book-scene {
        --library-book-width: 120px;
        --library-book-depth: 18px;
        height: 210px;
    }

    .library-book-scene--large {
        --library-book-width: 140px;
        --library-book-depth: 22px;
        height: 230px;
    }

    .library-book-scene--large .library-book-fallback-title { font-size: 18px; }
    .library-book-scene--large .library-book-fallback-author { font-size: 8px; }
    .library-book-scene--large .library-book-shadow { width: min(155px, 85%); }
}

@media (prefers-reduced-motion: reduce) {
    .library-book-volume { transition: none; }
}
</style>
