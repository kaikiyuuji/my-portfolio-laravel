<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full border-b border-[var(--line)] px-4 py-3 text-start font-mono text-[10px] font-semibold uppercase tracking-wider text-[var(--ink)] transition-colors last:border-b-0 hover:bg-[var(--paper)] hover:text-[var(--accent)] focus:bg-[var(--paper)] focus:outline-none"
    >
        <slot />
    </Link>
</template>
