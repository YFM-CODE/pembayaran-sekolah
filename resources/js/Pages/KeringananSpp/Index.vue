<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  programList: {
    type: Array,
    default: () => [],
  },
  daftarSiswa: {
    type: Object,
    default: () => ({ data: [] }),
  },
  listKelas: {
    type: Array,
    default: () => [],
  },
  listTahunAjaran: {
    type: Array,
    default: () => [],
  },
  tahunAjaranAktif: {
    type: String,
    default: '2026/2027',
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const page = usePage();
const activeTab = ref('program'); // 'program' | 'penetapan'

const routePrefix = computed(() => {
  const role = page.props.auth?.user?.role;
  return role === 'admin' ? 'admin' : 'bendahara';
});

// Filter Siswa
const searchSiswa = ref(props.filters?.search || '');
const kelasFilter = ref(props.filters?.kelas_id || '');
const tahunAjaranSelect = ref(props.tahunAjaranAktif);

const applyFilterSiswa = () => {
  router.get(
    route(`${routePrefix.value}.keringanan-spp.index`),
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
    formProgram.put(route(`${routePrefix.value}.keringanan-spp.update`, editingProgramId.value), {
      preserveScroll: true,
      onSuccess: () => closeProgramModal(),
    });
  } else {
    formProgram.post(route(`${routePrefix.value}.keringanan-spp.store`), {
      preserveScroll: true,
      onSuccess: () => closeProgramModal(),
    });
  }
};

const deleteProgram = (id) => {
  if (confirm('Yakin ingin menghapus program keringanan ini?')) {
    router.delete(route(`${routePrefix.value}.keringanan-spp.destroy`, id), { preserveScroll: true });
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
  formTetapkan.post(route(`${routePrefix.value}.keringanan-spp.tetapkan-siswa`), {
    preserveScroll: true,
    onSuccess: () => closeSiswaModal(),
  });
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
  <Head title="Program & Penetapan Keringanan SPP" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Program & Penetapan Keringanan SPP</h1>
          <p class="text-xs text-ink-muted sm:text-sm mt-0.5">Kelola skema bantuan beasiswa/keringanan SPP dan tetapkan SK penerima per siswa.</p>
        </div>

        <!-- Navigasi Tab -->
        <div class="flex rounded-xl border border-brand-border bg-white p-1 shadow-2xs">
          <button
            type="button"
            @click="activeTab = 'program'"
            class="rounded-lg px-4 py-2 text-xs font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
            :class="activeTab === 'program' ? 'bg-brand text-white shadow-xs' : 'text-ink-muted hover:bg-page hover:text-ink'"
          >
            Master Program ({{ programList.length }})
          </button>
          <button
            type="button"
            @click="activeTab = 'penetapan'"
            class="rounded-lg px-4 py-2 text-xs font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
            :class="activeTab === 'penetapan' ? 'bg-brand text-white shadow-xs' : 'text-ink-muted hover:bg-page hover:text-ink'"
          >
            Penetapan Siswa
          </button>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- ================= TAB 1: MASTER PROGRAM ================= -->
      <section v-if="activeTab === 'program'" class="space-y-4" aria-label="Master Program Keringanan">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <span class="text-xs font-medium text-ink-muted">Daftar skema keringanan aktif & non-aktif yang terdaftar di sistem</span>
          <button
            v-if="['admin', 'bendahara'].includes(page.props.auth?.user?.role)"
            @click="openCreateProgram"
            class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Program Keringanan</span>
          </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-brand-border bg-white shadow-xs">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
              <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
                <tr>
                  <th scope="col" class="px-5 py-3 font-semibold">Nama Program</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Bentuk Potongan</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Nilai Potongan</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Penerima Aktif</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                  <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-brand-border">
                <tr
                  v-for="item in programList"
                  :key="item.id"
                  class="odd:bg-white even:bg-page/50 transition-colors hover:bg-brand-soft/40"
                >
                  <td class="whitespace-nowrap px-5 py-3.5 font-semibold text-ink">
                    {{ item.nama_program }}
                    <p class="text-xs font-normal text-ink-muted mt-0.5">{{ item.syarat_ketentuan || 'Tidak ada catatan syarat.' }}</p>
                  </td>
                  <td class="whitespace-nowrap px-5 py-3.5">
                    <span class="capitalize text-xs font-medium text-ink">
                      {{ item.tipe_potongan === 'persentase' ? 'Persentase (%)' : 'Nominal Tetap (Rp)' }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-5 py-3.5 font-bold tabular-nums text-status-success">
                    {{ item.tipe_potongan === 'persentase' ? `${item.nilai_potongan}%` : formatRupiah(item.nilai_potongan) }}
                  </td>
                  <td class="whitespace-nowrap px-5 py-3.5 text-xs font-medium text-ink-muted">
                    {{ item.penerima_count || 0 }} Siswa
                  </td>
                  <td class="whitespace-nowrap px-5 py-3.5">
                    <span
                      class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                      :class="item.aktif ? 'bg-status-success/15 text-status-success' : 'bg-page text-ink-muted border border-brand-border'"
                    >
                      {{ item.aktif ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                  </td>
                  <td class="whitespace-nowrap px-5 py-3.5 text-right space-x-3 text-xs font-semibold">
                    <button
                      @click="openEditProgram(item)"
                      class="text-brand hover:text-brand-hover hover:underline"
                    >
                      Ubah
                    </button>
                    <button
                      @click="deleteProgram(item.id)"
                      class="text-status-danger hover:underline"
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
                <tr v-if="programList.length === 0">
                  <td colspan="6" class="px-5 py-8 text-center text-ink-muted">
                    Belum ada master program keringanan yang tercatat.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ================= TAB 2: PENETAPAN SISWA ================= -->
      <section v-if="activeTab === 'penetapan'" class="space-y-4" aria-label="Penetapan Keringanan Siswa">
        <!-- Filter Bar -->
        <div class="flex flex-col sm:flex-row flex-wrap items-center justify-between gap-4 rounded-xl border border-brand-border bg-white p-4 shadow-xs">
          <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="flex items-center gap-2">
              <label class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Tahun Ajaran:</label>
              <select
                v-model="tahunAjaranSelect"
                @change="applyFilterSiswa"
                class="rounded-lg border border-brand-border bg-white px-3 py-1.5 text-xs text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
              >
                <option v-for="ta in listTahunAjaran" :key="ta" :value="ta">{{ ta }}</option>
              </select>
            </div>

            <div class="flex items-center gap-2">
              <label class="text-xs font-semibold uppercase tracking-wider text-ink-muted">Kelas:</label>
              <select
                v-model="kelasFilter"
                @change="applyFilterSiswa"
                class="rounded-lg border border-brand-border bg-white px-3 py-1.5 text-xs text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
              >
                <option value="">Semua Kelas</option>
                <option v-for="k in listKelas" :key="k.id" :value="k.id">
                  {{ k.tingkat }} - {{ k.nama_kelas }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex items-center gap-2 w-full sm:w-auto">
            <input
              type="search"
              v-model="searchSiswa"
              @keyup.enter="applyFilterSiswa"
              placeholder="Cari Nama / NIS..."
              class="w-full sm:w-64 rounded-lg border border-brand-border bg-white px-3 py-1.5 text-xs text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
            />
            <button
              @click="applyFilterSiswa"
              class="rounded-lg border border-brand-border bg-white px-3 py-1.5 text-xs font-semibold text-ink shadow-2xs hover:bg-brand-soft"
            >
              Cari
            </button>
          </div>
        </div>

        <!-- Tabel Siswa -->
        <div class="overflow-hidden rounded-xl border border-brand-border bg-white shadow-xs">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-brand-border text-sm">
              <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
                <tr>
                  <th scope="col" class="px-5 py-3 font-semibold">Identitas Siswa</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Kelas</th>
                  <th scope="col" class="px-5 py-3 font-semibold">Program Keringanan</th>
                  <th scope="col" class="px-5 py-3 font-semibold">No. SK Bantuan</th>
                  <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-brand-border">
                <tr
                  v-for="s in (daftarSiswa.data || [])"
                  :key="s.id"
                  class="odd:bg-white even:bg-page/50 transition-colors hover:bg-brand-soft/40"
                >
                  <td class="whitespace-nowrap px-5 py-3.5 font-semibold text-ink">
                    {{ s.nama_lengkap }}
                    <p class="text-xs font-normal text-ink-muted">NIS: {{ s.nis }}</p>
                  </td>
                  <td class="whitespace-nowrap px-5 py-3.5 text-ink-muted">
                    {{ s.kelas ? `${s.kelas.tingkat} - ${s.kelas.nama_kelas}` : '-' }}
                  </td>
                  <td class="whitespace-nowrap px-5 py-3.5">
                    <span
                      v-if="s.keringanan && s.keringanan.length > 0"
                      class="inline-flex items-center rounded-full bg-brand-soft px-3 py-1 text-xs font-semibold text-brand"
                    >
                      {{ s.keringanan[0].keringanan?.nama_program }}
                    </span>
                    <span v-else class="text-xs text-ink-muted italic">Tanpa Keringanan (Reguler)</span>
                  </td>
                  <td class="whitespace-nowrap px-5 py-3.5 font-mono text-xs text-ink-muted">
                    {{ s.keringanan && s.keringanan.length > 0 ? (s.keringanan[0].nomor_sk || '-') : '-' }}
                  </td>
                  <td class="whitespace-nowrap px-5 py-3.5 text-right">
                    <button
                      v-if="['admin', 'bendahara'].includes(page.props.auth?.user?.role)"
                      @click="openTetapkanModal(s)"
                      class="inline-flex items-center rounded-lg bg-brand-soft px-3 py-1.5 text-xs font-semibold text-brand transition hover:bg-brand hover:text-white"
                    >
                      Atur SK Keringanan
                    </button>
                  </td>
                </tr>
                <tr v-if="!(daftarSiswa.data && daftarSiswa.data.length)">
                  <td colspan="5" class="px-5 py-8 text-center text-ink-muted">Tidak ada data siswa ditemukan.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal Form Program Keringanan -->
    <div v-if="isProgramModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 backdrop-blur-xs p-4">
      <div class="w-full max-w-md rounded-2xl border border-brand-border bg-white shadow-xl overflow-hidden">
        <div class="px-6 py-4 bg-brand-soft border-b border-brand-border flex justify-between items-center">
          <h3 class="text-base font-bold text-ink">
            {{ isEditingProgram ? 'Ubah Program Keringanan' : 'Tambah Program Keringanan' }}
          </h3>
          <button @click="closeProgramModal" class="text-ink-muted hover:text-ink text-xl leading-none">&times;</button>
        </div>

        <form @submit.prevent="submitProgram" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Nama Program</label>
            <input
              type="text"
              v-model="formProgram.nama_program"
              placeholder="Contoh: Beasiswa SKTM / Anak Yatim"
              required
              class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
            />
            <p v-if="formProgram.errors.nama_program" class="text-xs text-status-danger mt-1">{{ formProgram.errors.nama_program }}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Tipe Potongan</label>
              <select v-model="formProgram.tipe_potongan" class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus">
                <option value="nominal">Nominal (Rp)</option>
                <option value="persentase">Persentase (%)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Nilai Potongan</label>
              <input
                type="number"
                v-model="formProgram.nilai_potongan"
                :placeholder="formProgram.tipe_potongan === 'persentase' ? 'Misal: 50' : 'Misal: 100000'"
                required
                class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
              />
            </div>
          </div>
          <p v-if="formProgram.errors.nilai_potongan" class="text-xs text-status-danger mt-1">{{ formProgram.errors.nilai_potongan }}</p>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Syarat & Ketentuan</label>
            <textarea
              v-model="formProgram.syarat_ketentuan"
              rows="3"
              placeholder="Catatan persyaratan siswa..."
              class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
            ></textarea>
          </div>

          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="formProgram.aktif" class="rounded border-brand-border text-brand focus:ring-brand-focus" />
            <span class="text-xs font-medium text-ink">Program Aktif</span>
          </label>

          <div class="flex justify-end gap-3 pt-3 border-t border-brand-border">
            <button type="button" @click="closeProgramModal" class="rounded-lg border border-brand-border bg-white px-4 py-2 text-xs font-semibold text-ink hover:bg-brand-soft">Batal</button>
            <button type="submit" :disabled="formProgram.processing" class="rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus disabled:opacity-50">
              {{ formProgram.processing ? 'Menyimpan...' : 'Simpan Program' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Penetapan Siswa -->
    <div v-if="isSiswaModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 backdrop-blur-xs p-4">
      <div class="w-full max-w-md rounded-2xl border border-brand-border bg-white shadow-xl overflow-hidden">
        <div class="px-6 py-4 bg-brand-soft border-b border-brand-border flex justify-between items-center">
          <div>
            <h3 class="text-base font-bold text-ink">Penetapan SK Keringanan Siswa</h3>
            <p class="text-xs text-ink-muted">{{ selectedSiswa?.nama_lengkap }} (NIS: {{ selectedSiswa?.nis }})</p>
          </div>
          <button @click="closeSiswaModal" class="text-ink-muted hover:text-ink text-xl leading-none">&times;</button>
        </div>

        <form @submit.prevent="submitTetapkan" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Pilih Program Keringanan</label>
            <select v-model="formTetapkan.keringanan_spp_id" class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus">
              <option value="">-- Hapus / Reguler (Tanpa Keringanan) --</option>
              <option v-for="prog in programList.filter(p => p.aktif)" :key="prog.id" :value="prog.id">
                {{ prog.nama_program }} (Potongan: {{ prog.tipe_potongan === 'persentase' ? `${prog.nilai_potongan}%` : formatRupiah(prog.nilai_potongan) }})
              </option>
            </select>
            <p v-if="formTetapkan.errors.keringanan_spp_id" class="text-xs text-status-danger mt-1">{{ formTetapkan.errors.keringanan_spp_id }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Nomor Surat Keputusan (SK)</label>
            <input
              type="text"
              v-model="formTetapkan.nomor_sk"
              placeholder="Contoh: 421.3/088/SK-BEASISWA/2026"
              class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
            />
            <p v-if="formTetapkan.errors.nomor_sk" class="text-xs text-status-danger mt-1">{{ formTetapkan.errors.nomor_sk }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Tahun Ajaran Berlaku</label>
            <input
              type="text"
              v-model="formTetapkan.tahun_ajaran"
              readonly
              class="w-full rounded-lg border border-brand-border bg-page px-3 py-2 text-sm text-ink-muted cursor-not-allowed shadow-xs"
            />
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-brand-border">
            <button type="button" @click="closeSiswaModal" class="rounded-lg border border-brand-border bg-white px-4 py-2 text-xs font-semibold text-ink hover:bg-brand-soft">Batal</button>
            <button type="submit" :disabled="formTetapkan.processing" class="rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus disabled:opacity-50">
              {{ formTetapkan.processing ? 'Menyimpan...' : 'Simpan SK' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>