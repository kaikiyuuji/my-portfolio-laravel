<template>
    <button
        class="inline-flex min-h-11 items-center justify-center gap-2 border border-red-700 bg-red-600 px-5 py-2.5 font-mono text-[10px] font-bold uppercase tracking-[0.13em] text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-[var(--paper)] disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
