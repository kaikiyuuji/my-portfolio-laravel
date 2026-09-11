<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="font-mono text-[10px] font-semibold uppercase tracking-wider text-red-600">
            {{ message }}
        </p>
    </div>
</template>
