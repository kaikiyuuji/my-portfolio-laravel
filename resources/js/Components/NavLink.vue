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
        ? 'inline-flex items-center border-b-2 border-[var(--accent)] px-3 py-2 font-mono text-[10px] font-bold uppercase tracking-wider text-[var(--accent)] focus:outline-none'
        : 'inline-flex items-center border-b-2 border-transparent px-3 py-2 font-mono text-[10px] font-semibold uppercase tracking-wider text-[var(--muted)] transition-colors hover:border-[var(--line)] hover:text-[var(--ink)] focus:outline-none'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
