<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Sidebar mobile (drawer)
const sidebarOpen = ref(false);

// Tutup drawer otomatis saat berpindah halaman
watch(() => page.url, () => (sidebarOpen.value = false));

// Tutup drawer dengan tombol Escape
const onKeydown = (e) => e.key === 'Escape' && (sidebarOpen.value = false);
onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));

// Path ikon (Heroicons outline, viewBox 24x24)
const icons = {
    home: 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
    tag: 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z',
    discount: 'M9 14.25l6-6m-5.25.75h.008v.008H9.75V9zm4.5 5.25h.008v.008h-.008v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    receipt: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
    users: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
    building: 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
    cap: 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
    verify: 'M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z',
    cash: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
    lock: 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
    document: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
    chart: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
    review: 'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
    profile: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
    logout: 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9',
    close: 'M6 18L18 6M6 6l12 12',
    menu: 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5',
};

// Menu per peran. Tambah/ubah menu cukup di sini.
const menus = {
    admin: [
        { label: 'Tarif SPP', route: 'admin.tarif-spp.index', active: 'admin.tarif-spp.*', icon: 'tag' },
        { label: 'Keringanan SPP', route: 'admin.keringanan-spp.index', active: 'admin.keringanan-spp.*', icon: 'discount' },
        { label: 'Tagihan Siswa', route: 'admin.tagihan-spp.index', active: 'admin.tagihan-spp.*', icon: 'receipt' },
        { label: 'Manajemen Pengguna', route: 'admin.users.index', active: 'admin.users.*', icon: 'users' },
        { label: 'Master Kelas', route: 'admin.kelas.index', active: 'admin.kelas.*', icon: 'building' },
        { label: 'Master Siswa', route: 'admin.siswa.index', active: 'admin.siswa.*', icon: 'cap' },
    ],
    bendahara: [
        { label: 'Keringanan SPP', route: 'bendahara.keringanan-spp.index', active: 'bendahara.keringanan-spp.*', icon: 'discount' },
        { label: 'Tagihan SPP', route: 'bendahara.tagihan-spp.index', active: 'bendahara.tagihan-spp.*', icon: 'receipt' },
        { label: 'Verifikasi Setoran', route: 'bendahara.setoran-kasir.index', active: 'bendahara.setoran-kasir.*', icon: 'verify' },
    ],
    staf_tu: [
        { label: 'Loket Pembayaran', route: 'tu.pembayaran-spp.index', active: 'tu.pembayaran-spp.*', icon: 'cash' },
        { label: 'Tutup Kasir', route: 'tu.setoran-kasir.index', active: 'tu.setoran-kasir.*', icon: 'lock' },
        { label: 'Pengajuan Dispensasi', route: 'tu.dispensasi-spp.index', active: 'tu.dispensasi-spp.*', icon: 'document' },
    ],
    kepala_sekolah: [
        { label: 'Persetujuan Dispensasi', route: 'kepsek.dispensasi-spp.index', active: 'kepsek.dispensasi-spp.*', icon: 'verify' },
        { label: 'Monitoring & Laporan', route: 'kepsek.laporan-spp.index', active: 'kepsek.laporan-spp.*', icon: 'chart' },
    ],
    komite_sekolah: [
        { label: 'Tinjau Tarif SPP', route: 'komite.tarif-spp.index', active: 'komite.tarif-spp.*', icon: 'review' },
    ],
    siswa: [
        { label: 'Tagihan Saya', route: 'siswa.tagihan-saya.index', active: 'siswa.tagihan-saya.*', icon: 'receipt' },
    ],
};

const items = computed(() => [
    { label: 'Dashboard', route: 'dashboard', icon: 'home', isActive: route().current('dashboard') || route().current('*.dashboard') },
    ...(menus[user.value.role] ?? []).map((m) => ({ ...m, isActive: route().current(m.active) })),
]);
</script>

<template>
    <div class="layout">
        <!-- Overlay (mobile) -->
        <div v-if="sidebarOpen" class="overlay" aria-hidden="true" @click="sidebarOpen = false" />

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ terbuka: sidebarOpen }">
            <div class="side-top">
                <Link :href="route('dashboard')" class="brand">
                    <span class="brand-mark" aria-hidden="true"></span>
                    SPP Sekolah
                </Link>
                <button type="button" class="ikon-btn tutup" aria-label="Tutup menu" @click="sidebarOpen = false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons.close" />
                    </svg>
                </button>
            </div>

            <nav class="side-nav" aria-label="Menu utama">
                <Link
                    v-for="item in items"
                    :key="item.route"
                    :href="route(item.route)"
                    class="nav-link"
                    :class="{ aktif: item.isActive }"
                    :aria-current="item.isActive ? 'page' : undefined"
                >
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons[item.icon]" />
                    </svg>
                    <span class="teks">{{ item.label }}</span>
                </Link>
            </nav>

            <div class="side-user">
                <div class="user-info">
                    <div class="user-nama">{{ user.name }}</div>
                    <div class="user-peran">{{ user.role_label || user.role }}</div>
                    <div class="user-email">{{ user.email }}</div>
                </div>

                <Link :href="route('profile.edit')" class="nav-link" :class="{ aktif: route().current('profile.*') }">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons.profile" />
                    </svg>
                    <span class="teks">Profile</span>
                </Link>
                <Link :href="route('logout')" method="post" as="button" class="nav-link">
                    <svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons.logout" />
                    </svg>
                    <span class="teks">Log Out</span>
                </Link>
            </div>
        </aside>

        <!-- Konten -->
        <div class="konten">
            <div class="topbar">
                <button type="button" class="ikon-btn" aria-label="Buka menu" @click="sidebarOpen = true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons.menu" />
                    </svg>
                </button>
                <Link :href="route('dashboard')" class="brand">
                    <span class="brand-mark" aria-hidden="true"></span>
                    SPP Sekolah
                </Link>
            </div>

            <header v-if="$slots.header" class="page-header">
                <div class="page-header-inner">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/*
 * Disamakan dengan halaman Masuk: panel kiri berlatar --color-brand dengan
 * teks putih, area kanan berlatar --color-bg. Token dari resources/css/tokens.css.
 */
