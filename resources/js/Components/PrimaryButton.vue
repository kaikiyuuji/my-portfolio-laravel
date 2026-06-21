<template>
    <button
        class="inline-flex min-h-11 items-center justify-center gap-2 border border-[var(--ink)] bg-[var(--ink)] px-5 py-2.5 font-mono text-[10px] font-bold uppercase tracking-[0.13em] text-[var(--paper)] transition-all duration-300 hover:-translate-y-0.5 hover:border-[var(--accent)] hover:bg-[var(--accent)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:ring-offset-2 focus:ring-offset-[var(--paper)] disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
