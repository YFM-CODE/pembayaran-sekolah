<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    dispensasi: Object,
    daftarSiswaTagihan: Array,
    auth: Object,
});

const page = usePage();

const userRole = computed(() => {
    const r = page.props.auth?.user?.role;
    return typeof r === 'object' && r !== null ? r.value : r;
});

const isModalAjukanOpen = ref(false);
const isModalPersetujuanOpen = ref(false);
const selectedDispensasi = ref(null);
const selectedSiswaId = ref('');

const formAjukan = useForm({
    tagihan_spp_id: '',
    batas_waktu_baru: '',
    alasan_penundaan: '',
    dokumen_pendukung: null,
});

const formPersetujuan = useForm({
    status: 'disetujui',
    catatan_pimpinan: '',
});

const getStatusValue = (item) => {
    const raw = item?.status ?? item?.status_pengajuan;
    return typeof raw === 'object' && raw !== null ? raw.value : raw;
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const cleanDate = typeof dateStr === 'string' ? dateStr.split('T')[0] : dateStr;
    const parts = cleanDate.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return cleanDate;
};

const daftarTagihanTersedia = computed(() => {
    if (!selectedSiswaId.value) return [];
    const siswa = props.daftarSiswaTagihan?.find(s => Number(s.id) === Number(selectedSiswaId.value));
    return siswa ? siswa.tagihan : [];
});

const onSiswaChange = () => {
    formAjukan.tagihan_spp_id = '';
};

const openModalAjukan = () => {
    formAjukan.reset();
    formAjukan.clearErrors();
    selectedSiswaId.value = '';
    isModalAjukanOpen.value = true;
};

const submitAjukan = () => {
    formAjukan.post(route('tu.dispensasi-spp.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            isModalAjukanOpen.value = false;
            formAjukan.reset();
            selectedSiswaId.value = '';
        },
    });
};

const openModalPersetujuan = (item) => {
    selectedDispensasi.value = item;
    formPersetujuan.reset();
    formPersetujuan.clearErrors();
    formPersetujuan.status = 'disetujui';
    formPersetujuan.catatan_pimpinan = item.catatan_pimpinan || '';
    isModalPersetujuanOpen.value = true;
};

const submitPersetujuan = () => {
    formPersetujuan.patch(route('kepsek.dispensasi-spp.persetujuan', selectedDispensasi.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isModalPersetujuanOpen.value = false;
            formPersetujuan.reset();
            selectedDispensasi.value = null;
        },
    });
};

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
</script>

