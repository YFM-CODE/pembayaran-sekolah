<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import AdminSidebar from '@/Layouts/AdminSidebar.vue';
import SiswaSidebar from '@/Layouts/SiswaSidebar.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');
const isSiswa = computed(() => page.props.auth?.user?.role === 'siswa');
const studentDisplayName = computed(() => page.props.auth?.siswa?.nama_lengkap || page.props.auth?.user?.name || 'Siswa');

// Admin layout state
const desktopCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

// Non-admin legacy menu state
const showingNavigationDropdown = ref(false);

// Close mobile drawer on escape
const handleKeydown = (e) => {
    if (e.key === 'Escape' && mobileSidebarOpen.value) {
        mobileSidebarOpen.value = false;
    }
};

watch(mobileSidebarOpen, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <div>
        <!-- ========================================== -->
        <!-- 1. ADMIN MODERN VERTICAL SIDEBAR LAYOUT    -->
        <!-- ========================================== -->
        <div v-if="isAdmin" class="min-h-screen bg-page flex">
            <!-- Mobile Drawer Backdrop -->
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="mobileSidebarOpen"
                    class="fixed inset-0 z-40 bg-ink/50 backdrop-blur-xs md:hidden"
                    @click="mobileSidebarOpen = false"
                    aria-hidden="true"
                />
            </Transition>

            <!-- Mobile Drawer -->
            <Transition
                enter-active-class="transform transition ease-in-out duration-300"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-300"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <div
                    v-if="mobileSidebarOpen"
                    class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] md:hidden shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Navigasi Menu Admin"
                >
                    <AdminSidebar :collapsed="false" @close-mobile="mobileSidebarOpen = false" />
                </div>
            </Transition>

            <!-- Desktop Sidebar -->
            <div class="hidden shrink-0 md:block">
                <div class="sticky top-0 h-screen">
                    <AdminSidebar :collapsed="desktopCollapsed" />
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex min-w-0 flex-1 flex-col">
                <!-- Mini Topbar -->
                <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between border-b border-brand-border bg-white/95 px-4 backdrop-blur-sm sm:px-6">
                    <div class="flex items-center gap-3">
                        <!-- Mobile Toggle Button -->
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-ink hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus md:hidden"
                            aria-label="Buka navigasi menu"
                            @click="mobileSidebarOpen = true"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Desktop Toggle Button -->
                        <button
                            type="button"
                            class="hidden h-10 w-10 items-center justify-center rounded-lg text-ink hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus md:inline-flex"
                            :aria-label="desktopCollapsed ? 'Perluas navigasi' : 'Ciutkan navigasi'"
                            :title="desktopCollapsed ? 'Perluas navigasi' : 'Ciutkan navigasi'"
                            @click="desktopCollapsed = !desktopCollapsed"
                        >
                            <svg
                                class="h-5 w-5 text-ink-muted transition-transform"
                                :class="{ 'rotate-180': desktopCollapsed }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Breadcrumb & Brand Indicator -->
                        <div class="hidden sm:flex sm:items-center sm:gap-2 text-sm">
                            <span class="font-medium text-ink-muted">Sistem SPP</span>
                            <span class="text-brand-border">/</span>
                            <span class="font-semibold text-brand">Admin Area</span>
                        </div>
                    </div>

                    <!-- Right Controls / Profile Dropdown -->
                    <div class="flex items-center gap-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-medium text-ink transition hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                                >
                                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-brand text-xs font-bold text-white shadow-xs">
                                        {{ (page.props.auth?.user?.name || 'A').charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="hidden text-left sm:block">
                                        <div class="text-xs font-semibold leading-tight text-ink">
                                            {{ page.props.auth?.user?.name }}
                                        </div>
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-brand">
                                            Administrator
                                        </div>
                                    </div>
                                    <svg class="h-4 w-4 text-ink-muted" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="border-b border-brand-border px-4 py-2.5">
                                    <p class="text-xs font-semibold text-ink">{{ page.props.auth?.user?.name }}</p>
                                    <p class="truncate text-xs text-ink-muted">{{ page.props.auth?.user?.email }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')">
                                    Profil Akun
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Keluar
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <!-- Page Header (if slot provided) -->
                <header class="border-b border-brand-border bg-white px-4 py-4 sm:px-6 lg:px-8 shadow-xs" v-if="$slots.header">
                    <slot name="header" />
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    <slot />
                </main>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. STUDENT MODERN VERTICAL SIDEBAR LAYOUT  -->
        <!-- ========================================== -->
        <div v-else-if="isSiswa" class="min-h-screen bg-page flex">
            <!-- Mobile Drawer Backdrop -->
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="mobileSidebarOpen"
                    class="fixed inset-0 z-40 bg-ink/50 backdrop-blur-xs md:hidden"
                    @click="mobileSidebarOpen = false"
                    aria-hidden="true"
                />
            </Transition>

            <!-- Mobile Drawer -->
            <Transition
                enter-active-class="transform transition ease-in-out duration-300"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transform transition ease-in-out duration-300"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <div
                    v-if="mobileSidebarOpen"
                    class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] md:hidden shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Navigasi Menu Siswa"
                >
                    <SiswaSidebar :collapsed="false" @close-mobile="mobileSidebarOpen = false" />
                </div>
            </Transition>

            <!-- Desktop Sidebar -->
            <div class="hidden shrink-0 md:block">
                <div class="sticky top-0 h-screen">
                    <SiswaSidebar :collapsed="desktopCollapsed" />
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex min-w-0 flex-1 flex-col">
                <!-- Mini Topbar -->
                <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between border-b border-brand-border bg-white/95 px-4 backdrop-blur-sm sm:px-6">
                    <div class="flex items-center gap-3">
                        <!-- Mobile Toggle Button -->
                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-ink hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus md:hidden"
                            aria-label="Buka navigasi menu"
                            @click="mobileSidebarOpen = true"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Desktop Toggle Button -->
                        <button
                            type="button"
                            class="hidden h-10 w-10 items-center justify-center rounded-lg text-ink hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus md:inline-flex"
                            :aria-label="desktopCollapsed ? 'Perluas navigasi' : 'Ciutkan navigasi'"
                            :title="desktopCollapsed ? 'Perluas navigasi' : 'Ciutkan navigasi'"
                            @click="desktopCollapsed = !desktopCollapsed"
                        >
                            <svg
                                class="h-5 w-5 text-ink-muted transition-transform"
                                :class="{ 'rotate-180': desktopCollapsed }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Breadcrumb & Brand Indicator -->
                        <div class="hidden sm:flex sm:items-center sm:gap-2 text-sm">
                            <span class="font-medium text-ink-muted">Sistem SPP</span>
                            <span class="text-brand-border">/</span>
                            <span class="font-semibold text-brand">Portal Siswa</span>
                            <template v-if="page.props.auth?.siswa?.kelas">
                                <span class="text-brand-border">/</span>
                                <span class="rounded bg-brand-soft px-2 py-0.5 text-xs font-semibold text-brand">
                                    {{ page.props.auth.siswa.kelas }}
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Right Controls / Profile Dropdown -->
                    <div class="flex items-center gap-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-sm font-medium text-ink transition hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                                >
                                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-brand text-xs font-bold text-white shadow-xs">
                                        {{ studentDisplayName.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="hidden text-left sm:block">
                                        <div class="text-xs font-semibold leading-tight text-ink">
                                            {{ studentDisplayName }}
                                        </div>
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-brand">
                                            Siswa / Wali Murid
                                        </div>
                                    </div>
                                    <svg class="h-4 w-4 text-ink-muted" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="border-b border-brand-border px-4 py-2.5">
                                    <p class="text-xs font-semibold text-ink">{{ studentDisplayName }}</p>
                                    <p class="truncate text-xs text-ink-muted">
                                        {{ page.props.auth?.siswa ? `NIS: ${page.props.auth.siswa.nis}` : page.props.auth?.user?.email }}
                                    </p>
                                </div>
                                <DropdownLink :href="route('profile.edit')">
                                    Profil Akun
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Keluar
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <!-- Page Header (if slot provided) -->
                <header class="border-b border-brand-border bg-white px-4 py-4 sm:px-6 lg:px-8 shadow-xs" v-if="$slots.header">
                    <slot name="header" />
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    <slot />
                </main>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. OTHER ROLES (PRESERVED FOR STABILITY)   -->
        <!-- ========================================== -->
        <div v-else class="min-h-screen bg-page">
            <nav class="border-b border-brand-border bg-white">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="flex items-center gap-3">
                                    <ApplicationLogo class="block h-9 w-9 shadow-sm" />
                                    <span class="font-bold tracking-tight text-ink">SPP Sekolah</span>
                                </Link>
                            </div>

                            <!-- Navigation Links Berdasarkan Peran (Desktop) -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard') || route().current('*.dashboard')"
                                >
                                    Dashboard
                                </NavLink>

                                <!-- Admin -->
                                <template v-if="$page.props.auth.user.role === 'admin'">
                                    <NavLink :href="route('admin.tarif-spp.index')" :active="route().current('admin.tarif-spp.*')">
                                        Tarif SPP
                                    </NavLink>
                                    <NavLink :href="route('admin.keringanan-spp.index')" :active="route().current('admin.keringanan-spp.*')">
                                        Keringanan SPP
                                    </NavLink>
                                    <NavLink :href="route('admin.tagihan-spp.index')" :active="route().current('admin.tagihan-spp.*')">
                                        Tagihan Siswa
                                    </NavLink>
                                    <NavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                                        Manajemen Pengguna
                                    </NavLink>
                                    <NavLink :href="route('admin.kelas.index')" :active="route().current('admin.kelas.*')">
                                        Master Kelas
                                    </NavLink>
                                    <NavLink :href="route('admin.siswa.index')" :active="route().current('admin.siswa.*')">
                                        Master Siswa
                                    </NavLink>
                                </template>

                                <!-- Bendahara -->
                                <template v-if="$page.props.auth.user.role === 'bendahara'">
                                    <NavLink :href="route('bendahara.keringanan-spp.index')" :active="route().current('bendahara.keringanan-spp.*')">
                                        Keringanan SPP
                                    </NavLink>
                                    <NavLink :href="route('bendahara.tagihan-spp.index')" :active="route().current('bendahara.tagihan-spp.*')">
                                        Tagihan SPP
                                    </NavLink>
                                    <NavLink :href="route('bendahara.setoran-kasir.index')" :active="route().current('bendahara.setoran-kasir.*')">
                                        Verifikasi Setoran
                                    </NavLink>
                                </template>

                                <!-- Staf TU -->
                                <template v-if="$page.props.auth.user.role === 'staf_tu'">
                                    <NavLink :href="route('tu.pembayaran-spp.index')" :active="route().current('tu.pembayaran-spp.*')">
                                        Loket Pembayaran
                                    </NavLink>
                                    <NavLink :href="route('tu.setoran-kasir.index')" :active="route().current('tu.setoran-kasir.*')">
                                        Tutup Kasir
                                    </NavLink>
                                    <NavLink :href="route('tu.dispensasi-spp.index')" :active="route().current('tu.dispensasi-spp.*')">
                                        Pengajuan Dispensasi
                                    </NavLink>
                                </template>

                                <!-- Kepala Sekolah -->
                                <template v-if="$page.props.auth.user.role === 'kepala_sekolah'">
                                    <NavLink :href="route('kepsek.dispensasi-spp.index')" :active="route().current('kepsek.dispensasi-spp.*')">
                                        Persetujuan Dispensasi
                                    </NavLink>
                                    <NavLink :href="route('kepsek.laporan-spp.index')" :active="route().current('kepsek.laporan-spp.*')">
                                        Monitoring & Laporan
                                    </NavLink>
                                </template>

                                <!-- Komite Sekolah -->
                                <template v-if="$page.props.auth.user.role === 'komite_sekolah'">
                                    <NavLink :href="route('komite.tarif-spp.index')" :active="route().current('komite.tarif-spp.*')">
                                        Tinjau Tarif SPP
                                    </NavLink>
                                </template>

                                <!-- Siswa -->
                                <template v-if="$page.props.auth.user.role === 'siswa'">
                                    <NavLink :href="route('siswa.tagihan-saya.index')" :active="route().current('siswa.tagihan-saya.*')">
                                        Tagihan Saya
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-2 rounded-md border border-brand-border bg-white px-3 py-2 text-sm font-medium leading-4 text-ink transition hover:bg-brand-soft focus:outline-none"
                                            >
                                                <div class="text-left">
                                                    <div class="font-medium text-ink">
                                                        {{ $page.props.auth.user.name }}
                                                    </div>
                                                    <div class="text-xs font-semibold text-brand uppercase tracking-wider">
                                                        {{ $page.props.auth.user.role_label || $page.props.auth.user.role }}
                                                    </div>
                                                </div>

                                                <svg class="-me-0.5 ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profil
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Keluar
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Mobile Hamburger for non-admin -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-ink-muted hover:bg-brand-soft hover:text-ink focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard') || route().current('*.dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>

                        <!-- Admin Mobile -->
                        <template v-if="$page.props.auth.user.role === 'admin'">
                            <ResponsiveNavLink :href="route('admin.tarif-spp.index')" :active="route().current('admin.tarif-spp.*')">
                                Tarif SPP
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.keringanan-spp.index')" :active="route().current('admin.keringanan-spp.*')">
                                Keringanan SPP
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.tagihan-spp.index')" :active="route().current('admin.tagihan-spp.*')">
                                Tagihan Siswa
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.users.index')" :active="route().current('admin.users.*')">
                                Manajemen Pengguna
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.kelas.index')" :active="route().current('admin.kelas.*')">
                                Master Kelas
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('admin.siswa.index')" :active="route().current('admin.siswa.*')">
                                Master Siswa
                            </ResponsiveNavLink>
                        </template>

                        <!-- Bendahara Mobile -->
                        <template v-if="$page.props.auth.user.role === 'bendahara'">
                            <ResponsiveNavLink :href="route('bendahara.keringanan-spp.index')" :active="route().current('bendahara.keringanan-spp.*')">
                                Keringanan SPP
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('bendahara.tagihan-spp.index')" :active="route().current('bendahara.tagihan-spp.*')">
                                Tagihan SPP
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('bendahara.setoran-kasir.index')" :active="route().current('bendahara.setoran-kasir.*')">
                                Verifikasi Setoran
                            </ResponsiveNavLink>
                        </template>

                        <!-- Staf TU Mobile -->
                        <template v-if="$page.props.auth.user.role === 'staf_tu'">
                            <ResponsiveNavLink :href="route('tu.pembayaran-spp.index')" :active="route().current('tu.pembayaran-spp.*')">
                                Loket Pembayaran
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('tu.setoran-kasir.index')" :active="route().current('tu.setoran-kasir.*')">
                                Tutup Kasir
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('tu.dispensasi-spp.index')" :active="route().current('tu.dispensasi-spp.*')">
                                Pengajuan Dispensasi
                            </ResponsiveNavLink>
                        </template>

                        <!-- Kepala Sekolah Mobile -->
                        <template v-if="$page.props.auth.user.role === 'kepala_sekolah'">
                            <ResponsiveNavLink :href="route('kepsek.dispensasi-spp.index')" :active="route().current('kepsek.dispensasi-spp.*')">
                                Persetujuan Dispensasi
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('kepsek.laporan-spp.index')" :active="route().current('kepsek.laporan-spp.*')">
                                Monitoring & Laporan
                            </ResponsiveNavLink>
                        </template>

                        <!-- Komite Sekolah Mobile -->
                        <template v-if="$page.props.auth.user.role === 'komite_sekolah'">
                            <ResponsiveNavLink :href="route('komite.tarif-spp.index')" :active="route().current('komite.tarif-spp.*')">
                                Tinjau Tarif SPP
                            </ResponsiveNavLink>
                        </template>

                        <!-- Siswa Mobile -->
                        <template v-if="$page.props.auth.user.role === 'siswa'">
                            <ResponsiveNavLink :href="route('siswa.tagihan-saya.index')" :active="route().current('siswa.tagihan-saya.*')">
                                Tagihan Saya
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <div class="border-t border-brand-border pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-ink">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-xs font-semibold text-brand uppercase tracking-wider">
                                {{ $page.props.auth.user.role_label || $page.props.auth.user.role }}
                            </div>
                            <div class="text-sm font-medium text-ink-muted">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Keluar
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white shadow-xs border-b border-brand-border" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>