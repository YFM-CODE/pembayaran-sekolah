<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  tarif: Object, // Paginator atau list tarif dari controller
  filters: Object,
});

// Normalisasi list data jika menggunakan pagination atau collection biasa
const daftarTarif = computed(() => {
  return props.tarif?.data || props.tarif || [];
});

// State Modal Form (Tambah / Edit)
const isModalOpen = ref(false);
const isEditing = ref(false);
const activeId = ref(null);

const form = useForm({
  tahun_ajaran: '',
  tingkat: '',
  nominal_standar: '',
  keterangan: '',
});

// State Modal Konfirmasi Hapus
const isDeleteModalOpen = ref(false);
const itemToDelete = ref(null);

const openCreateModal = () => {
  isEditing.value = false;
  activeId.value = null;
  form.reset();
  form.clearErrors();
  isModalOpen.value = true;
};

const openEditModal = (item) => {
  // Cegah pembukaan form edit jika sudah disetujui komite
  if (isDisetujui(item)) {
    alert('Tarif ini telah disahkan oleh Berita Acara Komite dan tidak dapat diubah.');
    return;
  }

  isEditing.value = true;
  activeId.value = item.id;
  form.clearErrors();

  form.tahun_ajaran = item.tahun_ajaran;
  form.tingkat = item.tingkat;
  form.nominal_standar = item.nominal_standar;
  form.keterangan = item.keterangan || '';

  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(route('admin.tarif-spp.update', activeId.value), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('admin.tarif-spp.store'), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  }
};

const confirmDelete = (item) => {
  if (isDisetujui(item)) {
    alert('Tarif ini telah disahkan oleh Berita Acara Komite dan tidak dapat dihapus.');
    return;
  }
  itemToDelete.value = item;
  isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false;
  itemToDelete.value = null;
};

const executeDelete = () => {
  if (!itemToDelete.value) return;

  router.delete(route('admin.tarif-spp.destroy', itemToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => closeDeleteModal(),
  });
};

// Pengecekan status persetujuan komite
const isDisetujui = (item) => {
  const status = item.persetujuan_komite?.status_persetujuan || item.persetujuan?.status_persetujuan || item.status_komite;
  return status === 'disetujui';
};

const formatRupiah = (val) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(val || 0);
};
</script>

