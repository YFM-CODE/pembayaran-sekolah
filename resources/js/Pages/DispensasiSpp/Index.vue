<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    dispensasi: Object,
    auth: Object,
});

const page = usePage();
const userRole = computed(() => props.auth?.user?.role || page.props.auth?.user?.role);
const isSiswa = computed(() => userRole.value === 'siswa');

const isModalAjukanOpen = ref(false);
const isModalPersetujuanOpen = ref(false);
const selectedDispensasi = ref(null);

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

const submitAjukan = () => {
    formAjukan.post(route('tu.dispensasi-spp.store'), {
        onSuccess: () => (isModalAjukanOpen.value = false),
    });
};

const openModalPersetujuan = (item) => {
    selectedDispensasi.value = item;
    formPersetujuan.reset();
    isModalPersetujuanOpen.value = true;
};

const submitPersetujuan = () => {
    formPersetujuan.patch(route('kepsek.dispensasi-spp.persetujuan', selectedDispensasi.value.id), {
        onSuccess: () => (isModalPersetujuanOpen.value = false),
    });
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
</script>

<template>
    <Head :title="isSiswa ? 'Dispensasi Pembayaran Saya' : 'Pengajuan & Persetujuan Dispensasi'" />

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
                    v-if="userRole === 'staf_tu'"
                    @click="isModalAjukanOpen = true"
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
                                <th scope="col" class="px-5 py-3.5 font-semibold">Siswa</th>
                                <th scope="col" class="px-5 py-3.5 font-semibold">Periode Tagihan</th>
                                <th scope="col" class="px-5 py-3.5 font-semibold">Batas Waktu Baru</th>
                                <th scope="col" class="px-5 py-3.5 font-semibold">Alasan Penundaan</th>
                                <th scope="col" class="px-5 py-3.5 font-semibold">Status</th>
                                <th v-if="userRole === 'kepala_sekolah'" scope="col" class="px-5 py-3.5 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border bg-white">
                            <tr
                                v-for="item in props.dispensasi.data"
                                :key="item.id"
                                class="odd:bg-white even:bg-page/50 hover:bg-brand-soft/40 transition-colors"
                            >
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <div class="font-bold text-ink">{{ item.tagihan?.siswa?.nama_lengkap }}</div>
                                    <div class="text-xs text-ink-muted">NIS: {{ item.tagihan?.siswa?.nis }} &bull; {{ item.tagihan?.siswa?.kelas?.nama_kelas ?? '' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 font-medium text-ink">
                                    Bulan {{ item.tagihan?.bulan }} / {{ item.tagihan?.tahun }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 font-bold text-brand">
                                    {{ formatTanggal(item.batas_waktu_baru) }}
                                </td>
                                <td class="px-5 py-3.5 text-ink-muted max-w-xs truncate">
                                    {{ item.alasan_penundaan }}
                                    <div v-if="item.catatan_pimpinan" class="text-[11px] text-ink italic mt-0.5">
                                        Catatan Kepsek: "{{ item.catatan_pimpinan }}"
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span
                                        :class="{
                                            'bg-status-success/15 text-status-success': item.status === 'disetujui',
                                            'bg-status-warning/15 text-status-warning': item.status === 'menunggu_persetujuan',
                                            'bg-status-danger/15 text-status-danger': item.status === 'ditolak'
                                        }"
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full" :class="{
                                            'bg-status-success': item.status === 'disetujui',
                                            'bg-status-warning': item.status === 'menunggu_persetujuan',
                                            'bg-status-danger': item.status === 'ditolak'
                                        }"></span>
                                        {{ item.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td v-if="userRole === 'kepala_sekolah'" class="whitespace-nowrap px-5 py-3.5 text-right">
                                    <button
                                        v-if="item.status === 'menunggu_persetujuan'"
                                        @click="openModalPersetujuan(item)"
                                        class="inline-flex items-center gap-1 rounded-md border border-brand-border bg-white px-2.5 py-1 text-xs font-semibold text-brand shadow-2xs hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                                    >
                                        Proses Persetujuan
                                    </button>
                                </td>
                            </tr>

                            <!-- Empty state row -->
                            <tr v-if="!props.dispensasi.data || props.dispensasi.data.length === 0">
                                <td :colspan="userRole === 'kepala_sekolah' ? 6 : 5" class="py-12 text-center text-xs text-ink-muted">
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
            <div class="w-full max-w-md rounded-2xl border border-brand-border bg-white p-6 shadow-2xl">
                <h3 class="mb-4 text-base font-bold text-ink">Form Pengajuan Dispensasi</h3>
                <form @submit.prevent="submitAjukan" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-ink">ID Tagihan SPP</label>
                        <input v-model="formAjukan.tagihan_spp_id" type="number" required class="mt-1 w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink">Jatuh Tempo Baru yang Diajukan</label>
                        <input v-model="formAjukan.batas_waktu_baru" type="date" required class="mt-1 w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink">Alasan Penundaan</label>
                        <textarea v-model="formAjukan.alasan_penundaan" required rows="3" class="mt-1 w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink">Dokumen Bukti Pendukung</label>
                        <input type="file" @input="formAjukan.dokumen_pendukung = $event.target.files[0]" class="mt-1 w-full text-xs text-ink-muted" />
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="isModalAjukanOpen = false" class="rounded-lg border border-brand-border px-3.5 py-2 text-xs font-semibold text-ink hover:bg-page">Batal</button>
                        <button type="submit" :disabled="formAjukan.processing" class="rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus">Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Keputusan Kepala Sekolah -->
        <div v-if="isModalPersetujuanOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md rounded-2xl border border-brand-border bg-white p-6 shadow-2xl">
                <h3 class="mb-4 text-base font-bold text-ink">Keputusan Kepala Sekolah</h3>
                <form @submit.prevent="submitPersetujuan" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-ink">Keputusan</label>
                        <select v-model="formPersetujuan.status" class="mt-1 w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus">
                            <option value="disetujui">Setujui Dispensasi</option>
                            <option value="ditolak">Tolak Dispensasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink">Catatan Pimpinan</label>
                        <textarea v-model="formPersetujuan.catatan_pimpinan" rows="3" class="mt-1 w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-xs text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="isModalPersetujuanOpen = false" class="rounded-lg border border-brand-border px-3.5 py-2 text-xs font-semibold text-ink hover:bg-page">Batal</button>
                        <button type="submit" :disabled="formPersetujuan.processing" class="rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus">Simpan Keputusan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
