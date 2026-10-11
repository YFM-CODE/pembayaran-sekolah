<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['closeMobile']);

const page = usePage();

const groups = computed(() => {
    const unpaidCount = page.props.badges?.tagihan_belum_lunas ?? 0;

    return [
        {
            title: 'Ringkasan',
            items: [
                {
                    label: 'Dashboard',
                    href: route().has('admin.dashboard') ? route('admin.dashboard') : route('dashboard'),
                    active: route().current('admin.dashboard') || route().current('dashboard'),
                    icon: 'dashboard',
                    badge: null,
                },
            ],
        },
        {
            title: 'Master Data',
            items: [
                {
                    label: 'Data Siswa',
                    href: route('admin.siswa.index'),
                    active: route().current('admin.siswa.*'),
                    icon: 'siswa',
                    badge: null,
                },
                {
                    label: 'Data Kelas',
                    href: route('admin.kelas.index'),
                    active: route().current('admin.kelas.*'),
                    icon: 'kelas',
                    badge: null,
                },
                {
                    label: 'Skema Tarif SPP',
                    href: route('admin.tarif-spp.index'),
                    active: route().current('admin.tarif-spp.*'),
                    icon: 'tarif',
                    badge: null,
                },
                {
                    label: 'Program Keringanan',
                    href: route('admin.keringanan-spp.index'),
                    active: route().current('admin.keringanan-spp.*'),
                    icon: 'keringanan',
                    badge: null,
                },
            ],
        },
        {
            title: 'Keuangan SPP',
            items: [
                {
                    label: 'Tagihan Siswa',
                    href: route('admin.tagihan-spp.index'),
                    active: route().current('admin.tagihan-spp.*'),
                    icon: 'tagihan',
                    badge: unpaidCount > 0 ? unpaidCount : null,
                    badgeAria: `${unpaidCount} tagihan belum lunas`,
                },
            ],
        },
        {
            title: 'Pengaturan',
            items: [
                {
                    label: 'Manajemen Pengguna',
                    href: route('admin.users.index'),
                    active: route().current('admin.users.*'),
                    icon: 'users',
                    badge: null,
                },
            ],
        },
    ];
});
</script>

<template>
    <aside
        class="flex h-full flex-col border-r border-brand-border bg-white transition-[width] duration-200 ease-in-out"
        :class="collapsed ? 'w-20' : 'w-64'"
        aria-label="Navigasi Utama Admin"
    >
        <!-- Logo / App Name -->
        <div class="flex h-16 shrink-0 items-center border-b border-brand-border px-4">
            <Link
                :href="route('dashboard')"
                class="flex items-center gap-3 overflow-hidden rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                :title="collapsed ? 'SPP Sekolah - Administrator' : undefined"
            >
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand text-white shadow-sm">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div v-if="!collapsed" class="min-w-0 flex-1">
                    <span class="block truncate text-base font-bold tracking-tight text-ink">SPP Sekolah</span>
                    <span class="block truncate text-xs font-semibold text-brand">Panel Admin</span>
                </div>
            </Link>
        </div>

        <!-- Navigation items -->
        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
            <section v-for="group in groups" :key="group.title">
                <h2
                    v-if="!collapsed"
                    class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-ink-muted"
                >
                    {{ group.title }}
                </h2>
                <div v-else class="mb-2 text-center">
                    <span class="inline-block h-0.5 w-6 rounded-full bg-brand-border/60"></span>
                </div>

                <div class="space-y-1">
                    <Link
                        v-for="item in group.items"
                        :key="item.label"
                        :href="item.href"
                        :aria-current="item.active ? 'page' : undefined"
                        :title="collapsed ? item.label : undefined"
                        @click="emit('closeMobile')"
                        class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                        :class="[
                            collapsed ? 'justify-center' : 'justify-between',
                            item.active
                                ? 'bg-brand-soft font-semibold text-brand shadow-xs'
                                : 'text-ink-muted hover:bg-page hover:text-ink'
                        ]"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Icon -->
                            <div class="shrink-0 transition-colors" :class="item.active ? 'text-brand' : 'text-ink-muted group-hover:text-ink'">
                                <!-- Dashboard -->
                                <svg v-if="item.icon === 'dashboard'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                                <!-- Siswa -->
                                <svg v-else-if="item.icon === 'siswa'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                <!-- Kelas -->
                                <svg v-else-if="item.icon === 'kelas'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                                </svg>
                                <!-- Tarif -->
                                <svg v-else-if="item.icon === 'tarif'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                </svg>
                                <!-- Keringanan -->
                                <svg v-else-if="item.icon === 'keringanan'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                </svg>
                                <!-- Tagihan -->
                                <svg v-else-if="item.icon === 'tagihan'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <!-- Users -->
                                <svg v-else-if="item.icon === 'users'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>

                            <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
                        </div>

                        <!-- Badge -->
                        <span
                            v-if="item.badge && !collapsed"
                            class="inline-flex items-center rounded-full bg-status-warning/15 px-2 py-0.5 text-xs font-semibold tabular-nums text-status-warning"
                            :aria-label="item.badgeAria"
                        >
                            {{ item.badge }}
                        </span>
                        <span
                            v-else-if="item.badge && collapsed"
                            class="absolute right-3 top-2 h-2 w-2 rounded-full bg-status-warning"
                            :aria-label="item.badgeAria"
                        ></span>
                    </Link>
                </div>
            </section>
        </nav>

        <!-- User profile shortcut at bottom -->
        <div class="border-t border-brand-border p-3">
            <Link
                :href="route('profile.edit')"
                class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-page focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                :title="collapsed ? 'Profil Administrator' : undefined"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-soft text-sm font-bold text-brand">
                    {{ (page.props.auth?.user?.name || 'A').charAt(0).toUpperCase() }}
                </div>
                <div v-if="!collapsed" class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-ink">{{ page.props.auth?.user?.name }}</p>
                    <p class="truncate text-xs text-ink-muted">{{ page.props.auth?.user?.email }}</p>
                </div>
            </Link>
        </div>
    </aside>
</template>
