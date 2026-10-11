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
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-brand text-sm font-semibold leading-5 text-brand focus:outline-none transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-ink-muted hover:text-ink hover:border-brand-border focus:outline-none transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :aria-current="active ? 'page' : undefined" :class="classes">
        <slot />
    </Link>
</template>
