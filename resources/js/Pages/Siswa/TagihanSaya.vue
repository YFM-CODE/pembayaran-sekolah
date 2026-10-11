<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    tagihan: {
        type: Array,
        default: () => [],
    },
    siswa: {
        type: Object,
        default: null,
    },
});

const page = usePage();

// Identitas Siswa
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

// Format Rupiah
const rupiah = (val) => {
    return 'Rp ' + Number(val ?? 0).toLocaleString('id-ID');
};

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

const getStatusValue = (item) => {
    const raw = item?.status;
    return typeof raw === 'object' && raw !== null ? raw.value : (raw || '');
};

const isJatuhTempoLewat = (tglStr, status) => {
    const st = typeof status === 'object' && status !== null ? status.value : status;
    if (!tglStr || st === 'lunas') return false;
    const tgl = new Date(tglStr);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return tgl < today;
};

// Filter Tab
const activeTab = ref('semua'); // 'semua' | 'belum_lunas' | 'lunas'
const searchQuery = ref('');

// Total Ringkasan
const totalTagihan = computed(() => {
    return props.tagihan.reduce((acc, item) => acc + Number(item.nominal_tagihan ?? item.total_harus_bayar ?? 0), 0);
});

const totalTerbayar = computed(() => {
    return props.tagihan.reduce((acc, item) => acc + Number(item.total_terbayar ?? 0), 0);
});

const totalSisaKewajiban = computed(() => {
    return props.tagihan.reduce((acc, item) => acc + Number(item.sisa_tagihan ?? 0), 0);
});

const countLunas = computed(() => {
    return props.tagihan.filter((item) => getStatusValue(item) === 'lunas').length;
});

const countBelumLunas = computed(() => {
    return props.tagihan.filter((item) => getStatusValue(item) !== 'lunas').length;
});

// Daftar tagihan yang difilter
const tagihanFiltered = computed(() => {
    let list = [...props.tagihan];

    // Filter status tab
    if (activeTab.value === 'belum_lunas') {
        list = list.filter((item) => getStatusValue(item) !== 'lunas');
    } else if (activeTab.value === 'lunas') {
        list = list.filter((item) => getStatusValue(item) === 'lunas');
    }

    // Filter pencarian (bulan/tahun)
    if (searchQuery.value.trim() !== '') {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter((item) => {
            const periodeStr = formatPeriodeBulan(item.bulan, item.tahun).toLowerCase();
            const tahunStr = String(item.tahun);
            return periodeStr.includes(q) || tahunStr.includes(q);
        });
    }

    return list;
});
</script>

