<script setup>
defineProps({
    name: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <svg
        v-if="name === 'dashboard'"
        class="admin-nav-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        aria-hidden="true"
    >
        <path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z" />
    </svg>

    <svg
        v-else-if="name === 'profile'"
        class="admin-nav-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        aria-hidden="true"
    >
        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
        <path d="M4 21v-2a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2" />
    </svg>

    <svg
        v-else-if="name === 'code'"
        class="admin-nav-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        aria-hidden="true"
    >
        <path d="m8 6-6 6 6 6M16 6l6 6-6 6M14 3l-4 18" />
    </svg>

    <svg
        v-else-if="name === 'projects'"
        class="admin-nav-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        aria-hidden="true"
    >
        <path d="M3 6h7l2 2h9v11H3z" />
        <path d="M3 10h18M8 14h8M8 17h5" />
    </svg>

    <svg
        v-else-if="name === 'social'"
        class="admin-nav-icon"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        aria-hidden="true"
    >
        <circle cx="6" cy="12" r="3" />
        <circle cx="18" cy="6" r="3" />
        <circle cx="18" cy="18" r="3" />
        <path d="m8.6 10.5 6.8-3M8.6 13.5l6.8 3" />
    </svg>
</template>
