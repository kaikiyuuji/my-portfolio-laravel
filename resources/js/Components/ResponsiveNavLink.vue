<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full border-l-4 border-[var(--accent)] bg-[var(--paper)] px-4 py-3 text-start font-mono text-[10px] font-bold uppercase tracking-wider text-[var(--accent)] focus:outline-none'
        : 'block w-full border-l-4 border-transparent px-4 py-3 text-start font-mono text-[10px] font-semibold uppercase tracking-wider text-[var(--muted)] transition-colors hover:border-[var(--line)] hover:bg-[var(--paper)] hover:text-[var(--ink)] focus:outline-none',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
