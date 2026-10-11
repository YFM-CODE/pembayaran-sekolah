<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    transaksi: Object,
    setoranKasir: Array,
    totalPenerimaan: Number,
    filters: Object,
});

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');

const filterLaporan = () => {
    router.get(route('kepsek.laporan.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, { preserveState: true });
};

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
</script>

<template>
    <Head title="Monitoring & Laporan Eksekutif" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-900">Monitoring & Laporan Eksekutif</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Rekapitulasi penerimaan kas loket dan audit setoran harian</p>
                </div>
                <!-- Tombol Cetak PDF Resmi -->
                <a
                    :href="route('kepsek.laporan.cetak', { start_date: startDate, end_date: endDate })"
                    target="_blank"
                    class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition inline-flex items-center gap-1.5 self-start"
                >
                    🖨️ Cetak Laporan Resmi
                </a>
            </div>
        </template>

        <div class="py-8 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Filter Tanggal & Total Penerimaan -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Dari Tanggal</label>
                        <input type="date" v-model="startDate" class="rounded-lg border-gray-300 text-xs focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Sampai Tanggal</label>
                        <input type="date" v-model="endDate" class="rounded-lg border-gray-300 text-xs focus:ring-indigo-500" />
                    </div>
                    <button
                        @click="filterLaporan"
                        class="mt-5 px-4 py-2 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition"
                    >
                        Terapkan Filter
                    </button>
                </div>

                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3.5 px-5 text-right">
                    <span class="text-xs text-indigo-700 font-semibold uppercase">Total Penerimaan Periode Ini</span>
                    <div class="text-xl font-black text-indigo-900 mt-0.5">{{ formatRupiah(props.totalPenerimaan) }}</div>
                </div>
            </div>

            <!-- Tabel Transaksi Penerimaan -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-900 text-sm">Daftar Penerimaan SPP Periode Terpilih</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-500 uppercase">
                            <tr>
                                <th class="p-3">Kuitansi</th>
                                <th class="p-3">Siswa</th>
                                <th class="p-3">Periode</th>
                                <th class="p-3 text-right">Nominal</th>
                                <th class="p-3 text-center">Metode</th>
                                <th class="p-3">Waktu</th>
                                <th class="p-3">Kasir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="t in props.transaksi.data" :key="t.id" class="hover:bg-gray-50/60">
                                <td class="p-3 font-mono font-semibold text-indigo-600">{{ t.nomor_kuitansi }}</td>
                                <td class="p-3">
                                    <div class="font-bold text-gray-900">{{ t.tagihan?.siswa?.nama_lengkap }}</div>
                                    <div class="text-gray-400 text-[11px]">{{ t.tagihan?.siswa?.kelas?.nama_kelas || '-' }}</div>
                                </td>
                                <td class="p-3 text-gray-600">Bln {{ t.tagihan?.bulan }}/{{ t.tagihan?.tahun }}</td>
                                <td class="p-3 text-right font-black text-emerald-600">{{ formatRupiah(t.jumlah_dibayar) }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-gray-100 text-gray-700">
                                        {{ t.metode_pembayaran?.value || t.metode_pembayaran }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-500">{{ t.waktu_transaksi }}</td>
                                <td class="p-3 text-gray-500">{{ t.petugas?.name || '-' }}</td>
                            </tr>
                            <tr v-if="!props.transaksi.data?.length">
                                <td colspan="7" class="p-6 text-center text-gray-400">Tidak ada data transaksi pada rentang tanggal ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabel Audit Setoran Kasir Harian -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-900 text-sm">Audit Rekonsiliasi & Tutup Kasir Harian</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-500 uppercase">
                            <tr>
                                <th class="p-3">No. Setoran</th>
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Kasir Loket</th>
                                <th class="p-3 text-right">Tercatat Sistem</th>
                                <th class="p-3 text-right">Fisik Diterima</th>
                                <th class="p-3 text-right">Selisih</th>
                                <th class="p-3 text-center">Status</th>
                                <th class="p-3">Bendahara Penerima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="s in props.setoranKasir" :key="s.id" class="hover:bg-gray-50/60">
                                <td class="p-3 font-mono font-semibold text-gray-900">{{ s.nomor_setoran }}</td>
                                <td class="p-3 text-gray-600">{{ s.tanggal_setoran }}</td>
                                <td class="p-3 text-gray-700">{{ s.staf_tu?.name || '-' }}</td>
                                <td class="p-3 text-right font-medium text-gray-900">{{ formatRupiah(s.total_sistem) }}</td>
                                <td class="p-3 text-right font-bold text-emerald-600">{{ formatRupiah(s.total_fisik) }}</td>
                                <td class="p-3 text-right font-bold" :class="s.selisih == 0 ? 'text-gray-500' : 'text-rose-600'">
                                    {{ formatRupiah(s.selisih) }}
                                </td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ s.status?.value || s.status }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-700">{{ s.bendahara?.name || '-' }}</td>
                            </tr>
                            <tr v-if="!props.setoranKasir?.length">
                                <td colspan="8" class="p-6 text-center text-gray-400">Belum ada riwayat setoran pada periode ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>