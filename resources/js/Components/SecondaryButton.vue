<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex min-h-11 items-center justify-center gap-2 border border-[var(--line)] bg-[var(--paper-raised)] px-5 py-2.5 font-mono text-[10px] font-bold uppercase tracking-[0.13em] text-[var(--ink)] transition-all duration-300 hover:border-[var(--ink)] hover:bg-[var(--paper)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:ring-offset-2 focus:ring-offset-[var(--paper)] disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
