<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: [String, Number],
    required: false,
    default: '',
});

defineProps({
    type: {
        type: String,
        default: 'text',
    },
    invalid: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const input = ref(null);

onMounted(() => {
    if (input.value && input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        :type="type"
        :disabled="disabled"
        :aria-invalid="invalid ? 'true' : 'false'"
        class="block min-h-11 w-full rounded-lg border bg-white px-3 py-2 text-sm text-ink placeholder:text-ink-muted/80 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-brand-focus disabled:cursor-not-allowed disabled:bg-page disabled:opacity-70"
        :class="invalid ? 'border-status-danger focus:border-status-danger focus:ring-status-danger/40' : 'border-brand-border focus:border-brand'"
        v-model="model"
        ref="input"
    />
</template>
