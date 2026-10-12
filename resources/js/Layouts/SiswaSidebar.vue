<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['closeMobile']);

const page = usePage();

const groups = computed(() => {
    const unpaidCount = page.props.badges?.tagihan_siswa_belum_lunas ?? 0;

    return [
        {
            title: 'Menu Utama',
            items: [
                {
                    label: 'Dashboard',
                    href: route().has('siswa.dashboard') ? route('siswa.dashboard') : route('dashboard'),
                    active: route().current('siswa.dashboard') || route().current('dashboard'),
                    icon: 'dashboard',
                    badge: null,
                },
                {
                    label: 'Tagihan SPP Saya',
                    href: route('siswa.tagihan-saya.index'),
                    active: route().current('siswa.tagihan-saya.*'),
                    icon: 'tagihan',
                    badge: unpaidCount > 0 ? unpaidCount : null,
                    badgeAria: `${unpaidCount} tagihan belum lunas`,
                },
                {
                    label: 'Dispensasi Saya',
                    href: route('siswa.dispensasi-saya.index'),
                    active: route().current('siswa.dispensasi-saya.*'),
                    icon: 'dispensasi',
                    badge: null,
                },
            ],
        },
        {
            title: 'Akun',
            items: [
                {
                    label: 'Profil Saya',
                    href: route('profile.edit'),
                    active: route().current('profile.edit'),
                    icon: 'profile',
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
        aria-label="Navigasi Utama Siswa"
    >
        <!-- Logo / App Name -->
        <div class="flex h-16 shrink-0 items-center border-b border-brand-border px-4">
            <Link
                :href="route('dashboard')"
                class="flex items-center gap-3 overflow-hidden rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                :title="collapsed ? 'SPP Sekolah - Portal Siswa' : undefined"
            >
                <ApplicationLogo class="h-10 w-10 shrink-0 shadow-xs" />
                <div v-if="!collapsed" class="min-w-0 flex-1">
                    <span class="block truncate text-base font-bold tracking-tight text-ink">SPP Sekolah</span>
                    <span class="block truncate text-xs font-semibold text-brand">Portal Siswa</span>
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
                                <!-- Tagihan SPP -->
                                <svg v-else-if="item.icon === 'tagihan'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                </svg>
                                <!-- Dispensasi -->
                                <svg v-else-if="item.icon === 'dispensasi'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <!-- Profil -->
                                <svg v-else-if="item.icon === 'profile'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>

                            <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
                        </div>

                        <!-- Badge -->
                        <span
                            v-if="item.badge && !collapsed"
                            class="inline-flex items-center rounded-full bg-status-danger/15 px-2 py-0.5 text-xs font-semibold tabular-nums text-status-danger"
                            :aria-label="item.badgeAria"
                        >
                            {{ item.badge }}
                        </span>
                        <span
                            v-else-if="item.badge && collapsed"
                            class="absolute right-3 top-2 h-2 w-2 rounded-full bg-status-danger"
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
                :title="collapsed ? 'Profil Saya' : undefined"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-soft text-sm font-bold text-brand">
                    {{ ((page.props.auth?.siswa?.nama_lengkap || page.props.auth?.user?.name) || 'S').charAt(0).toUpperCase() }}
                </div>
                <div v-if="!collapsed" class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-ink">
                        {{ page.props.auth?.siswa?.nama_lengkap || page.props.auth?.user?.name }}
                    </p>
                    <p class="truncate text-xs text-ink-muted">
                        {{ page.props.auth?.siswa ? `${page.props.auth.siswa.kelas} • NIS ${page.props.auth.siswa.nis}` : page.props.auth?.user?.email }}
                    </p>
                </div>
            </Link>
        </div>
    </aside>
</template>