<template>
    <Head title="Kartu Tagihan SPP Saya - Portal Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-semibold text-brand">
                            Kartu SPP Siswa
                        </span>
                        <span v-if="studentClass !== '-'" class="rounded-full bg-page px-2 py-0.5 text-xs font-medium text-ink-muted border border-brand-border">
                            {{ studentClass }}
                        </span>
                        <span v-if="studentNis !== '-'" class="rounded-full bg-page px-2 py-0.5 text-xs font-medium text-ink-muted border border-brand-border">
                            NIS: {{ studentNis }}
                        </span>
                    </div>
                    <h1 class="mt-1.5 text-xl font-bold tracking-tight text-ink sm:text-2xl">
                        Kartu SPP & Riwayat Pembayaran
                    </h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">
                        Daftar lengkap tagihan SPP bulanan, histori potongan/keringanan, dan kuitansi transaksi pembayaran sah.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('siswa.dashboard')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-brand-border bg-white px-3 py-2 text-xs font-semibold text-ink shadow-2xs hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                    >
                        &larr; Kembali ke Dashboard
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- 1. SUMMARY STATS BAR -->
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3" aria-label="Ringkasan Kartu SPP">
                <div class="ui-panel p-4 sm:p-5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Total Tagihan Diterbitkan</span>
                    <div class="mt-2 text-xl font-bold tabular-nums text-ink sm:text-2xl">
                        {{ rupiah(totalTagihan) }}
                    </div>
                    <p class="mt-1 text-xs text-ink-muted">
                        Dari {{ props.tagihan.length }} periode tagihan resmi
                    </p>
                </div>

                <div class="ui-panel p-4 sm:p-5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Total Telah Terbayar</span>
                    <div class="mt-2 text-xl font-bold tabular-nums text-status-success sm:text-2xl">
                        {{ rupiah(totalTerbayar) }}
                    </div>
                    <p class="mt-1 text-xs text-ink-muted">
                        {{ countLunas }} tagihan lunas penuh
                    </p>
                </div>

                <div class="ui-panel p-4 sm:p-5">
                    <span class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Sisa Kewajiban Belum Lunas</span>
                    <div class="mt-2 text-xl font-bold tabular-nums sm:text-2xl" :class="totalSisaKewajiban > 0 ? 'text-status-danger' : 'text-status-success'">
                        {{ rupiah(totalSisaKewajiban) }}
                    </div>
                    <p class="mt-1 text-xs text-ink-muted">
                        {{ countBelumLunas }} tagihan menunggu pelunasan
                    </p>
                </div>
            </section>

            <!-- 2. FILTER & PENCARIAN -->
            <div class="ui-panel p-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Tabs -->
                    <div class="flex flex-wrap items-center gap-1.5 border-b border-brand-border/60 pb-2 sm:border-b-0 sm:pb-0" role="tablist">
                        <button
                            type="button"
                            @click="activeTab = 'semua'"
                            :class="activeTab === 'semua'
                                ? 'bg-brand text-white shadow-xs font-semibold'
                                : 'text-ink-muted hover:bg-page hover:text-ink font-medium'"
                            class="rounded-lg px-3 py-1.5 text-xs transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                        >
                            Semua ({{ props.tagihan.length }})
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'belum_lunas'"
                            :class="activeTab === 'belum_lunas'
                                ? 'bg-status-danger text-white shadow-xs font-semibold'
                                : 'text-ink-muted hover:bg-page hover:text-ink font-medium'"
                            class="rounded-lg px-3 py-1.5 text-xs transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                        >
                            Belum Lunas ({{ countBelumLunas }})
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'lunas'"
                            :class="activeTab === 'lunas'
                                ? 'bg-status-success text-white shadow-xs font-semibold'
                                : 'text-ink-muted hover:bg-page hover:text-ink font-medium'"
                            class="rounded-lg px-3 py-1.5 text-xs transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                        >
                            Lunas ({{ countLunas }})
                        </button>
                    </div>

                    <!-- Input Pencarian -->
                    <div class="relative sm:w-64">
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Cari bulan atau tahun..."
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-1.5 text-xs text-ink placeholder:text-ink-muted/80 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            @click="searchQuery = ''"
                            class="absolute right-2.5 top-2 text-ink-muted hover:text-ink text-xs"
                            title="Hapus pencarian"
                        >
                            &times;
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. DAFTAR KARTU TAGIHAN SPP -->
            <!-- EMPTY STATE: JIKA TIDAK ADA DATA SAMA SEKALI -->
            <div
                v-if="props.tagihan.length === 0"
                class="ui-panel p-12 text-center"
            >
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-soft text-brand">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-bold text-ink">
                    Belum Ada Tagihan SPP Diterbitkan
                </h3>
                <p class="mx-auto mt-1.5 max-w-md text-xs text-ink-muted">
                    Saat ini belum ada tagihan iuran SPP yang diterbitkan untuk akun siswa Anda. Tagihan akan otomatis tampil begitu diterbitkan oleh staf sekolah.
                </p>
                <div class="mt-4 text-xs font-semibold text-brand">
                    Jika Anda merasa data belum sesuai, silakan hubungi bagian Tata Usaha sekolah.
                </div>
            </div>

            <!-- EMPTY STATE: JIKA HASIL FILTER KOSONG -->
            <div
                v-else-if="tagihanFiltered.length === 0"
                class="ui-panel p-10 text-center"
            >
                <p class="text-sm font-semibold text-ink">
                    Tidak ada tagihan yang cocok dengan filter "{{ activeTab === 'belum_lunas' ? 'Belum Lunas' : (activeTab === 'lunas' ? 'Lunas' : 'Semua') }}"
                    <span v-if="searchQuery">dan kata kunci "{{ searchQuery }}"</span>.
                </p>
                <button
                    type="button"
                    @click="activeTab = 'semua'; searchQuery = '';"
                    class="mt-3 inline-flex items-center gap-1 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-xs font-semibold text-brand shadow-2xs hover:bg-brand-soft"
                >
                    Reset Filter
                </button>
            </div>

            <!-- LIST KARTU TAGIHAN -->
            <div v-else class="space-y-4">
                <article
                    v-for="item in tagihanFiltered"
                    :key="item.id"
                    class="ui-panel overflow-hidden transition-shadow hover:shadow-md"
                >
                    <!-- Header Kartu -->
                    <div class="flex flex-col gap-2 border-b border-brand-border bg-page/70 px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="text-base font-bold text-ink">
                                SPP {{ formatPeriodeBulan(item.bulan, item.tahun) }}
                            </span>

                            <!-- Status Badge Konsisten -->
                            <!-- Lunas: Hijau -->
                            <span
                                v-if="getStatusValue(item) === 'lunas'"
                                class="inline-flex items-center gap-1 rounded-full bg-status-success/15 px-2.5 py-0.5 text-xs font-bold text-status-success"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-status-success"></span>
                                Lunas
                            </span>
                            <!-- Sebagian: Kuning -->
                            <span
                                v-else-if="['sebagian', 'belum_lunas'].includes(getStatusValue(item)) && item.total_terbayar > 0"
                                class="inline-flex items-center gap-1 rounded-full bg-status-warning/15 px-2.5 py-0.5 text-xs font-bold text-status-warning"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-status-warning"></span>
                                Dicicil Sebagian
                            </span>
                            <!-- Belum Bayar: Merah -->
                            <span
                                v-else
                                class="inline-flex items-center gap-1 rounded-full bg-status-danger/15 px-2.5 py-0.5 text-xs font-bold text-status-danger"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-status-danger"></span>
                                Belum Bayar
                            </span>

                            <!-- Potongan / Keringanan badge -->
                            <span
                                v-if="(item.nominal_potongan && item.nominal_potongan > 0) || (item.potongan && item.potongan > 0)"
                                class="inline-flex items-center rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-semibold text-brand"
                            >
                                Diskon/Keringanan: {{ rupiah(item.nominal_potongan || item.potongan) }}
                            </span>

                            <!-- Overdue badge -->
                            <span
                                v-if="isJatuhTempoLewat(item.jatuh_tempo, item.status)"
                                class="inline-flex rounded-full bg-status-danger/15 px-2 py-0.5 text-[11px] font-semibold text-status-danger"
                            >
                                Lewat Jatuh Tempo
                            </span>
                        </div>

                        <div class="text-xs text-ink-muted">
                            Jatuh Tempo: <strong>{{ formatTanggal(item.jatuh_tempo) }}</strong>
                        </div>
                    </div>

                    <!-- Body Kartu: Grid Rincian Nominal -->
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <!-- Nominal Asli -->
                            <div>
                                <span class="block text-[11px] uppercase tracking-wider text-ink-muted font-medium">Nominal Asli</span>
                                <span class="mt-1 block text-sm font-semibold tabular-nums text-ink">
                                    {{ rupiah(item.nominal_asli || item.nominal_tagihan) }}
                                </span>
                                <span v-if="item.tarif?.keterangan" class="text-[11px] text-ink-muted truncate block">
                                    {{ item.tarif.keterangan }}
                                </span>
                            </div>

                            <!-- Potongan / Keringanan -->
                            <div>
                                <span class="block text-[11px] uppercase tracking-wider text-ink-muted font-medium">Potongan / Beasiswa</span>
                                <span class="mt-1 block text-sm font-semibold tabular-nums text-brand">
                                    {{ (item.nominal_potongan > 0 || item.potongan > 0) ? '- ' + rupiah(item.nominal_potongan || item.potongan) : 'Rp 0' }}
                                </span>
                                <span v-if="item.keringanan?.nama_program" class="text-[11px] text-brand font-medium truncate block">
                                    {{ item.keringanan.nama_program }}
                                </span>
                            </div>

                            <!-- Total Wajib Bayar -->
                            <div>
                                <span class="block text-[11px] uppercase tracking-wider text-ink-muted font-medium">Total Tagihan</span>
                                <span class="mt-1 block text-sm font-bold tabular-nums text-ink">
                                    {{ rupiah(item.nominal_tagihan ?? item.total_harus_bayar) }}
                                </span>
                                <span class="text-[11px] text-ink-muted block">
                                    Terbayar: {{ rupiah(item.total_terbayar) }}
                                </span>
                            </div>

                            <!-- Sisa Kewajiban -->
                            <div class="border-t border-brand-border pt-2 sm:border-t-0 sm:pt-0 sm:border-l sm:border-brand-border sm:pl-4">
                                <span class="block text-[11px] uppercase tracking-wider text-ink-muted font-medium">Sisa Tagihan</span>
                                <span
                                    class="mt-1 block text-lg font-bold tabular-nums"
                                    :class="item.sisa_tagihan > 0 ? 'text-status-danger' : 'text-status-success'"
                                >
                                    {{ rupiah(item.sisa_tagihan) }}
                                </span>
                                <span
                                    class="text-[11px] font-semibold"
                                    :class="item.sisa_tagihan > 0 ? 'text-status-danger' : 'text-status-success'"
                                >
                                    {{ item.sisa_tagihan > 0 ? 'Perlu Dilunasi' : 'Lunas Terverifikasi' }}
                                </span>
                            </div>
                        </div>

                        <!-- Info Dispensasi Jika Ada -->
                        <div
                            v-if="item.dispensasi && item.dispensasi.length > 0"
                            class="mt-4 rounded-lg bg-amber-50/70 border border-status-warning/30 p-3 text-xs"
                        >
                            <div class="flex items-center gap-2 font-semibold text-status-warning">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Catatan Dispensasi Pembayaran</span>
                            </div>
                            <div
                                v-for="disp in item.dispensasi"
                                :key="disp.id"
                                class="mt-1 text-ink-muted"
                            >
                                Status: <strong class="capitalize text-ink">{{ (disp.status || '').replace('_', ' ') }}</strong>
                                <span v-if="disp.batas_waktu_baru"> &bull; Jatuh tempo baru: <strong>{{ formatTanggal(disp.batas_waktu_baru) }}</strong></span>
                                <span v-if="disp.alasan_penundaan"> &bull; Alasan: "{{ disp.alasan_penundaan }}"</span>
                            </div>
                        </div>

                        <!-- Riwayat Pembayaran & Kuitansi -->
                        <div class="mt-5 border-t border-brand-border pt-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-xs font-semibold text-ink">
                                    Riwayat Transaksi & Kuitansi Sah:
                                </span>

                                <!-- Jika ada transaksi -->
                                <div v-if="item.transaksi && item.transaksi.length > 0" class="flex flex-wrap items-center gap-2">
                                    <div
                                        v-for="trx in item.transaksi"
                                        :key="trx.id"
                                        class="flex items-center gap-1.5 rounded-lg border border-brand-border bg-page px-2.5 py-1.5 text-xs"
                                    >
                                        <span class="font-mono font-bold text-brand">
                                            {{ trx.nomor_kuitansi || ('#TRX-' + trx.id) }}
                                        </span>
                                        <span class="text-ink-muted">
                                            ({{ rupiah(trx.jumlah_dibayar) }})
                                        </span>
                                        <span class="text-brand-border">|</span>
                                        <a
                                            :href="route('cetak-kuitansi.kuitansi', trx.id)"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex items-center gap-1 font-semibold text-brand hover:underline"
                                            title="Cetak Kuitansi Resmi"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            <span>Kuitansi</span>
                                        </a>
                                        <a
                                            :href="route('cetak-kuitansi.thermal', trx.id)"
                                            target="_blank"
                                            rel="noopener"
                                            class="inline-flex items-center gap-1 font-semibold text-ink-muted hover:text-ink"
                                            title="Cetak Struk Thermal"
                                        >
                                            <span>Thermal</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Jika belum ada transaksi -->
                                <div v-else class="text-xs text-ink-muted">
                                    Belum ada transaksi pembayaran untuk periode ini.
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <!-- 4. KOTAK INFORMASI CARA BAYAR DI LOKET TU -->
            <div class="rounded-xl border border-brand-border bg-white p-5 shadow-2xs">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-soft text-brand">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-ink">Informasi Pembayaran di Loket TU</h2>
                        <p class="mt-1 text-xs text-ink-muted leading-relaxed">
                            Pembayaran SPP dapat diselesaikan langsung di Loket Tata Usaha sekolah (Senin - Jumat, pukul 07.30 - 15.00 WIB) dengan menyebutkan NIS (<strong>{{ studentNis }}</strong>).
                            Setelah transaksi berhasil diproses oleh kasir loket, lembar bukti kuitansi resmi akan langsung dicetak dan otomatis terdata pada kartu di atas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>