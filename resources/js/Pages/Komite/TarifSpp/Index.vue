<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  daftarTarif: Array,
  listTahunAjaran: Array,
  filters: Object,
});

const filterTahun = ref(props.filters.tahun_ajaran || '');
const applyFilter = () => {
  router.get(route('komite.tarif-spp.index'), { tahun_ajaran: filterTahun.value }, { preserveState: true });
};

// Modal State
const isModalOpen = ref(false);
const activeTarif = ref(null);

const form = useForm({
  status_persetujuan: 'disetujui',
  nomor_berita_acara: '',
  tanggal_kesepakatan: new Date().toISOString().slice(0, 10),
  catatan_komite: '',
  dokumen_ba_pdf: null,
});

const openModal = (tarif) => {
  activeTarif.value = tarif;
  form.clearErrors();
  form.reset();

  if (tarif.persetujuan) {
    form.status_persetujuan = tarif.persetujuan.status_persetujuan === 'draft' ? 'disetujui' : tarif.persetujuan.status_persetujuan;
    form.nomor_berita_acara = tarif.persetujuan.nomor_berita_acara || '';
    form.tanggal_kesepakatan = tarif.persetujuan.tanggal_kesepakatan || new Date().toISOString().slice(0, 10);
    form.catatan_komite = tarif.persetujuan.catatan_komite || '';
  }

  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  activeTarif.value = null;
  form.reset();
};

const onFileChange = (e) => {
  form.dokumen_ba_pdf = e.target.files[0];
};

