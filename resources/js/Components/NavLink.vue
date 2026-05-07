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
        ? 'inline-flex items-center px-3 py-2 text-sm font-semibold text-indigo-600 border-b-2 border-indigo-600 focus:outline-none transition-colors'
        : 'inline-flex items-center px-3 py-2 text-sm font-medium text-slate-500 border-b-2 border-transparent hover:text-slate-800 hover:border-slate-300 focus:outline-none transition-colors'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
