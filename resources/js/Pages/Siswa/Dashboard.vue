<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    siswa: {
        type: Object,
        default: null,
    },
    ringkasan: {
        type: Object,
        default: () => ({
            total_tagihan: 0,
            total_dibayar: 0,
            sisa_kewajiban: 0,
            count_tagihan: 0,
            count_lunas: 0,
            count_sebagian: 0,
            count_belum_lunas: 0,
            status_keseluruhan: 'belum_ada_tagihan',
        }),
    },
    tagihanBelumLunas: {
        type: Array,
        default: () => [],
    },
    transaksiTerbaru: {
        type: Array,
        default: () => [],
    },
    dispensasiAktif: {
        type: Object,
        default: null,
    },
    // Backward compatibility props
    totalKewajiban: {
        type: Number,
        default: 0,
    },
    tagihanList: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

// Nama & identitas siswa
const studentName = computed(() => {
    return props.siswa?.nama_lengkap
        || page.props.auth?.siswa?.nama_lengkap
        || page.props.auth?.user?.name
        || 'Siswa';
});

const studentClass = computed(() => {
    return props.siswa?.kelas
        || page.props.auth?.siswa?.kelas
        || '-';
});

const studentNis = computed(() => {
    return props.siswa?.nis
        || page.props.auth?.siswa?.nis
        || '-';
});

const studentNisn = computed(() => {
    return props.siswa?.nisn
        || page.props.auth?.siswa?.nisn
        || '-';
});

// Format Rupiah
const rupiah = (val) => {
    return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID');
};

// Format Tanggal Indonesia
const BULAN_NAMA = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const formatPeriodeBulan = (bulan, tahun) => {
    const b = Number(bulan);
    const namaBulan = (b >= 1 && b <= 12) ? BULAN_NAMA[b - 1] : `Bulan ${bulan}`;
    return `${namaBulan} ${tahun}`;
};

const formatTanggal = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};