const submitPersetujuan = () => {
  if (!activeTarif.value) return;

  form.post(route('komite.tarif-spp.proses', activeTarif.value.id), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => closeModal(),
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

const getBadgeColor = (status) => {
  switch (status) {
    case 'disetujui':
      return 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20';
    case 'ditolak':
      return 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20';
    default:
      return 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20';
  }
};
</script>

<template>
  <Head title="Verifikasi Tarif SPP - Komite" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Peninjauan & Persetujuan Tarif SPP
          </h2>
          <p class="text-xs text-gray-500 mt-1">Legalisasi usulan tarif dari Bendahara melalui Berita Acara resmi.</p>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
        <!-- Filter Card -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center space-x-3">
            <label class="text-sm font-medium text-gray-700">Tahun Ajaran:</label>
            <select
              v-model="filterTahun"
              @change="applyFilter"
              class="text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
              <option value="">Semua Tahun Ajaran</option>
              <option v-for="ta in listTahunAjaran" :key="ta" :value="ta">{{ ta }}</option>
            </select>
          </div>
          <span class="text-xs text-gray-400">Total usulan: {{ daftarTarif.length }}</span>
        </div>

        <!-- Tabel Usulan Tarif -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-200">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-gray-50 text-gray-600 font-medium">
                <tr>
                  <th class="px-5 py-3 text-left">Tahun Ajaran</th>
                  <th class="px-5 py-3 text-left">Tingkat</th>
                  <th class="px-5 py-3 text-left">Usulan Nominal</th>
                  <th class="px-5 py-3 text-left">Keterangan Usulan</th>
                  <th class="px-5 py-3 text-left">Status Komite</th>
                  <th class="px-5 py-3 text-left">Berita Acara</th>
                  <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="tarif in daftarTarif" :key="tarif.id" class="hover:bg-gray-50/75 transition">
                  <td class="px-5 py-4 font-medium text-gray-900">{{ tarif.tahun_ajaran }}</td>
                  <td class="px-5 py-4 text-gray-600">Kelas {{ tarif.tingkat }}</td>
                  <td class="px-5 py-4 font-semibold text-gray-900">{{ formatRupiah(tarif.nominal_standar) }}</td>
                  <td class="px-5 py-4 text-xs text-gray-500 max-w-xs truncate">{{ tarif.keterangan || '-' }}</td>
                  <td class="px-5 py-4">
                    <span
                      class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium"
                      :class="getBadgeColor(tarif.persetujuan?.status_persetujuan || 'draft')"
                    >
                      {{ tarif.persetujuan ? tarif.persetujuan.status_persetujuan.toUpperCase() : 'MENUNGGU' }}
                    </span>
                  </td>
                  <td class="px-5 py-4">
                    <div v-if="tarif.persetujuan?.ada_dokumen">
                      <a
                        :href="route('komite.tarif-spp.dokumen-ba', tarif.persetujuan.id)"
                        target="_blank"
                        class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800"
                      >
                        Lihat Berita Acara (PDF)
                      </a>
                      <p class="text-[11px] text-gray-400">No: {{ tarif.persetujuan.nomor_berita_acara }}</p>
                    </div>
                    <span v-else class="text-xs text-gray-400">Belum ada file</span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <button
                      @click="openModal(tarif)"
                      class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none"
                    >
                      Tinjau & Beri Keputusan
                    </button>
                  </td>
                </tr>
                <tr v-if="daftarTarif.length === 0">
                  <td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500">
                    Tidak ada usulan tarif yang ditemukan.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Peninjauan & Berita Acara -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white rounded-xl shadow-xl overflow-hidden animate-fadeIn">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
          <div>
            <h3 class="text-base font-semibold text-gray-900">
              Penetapan Keputusan Komite
            </h3>
            <p class="text-xs text-gray-500">
              Tingkat {{ activeTarif?.tingkat }} ({{ activeTarif?.tahun_ajaran }}) &bull; {{ formatRupiah(activeTarif?.nominal_standar) }}
            </p>
          </div>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
        </div>

        <form @submit.prevent="submitPersetujuan" class="p-6 space-y-4">
          <!-- Pilihan Keputusan -->
          <div>
            <label class="block text-xs font-medium uppercase tracking-wider text-gray-700 mb-2">Keputusan Komite</label>
            <div class="grid grid-cols-2 gap-3">
              <label
                class="flex items-center p-3 border rounded-lg cursor-pointer transition"
                :class="form.status_persetujuan === 'disetujui' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200'"
              >
                <input type="radio" v-model="form.status_persetujuan" value="disetujui" class="text-emerald-600 focus:ring-emerald-500" />
                <span class="ml-2 text-sm font-medium text-gray-900">Setujui Tarif</span>
              </label>
              <label
                class="flex items-center p-3 border rounded-lg cursor-pointer transition"
                :class="form.status_persetujuan === 'ditolak' ? 'border-rose-500 bg-rose-50/50' : 'border-gray-200'"
              >
                <input type="radio" v-model="form.status_persetujuan" value="ditolak" class="text-rose-600 focus:ring-rose-500" />
                <span class="ml-2 text-sm font-medium text-gray-900">Tolak Tarif</span>
              </label>
            </div>
            <p v-if="form.errors.status_persetujuan" class="mt-1 text-xs text-rose-600">{{ form.errors.status_persetujuan }}</p>
          </div>

          <!-- Nomor Berita Acara -->
          <div>
            <label class="block text-xs font-medium uppercase tracking-wider text-gray-700 mb-1">Nomor Berita Acara (BA)</label>
            <input
              type="text"
              v-model="form.nomor_berita_acara"
              placeholder="Contoh: 005/BA-KOMITE/VII/2026"
              class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <p v-if="form.errors.nomor_berita_acara" class="mt-1 text-xs text-rose-600">{{ form.errors.nomor_berita_acara }}</p>
          </div>

          <!-- Tanggal Kesepakatan -->
          <div>
            <label class="block text-xs font-medium uppercase tracking-wider text-gray-700 mb-1">Tanggal Kesepakatan Rapat</label>
            <input
              type="date"
              v-model="form.tanggal_kesepakatan"
              class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />
            <p v-if="form.errors.tanggal_kesepakatan" class="mt-1 text-xs text-rose-600">{{ form.errors.tanggal_kesepakatan }}</p>
          </div>

          <!-- Upload Dokumen PDF BA -->
          <div>
            <label class="block text-xs font-medium uppercase tracking-wider text-gray-700 mb-1">
              Unggah Berkas Scan Berita Acara (.pdf)
              <span v-if="form.status_persetujuan === 'disetujui'" class="text-rose-500">*</span>
            </label>
            <input
              type="file"
              accept="application/pdf"
              @change="onFileChange"
              class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
            />
            <p class="mt-1 text-[11px] text-gray-400">Maksimum ukuran berkas 2MB. Pastikan scan lembar tanda tangan terbaca jelas.</p>
            <p v-if="form.errors.dokumen_ba_pdf" class="mt-1 text-xs text-rose-600">{{ form.errors.dokumen_ba_pdf }}</p>
          </div>

          <!-- Catatan Komite -->
          <div>
            <label class="block text-xs font-medium uppercase tracking-wider text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
            <textarea
              v-model="form.catatan_komite"
              rows="3"
              placeholder="Berikan alasan atau penyesuaian jika usulan tarif ditolak/memerlukan revisi..."
              class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            ></textarea>
            <p v-if="form.errors.catatan_komite" class="mt-1 text-xs text-rose-600">{{ form.errors.catatan_komite }}</p>
          </div>

          <div class="mt-6 flex justify-end space-x-3 border-t pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50"
            >
              Simpan Keputusan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