.layout {
    --lebar-sidebar: 17rem;
    --garis-terang: rgba(255, 255, 255, 0.18);
    --putih-redup: rgba(255, 255, 255, 0.85);
    font-family: var(--font-sans);
    font-size: var(--text-base);
    color: var(--color-ink);
    background: var(--color-bg);
    min-height: 100vh;
    min-height: 100dvh;
    line-height: var(--leading-body);
    -webkit-font-smoothing: antialiased;
}

/* Brand (sama dengan .brand-link di halaman Masuk) */
.brand {
    display: inline-flex;
    align-items: center;
    gap: var(--space-3);
    height: var(--control-height);
    color: #ffffff;
    font-weight: var(--weight-bold);
    font-size: var(--text-lg);
    letter-spacing: -0.01em;
    text-decoration: none;
}

.brand-mark {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    border-radius: 5px;
    background: #ffffff;
    box-shadow: inset 0 -6px 0 rgba(16, 36, 43, 0.2);
}

/* Sidebar */
.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 50;
    width: var(--lebar-sidebar);
    display: flex;
    flex-direction: column;
    background: var(--color-brand);
    color: #ffffff;
    transform: translateX(-100%);
    transition: transform 0.2s ease;
}

.sidebar.terbuka {
    transform: none;
}

.overlay {
    position: fixed;
    inset: 0;
    z-index: 40;
    background: rgba(16, 36, 43, 0.5);
}

.side-top {
    flex: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-4) var(--space-5) var(--space-3);
}

.side-nav {
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    margin: 0 var(--space-4);
    padding: var(--space-4) 0;
    border-top: 1px solid var(--garis-terang);
}

/* Tautan menu */
.nav-link {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    width: 100%;
    padding: var(--space-2) var(--space-3);
    border: 0;
    border-radius: var(--radius);
    background: transparent;
    color: var(--putih-redup);
    font: inherit;
    font-size: var(--text-sm);
    font-weight: var(--weight-medium);
    text-align: left;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.15s, color 0.15s;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.nav-link.aktif {
    background: rgba(255, 255, 255, 0.16);
    color: #ffffff;
    box-shadow: inset 3px 0 0 #ffffff;
}

.nav-link:focus-visible,
.brand:focus-visible,
.ikon-btn:focus-visible {
    outline: 2px solid #ffffff;
    outline-offset: 2px;
}

.ikon {
    flex: none;
    width: 20px;
    height: 20px;
}

.teks {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Blok pengguna: berperan seperti .banner-footer di halaman Masuk */
.side-user {
    flex: none;
    display: flex;
    flex-direction: column;
    gap: var(--space-1);
    margin: 0 var(--space-4);
    padding: var(--space-4) 0 var(--space-5);
    border-top: 1px solid var(--garis-terang);
}

.user-info {
    min-width: 0;
    padding: 0 var(--space-3) var(--space-3);
}

.user-nama,
.user-peran,
.user-email {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-nama {
    font-size: var(--text-sm);
    font-weight: var(--weight-bold);
}

.user-peran {
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
    color: var(--putih-redup);
}

.user-email {
    font-size: var(--text-xs);
    color: rgba(255, 255, 255, 0.75);
}

/* Top bar (mobile): blok brand di atas, seperti banner login di layar kecil */
.topbar {
    position: sticky;
    top: 0;
    z-index: 30;
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-4);
    background: var(--color-brand);
}

.ikon-btn {
    display: inline-grid;
    place-items: center;
    width: 36px;
    height: 36px;
    padding: 0;
    border: 1px solid rgba(255, 255, 255, 0.35);
    border-radius: var(--radius-sm);
    background: transparent;
    color: #ffffff;
    cursor: pointer;
    transition: background-color 0.15s;
}

.ikon-btn:hover {
    background: rgba(255, 255, 255, 0.12);
}

.ikon-btn svg {
    width: 20px;
    height: 20px;
}

/* Heading halaman: permukaan putih dengan garis tipis */
.page-header {
    background: var(--color-surface);
    border-bottom: var(--border);
}

.page-header-inner {
    max-width: 80rem;
    margin: 0 auto;
    padding: var(--space-5) var(--space-4);
}

/* Desktop */
@media (min-width: 1024px) {
    .sidebar {
        transform: none;
    }

    .konten {
        padding-left: var(--lebar-sidebar);
    }

    .topbar,
    .tutup,
    .overlay {
        display: none;
    }

    .page-header-inner {
        padding-inline: var(--space-5);
    }
}

@media (prefers-reduced-motion: reduce) {
    .sidebar,
    .nav-link,
    .ikon-btn {
        transition: none;
    }
}
</style>