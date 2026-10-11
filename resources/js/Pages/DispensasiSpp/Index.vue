<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    dispensasi: {
        type: Object,
        default: () => ({ data: [] }),
    },
    daftarSiswaTagihan: {
        type: Array,
        default: () => [],
    },
    auth: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const userRole = computed(() => {
    const r = props.auth?.user?.role || page.props.auth?.user?.role;
    return typeof r === 'object' && r !== null ? r.value : r;
});

const isSiswa = computed(() => userRole.value === 'siswa');
const isKepsek = computed(() => userRole.value === 'kepala_sekolah');
const isStafTu = computed(() => userRole.value === 'staf_tu');

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

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);

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
    if (!selectedDispensasi.value) return;
    formPersetujuan.patch(route('kepsek.dispensasi-spp.persetujuan', selectedDispensasi.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isModalPersetujuanOpen.value = false;
            formPersetujuan.reset();
            selectedDispensasi.value = null;
        },
    });
};
</script>

<template>
    <Head :title="isSiswa ? 'Dispensasi Pembayaran Saya' : 'Dispensasi Pembayaran SPP'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">
                        {{ isSiswa ? 'Status Dispensasi SPP Saya' : 'Dispensasi Pembayaran SPP' }}
                    </h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">
                        {{ isSiswa
                            ? 'Pantau permohonan penundaan batas waktu pelunasan SPP Anda.'
                            : 'Kelola permohonan penundaan batas waktu pembayaran SPP siswa.' }}
                    </p>
                </div>
                <button
                    v-if="isStafTu"
                    @click="openModalAjukan"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                >
                    + Ajukan Dispensasi
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <div class="ui-panel overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-xs sm:text-sm">
                        <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 font-semibold">Identitas Siswa</th>
                                <th scope="col" class="px-5 py-3.5 font-semibold">Periode Tagihan</th>
                                <th scope="col" class="px-5 py-3.5 font-semibold">Batas Waktu Baru</th>
                                <th scope="col" class="px-5 py-3.5 font-semibold">Alasan & Dokumen</th>
                                <th scope="col" class="px-5 py-3.5 font-semibold text-center">Status</th>
                                <th v-if="isKepsek" scope="col" class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border bg-white">
                            <tr
                                v-for="item in (props.dispensasi?.data || [])"
                                :key="item.id"
                                class="odd:bg-white even:bg-page/50 hover:bg-brand-soft/40 transition-colors"
                            >
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <div class="font-bold text-ink">{{ item.tagihan?.siswa?.nama_lengkap }}</div>
                                    <div class="text-xs text-ink-muted">NIS: {{ item.tagihan?.siswa?.nis }} &bull; {{ item.tagihan?.siswa?.kelas?.nama_kelas ?? '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 font-medium text-ink">
                                    Bulan {{ item.tagihan?.bulan }} / {{ item.tagihan?.tahun }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 font-bold text-brand">
                                    {{ formatTanggal(item.batas_waktu_baru) }}
                                </td>
                                <td class="px-5 py-3.5 text-ink-muted max-w-xs">
                                    <div>{{ item.alasan_penundaan }}</div>
                                    <div v-if="item.dokumen_pendukung" class="mt-1">
                                        <a
                                            :href="'/storage/' + item.dokumen_pendukung"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-brand hover:underline"
                                        >
                                            📎 Lihat Dokumen ↗
                                        </a>
                                    </div>
                                    <div v-if="item.catatan_pimpinan" class="text-[11px] text-ink italic mt-1">
                                        Catatan Kepsek: "{{ item.catatan_pimpinan }}"
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                    <span
                                        :class="{
                                            'bg-status-success/15 text-status-success': getStatusValue(item) === 'disetujui',
                                            'bg-status-warning/15 text-status-warning': ['menunggu', 'menunggu_persetujuan'].includes(getStatusValue(item)),
                                            'bg-status-danger/15 text-status-danger': getStatusValue(item) === 'ditolak'
                                        }"
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="{
                                            'bg-status-success': getStatusValue(item) === 'disetujui',
                                            'bg-status-warning': ['menunggu', 'menunggu_persetujuan'].includes(getStatusValue(item)),
                                            'bg-status-danger': getStatusValue(item) === 'ditolak'
                                        }"></span>
                                        {{ (getStatusValue(item) || '').replace('_', ' ') }}
                                    </span>
                                </td>
                                <td v-if="isKepsek" class="whitespace-nowrap px-5 py-3.5 text-right">
                                    <button
                                        v-if="['menunggu', 'menunggu_persetujuan'].includes(getStatusValue(item))"
                                        @click="openModalPersetujuan(item)"
                                        class="inline-flex items-center gap-1 rounded-lg border border-brand-border bg-white px-2.5 py-1 text-xs font-semibold text-brand shadow-2xs hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                                    >
                                        ⚖️ Beri Keputusan
                                    </button>
                                    <span v-else class="text-xs text-ink-muted">-</span>
                                </td>
                            </tr>

                            <!-- Empty state row -->
                            <tr v-if="!props.dispensasi?.data || props.dispensasi.data.length === 0">
                                <td :colspan="isKepsek ? 6 : 5" class="py-12 text-center text-xs text-ink-muted">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-soft text-brand">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="mt-3 font-semibold text-ink">
                                        {{ isSiswa ? 'Belum Ada Permohonan Dispensasi' : 'Tidak Ada Data Dispensasi' }}
                                    </p>
                                    <p class="mt-1 text-ink-muted">
                                        {{ isSiswa
                                            ? 'Anda belum pernah mengajukan permohonan penundaan pembayaran SPP.'
                                            : 'Belum ada data pengajuan dispensasi yang tercatat.' }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Ajukan Dispensasi (Staf TU) -->
        <div v-if="isModalAjukanOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-lg rounded-2xl border border-brand-border bg-white p-6 shadow-2xl space-y-4">
                <div class="border-b border-brand-border pb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-bold text-ink">Form Pengajuan Dispensasi SPP</h3>
                        <p class="text-xs text-ink-muted mt-0.5">Pilih siswa dan bulan tagihan yang dimintakan penundaan tenggat waktu</p>
                    </div>
                    <button @click="isModalAjukanOpen = false" class="text-ink-muted hover:text-ink text-xl leading-none">&times;</button>
                </div>

                <div v-if="Object.keys(formAjukan.errors).length > 0" class="p-3 bg-rose-50 border border-rose-200 text-status-danger rounded-lg text-xs">
                    <p class="font-bold">Gagal mengirim permohonan:</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        <li v-for="(err, key) in formAjukan.errors" :key="key">{{ err }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submitAjukan" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-ink mb-1">Pilih Siswa</label>
                        <select 
                            v-model="selectedSiswaId" 
                            @change="onSiswaChange"
                            required 
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        >
                            <option value="">-- Pilih Siswa Penunggak --</option>
                            <option v-for="s in props.daftarSiswaTagihan" :key="s.id" :value="s.id">
                                {{ s.nis }} - {{ s.nama_lengkap }} ({{ s.kelas }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-ink mb-1">Pilih Bulan Tagihan SPP</label>
                        <select 
                            v-model="formAjukan.tagihan_spp_id" 
                            :disabled="!selectedSiswaId"
                            required 
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus disabled:bg-page disabled:text-ink-muted"
                        >
                            <option value="">-- {{ selectedSiswaId ? 'Pilih Periode Tagihan' : 'Pilih Siswa Terlebih Dahulu' }} --</option>
                            <option v-for="t in daftarTagihanTersedia" :key="t.id" :value="t.id">
                                {{ t.periode }} — Sisa: {{ formatRupiah(t.sisa) }}
                            </option>
                        </select>
                        <div v-if="formAjukan.errors.tagihan_spp_id" class="text-status-danger mt-1">{{ formAjukan.errors.tagihan_spp_id }}</div>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-ink mb-1">Batas Waktu (Jatuh Tempo) Baru</label>
                        <input 
                            v-model="formAjukan.batas_waktu_baru" 
                            type="date" 
                            required 
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus" 
                        />
                        <div v-if="formAjukan.errors.batas_waktu_baru" class="text-status-danger mt-1">{{ formAjukan.errors.batas_waktu_baru }}</div>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-ink mb-1">Alasan Penundaan Pembayaran</label>
                        <textarea 
                            v-model="formAjukan.alasan_penundaan" 
                            rows="3" 
                            placeholder="Contoh: Orang tua siswa sedang cuti sakit tanpa upah sampai akhir bulan..."
                            required 
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        ></textarea>
                        <div v-if="formAjukan.errors.alasan_penundaan" class="text-status-danger mt-1">{{ formAjukan.errors.alasan_penundaan }}</div>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-ink mb-1">Dokumen Surat Keterangan / Bukti (PDF / Foto)</label>
                        <input 
                            type="file" 
                            accept=".pdf,.jpg,.jpeg,.png"
                            @change="formAjukan.dokumen_pendukung = $event.target.files[0]" 
                            class="w-full text-xs text-ink-muted file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-soft file:text-brand hover:file:bg-brand-soft/80" 
                        />
                        <div v-if="formAjukan.errors.dokumen_pendukung" class="text-status-danger mt-1">{{ formAjukan.errors.dokumen_pendukung }}</div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-brand-border">
                        <button type="button" @click="isModalAjukanOpen = false" class="rounded-lg border border-brand-border px-4 py-2 text-xs font-semibold text-ink hover:bg-brand-soft">Batal</button>
                        <button type="submit" :disabled="formAjukan.processing" class="rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus disabled:opacity-50">
                            {{ formAjukan.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Keputusan Kepala Sekolah -->
        <div v-if="isModalPersetujuanOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-brand-border bg-white p-6 shadow-2xl space-y-4">
                <div class="border-b border-brand-border pb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-bold text-ink">Keputusan Kepala Sekolah</h3>
                        <p class="text-xs text-ink-muted">{{ selectedDispensasi?.tagihan?.siswa?.nama_lengkap }} (NIS: {{ selectedDispensasi?.tagihan?.siswa?.nis }})</p>
                    </div>
                    <button @click="isModalPersetujuanOpen = false" class="text-ink-muted hover:text-ink text-xl leading-none">&times;</button>
                </div>

                <div v-if="Object.keys(formPersetujuan.errors).length > 0" class="p-3 bg-rose-50 border border-rose-200 text-status-danger rounded-lg text-xs">
                    <p class="font-bold">Gagal memproses keputusan:</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        <li v-for="(err, key) in formPersetujuan.errors" :key="key">{{ err }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submitPersetujuan" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-ink mb-1">Keputusan</label>
                        <select v-model="formPersetujuan.status" class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus">
                            <option value="disetujui">Setujui Dispensasi</option>
                            <option value="ditolak">Tolak Dispensasi</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold uppercase tracking-wider text-ink mb-1">Catatan Pimpinan</label>
                        <textarea v-model="formPersetujuan.catatan_pimpinan" rows="3" class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus" placeholder="Tuliskan catatan arahan pimpinan..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-brand-border">
                        <button type="button" @click="isModalPersetujuanOpen = false" class="rounded-lg border border-brand-border px-4 py-2 text-xs font-semibold text-ink hover:bg-brand-soft">Batal</button>
                        <button type="submit" :disabled="formPersetujuan.processing" class="rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus disabled:opacity-50">
                            {{ formPersetujuan.processing ? 'Menyimpan...' : 'Simpan Keputusan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>