<template>
    <Head title="Pengajuan & Persetujuan Dispensasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-900">Dispensasi Pembayaran SPP</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Pengajuan perpanjangan tenggat jatuh tempo SPP bagi siswa</p>
                </div>
                <button
                    v-if="userRole === 'staf_tu'"
                    @click="openModalAjukan"
                    class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition"
                >
                    + Ajukan Dispensasi
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                                <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-500">
                                    <tr>
                                        <th class="px-5 py-3">Siswa</th>
                                        <th class="px-5 py-3">Tagihan</th>
                                        <th class="px-5 py-3">Tenggat Baru</th>
                                        <th class="px-5 py-3">Alasan</th>
                                        <th class="px-5 py-3">Bukti Dokumen</th>
                                        <th class="px-5 py-3 text-center">Status</th>
                                        <th class="px-5 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr v-for="item in props.dispensasi?.data" :key="item.id" class="hover:bg-gray-50/70 transition">
                                        <td class="whitespace-nowrap px-5 py-3.5">
                                            <div class="font-medium text-gray-900">{{ item.tagihan?.siswa?.nama_lengkap }}</div>
                                            <div class="text-xs text-gray-500 font-mono">NIS: {{ item.tagihan?.siswa?.nis }} ({{ item.tagihan?.siswa?.kelas?.nama_kelas || '-' }})</div>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-xs text-gray-700">
                                            Bulan <strong>{{ item.tagihan?.bulan }}/{{ item.tagihan?.tahun }}</strong>
                                            <div class="text-[11px] text-gray-400">Sisa: {{ formatRupiah(item.tagihan?.sisa_tagihan) }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 font-semibold text-rose-600 text-xs">
                                            {{ formatDate(item.batas_waktu_baru) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-gray-600 max-w-xs truncate" :title="item.alasan_penundaan">
                                            {{ item.alasan_penundaan }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-xs">
                                            <a 
                                                v-if="item.dokumen_pendukung" 
                                                :href="`/storage/${item.dokumen_pendukung}`" 
                                                target="_blank" 
                                                class="text-indigo-600 hover:underline font-semibold"
                                            >
                                                Lihat Berkas ↗
                                            </a>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                            <span
                                                :class="{
                                                    'bg-emerald-50 text-emerald-700 border border-emerald-200': getStatusValue(item) === 'disetujui',
                                                    'bg-amber-50 text-amber-700 border border-amber-200': ['menunggu', 'menunggu_persetujuan'].includes(getStatusValue(item)),
                                                    'bg-rose-50 text-rose-700 border border-rose-200': getStatusValue(item) === 'ditolak'
                                                }"
                                                class="rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase inline-block"
                                            >
                                                {{ getStatusValue(item)?.replace('_', ' ') || 'MENUNGGU PERSETUJUAN' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-center text-xs">
                                            <button
                                                v-if="userRole === 'kepala_sekolah' && ['menunggu', 'menunggu_persetujuan'].includes(getStatusValue(item))"
                                                @click="openModalPersetujuan(item)"
                                                class="font-semibold text-indigo-700 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg border border-indigo-200 transition"
                                            >
                                                ⚖️ Beri Keputusan
                                            </button>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!props.dispensasi?.data?.length">
                                        <td colspan="7" class="p-6 text-center text-sm text-gray-400">Belum ada permohonan dispensasi yang tercatat.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ajukan Dispensasi -->
        <div v-if="isModalAjukanOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl space-y-4">
                <div class="border-b pb-3">
                    <h3 class="text-base font-bold text-gray-900">Form Pengajuan Dispensasi SPP</h3>
                    <p class="text-xs text-gray-500">Pilih siswa dan bulan tagihan yang dimintakan penundaan tenggat waktu</p>
                </div>

                <div v-if="Object.keys(formAjukan.errors).length > 0" class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-xs">
                    <p class="font-bold">Gagal mengirim permohonan:</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        <li v-for="(err, key) in formAjukan.errors" :key="key">{{ err }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submitAjukan" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pilih Siswa</label>
                        <select 
                            v-model="selectedSiswaId" 
                            @change="onSiswaChange"
                            required 
                            class="w-full rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">-- Pilih Siswa Penunggak --</option>
                            <option v-for="s in props.daftarSiswaTagihan" :key="s.id" :value="s.id">
                                {{ s.nis }} - {{ s.nama_lengkap }} ({{ s.kelas }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pilih Bulan Tagihan SPP</label>
                        <select 
                            v-model="formAjukan.tagihan_spp_id" 
                            :disabled="!selectedSiswaId"
                            required 
                            class="w-full rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                        >
                            <option value="">-- {{ selectedSiswaId ? 'Pilih Periode Tagihan' : 'Pilih Siswa Terlebih Dahulu' }} --</option>
                            <option v-for="t in daftarTagihanTersedia" :key="t.id" :value="t.id">
                                {{ t.periode }} — Sisa: {{ formatRupiah(t.sisa) }}
                            </option>
                        </select>
                        <div v-if="formAjukan.errors.tagihan_spp_id" class="text-rose-600 mt-1">{{ formAjukan.errors.tagihan_spp_id }}</div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Batas Waktu (Jatuh Tempo) Baru yang Diajukan</label>
                        <input 
                            v-model="formAjukan.batas_waktu_baru" 
                            type="date" 
                            required 
                            class="w-full rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500" 
                        />
                        <div v-if="formAjukan.errors.batas_waktu_baru" class="text-rose-600 mt-1">{{ formAjukan.errors.batas_waktu_baru }}</div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Alasan Penundaan Pembayaran</label>
                        <textarea 
                            v-model="formAjukan.alasan_penundaan" 
                            rows="3" 
                            placeholder="Contoh: Orang tua siswa sedang cuti sakit tanpa upah sampai akhir bulan..."
                            required 
                            class="w-full rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500"
                        ></textarea>
                        <div v-if="formAjukan.errors.alasan_penundaan" class="text-rose-600 mt-1">{{ formAjukan.errors.alasan_penundaan }}</div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Dokumen Surat Keterangan / Bukti (PDF / Foto)</label>
                        <input 
                            type="file" 
                            accept=".pdf,.jpg,.jpeg,.png"
                            @change="formAjukan.dokumen_pendukung = $event.target.files[0]" 
                            class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" 
                        />
                        <div v-if="formAjukan.errors.dokumen_pendukung" class="text-rose-600 mt-1">{{ formAjukan.errors.dokumen_pendukung }}</div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t">
                        <button type="button" @click="isModalAjukanOpen = false" class="rounded-lg border px-4 py-2 text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="formAjukan.processing" class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Keputusan Kepala Sekolah -->
        <div v-if="isModalPersetujuanOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4">
                <div class="border-b pb-3">
                    <h3 class="text-base font-bold text-gray-900">Keputusan Kepala Sekolah</h3>
                    <p class="text-xs text-gray-500">{{ selectedDispensasi?.tagihan?.siswa?.nama_lengkap }} ({{ selectedDispensasi?.tagihan?.siswa?.nis }})</p>
                </div>

                <div v-if="Object.keys(formPersetujuan.errors).length > 0" class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-xs">
                    <p class="font-bold">Gagal memproses keputusan:</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        <li v-for="(err, key) in formPersetujuan.errors" :key="key">{{ err }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submitPersetujuan" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Keputusan</label>
                        <select v-model="formPersetujuan.status" class="w-full rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="disetujui">Setujui Dispensasi</option>
                            <option value="ditolak">Tolak Dispensasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Catatan Pimpinan</label>
                        <textarea v-model="formPersetujuan.catatan_pimpinan" rows="3" class="w-full rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tuliskan catatan arahan pimpinan..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="isModalPersetujuanOpen = false" class="rounded-lg border px-4 py-2 text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="formPersetujuan.processing" class="rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700">Simpan Keputusan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>