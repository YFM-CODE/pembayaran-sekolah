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
        default: false,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand text-start text-base font-semibold text-brand bg-brand-soft focus:outline-none transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-ink-muted hover:text-ink hover:bg-page hover:border-brand-border focus:outline-none transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :aria-current="active ? 'page' : undefined" :class="classes">
        <slot />
    </Link>
</template>
