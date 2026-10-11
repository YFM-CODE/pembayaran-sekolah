<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    setoran: Object,
    ringkasanKasir: Object,
    auth: Object,
});

const isModalVerifikasiOpen = ref(false);
const selectedSetoran = ref(null);

const formVerifikasi = useForm({
    total_fisik: '',
    status: 'diterima',
    catatan_bendahara: '',
});

const tutupKasir = () => {
    if (confirm('Lakukan tutup kasir sekarang dan serahkan uang tunai fisik ke Bendahara?')) {
        router.post(route('tu.setoran-kasir.tutup-kasir'));
    }
};

const openModalVerifikasi = (item) => {
    selectedSetoran.value = item;
    formVerifikasi.total_fisik = item.total_sistem;
    formVerifikasi.status = 'diterima';
    formVerifikasi.catatan_bendahara = '';
    isModalVerifikasiOpen.value = true;
};

const submitVerifikasi = () => {
    formVerifikasi.post(route('bendahara.setoran-kasir.verifikasi', selectedSetoran.value.id), {
        onSuccess: () => (isModalVerifikasiOpen.value = false),
    });
};

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
</script>

<template>
    <Head title="Rekonsiliasi & Setoran Kasir" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-900">Setoran & Rekonsiliasi Kasir</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Rekapitulasi tutup kasir harian loket dan serah terima ke bendahara</p>
                </div>
                
                <button
                    v-if="props.auth.user.role === 'staf_tu' || props.auth.user.role?.value === 'staf_tu'"
                    @click="tutupKasir"
                    :disabled="props.ringkasanKasir?.jumlah_transaksi === 0"
                    class="rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 transition"
                >
                    🔒 Tutup Kasir Hari Ini
                </button>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Card Ringkasan Shift Berjalan -->
            <div v-if="props.auth.user.role === 'staf_tu' || props.auth.user.role?.value === 'staf_tu'" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-400">Tunai Siap Setor (Fisik)</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ formatRupiah(props.ringkasanKasir?.total_tunai) }}</p>
                    <p class="text-[11px] text-gray-400 mt-1">Uang fisik yang wajib diserahkan</p>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-400">Transfer Bank (Rekening)</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ formatRupiah(props.ringkasanKasir?.total_transfer) }}</p>
                    <p class="text-[11px] text-gray-400 mt-1">Langsung masuk ke rekening sekolah</p>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-xs font-semibold uppercase text-gray-400">Total Transaksi Belum Tutup</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ props.ringkasanKasir?.jumlah_transaksi || 0 }} <span class="text-sm font-normal text-gray-500">Kuitansi</span></p>
                    <p class="text-[11px] text-gray-400 mt-1">Total: {{ formatRupiah(props.ringkasanKasir?.total_semua) }}</p>
                </div>
            </div>

            <!-- Tabel Histori Setoran Kasir -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-4">Riwayat Tutup Kasir</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">No. Setoran</th>
                                    <th class="px-5 py-3">Tanggal</th>
                                    <th class="px-5 py-3">Staf Loket</th>
                                    <th class="px-5 py-3 text-right">Tercatat Sistem</th>
                                    <th class="px-5 py-3 text-right">Fisik Diterima</th>
                                    <th class="px-5 py-3 text-right">Selisih</th>
                                    <th class="px-5 py-3 text-center">Status</th>
                                    <th class="px-5 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="item in props.setoran?.data" :key="item.id" class="hover:bg-gray-50/70 transition">
                                    <td class="whitespace-nowrap px-5 py-3.5 font-mono text-xs font-semibold text-indigo-600">{{ item.nomor_setoran }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-xs text-gray-600">{{ item.tanggal_setoran }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium text-gray-900">{{ item.staf_tu?.name || '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-right text-gray-800">{{ formatRupiah(item.total_sistem) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-right text-emerald-600">
                                        {{ item.total_fisik ? formatRupiah(item.total_fisik) : '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-xs text-right font-semibold" :class="Number(item.selisih) === 0 ? 'text-gray-500' : (Number(item.selisih) < 0 ? 'text-rose-600' : 'text-blue-600')">
                                        {{ formatRupiah(item.selisih) }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                        <span
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 border border-emerald-200': (item.status?.value || item.status) === 'diterima',
                                                'bg-amber-50 text-amber-700 border border-amber-200': (item.status?.value || item.status) === 'menunggu_verifikasi',
                                                'bg-rose-50 text-rose-700 border border-rose-200': (item.status?.value || item.status) === 'ditolak'
                                            }"
                                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase"
                                        >
                                            {{ (item.status?.value || item.status)?.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-center text-xs">
                                        <button
                                            v-if="(props.auth.user.role === 'bendahara' || props.auth.user.role?.value === 'bendahara') && (item.status?.value || item.status) === 'menunggu_verifikasi'"
                                            @click="openModalVerifikasi(item)"
                                            class="font-semibold text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100"
                                        >
                                            Verifikasi Fisik
                                        </button>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                </tr>
                                <tr v-if="!props.setoran?.data?.length">
                                    <td colspan="8" class="p-6 text-center text-sm text-gray-400">Belum ada riwayat penutupan kasir.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Verifikasi Bendahara -->
        <div v-if="isModalVerifikasiOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4">
                <div class="border-b pb-3">
                    <h3 class="text-base font-bold text-gray-900">Verifikasi Uang Fisik Kasir</h3>
                    <p class="text-xs text-gray-500 font-mono">{{ selectedSetoran?.nomor_setoran }}</p>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg text-xs space-y-1">
                    <div class="flex justify-between text-gray-600">
                        <span>Tercatat di Sistem (Tunai):</span>
                        <span class="font-bold text-gray-900">{{ formatRupiah(selectedSetoran?.total_sistem) }}</span>
                    </div>
                </div>

                <form @submit.prevent="submitVerifikasi" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Total Uang Fisik Diterima (Rp)</label>
                        <input v-model="formVerifikasi.total_fisik" type="number" min="0" required class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Keputusan</label>
                        <select v-model="formVerifikasi.status" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="diterima">Cocok / Diterima</option>
                            <option value="ditolak">Tolak (Selisih / Belum Sesuai)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Verifikasi</label>
                        <textarea v-model="formVerifikasi.catatan_bendahara" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="isModalVerifikasiOpen = false" class="rounded-lg border px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="formVerifikasi.processing" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 shadow-sm">Simpan Verifikasi</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>