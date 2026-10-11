<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
