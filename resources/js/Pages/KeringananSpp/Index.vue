<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  programList: Array,
  daftarSiswa: Object,
  listKelas: Array,
  listTahunAjaran: Array,
  tahunAjaranAktif: String,
  filters: Object,
});

const activeTab = ref('program'); // 'program' | 'penetapan'

// Filter Siswa
const searchSiswa = ref(props.filters.search || '');
const kelasFilter = ref(props.filters.kelas_id || '');
const tahunAjaranSelect = ref(props.tahunAjaranAktif);

const applyFilterSiswa = () => {
  router.get(
    route('bendahara.keringanan-spp.index'),
    {
      search: searchSiswa.value,
      kelas_id: kelasFilter.value,
      tahun_ajaran: tahunAjaranSelect.value,
    },
    { preserveState: true, replace: true }
  );
};

// --- MODAL PROGRAM KERINGANAN ---
const isProgramModalOpen = ref(false);
const isEditingProgram = ref(false);
const editingProgramId = ref(null);

const formProgram = useForm({
  nama_program: '',
  tipe_potongan: 'nominal',
  nilai_potongan: '',
  syarat_ketentuan: '',
  aktif: true,
});

const openCreateProgram = () => {
  isEditingProgram.value = false;
  editingProgramId.value = null;
  formProgram.reset();
  formProgram.clearErrors();
  isProgramModalOpen.value = true;
};

const openEditProgram = (item) => {
  isEditingProgram.value = true;
  editingProgramId.value = item.id;
  formProgram.clearErrors();
  formProgram.nama_program = item.nama_program;
  formProgram.tipe_potongan = item.tipe_potongan;
  formProgram.nilai_potongan = item.nilai_potongan;
  formProgram.syarat_ketentuan = item.syarat_ketentuan || '';
  formProgram.aktif = Boolean(item.aktif);
  isProgramModalOpen.value = true;
};

const closeProgramModal = () => {
  isProgramModalOpen.value = false;
  formProgram.reset();
};

const submitProgram = () => {
  if (isEditingProgram.value) {
    formProgram.put(route('bendahara.keringanan-spp.update', editingProgramId.value), {
      preserveScroll: true,
      onSuccess: () => closeProgramModal(),
    });
  } else {
    formProgram.post(route('bendahara.keringanan-spp.store'), {
      preserveScroll: true,
      onSuccess: () => closeProgramModal(),
    });
  }
};

const deleteProgram = (id) => {
  if (confirm('Yakin ingin menghapus program keringanan ini?')) {
    router.delete(route('bendahara.keringanan-spp.destroy', id), { preserveScroll: true });
  }
};

// --- MODAL PENETAPAN SISWA ---
const isSiswaModalOpen = ref(false);
const selectedSiswa = ref(null);

const formTetapkan = useForm({
  siswa_id: null,
  keringanan_spp_id: '',
  tahun_ajaran: props.tahunAjaranAktif,
  nomor_sk: '',
  aktif: true,
});

const openTetapkanModal = (siswa) => {
  selectedSiswa.value = siswa;
  const keringananSaatIni = siswa.keringanan && siswa.keringanan.length > 0 ? siswa.keringanan[0] : null;

  formTetapkan.clearErrors();
  formTetapkan.siswa_id = siswa.id;
  formTetapkan.tahun_ajaran = tahunAjaranSelect.value;
  formTetapkan.keringanan_spp_id = keringananSaatIni ? keringananSaatIni.keringanan_spp_id : '';
  formTetapkan.nomor_sk = keringananSaatIni ? keringananSaatIni.nomor_sk || '' : '';
  formTetapkan.aktif = keringananSaatIni ? Boolean(keringananSaatIni.aktif) : true;

  isSiswaModalOpen.value = true;
};

const closeSiswaModal = () => {
  isSiswaModalOpen.value = false;
  selectedSiswa.value = null;
  formTetapkan.reset();
};