<template>
  <Head title="Manajemen Tarif SPP" />

  <AuthenticatedLayout>
    <div class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Manajemen Tarif SPP</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola draf usulan tarif SPP sebelum disahkan oleh Komite Sekolah.</p>
          </div>
          <div>
            <button
              type="button"
              @click="openCreateModal"
              class="inline-flex items-center justify-center rounded-xl bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus transition"
            >
              + Tambah Tarif
            </button>
          </div>
        </div>

        <!-- Tabel Data Tarif -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead>
                <tr class="bg-gray-50/75 text-[11px] font-bold uppercase tracking-wider text-gray-400">
                  <th scope="col" class="px-6 py-4 text-left">Tahun Ajaran</th>
                  <th scope="col" class="px-6 py-4 text-left">Tingkat</th>
                  <th scope="col" class="px-6 py-4 text-left">Nominal</th>
                  <th scope="col" class="px-6 py-4 text-left">Status Komite</th>
                  <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm">
                <tr
                  v-for="item in daftarTarif"
                  :key="item.id"
                  class="hover:bg-gray-50/60 transition duration-150"
                >
                  <!-- Tahun Ajaran -->
                  <td class="whitespace-nowrap px-6 py-4 font-semibold text-gray-900">
                    {{ item.tahun_ajaran }}
                  </td>

                  <!-- Tingkat -->
                  <td class="whitespace-nowrap px-6 py-4 text-gray-600">
                    Kelas {{ item.tingkat }}
                  </td>

                  <!-- Nominal -->
                  <td class="whitespace-nowrap px-6 py-4 font-semibold text-emerald-600">
                    {{ formatRupiah(item.nominal_standar) }}
                  </td>

                  <!-- Status Komite -->
                  <td class="whitespace-nowrap px-6 py-4">
                    <span
                      v-if="isDisetujui(item)"
                      class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
                    >
                      Disetujui Komite
                    </span>
                    <span
                      v-else-if="(item.persetujuan_komite?.status_persetujuan || item.persetujuan?.status_persetujuan) === 'ditolak'"
                      class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/20"
                    >
                      Ditolak Komite
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20"
                    >
                      Menunggu Kesepakatan
                    </span>
                  </td>

                  <!-- Aksi (Terkunci jika sudah disetujui) -->
                  <td class="whitespace-nowrap px-6 py-4 text-right text-xs font-medium space-x-3">
                    <template v-if="isDisetujui(item)">
                      <span class="inline-flex items-center text-gray-400 italic cursor-not-allowed select-none" title="Tarif sah tidak dapat diubah atau dihapus">
                        🔒 Terkunci (Sah)
                      </span>
                    </template>
                    <template v-else>
                      <button
                        type="button"
                        @click="openEditModal(item)"
                        class="text-amber-600 hover:text-amber-800 transition"
                      >
                        Ubah
                      </button>
                      <button
                        type="button"
                        @click="confirmDelete(item)"
                        class="text-rose-600 hover:text-rose-800 transition"
                      >
                        Hapus
                      </button>
                    </template>
                  </td>
                </tr>

                <tr v-if="daftarTarif.length === 0">
                  <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">
                    Belum ada data tarif SPP yang ditambahkan.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form (Tambah / Edit Tarif) -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 overflow-y-auto"
    >
      <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden animate-fadeIn">
        <div class="px-6 py-4 bg-gray-50/75 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-base font-semibold text-gray-900">
            {{ isEditing ? 'Ubah Tarif SPP' : 'Tambah Tarif SPP Baru' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>

        <form @submit.prevent="submitForm" class="p-6 space-y-4">
          <!-- Tahun Ajaran -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Tahun Ajaran</label>
            <input
              type="text"
              v-model="form.tahun_ajaran"
              placeholder="Contoh: 2026/2027"
              class="w-full text-sm rounded-lg border-brand-border shadow-xs focus:border-brand focus:ring-brand-focus"
            />
            <p v-if="form.errors.tahun_ajaran" class="mt-1 text-xs text-rose-600">{{ form.errors.tahun_ajaran }}</p>
          </div>

          <!-- Tingkat Kelas -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Tingkat Kelas</label>
            <select
              v-model="form.tingkat"
              class="w-full text-sm rounded-lg border-brand-border shadow-xs focus:border-brand focus:ring-brand-focus"
            >
              <option value="" disabled>Pilih Tingkat</option>
              <option value="X">Kelas X</option>
              <option value="XI">Kelas XI</option>
              <option value="XII">Kelas XII</option>
            </select>
            <p v-if="form.errors.tingkat" class="mt-1 text-xs text-rose-600">{{ form.errors.tingkat }}</p>
          </div>

          <!-- Nominal Standar -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Nominal Standar (Rp)</label>
            <input
              type="number"
              v-model="form.nominal_standar"
              placeholder="Contoh: 250000"
              min="0"
              step="1000"
              class="w-full text-sm rounded-lg border-brand-border shadow-xs focus:border-brand focus:ring-brand-focus"
            />
            <p v-if="form.errors.nominal_standar" class="mt-1 text-xs text-rose-600">{{ form.errors.nominal_standar }}</p>
          </div>

          <!-- Keterangan -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Keterangan (Opsional)</label>
            <textarea
              v-model="form.keterangan"
              rows="3"
              placeholder="Catatan tambahan tarif..."
              class="w-full text-sm rounded-lg border-brand-border shadow-xs focus:border-brand focus:ring-brand-focus"
            ></textarea>
            <p v-if="form.errors.keterangan" class="mt-1 text-xs text-rose-600">{{ form.errors.keterangan }}</p>
          </div>

          <div class="mt-6 flex justify-end gap-3 pt-3 border-t border-gray-100">
            <button
              type="button"
              @click="closeModal"
              class="rounded-xl px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl px-4 py-2 text-xs font-semibold text-white bg-brand hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus transition disabled:opacity-50"
            >
              {{ isEditing ? 'Simpan Perubahan' : 'Tambah Tarif' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div
      v-if="isDeleteModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
    >
      <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl text-center space-y-4">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600 text-xl font-bold">
          !
        </div>
        <div>
          <h3 class="text-base font-bold text-gray-900">Hapus Usulan Tarif?</h3>
          <p class="text-xs text-gray-500 mt-1">
            Yakin ingin menghapus tarif untuk Kelas {{ itemToDelete?.tingkat }} ({{ itemToDelete?.tahun_ajaran }})? Tindakan ini tidak dapat dibatalkan.
          </p>
        </div>
        <div class="flex justify-center gap-3 pt-2">
          <button
            type="button"
            @click="closeDeleteModal"
            class="rounded-xl px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition"
          >
            Batal
          </button>
          <button
            type="button"
            @click="executeDelete"
            class="rounded-xl px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 transition"
          >
            Ya, Hapus
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>