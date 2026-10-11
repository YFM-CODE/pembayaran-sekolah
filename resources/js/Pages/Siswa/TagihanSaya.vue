<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tagihan: Array,
});

// Helper Format Rupiah
const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);

// Helper Format Tanggal Indonesia (dd MMMM yyyy)
const formatTanggal = (dateStr) => {
    if (!dateStr) return '-';
    const cleanDate = typeof dateStr === 'string' ? dateStr.split('T')[0] : dateStr;
    const parts = cleanDate.split('-');
    if (parts.length === 3) {
        const [year, month, day] = parts;
        const bulanIndo = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        return `${parseInt(day, 10)} ${bulanIndo[parseInt(month, 10) - 1]} ${year}`;
    }
    return cleanDate;
};

// Helper Nama Periode Bulan
const getNamaBulan = (bulanAngka, tahun) => {
    const bulanIndo = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const nama = bulanIndo[parseInt(bulanAngka, 10) - 1] || `Bulan ${bulanAngka}`;
    return `${nama} ${tahun}`;
};

// Helper Nilai Status String Murni
const getStatusValue = (item) => {
    const raw = item?.status;
    return typeof raw === 'object' && raw !== null ? raw.value : (raw || '');
};
</script>

<template>
    <Head title="Kartu Tagihan SPP Saya" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-900">Kartu SPP & Riwayat Pembayaran</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar kewajiban SPP per bulan beserta bukti kuitansi transaksi</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="space-y-4">
                    <div
                        v-for="item in props.tagihan"
                        :key="item.id"
                        class="overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:shadow-md sm:p-6"
                    >
                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                            <!-- Bagian Kiri: Periode & Detail Waktu -->
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2.5">
                                    <h3 class="text-base font-bold text-gray-900">
                                        {{ getNamaBulan(item.bulan, item.tahun) }}
                                    </h3>

                                    <!-- Badge Status Komprehensif -->
                                    <span
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': getStatusValue(item) === 'lunas',
                                            'bg-amber-50 text-amber-700 border-amber-200': getStatusValue(item) === 'sebagian',
                                            'bg-rose-50 text-rose-700 border-rose-200': ['belum_lunas', 'belum_bayar', 'menunggu_pembayaran'].includes(getStatusValue(item))
                                        }"
                                        class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider"
                                    >
                                        {{ getStatusValue(item)?.replace('_', ' ') || 'BELUM BAYAR' }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <span>📅 Jatuh Tempo:</span>
                                        <span class="font-medium text-gray-700">{{ formatTanggal(item.jatuh_tempo) }}</span>
                                    </div>
                                    <div v-if="item.nominal_tagihan" class="text-gray-400">•</div>
                                    <div v-if="item.nominal_tagihan" class="text-gray-500">
                                        Nominal: <span class="font-medium text-gray-700">{{ formatRupiah(item.nominal_tagihan) }}</span>
                                    </div>
                                </div>

                                <div v-if="item.potongan > 0" class="inline-flex items-center text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">
                                    🎉 Diskon / Keringanan: {{ formatRupiah(item.potongan) }}
                                </div>
                            </div>

                            <!-- Bagian Kanan: Sisa Tagihan & Tombol Kuitansi -->
                            <div class="flex flex-col items-start border-t border-gray-100 pt-3 sm:items-end sm:border-0 sm:pt-0">
                                <span class="text-xs font-medium text-gray-500">Sisa Tagihan</span>
                                <span
                                    class="text-xl font-extrabold tracking-tight"
                                    :class="item.sisa_tagihan > 0 ? 'text-rose-600' : 'text-emerald-600'"
                                >
                                    {{ formatRupiah(item.sisa_tagihan) }}
                                </span>

                                <!-- Kuitansi Pembayaran (Jika ada riwayat bayar) -->
                                <div v-if="item.transaksi && item.transaksi.length > 0" class="mt-2.5 flex flex-wrap gap-2">
                                    <a
                                        v-for="trx in item.transaksi"
                                        :key="trx.id"
                                        :href="route('cetak-kuitansi.kuitansi', trx.id)"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 transition hover:bg-gray-100 hover:text-indigo-600"
                                    >
                                        📄 Kuitansi #{{ trx.nomor_transaksi || trx.id }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- State Kosong -->
                    <div v-if="!props.tagihan || props.tagihan.length === 0" class="rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center shadow-sm">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-2xl">
                            📋
                        </div>
                        <h4 class="mt-3 text-sm font-semibold text-gray-900">Belum Ada Tagihan</h4>
                        <p class="mt-1 text-xs text-gray-500">Saat ini belum ada data tagihan SPP yang diterbitkan untuk akun Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>