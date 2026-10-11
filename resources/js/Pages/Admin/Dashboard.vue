<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    ringkasan: { type: Array, default: () => [] },
    grafik: { type: Object, default: null },
    transaksiTerbaru: { type: Array, default: () => [] },
});

const page = usePage();
const adminName = computed(() => page.props.auth?.user?.name ?? 'Admin');
const rupiah = (n) => 'Rp ' + Number(n ?? 0).toLocaleString('id-ID');
const tanggalHariIni = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const formatWaktu = (isoString) => {
    if (!isoString) return '-';
    try {
        const d = new Date(isoString);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return isoString;
    }
};

// Pemisahan kartu sorotan tunggakan & kartu ringkasan
const tunggakan = computed(() => props.ringkasan.find((i) => i.tipe === 'rupiah'));
const kartuRingkasan = computed(() => props.ringkasan.filter((i) => i.tipe !== 'rupiah'));

// Data grafik & persentase pelunasan
const lunas = computed(() => Number(props.grafik?.persentase_lunas ?? 0));
const totalSiswa = computed(() => {
    const g = props.grafik;
    return g ? (g.siswa_aktif || 0) + (g.siswa_lulus || 0) + (g.siswa_keluar || 0) : 0;
});
const rasioSiswa = (val) => (totalSiswa.value > 0 ? Math.round(((val || 0) / totalSiswa.value) * 100) : 0);

// Ikon & aksen warna konsisten untuk tiap kartu ringkasan
const konfigurasiIkon = {
    'Total Siswa Terdaftar': {
        icon: 'siswa',
        badgeColor: 'bg-brand-soft text-brand',
    },
    'Master Rombel / Kelas': {
        icon: 'kelas',
        badgeColor: 'bg-brand-soft text-brand',
    },
    'Pengguna Sistem': {
        icon: 'users',
        badgeColor: 'bg-brand-soft text-brand',
    },
    'Skema Tarif SPP': {
        icon: 'tarif',
        badgeColor: 'bg-brand-soft text-brand',
    },
    'Program Keringanan': {
        icon: 'keringanan',
        badgeColor: 'bg-brand-soft text-brand',
    },
    'Tagihan Belum Lunas': {
        icon: 'tagihan',
        badgeColor: 'bg-status-warning/15 text-status-warning',
    },
};

const getMetaKartu = (label) => {
    return konfigurasiIkon[label] ?? {
        icon: 'default',
        badgeColor: 'bg-brand-soft text-brand',
    };
};