const submitTetapkan = () => {
  formTetapkan.post(route('bendahara.keringanan-spp.tetapkan-siswa'), {
    preserveScroll: true,
    onSuccess: () => closeSiswaModal(),
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <Head title="Keringanan SPP - Bendahara" />

  <AuthenticatedLayout>
    <div class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Program & Penetapan Keringanan SPP</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola jenis bantuan beasiswa dan tetapkan Surat Keputusan (SK) penerima.</p>
          </div>

          <!-- Navigasi Tab -->
          <div class="flex rounded-xl bg-gray-100 p-1">
            <button
              @click="activeTab = 'program'"
              class="px-4 py-2 text-xs font-semibold rounded-lg transition"
              :class="activeTab === 'program' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'"
            >
              Master Program
            </button>
            <button
              @click="activeTab = 'penetapan'"
              class="px-4 py-2 text-xs font-semibold rounded-lg transition"
              :class="activeTab === 'penetapan' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900'"
            >
              Penetapan Siswa
            </button>
          </div>
        </div>

        <!-- ================= TAB 1: MASTER PROGRAM ================= -->
        <div v-if="activeTab === 'program'" class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-xs font-medium text-gray-500">Daftar skema keringanan aktif & non-aktif</span>
            <button
              @click="openCreateProgram"
              class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition"
            >
              + Tambah Program Keringanan
            </button>
          </div>

          <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
              <thead class="bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                <tr>
                  <th class="px-6 py-4 text-left">Nama Program</th>
                  <th class="px-6 py-4 text-left">Bentuk Potongan</th>
                  <th class="px-6 py-4 text-left">Nilai Potongan</th>
                  <th class="px-6 py-4 text-left">Penerima Aktif</th>
                  <th class="px-6 py-4 text-left">Status</th>
                  <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in programList" :key="item.id" class="hover:bg-gray-50/60">
                  <td class="px-6 py-4 font-semibold text-gray-900">
                    {{ item.nama_program }}
                    <p class="text-xs font-normal text-gray-400 mt-0.5">{{ item.syarat_ketentuan || 'Tidak ada catatan syarat.' }}</p>
                  </td>
                  <td class="px-6 py-4">
                    <span class="capitalize text-xs font-medium text-gray-700">
                      {{ item.tipe_potongan === 'persentase' ? 'Persentase (%)' : 'Nominal Tetap (Rp)' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 font-semibold text-emerald-600">
                    {{ item.tipe_potongan === 'persentase' ? `${item.nilai_potongan}%` : formatRupiah(item.nilai_potongan) }}
                  </td>
                  <td class="px-6 py-4 text-xs font-medium text-gray-600">
                    {{ item.penerima_count || 0 }} Siswa
                  </td>
                  <td class="px-6 py-4">
                    <span
                      class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                      :class="item.aktif ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-gray-100 text-gray-500'"
                    >
                      {{ item.aktif ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right space-x-3 text-xs font-medium">
                    <button @click="openEditProgram(item)" class="text-amber-600 hover:text-amber-800">Ubah</button>
                    <button @click="deleteProgram(item.id)" class="text-rose-600 hover:text-rose-800">Hapus</button>
                  </td>
                </tr>
                <tr v-if="programList.length === 0">
                  <td colspan="6" class="px-6 py-8 text-center text-xs text-gray-400">Belum ada master program keringanan.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ================= TAB 2: PENETAPAN SISWA ================= -->
        <div v-if="activeTab === 'penetapan'" class="space-y-4">
          <!-- Filter Bar -->
          <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <label class="text-xs font-bold uppercase text-gray-500">Tahun Ajaran:</label>
                <select
                  v-model="tahunAjaranSelect"
                  @change="applyFilterSiswa"
                  class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option v-for="ta in listTahunAjaran" :key="ta" :value="ta">{{ ta }}</option>
                </select>
              </div>

              <div class="flex items-center gap-2">
                <label class="text-xs font-bold uppercase text-gray-500">Kelas:</label>
                <select
                  v-model="kelasFilter"
                  @change="applyFilterSiswa"
                  class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">Semua Kelas</option>
                  <option v-for="k in listKelas" :key="k.id" :value="k.id">
                    {{ k.tingkat }} - {{ k.nama_kelas }}
                  </option>
                </select>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <input
                type="text"
                v-model="searchSiswa"
                @keyup.enter="applyFilterSiswa"
                placeholder="Cari Nama / NIS..."
                class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <button
                @click="applyFilterSiswa"
                class="rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200"
              >
                Cari
              </button>
            </div>
          </div>

          <!-- Tabel Siswa -->
          <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
              <thead class="bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                <tr>
                  <th class="px-6 py-4 text-left">Siswa</th>
                  <th class="px-6 py-4 text-left">Kelas</th>
                  <th class="px-6 py-4 text-left">Program Keringanan</th>
                  <th class="px-6 py-4 text-left">No. SK Bantuan</th>
                  <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="s in daftarSiswa.data" :key="s.id" class="hover:bg-gray-50/60">
                  <td class="px-6 py-4 font-semibold text-gray-900">
                    {{ s.nama_lengkap }}
                    <p class="text-xs font-normal text-gray-400">NIS: {{ s.nis }}</p>
                  </td>
                  <td class="px-6 py-4 text-gray-600">
                    {{ s.kelas ? `${s.kelas.tingkat} - ${s.kelas.nama_kelas}` : '-' }}
                  </td>
                  <td class="px-6 py-4">
                    <span
                      v-if="s.keringanan && s.keringanan.length > 0"
                      class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/20"
                    >
                      {{ s.keringanan[0].keringanan?.nama_program }}
                    </span>
                    <span v-else class="text-xs text-gray-400 italic">Tanpa Keringanan (Reguler)</span>
                  </td>
                  <td class="px-6 py-4 text-xs text-gray-600">
                    {{ s.keringanan && s.keringanan.length > 0 ? (s.keringanan[0].nomor_sk || '-') : '-' }}
                  </td>
                  <td class="px-6 py-4 text-right">
                    <button
                      @click="openTetapkanModal(s)"
                      class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-100"
                    >
                      Atur SK Keringanan
                    </button>
                  </td>
                </tr>
                <tr v-if="daftarSiswa.data.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-xs text-gray-400">Tidak ada data siswa ditemukan.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form Program Keringanan -->
    <div v-if="isProgramModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden animate-fadeIn">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
          <h3 class="text-base font-semibold text-gray-900">
            {{ isEditingProgram ? 'Ubah Program Keringanan' : 'Tambah Program Keringanan' }}
          </h3>
          <button @click="closeProgramModal" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>

        <form @submit.prevent="submitProgram" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Nama Program</label>
            <input
              type="text"
              v-model="formProgram.nama_program"
              placeholder="Contoh: Beasiswa SKTM / Anak Yatim"
              class="w-full text-sm rounded-lg border-gray-300"
            />
            <p v-if="formProgram.errors.nama_program" class="text-xs text-rose-600 mt-1">{{ formProgram.errors.nama_program }}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Tipe Potongan</label>
              <select v-model="formProgram.tipe_potongan" class="w-full text-sm rounded-lg border-gray-300">
                <option value="nominal">Nominal (Rp)</option>
                <option value="persentase">Persentase (%)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Nilai Potongan</label>
              <input
                type="number"
                v-model="formProgram.nilai_potongan"
                :placeholder="formProgram.tipe_potongan === 'persentase' ? 'Misal: 50' : 'Misal: 100000'"
                class="w-full text-sm rounded-lg border-gray-300"
              />
            </div>
          </div>
          <p v-if="formProgram.errors.nilai_potongan" class="text-xs text-rose-600 mt-1">{{ formProgram.errors.nilai_potongan }}</p>

          <div>
            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Syarat & Ketentuan</label>
            <textarea v-model="formProgram.syarat_ketentuan" rows="3" class="w-full text-sm rounded-lg border-gray-300"></textarea>
          </div>

          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="formProgram.aktif" class="rounded text-indigo-600" />
            <span class="text-xs font-medium text-gray-700">Program Aktif</span>
          </label>

          <div class="flex justify-end gap-3 pt-3 border-t">
            <button type="button" @click="closeProgramModal" class="px-4 py-2 text-xs font-semibold text-gray-700 border rounded-lg">Batal</button>
            <button type="submit" :disabled="formProgram.processing" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Penetapan Siswa -->
    <div v-if="isSiswaModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4">
      <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden animate-fadeIn">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
          <div>
            <h3 class="text-base font-semibold text-gray-900">Penetapan SK Keringanan Siswa</h3>
            <p class="text-xs text-gray-500">{{ selectedSiswa?.nama_lengkap }} ({{ selectedSiswa?.nis }})</p>
          </div>
          <button @click="closeSiswaModal" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>

        <form @submit.prevent="submitTetapkan" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Pilih Program Keringanan</label>
            <select v-model="formTetapkan.keringanan_spp_id" class="w-full text-sm rounded-lg border-gray-300">
              <option value="">-- Hapus / Reguler (Tanpa Keringanan) --</option>
              <option v-for="prog in programList.filter(p => p.aktif)" :key="prog.id" :value="prog.id">
                {{ prog.nama_program }} (Potongan: {{ prog.tipe_potongan === 'persentase' ? `${prog.nilai_potongan}%` : formatRupiah(prog.nilai_potongan) }})
              </option>
            </select>
            <p v-if="formTetapkan.errors.keringanan_spp_id" class="text-xs text-rose-600 mt-1">{{ formTetapkan.errors.keringanan_spp_id }}</p>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Nomor Surat Keputusan (SK)</label>
            <input
              type="text"
              v-model="formTetapkan.nomor_sk"
              placeholder="Contoh: 421.3/088/SK-BEASISWA/2026"
              class="w-full text-sm rounded-lg border-gray-300"
            />
            <p v-if="formTetapkan.errors.nomor_sk" class="text-xs text-rose-600 mt-1">{{ formTetapkan.errors.nomor_sk }}</p>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Tahun Ajaran Berlaku</label>
            <input
              type="text"
              v-model="formTetapkan.tahun_ajaran"
              readonly
              class="w-full text-sm rounded-lg border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed"
            />
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t">
            <button type="button" @click="closeSiswaModal" class="px-4 py-2 text-xs font-semibold text-gray-700 border rounded-lg">Batal</button>
            <button type="submit" :disabled="formTetapkan.processing" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg">Simpan SK</button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>