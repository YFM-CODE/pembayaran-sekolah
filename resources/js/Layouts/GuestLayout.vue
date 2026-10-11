<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-page pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/" class="flex flex-col items-center gap-2.5 group">
                <ApplicationLogo class="h-16 w-16 shadow-md transition-transform duration-200 group-hover:scale-105" />
                <span class="text-xl font-extrabold tracking-tight text-ink">SPP Sekolah</span>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden border border-brand-border bg-white px-6 py-6 shadow-panel sm:max-w-md sm:rounded-xl"
        >
            <slot />
        </div>
    </div>
</template>