// Aksi Cepat Admin
const aksiCepat = [
    {
        judul: 'Pendaftaran Siswa',
        deskripsi: 'Input data siswa baru & rombel',
        rute: 'admin.siswa.create',
        icon: 'plus-user',
    },
    {
        judul: 'Tambah Kelas',
        deskripsi: 'Buka rombel / kelas baru',
        rute: 'admin.kelas.create',
        icon: 'plus-class',
    },
    {
        judul: 'Skema Tarif SPP',
        deskripsi: 'Susun tarif tingkat & tahun ajaran',
        rute: 'admin.tarif-spp.create',
        icon: 'plus-tarif',
    },
    {
        judul: 'Kelola Pengguna',
        deskripsi: 'Atur akun staf, kasir & kepsek',
        rute: 'admin.users.index',
        icon: 'manage-users',
    },
    {
        judul: 'Pantau Tagihan Siswa',
        deskripsi: 'Generate & monitoring pelunasan',
        rute: 'admin.tagihan-spp.index',
        icon: 'monitor-bills',
    },
];
</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">
                        Dashboard Administrator
                    </h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">
                        Pusat kendali data induk, akun pengguna, dan monitoring keuangan SPP sekolah.
                    </p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-lg border border-brand-border bg-page px-3 py-1.5 text-xs font-medium text-ink-muted shadow-2xs">
                    <svg class="h-4 w-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ tanggalHariIni }}</span>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- 1. BANNER UTAMA DENGAN SOROTAN KEUANGAN & RASIO PELUNASAN -->
            <section class="rounded-2xl border border-brand-border bg-gradient-to-br from-brand to-brand-hover p-6 text-white shadow-sm sm:p-8">
                <div class="grid gap-8 lg:grid-cols-5 lg:items-center">
                    <div class="space-y-4 lg:col-span-3">
                        <div class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur-xs">
                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            <span>Sistem SPP Terhubung</span>
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight sm:text-3xl text-white">
                            Selamat datang kembali, {{ adminName }}.
                        </h2>
                        <p class="max-w-xl text-sm leading-relaxed text-white/90">
                            Pantau seluruh kewajiban SPP yang berjalan, keaktifan siswa, dan akses cepat ke master data sekolah.
                        </p>

                        <!-- Total Tunggakan Highlight -->
                        <div v-if="tunggakan" class="pt-4 border-t border-white/20">
                            <p class="text-xs font-semibold uppercase tracking-wider text-white/80">
                                {{ tunggakan.label }}
                            </p>
                            <p class="mt-1 text-3xl font-extrabold tabular-nums tracking-tight text-amber-200 sm:text-4xl">
                                {{ rupiah(tunggakan.nilai) }}
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                                <span class="text-white/80">{{ tunggakan.sub }}</span>
                                <Link
                                    v-if="tunggakan.url"
                                    :href="tunggakan.url"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-white/20 px-3 py-1.5 font-semibold text-white transition hover:bg-white/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-200"
                                >
                                    <span>Buka Rincian Tagihan</span>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Donut Progress Pelunasan -->
                    <div v-if="grafik" class="flex flex-col items-center justify-center rounded-xl bg-black/15 p-6 backdrop-blur-xs lg:col-span-2">
                        <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-white/90 text-center">
                            Rasio Pelunasan SPP
                        </p>
                        <div class="relative h-36 w-36 shrink-0">
                            <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90" role="img" :aria-label="`${lunas}% tagihan lunas`">
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3.5" />
                                <circle
                                    cx="18"
                                    cy="18"
                                    r="15.9155"
                                    fill="none"
                                    stroke="#6ee7b7"
                                    stroke-width="3.5"
                                    stroke-linecap="round"
                                    :stroke-dasharray="`${lunas} ${100 - lunas}`"
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-extrabold tabular-nums text-white">{{ lunas }}%</span>
                                <span class="text-[11px] font-medium text-white/80">Lunas</span>
                            </div>
                        </div>

                        <div class="mt-4 flex w-full justify-around border-t border-white/15 pt-3 text-center text-xs">
                            <div>
                                <span class="block text-emerald-200 font-semibold">{{ grafik.lunas }}</span>
                                <span class="text-white/70 text-[11px]">Lunas</span>
                            </div>
                            <div class="h-8 w-px bg-white/20"></div>
                            <div>
                                <span class="block text-amber-200 font-semibold">{{ grafik.belum_lunas }}</span>
                                <span class="text-white/70 text-[11px]">Belum Lunas</span>
                            </div>
                            <div class="h-8 w-px bg-white/20"></div>
                            <div>
                                <span class="block text-white font-semibold">{{ grafik.total_tagihan }}</span>
                                <span class="text-white/70 text-[11px]">Total Tagihan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 2. SUMMARY CARDS DARI DATA APLIKASI AKTUAL -->
            <section>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-ink sm:text-lg">Ringkasan Sistem & Master Data</h2>
                        <p class="text-xs text-ink-muted">Metrik operasional sekolah terkini yang bersumber dari basis data.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <component
                        :is="item.url ? Link : 'div'"
                        v-for="item in kartuRingkasan"
                        :key="item.label"
                        :href="item.url"
                        class="group relative flex items-start gap-4 rounded-xl border border-brand-border bg-white p-5 shadow-xs transition-all hover:border-brand hover:shadow-panel focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                    >
                        <div :class="['flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-colors', getMetaKartu(item.label).badgeColor]">
                            <!-- Icon Siswa -->
                            <svg v-if="getMetaKartu(item.label).icon === 'siswa'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            <!-- Icon Kelas -->
                            <svg v-else-if="getMetaKartu(item.label).icon === 'kelas'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                            </svg>
                            <!-- Icon Users -->
                            <svg v-else-if="getMetaKartu(item.label).icon === 'users'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            <!-- Icon Tarif -->
                            <svg v-else-if="getMetaKartu(item.label).icon === 'tarif'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                            <!-- Icon Keringanan -->
                            <svg v-else-if="getMetaKartu(item.label).icon === 'keringanan'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                            </svg>
                            <!-- Icon Tagihan -->
                            <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-ink-muted">
                                {{ item.label }}
                            </span>
                            <span class="mt-1 block text-2xl font-bold tabular-nums text-ink">
                                {{ item.nilai }}
                            </span>
                            <span class="mt-0.5 block truncate text-xs text-ink-muted">
                                {{ item.sub }}
                            </span>
                        </div>

                        <span v-if="item.url" class="shrink-0 text-ink-muted/50 transition-transform group-hover:translate-x-0.5 group-hover:text-brand">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </component>
                </div>
            </section>

            <!-- 3. DISTRIBUSI SISWA & AKSI CEPAT ADMIN -->
            <div class="grid gap-6 lg:grid-cols-5">
                <!-- Status & Keaktifan Siswa -->
                <section v-if="grafik" class="rounded-xl border border-brand-border bg-white p-5 shadow-xs lg:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-ink">Status Siswa</h3>
                            <p class="text-xs text-ink-muted">Komposisi status seluruh siswa terdaftar</p>
                        </div>
                        <span class="rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-bold text-brand">
                            {{ totalSiswa }} Total Siswa
                        </span>
                    </div>

                    <!-- Progress Bar Multi-Segment -->
                    <div class="mb-5 flex h-3.5 overflow-hidden rounded-full bg-page p-0.5 border border-brand-border/60">
                        <div
                            class="rounded-l-full bg-status-success transition-all duration-300"
                            :style="{ width: rasioSiswa(grafik.siswa_aktif) + '%' }"
                            :title="`Aktif: ${grafik.siswa_aktif}`"
                        />
                        <div
                            class="bg-sky-500 transition-all duration-300"
                            :style="{ width: rasioSiswa(grafik.siswa_lulus) + '%' }"
                            :title="`Lulus: ${grafik.siswa_lulus}`"
                        />
                        <div
                            class="rounded-r-full bg-slate-400 transition-all duration-300"
                            :style="{ width: rasioSiswa(grafik.siswa_keluar) + '%' }"
                            :title="`Keluar/Pindah: ${grafik.siswa_keluar}`"
                        />
                    </div>

                    <dl class="space-y-3 divide-y divide-brand-border/50 text-sm">
                        <div class="flex items-center justify-between pt-2">
                            <dt class="flex items-center gap-2 text-ink">
                                <span class="h-2.5 w-2.5 rounded-full bg-status-success"></span>
                                <span>Siswa Aktif</span>
                            </dt>
                            <dd class="font-bold tabular-nums text-ink">
                                {{ grafik.siswa_aktif }}
                                <span class="font-normal text-xs text-ink-muted">({{ rasioSiswa(grafik.siswa_aktif) }}%)</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <dt class="flex items-center gap-2 text-ink">
                                <span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                                <span>Alumni / Lulus</span>
                            </dt>
                            <dd class="font-bold tabular-nums text-ink">
                                {{ grafik.siswa_lulus }}
                                <span class="font-normal text-xs text-ink-muted">({{ rasioSiswa(grafik.siswa_lulus) }}%)</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <dt class="flex items-center gap-2 text-ink">
                                <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                                <span>Pindah / Keluar</span>
                            </dt>
                            <dd class="font-bold tabular-nums text-ink">
                                {{ grafik.siswa_keluar }}
                                <span class="font-normal text-xs text-ink-muted">({{ rasioSiswa(grafik.siswa_keluar) }}%)</span>
                            </dd>
                        </div>
                    </dl>
                </section>

                <!-- Aksi Cepat Admin -->
                <section class="rounded-xl border border-brand-border bg-white p-5 shadow-xs lg:col-span-3">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-ink">Aksi Cepat Administrator</h3>
                        <p class="text-xs text-ink-muted">Pintasan praktis untuk mempercepat tugas administrasi harian.</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <Link
                            v-for="aksi in aksiCepat"
                            :key="aksi.rute"
                            :href="route(aksi.rute)"
                            class="group flex items-start gap-3 rounded-lg border border-brand-border/70 bg-page/50 p-3.5 transition-all hover:border-brand hover:bg-brand-soft/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                        >
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white border border-brand-border text-brand transition-colors group-hover:bg-brand group-hover:text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-ink group-hover:text-brand">
                                    {{ aksi.judul }}
                                </span>
                                <span class="block truncate text-xs text-ink-muted">
                                    {{ aksi.deskripsi }}
                                </span>
                            </div>
                        </Link>
                    </div>
                </section>
            </div>

            <!-- 4. TABEL AKTIVITAS TRANSAKSI TERBARU -->
            <section class="rounded-xl border border-brand-border bg-white shadow-xs overflow-hidden">
                <div class="flex flex-col gap-2 border-b border-brand-border p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-ink">Transaksi Pembayaran Terbaru</h3>
                        <p class="text-xs text-ink-muted">Catatan transaksi loket kasir terkini yang terekam di sistem.</p>
                    </div>
                    <Link
                        :href="route('admin.tagihan-spp.index')"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand hover:text-brand-hover focus-visible:outline-none"
                    >
                        <span>Lihat Semua Tagihan</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-sm">
                        <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold">No. Kuitansi</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Nama Siswa / NIS</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Petugas TU</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Waktu Transaksi</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold">Jumlah Dibayar</th>
                                <th scope="col" class="px-5 py-3 text-center font-semibold">Metode</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            <tr
                                v-for="t in transaksiTerbaru"
                                :key="t.id"
                                class="odd:bg-white even:bg-page/50 transition-colors hover:bg-brand-soft/40"
                            >
                                <td class="whitespace-nowrap px-5 py-3.5 font-medium text-brand font-mono text-xs">
                                    {{ t.nomor_kuitansi || ('#TRX-' + t.id) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span class="block font-medium text-ink">{{ t.siswa }}</span>
                                    <span class="block text-xs text-ink-muted">NIS: {{ t.nis }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-ink-muted">
                                    {{ t.petugas }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-ink-muted text-xs">
                                    {{ formatWaktu(t.waktu) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right font-bold tabular-nums text-ink">
                                    {{ rupiah(t.jumlah) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                    <span class="inline-flex rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-semibold capitalize text-brand">
                                        {{ t.metode || 'tunai' }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="!transaksiTerbaru || transaksiTerbaru.length === 0">
                                <td colspan="6" class="px-5 py-10 text-center text-ink-muted">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-brand-border mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="font-medium">Belum ada transaksi pembayaran yang tercatat.</p>
                                        <p class="text-xs text-ink-muted/80 mt-0.5">Transaksi yang diproses kasir TU akan tampil di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>