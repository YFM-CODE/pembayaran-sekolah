<script setup>
defineProps({
    variant: {
        type: String,
        default: 'badge', // 'badge' | 'mark' | 'mark-white' | 'monochrome'
    },
});
</script>

<template>
    <!-- 1. BADGE (Default: Full official emblem with brand squircle, golden mortarboard, and crisp open ledger) -->
    <svg
        v-if="variant === 'badge'"
        viewBox="0 0 64 64"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <!-- Background Squircle Kontur Institusional -->
        <rect width="64" height="64" rx="16" fill="#0E6B63" />
        <rect x="1" y="1" width="62" height="62" rx="15" stroke="white" stroke-opacity="0.12" stroke-width="1" />

        <!-- Topi Toga (Wisuda & Capaian Akademik) -->
        <path d="M32 13L48 21L32 29L16 21L32 13Z" fill="#F59E0B" />
        <path d="M22.5 24V28.5C22.5 31.8 26.5 33.5 32 33.5C37.5 33.5 41.5 31.8 41.5 28.5V24" stroke="#D97706" stroke-width="2.2" stroke-linecap="round" />
        <path d="M43.5 23.5V30C43.5 31 44.5 31.5 45.5 31" stroke="#D97706" stroke-width="1.8" stroke-linecap="round" />

        <!-- Buku Terbuka / Buku Pembukuan SPP (Transparansi Finansial Sekolah) -->
        <!-- Lembar Halaman Kiri (Putih Bersih) -->
        <path d="M31 38.5C23.5 35.2 16 36.5 12.5 38.5V48.5C16.5 46.5 23.5 45.5 31 48.5V38.5Z" fill="#FFFFFF" />
        <!-- Lembar Halaman Kanan (Mint Lembut) -->
        <path d="M33 38.5C40.5 35.2 48 36.5 51.5 38.5V48.5C47.5 46.5 40.5 45.5 33 48.5V38.5Z" fill="#E6F4F1" />
        <!-- Lapisan Sub-lembar Bertingkat (Akuntabilitas) -->
        <path d="M31 43.5C25 41.5 19 42 16 43V45.5C19 44.5 25 44 31 46V43.5Z" fill="#CBD5E1" fill-opacity="0.45" />
        <path d="M33 43.5C39 41.5 45 42 48 43V45.5C45 44.5 39 44 33 46V43.5Z" fill="#94A3B8" fill-opacity="0.3" />

        <!-- Segel Emas / Bintang Integritas -->
        <circle cx="32" cy="34" r="2" fill="#FBBF24" />
    </svg>

    <!-- 2. MARK (Transparent background, emas + brand teal, untuk latar belakang terang) -->
    <svg
        v-else-if="variant === 'mark'"
        viewBox="0 0 64 64"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <path d="M32 10L50 19L32 28L14 19L32 10Z" fill="#F59E0B" />
        <path d="M21 22.5V28C21 31.5 25.5 33.5 32 33.5C38.5 33.5 43 31.5 43 28V22.5" stroke="#D97706" stroke-width="2.4" stroke-linecap="round" />
        <path d="M45.5 22V29C45.5 30 46.5 30.8 47.5 30.5" stroke="#D97706" stroke-width="2" stroke-linecap="round" />
        <path d="M30.5 38C22 34.5 14 36 10 38.5V50C14 47.5 22 46 30.5 49.5V38Z" fill="#0E6B63" />
        <path d="M33.5 38C42 34.5 50 36 54 38.5V50C50 47.5 42 46 33.5 49.5V38Z" fill="#14847B" />
        <path d="M15 42C19 40.5 24 40.5 28 42" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-opacity="0.7" />
        <path d="M36 42C40 40.5 45 40.5 49 42" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-opacity="0.7" />
        <circle cx="32" cy="34" r="2.2" fill="#FBBF24" />
    </svg>

    <!-- 3. MARK-WHITE (Transparent background, emas lembut + putih/mint, untuk latar gelap / hijau brand) -->
    <svg
        v-else-if="variant === 'mark-white'"
        viewBox="0 0 64 64"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <path d="M32 10L50 19L32 28L14 19L32 10Z" fill="#FDE68A" />
        <path d="M21 22.5V28C21 31.5 25.5 33.5 32 33.5C38.5 33.5 43 31.5 43 28V22.5" stroke="#FDE68A" stroke-width="2.6" stroke-linecap="round" />
        <path d="M45.5 22V29C45.5 30 46.5 30.8 47.5 30.5" stroke="#FDE68A" stroke-width="2" stroke-linecap="round" />
        <path d="M30.5 38C22 34.5 14 36 10 38.5V50C14 47.5 22 46 30.5 49.5V38Z" fill="#FFFFFF" />
        <path d="M33.5 38C42 34.5 50 36 54 38.5V50C50 47.5 42 46 33.5 49.5V38Z" fill="#CCFBF1" />
        <path d="M15 42C19 40.5 24 40.5 28 42" stroke="#0E6B63" stroke-width="1.2" stroke-linecap="round" stroke-opacity="0.4" />
        <path d="M36 42C40 40.5 45 40.5 49 42" stroke="#0E6B63" stroke-width="1.2" stroke-linecap="round" stroke-opacity="0.4" />
        <circle cx="32" cy="34" r="2.2" fill="#FDE68A" />
    </svg>

    <!-- 4. MONOCHROME (Single color fill/stroke via currentColor) -->
    <svg
        v-else
        viewBox="0 0 64 64"
        fill="currentColor"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <path d="M32 10L50 19L32 28L14 19L32 10Z" />
        <path d="M21 22.5V28C21 31.5 25.5 33.5 32 33.5C38.5 33.5 43 31.5 43 28V22.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
        <path d="M45.5 22V29C45.5 30 46.5 30.8 47.5 30.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        <path d="M30.5 38C22 34.5 14 36 10 38.5V50C14 47.5 22 46 30.5 49.5V38Z" />
        <path d="M33.5 38C42 34.5 50 36 54 38.5V50C50 47.5 42 46 33.5 49.5V38Z" />
        <circle cx="32" cy="34" r="2.2" />
    </svg>
</template>
