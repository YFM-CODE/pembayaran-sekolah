<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    ringkasan: Object,
    transaksiTerkini: Array,
    topTunggakan: Array,
});

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
</script>

<template>
    <Head title="Dashboard Eksekutif Kepala Sekolah" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-900">Dashboard Eksekutif</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Monitoring keuangan SPP, kepatuhan pembayaran siswa, dan audit dispensasi</p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('kepsek.dispensasi-spp.index')"
                        class="rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition"
                    >
                        Tinjau Dispensasi ({{ props.ringkasan.dispensasi_menunggu }})
                    </Link>
                    <Link
                        :href="route('kepsek.laporan.index')"
                        class="rounded-xl bg-gray-900 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-gray-800 transition"
                    >
                        Laporan Rekapitulasi ↗
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- 4 Metrik Utama -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Penerimaan Bulan Ini</span>
                    <div class="text-2xl font-black text-indigo-600 mt-2">{{ formatRupiah(props.ringkasan.penerimaan_bulan_ini) }}</div>
                    <p class="text-xs text-gray-400 mt-1">Total tahun ini: {{ formatRupiah(props.ringkasan.penerimaan_tahun_ini) }}</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Total Tunggakan SPP</span>
                    <div class="text-2xl font-black text-rose-600 mt-2">{{ formatRupiah(props.ringkasan.total_tunggakan) }}</div>
                    <p class="text-xs text-rose-500 font-semibold mt-1">{{ props.ringkasan.siswa_menunggak }} siswa belum lunas</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Kepatuhan Siswa</span>
                    <div class="text-2xl font-black text-emerald-600 mt-2">{{ props.ringkasan.rasio_kepatuhan }}%</div>
                    <div class="w-full bg-gray-100 h-2 rounded-full mt-2 overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full" :style="{ width: props.ringkasan.rasio_kepatuhan + '%' }"></div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                    <span class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Dispensasi Menunggu</span>
                    <div class="text-2xl font-black text-amber-500 mt-2">{{ props.ringkasan.dispensasi_menunggu }}</div>
                    <Link :href="route('kepsek.dispensasi-spp.index')" class="text-xs text-indigo-600 font-semibold hover:underline mt-1 inline-block">
                        Lihat permohonan ↗
                    </Link>
                </div>
            </div>

            <!-- Tabel Transaksi Masuk Terkini & Top Siswa Menunggak -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 5 Transaksi Terkini -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h3 class="font-bold text-gray-900 text-sm">Penerimaan SPP Terkini</h3>
                        <Link :href="route('kepsek.laporan.index')" class="text-xs font-semibold text-indigo-600 hover:underline">Semua Transaksi ↗</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-gray-400 uppercase border-b">
                                <tr>
                                    <th class="pb-2">Siswa</th>
                                    <th class="pb-2 text-right">Nominal</th>
                                    <th class="pb-2 text-center">Metode</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="t in props.transaksiTerkini" :key="t.id" class="hover:bg-gray-50/50">
                                    <td class="py-2.5">
                                        <div class="font-bold text-gray-900">{{ t.tagihan?.siswa?.nama_lengkap }}</div>
                                        <div class="text-gray-400 text-[11px]">{{ t.tagihan?.siswa?.kelas?.nama_kelas || '-' }} • Bln {{ t.tagihan?.bulan }}/{{ t.tagihan?.tahun }}</div>
                                    </td>
                                    <td class="py-2.5 text-right font-bold text-emerald-600">{{ formatRupiah(t.jumlah_dibayar) }}</td>
                                    <td class="py-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-gray-100 text-gray-600">
                                            {{ t.metode_pembayaran?.value || t.metode_pembayaran }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!props.transaksiTerkini.length">
                                    <td colspan="3" class="py-4 text-center text-gray-400">Belum ada transaksi</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top 5 Penunggak Terbesar -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h3 class="font-bold text-gray-900 text-sm">Siswa Dengan Tunggakan Terbesar</h3>
                        <span class="text-xs text-rose-500 font-semibold">Prioritas Tindak Lanjut</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-gray-400 uppercase border-b">
                                <tr>
                                    <th class="pb-2">Siswa</th>
                                    <th class="pb-2">Kelas</th>
                                    <th class="pb-2 text-right">Total Tunggakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="s in props.topTunggakan" :key="s.id" class="hover:bg-gray-50/50">
                                    <td class="py-2.5">
                                        <div class="font-bold text-gray-900">{{ s.nama_lengkap }}</div>
                                        <div class="text-gray-400 text-[11px]">NIS: {{ s.nis }}</div>
                                    </td>
                                    <td class="py-2.5 text-gray-600">{{ s.kelas?.nama_kelas || '-' }}</td>
                                    <td class="py-2.5 text-right font-black text-rose-600">{{ formatRupiah(s.total_sisa_tunggakan) }}</td>
                                </tr>
                                <tr v-if="!props.topTunggakan.length">
                                    <td colspan="3" class="py-4 text-center text-gray-400">Tidak ada tunggakan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>