const formatWaktu = (isoStr) => {
    if (!isoStr) return '-';
    try {
        const d = new Date(isoStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return isoStr;
    }
};

const tanggalHariIni = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

// Data outstanding bills (fallback to tagihanList if tagihanBelumLunas empty)
const billsPending = computed(() => {
    if (props.tagihanBelumLunas && props.tagihanBelumLunas.length > 0) {
        return props.tagihanBelumLunas;
    }
    return props.tagihanList ?? [];
});

// Perhitungan metrik
const sisaKewajibanNominal = computed(() => {
    return props.ringkasan?.sisa_kewajiban ?? props.totalKewajiban ?? 0;
});

const totalDibayarNominal = computed(() => {
    return props.ringkasan?.total_dibayar ?? 0;
});

const totalTagihanNominal = computed(() => {
    return props.ringkasan?.total_tagihan ?? 0;
});

const persentaseLunas = computed(() => {
    if (totalTagihanNominal.value <= 0) return 100;
    const pct = Math.round((totalDibayarNominal.value / totalTagihanNominal.value) * 100);
    return Math.min(100, Math.max(0, pct));
});

// Cek apakah tanggal jatuh tempo sudah lewat
const isJatuhTempoLewat = (tglStr) => {
    if (!tglStr) return false;
    const tgl = new Date(tglStr);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return tgl < today;
};

// Modal panduan pembayaran
const showPaymentGuideModal = ref(false);
</script>

<template>
    <Head title="Dashboard Siswa - Sistem Informasi SPP" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-semibold text-brand">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>
                            Portal Siswa & Wali Murid
                        </span>
                        <span v-if="studentClass !== '-'" class="rounded-full bg-page px-2 py-0.5 text-xs font-medium text-ink-muted border border-brand-border">
                            Kelas {{ studentClass }}
                        </span>
                    </div>
                    <h1 class="mt-1.5 text-xl font-bold tracking-tight text-ink sm:text-2xl">
                        Selamat Datang, {{ studentName }}
                    </h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">
                        Pantau kewajiban iuran SPP, rincian tagihan berjalan, dan riwayat lembar kuitansi resmi.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="showPaymentGuideModal = true"
                        class="inline-flex items-center gap-2 rounded-lg border border-brand-border bg-white px-3 py-2 text-xs font-semibold text-ink shadow-2xs transition hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                    >
                        <svg class="h-4 w-4 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Petunjuk Bayar
                    </button>
                    <Link
                        :href="route('siswa.tagihan-saya.index')"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Lihat Kartu SPP
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- 1. NOTIFIKASI INFORMASI PENTING -->
            <!-- A. Notifikasi jika masih ada sisa kewajiban -->
            <div
                v-if="sisaKewajibanNominal > 0"
                class="flex flex-col gap-4 rounded-xl border border-status-warning/40 bg-amber-50/60 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-status-warning/15 text-status-warning">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-ink">
                            Anda memiliki kewajiban SPP menunggu pelunasan:
                            <span class="font-bold text-status-danger tabular-nums">{{ rupiah(sisaKewajibanNominal) }}</span>
                        </h2>
                        <p class="text-xs text-ink-muted mt-0.5">
                            Segera lakukan pembayaran di Loket Kasir Tata Usaha atau transfer resmi sekolah sebelum jatuh tempo untuk kelancaran administrasi sekolah.
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <Link
                        :href="route('siswa.tagihan-saya.index')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-xs font-semibold text-brand shadow-2xs hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                    >
                        Rincian Tagihan &rarr;
                    </Link>
                </div>
            </div>

            <!-- B. Notifikasi jika semua tagihan lunas -->
            <div
                v-else-if="ringkasan?.count_tagihan > 0 && sisaKewajibanNominal === 0"
                class="flex items-center gap-3 rounded-xl border border-status-success/30 bg-emerald-50/60 p-4"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-status-success/15 text-status-success">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-semibold text-ink">
                        Seluruh Tagihan SPP Anda Telah Lunas
                    </h2>
                    <p class="text-xs text-ink-muted mt-0.5">
                        Alhamdulillah, seluruh kewajiban SPP periode berjalan telah diselesaikan. Terima kasih atas ketertiban administrasi Anda.
                    </p>
                </div>
            </div>

            <!-- C. Notifikasi jika ada pengajuan dispensasi aktif -->
            <div
                v-if="dispensasiAktif"
                class="flex items-center gap-3 rounded-xl border border-brand-border bg-brand-soft/70 p-4"
            >
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-brand">Permohonan Dispensasi</span>
                        <span
                            :class="{
                                'bg-status-success/15 text-status-success': dispensasiAktif.status === 'disetujui',
                                'bg-status-warning/15 text-status-warning': dispensasiAktif.status === 'menunggu_persetujuan',
                                'bg-status-danger/15 text-status-danger': dispensasiAktif.status === 'ditolak',
                            }"
                            class="rounded-full px-2 py-0.5 text-[11px] font-semibold capitalize"
                        >
                            {{ dispensasiAktif.status.replace('_', ' ') }}
                        </span>
                    </div>
                    <p class="text-xs text-ink mt-0.5">
                        Pengajuan penundaan untuk <strong>{{ dispensasiAktif.periode }}</strong>
                        <span v-if="dispensasiAktif.batas_waktu_baru">
                            dengan usulan jatuh tempo baru <strong>{{ formatTanggal(dispensasiAktif.batas_waktu_baru) }}</strong>
                        </span>.
                    </p>
                </div>
                <Link
                    :href="route('siswa.dispensasi-saya.index')"
                    class="shrink-0 text-xs font-semibold text-brand hover:underline"
                >
                    Lihat Progres &rarr;
                </Link>
            </div>

            <!-- 2. SUMMARY METRIC CARDS (4 KARTU BERSIH) -->
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan Keuangan SPP">
                <!-- KARTU 1: SISA KEWAJIBAN -->
                <div class="ui-panel p-5 transition-shadow hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Sisa Kewajiban</span>
                        <span
                            :class="sisaKewajibanNominal > 0 ? 'bg-status-danger/15 text-status-danger' : 'bg-status-success/15 text-status-success'"
                            class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                        >
                            {{ sisaKewajibanNominal > 0 ? 'Perlu Dilunasi' : 'Bebas Tunggakan' }}
                        </span>
                    </div>
                    <div class="mt-3 text-2xl font-bold tabular-nums" :class="sisaKewajibanNominal > 0 ? 'text-status-danger' : 'text-status-success'">
                        {{ rupiah(sisaKewajibanNominal) }}
                    </div>
                    <p class="mt-1 text-xs text-ink-muted">
                        {{ ringkasan?.count_belum_lunas || billsPending.length }} periode tagihan belum lunas
                    </p>
                </div>

                <!-- KARTU 2: TOTAL DIBAYAR -->
                <div class="ui-panel p-5 transition-shadow hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Total Telah Dibayar</span>
                        <span class="inline-flex rounded-full bg-status-success/15 px-2 py-0.5 text-xs font-semibold text-status-success">
                            {{ persentaseLunas }}% Terpenuhi
                        </span>
                    </div>
                    <div class="mt-3 text-2xl font-bold tabular-nums text-ink">
                        {{ rupiah(totalDibayarNominal) }}
                    </div>
                    <div class="mt-2 flex items-center gap-2">
                        <div class="h-1.5 flex-1 rounded-full bg-brand-soft overflow-hidden">
                            <div class="h-full rounded-full bg-brand" :style="{ width: persentaseLunas + '%' }"></div>
                        </div>
                        <span class="text-[11px] font-semibold text-brand tabular-nums">{{ persentaseLunas }}%</span>
                    </div>
                </div>

                <!-- KARTU 3: TOTAL TAGIHAN DITERBITKAN -->
                <div class="ui-panel p-5 transition-shadow hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Total Tagihan SPP</span>
                        <span class="inline-flex rounded-full bg-brand-soft px-2 py-0.5 text-xs font-semibold text-brand">
                            {{ ringkasan?.count_tagihan || 0 }} Periode
                        </span>
                    </div>
                    <div class="mt-3 text-2xl font-bold tabular-nums text-ink">
                        {{ rupiah(totalTagihanNominal) }}
                    </div>
                    <p class="mt-1 text-xs text-ink-muted">
                        Akumulasi tagihan resmi yang diterbitkan
                    </p>
                </div>

                <!-- KARTU 4: STATUS PEMBAYARAN -->
                <div class="ui-panel p-5 transition-shadow hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Status Pembayaran</span>
                        <svg class="h-4 w-4 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="mt-3">
                        <span
                            v-if="sisaKewajibanNominal === 0 && (ringkasan?.count_tagihan > 0)"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-status-success/15 px-3 py-1 text-sm font-bold text-status-success"
                        >
                            <span class="h-2 w-2 rounded-full bg-status-success"></span>
                            Lunas Penuh
                        </span>
                        <span
                            v-else-if="totalDibayarNominal > 0 && sisaKewajibanNominal > 0"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-status-warning/15 px-3 py-1 text-sm font-bold text-status-warning"
                        >
                            <span class="h-2 w-2 rounded-full bg-status-warning"></span>
                            Sebagian Terbayar
                        </span>
                        <span
                            v-else-if="ringkasan?.count_tagihan > 0"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-status-danger/15 px-3 py-1 text-sm font-bold text-status-danger"
                        >
                            <span class="h-2 w-2 rounded-full bg-status-danger"></span>
                            Belum Bayar
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 rounded-lg bg-page px-3 py-1 text-sm font-bold text-ink-muted border border-brand-border"
                        >
                            Belum Ada Tagihan
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-ink-muted">
                        {{ ringkasan?.count_lunas || 0 }} lunas, {{ ringkasan?.count_belum_lunas || 0 }} menunggu
                    </p>
                </div>
            </section>

            <!-- 3. DUA KOLOM: RINCIAN TAGIHAN BERJALAN & RIWAYAT TRANSAKSI -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- KOLOM KIRI: DAFTAR TAGIHAN BELUM LUNAS (7/12) -->
                <section class="ui-panel p-5 sm:p-6 lg:col-span-7" aria-labelledby="tagihan-heading">
                    <div class="flex items-center justify-between border-b border-brand-border pb-4">
                        <div>
                            <h2 id="tagihan-heading" class="text-base font-bold text-ink">
                                Tagihan SPP Menunggu Pembayaran
                            </h2>
                            <p class="text-xs text-ink-muted mt-0.5">
                                Daftar tagihan berjalan yang belum dilunasi.
                            </p>
                        </div>
                        <Link
                            :href="route('siswa.tagihan-saya.index')"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:underline"
                        >
                            <span>Semua Tagihan</span>
                            <span aria-hidden="true">&rarr;</span>
                        </Link>
                    </div>

                    <!-- EMPTY STATE: JIKA TIDAK ADA TAGIHAN PENDING -->
                    <div
                        v-if="billsPending.length === 0"
                        class="flex flex-col items-center justify-center py-12 text-center"
                    >
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-status-success/10 text-status-success">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-ink">
                            Tidak Ada Tunggakan SPP
                        </h3>
                        <p class="mt-1 max-w-sm text-xs text-ink-muted">
                            Semua tagihan SPP Anda saat ini telah diselesaikan dengan tertib. Tagihan baru akan muncul saat periode berikutnya diterbitkan.
                        </p>
                        <Link
                            :href="route('siswa.tagihan-saya.index')"
                            class="mt-4 inline-flex items-center gap-1.5 rounded-lg border border-brand-border bg-white px-3.5 py-2 text-xs font-semibold text-ink shadow-2xs hover:bg-brand-soft"
                        >
                            Lihat Arsip Riwayat Tagihan
                        </Link>
                    </div>

                    <!-- DAFTAR KARTU TAGIHAN -->
                    <div v-else class="mt-4 divide-y divide-brand-border">
                        <div
                            v-for="item in billsPending"
                            :key="item.id"
                            class="py-4 first:pt-0 last:pb-0"
                        >
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-bold text-ink">
                                            SPP {{ formatPeriodeBulan(item.bulan, item.tahun) }}
                                        </span>
                                        <!-- Status badge -->
                                        <span
                                            :class="{
                                                'bg-status-danger/15 text-status-danger': item.status === 'belum_bayar',
                                                'bg-status-warning/15 text-status-warning': item.status === 'sebagian',
                                                'bg-status-success/15 text-status-success': item.status === 'lunas',
                                            }"
                                            class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                        >
                                            {{ item.status === 'belum_bayar' ? 'Belum Bayar' : (item.status === 'sebagian' ? 'Dicicil Sebagian' : 'Lunas') }}
                                        </span>

                                        <!-- Badge jika lewat jatuh tempo -->
                                        <span
                                            v-if="isJatuhTempoLewat(item.jatuh_tempo)"
                                            class="inline-flex rounded-full bg-status-danger/15 px-2 py-0.5 text-[11px] font-semibold text-status-danger"
                                        >
                                            Lewat Jatuh Tempo
                                        </span>
                                    </div>

                                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-muted">
                                        <span>Jatuh Tempo: <strong>{{ formatTanggal(item.jatuh_tempo) }}</strong></span>
                                        <span v-if="item.nominal_potongan > 0" class="text-brand font-medium">
                                            Keringanan: {{ rupiah(item.nominal_potongan) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Nominal Sisa Tagihan & Tombol -->
                                <div class="flex items-center justify-between sm:flex-col sm:items-end gap-1">
                                    <div class="text-left sm:text-right">
                                        <span class="block text-[11px] uppercase tracking-wider text-ink-muted">Sisa Bayar</span>
                                        <span class="text-base font-bold tabular-nums text-status-danger">
                                            {{ rupiah(item.sisa_tagihan) }}
                                        </span>
                                    </div>
                                    <Link
                                        :href="route('siswa.tagihan-saya.index')"
                                        class="inline-flex items-center gap-1 rounded-md border border-brand-border bg-page px-2.5 py-1 text-xs font-semibold text-ink hover:bg-brand-soft"
                                    >
                                        Rincian &rarr;
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KOLOM KANAN: RIWAYAT PEMBAYARAN TERBARU (5/12) -->
                <section class="ui-panel p-5 sm:p-6 lg:col-span-5" aria-labelledby="riwayat-heading">
                    <div class="flex items-center justify-between border-b border-brand-border pb-4">
                        <div>
                            <h2 id="riwayat-heading" class="text-base font-bold text-ink">
                                Riwayat Pembayaran
                            </h2>
                            <p class="text-xs text-ink-muted mt-0.5">
                                Kuitansi transaksi sah yang telah tercatat.
                            </p>
                        </div>
                        <span class="text-xs font-semibold text-brand">
                            {{ transaksiTerbaru.length }} Terbaru
                        </span>
                    </div>

                    <!-- EMPTY STATE -->
                    <div
                        v-if="transaksiTerbaru.length === 0"
                        class="flex flex-col items-center justify-center py-10 text-center"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft text-ink-muted">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="mt-3 text-xs text-ink-muted max-w-xs">
                            Belum ada catatan transaksi pembayaran yang tersimpan di sistem.
                        </p>
                    </div>

                    <!-- DAFTAR TRANSAKSI -->
                    <div v-else class="mt-4 space-y-3">
                        <div
                            v-for="trx in transaksiTerbaru"
                            :key="trx.id"
                            class="rounded-lg border border-brand-border bg-page p-3.5 transition hover:bg-brand-soft/50"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-brand">
                                            {{ trx.nomor_kuitansi }}
                                        </span>
                                        <span class="rounded bg-brand-soft px-1.5 py-0.5 text-[10px] font-semibold uppercase text-brand">
                                            {{ trx.metode }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-xs text-ink">
                                        Periode: <strong>SPP {{ formatPeriodeBulan(trx.bulan, trx.tahun) }}</strong>
                                    </div>
                                    <div class="mt-0.5 text-[11px] text-ink-muted">
                                        {{ formatWaktu(trx.waktu_transaksi) }} &bull; Kasir: {{ trx.petugas }}
                                    </div>
                                </div>

                                <div class="text-right">
                                    <span class="block text-sm font-bold tabular-nums text-status-success">
                                        {{ rupiah(trx.jumlah_dibayar) }}
                                    </span>
                                    <a
                                        :href="route('cetak-kuitansi.kuitansi', trx.id)"
                                        target="_blank"
                                        rel="noopener"
                                        class="mt-1.5 inline-flex items-center gap-1 rounded border border-brand-border bg-white px-2 py-0.5 text-[11px] font-semibold text-ink shadow-2xs hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                                        title="Buka / Cetak Lembar Kuitansi Resmi"
                                    >
                                        <svg class="h-3 w-3 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        <span>Kuitansi</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- 4. PANDUAN CARA PEMBAYARAN SPP (INFO RESMI) -->
            <section class="ui-panel p-5 sm:p-6" aria-labelledby="panduan-heading">
                <div class="flex items-center gap-2 border-b border-brand-border pb-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-soft text-brand">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="panduan-heading" class="text-sm font-bold text-ink">
                            Petunjuk Prosedur Pembayaran SPP Sekolah
                        </h2>
                        <p class="text-xs text-ink-muted">
                            Tata cara resmi pelunasan iuran SPP siswa di lingkungan sekolah.
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Langkah 1 -->
                    <div class="rounded-lg border border-brand-border bg-page p-4">
                        <div class="flex items-center gap-2 font-bold text-sm text-ink">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand text-xs font-bold text-white">1</span>
                            <span>Loket Kasir Tata Usaha</span>
                        </div>
                        <p class="mt-2 text-xs text-ink-muted leading-relaxed">
                            Datang langsung ke Loket Pembayaran TU pada hari kerja (Senin - Jumat, pukul 07.30 - 15.00 WIB). Sebutkan <strong>NIS ({{ studentNis }})</strong> atau nama lengkap siswa kepada kasir.
                        </p>
                    </div>

                    <!-- Langkah 2 -->
                    <div class="rounded-lg border border-brand-border bg-page p-4">
                        <div class="flex items-center gap-2 font-bold text-sm text-ink">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand text-xs font-bold text-white">2</span>
                            <span>Verifikasi & Pelunasan</span>
                        </div>
                        <p class="mt-2 text-xs text-ink-muted leading-relaxed">
                            Kasir memeriksa rincian tagihan bulan berjalan dan menerima pembayaran tunai atau verifikasi transfer bank sekolah. Pembayaran dapat dilakukan penuh atau dicicil sebagian.
                        </p>
                    </div>

                    <!-- Langkah 3 -->
                    <div class="rounded-lg border border-brand-border bg-page p-4">
                        <div class="flex items-center gap-2 font-bold text-sm text-ink">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand text-xs font-bold text-white">3</span>
                            <span>Terima Kuitansi Resmi</span>
                        </div>
                        <p class="mt-2 text-xs text-ink-muted leading-relaxed">
                            Pastikan Anda menerima lembar cetak kuitansi resmi bertanda cap sah dan nomor transaksi dari kasir loket. Lembar kuitansi juga tersimpan otomatis di akun portal ini.
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <!-- MODAL PETUNJUK PEMBAYARAN LENGKAP -->
        <div
            v-if="showPaymentGuideModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 p-4 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-guide-title"
        >
            <div class="w-full max-w-lg rounded-2xl border border-brand-border bg-white p-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-brand-border pb-3">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-soft text-brand">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 id="modal-guide-title" class="text-base font-bold text-ink">
                            Petunjuk Pembayaran SPP
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="showPaymentGuideModal = false"
                        class="rounded-lg p-1.5 text-ink-muted hover:bg-page hover:text-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                        aria-label="Tutup petunjuk"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4 text-xs text-ink-muted">
                    <div class="rounded-lg bg-brand-soft/70 p-3 text-ink">
                        <p class="font-semibold text-brand">Data Identitas Pembayaran Anda:</p>
                        <p class="mt-1">Nama: <strong>{{ studentName }}</strong> &bull; NIS: <strong>{{ studentNis }}</strong> &bull; Kelas: <strong>{{ studentClass }}</strong></p>
                    </div>

                    <div>
                        <h4 class="font-bold text-ink">1. Pembayaran Tunai (Loket TU)</h4>
                        <p class="mt-1">
                            Layanan loket dibuka setiap hari kerja Senin hingga Jumat pukul 07.30 s/d 15.00 WIB di Ruang Tata Usaha Sekolah. Sebutkan NIS Anda saat bertransaksi.
                        </p>
                    </div>

                    <div>
                        <h4 class="font-bold text-ink">2. Pembayaran Transfer Bank</h4>
                        <p class="mt-1">
                            Bagi pembayaran via transfer bank sekolah, simpan bukti transfer lalu serahkan atau konfirmasikan ke Staf TU untuk pencatatan dan pencetakan kuitansi resmi.
                        </p>
                    </div>

                    <div>
                        <h4 class="font-bold text-ink">3. Kendala Biaya / Dispensasi</h4>
                        <p class="mt-1">
                            Jika mengalami kendala pelunasan sebelum batas jatuh tempo, wali murid dapat mengajukan permohonan dispensasi penundaan waktu melalui formulir pengajuan di bagian Tata Usaha.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        @click="showPaymentGuideModal = false"
                        class="rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                    >
                        Tutup Petunjuk
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>