<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="min-h-11 border border-[var(--line)] bg-[var(--paper)] px-3 py-2.5 text-sm text-[var(--ink)] shadow-none placeholder:text-[var(--muted)]/60 focus:border-[var(--accent)] focus:ring-1 focus:ring-[var(--accent)] disabled:cursor-not-allowed disabled:opacity-50"
        v-model="model"
        ref="input"
    />
</template>
