<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tarif: Object,
    filters: Object,
});

const daftarTarif = computed(() => {
    return props.tarif?.data || props.tarif || [];
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const activeId = ref(null);

const form = useForm({
    tahun_ajaran: '',
    tingkat: '',
    nominal_standar: '',
    keterangan: '',
});

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
    <AuthenticatedLayout>
        <Head title="Manajemen Tarif SPP" />

        <template #header>
            <div class="page-head">
                <div>
                    <span class="page-head-eyebrow">Master Keuangan</span>
                    <h1 class="page-head-title">Skema Tarif SPP</h1>
                    <p class="page-head-sub">
                        Kelola usulan nominal tarif SPP per tingkat kelas dan tahun ajaran sebelum disahkan oleh Komite Sekolah.
                    </p>
                </div>
                <div class="page-head-actions">
                    <button type="button" class="btn-brand" @click="openCreateModal">
                        + Tambah Tarif SPP
                    </button>
                </div>
            </div>
        </template>

        <div class="admin-page">
            <div class="admin-wrap">
                <div class="table-card">
                    <div class="table-scroll">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>Tahun Ajaran</th>
                                    <th>Tingkat Kelas</th>
                                    <th>Nominal Standar</th>
                                    <th>Keterangan</th>
                                    <th>Status Pengesahan Komite</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in daftarTarif" :key="item.id">
                                    <td>
                                        <strong class="font-bold text-ink">{{ item.tahun_ajaran }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge-pill badge-neutral">Kelas {{ item.tingkat }}</span>
                                    </td>
                                    <td>
                                        <strong class="font-bold tabular-nums text-brand">
                                            {{ formatRupiah(item.nominal_standar) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="text-xs text-ink-muted">{{ item.keterangan || 'Tarif reguler bulanan' }}</span>
                                    </td>
                                    <td>
                                        <span v-if="isDisetujui(item)" class="badge-pill badge-success">
                                            <span class="badge-dot"></span>
                                            Disetujui Komite
                                        </span>
                                        <span
                                            v-else-if="(item.persetujuan_komite?.status_persetujuan || item.persetujuan?.status_persetujuan) === 'ditolak'"
                                            class="badge-pill badge-danger"
                                        >
                                            <span class="badge-dot"></span>
                                            Ditolak Komite
                                        </span>
                                        <span v-else class="badge-pill badge-warning">
                                            <span class="badge-dot"></span>
                                            Menunggu Kesepakatan
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div v-if="isDisetujui(item)" class="row-actions">
                                            <span class="badge-pill badge-neutral" title="Tarif sah tidak dapat diubah atau dihapus">
                                                Terkunci (Sah)
                                            </span>
                                        </div>
                                        <div v-else class="row-actions">
                                            <button type="button" class="act-btn act-btn-warn" @click="openEditModal(item)">
                                                Ubah
                                            </button>
                                            <button type="button" class="act-btn act-btn-danger" @click="confirmDelete(item)">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="daftarTarif.length === 0">
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <p class="empty-state-title">Belum Ada Skema Tarif SPP</p>
                                            <p class="empty-state-sub">Tambahkan usulan tarif SPP baru untuk tahun ajaran berjalan.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="props.tarif?.links?.length > 3" class="pagination-bar">
                        <span class="text-xs text-ink-muted">
                            Menampilkan {{ daftarTarif.length }} skema tarif
                        </span>
                        <div class="pagination-links">
                            <Link
                                v-for="(link, i) in props.tarif.links"
                                :key="i"
                                :href="link.url || '#'"
                                class="page-link"
                                :class="{ active: link.active, disabled: !link.url }"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form (Tambah / Edit Tarif) -->
        <div v-if="isModalOpen" class="modal-backdrop" @click.self="closeModal">
            <div class="modal-box">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">
                            {{ isEditing ? 'Ubah Skema Tarif SPP' : 'Tambah Tarif SPP Baru' }}
                        </h3>
                        <p class="modal-subtitle">Tentukan nominal standar per bulan untuk tingkat kelas terkait.</p>
                    </div>
                    <button type="button" class="modal-close" aria-label="Tutup modal" @click="closeModal">&times;</button>
                </div>

                <form @submit.prevent="submitForm" class="modal-body">
                    <div>
                        <label class="ui-label">Tahun Ajaran</label>
                        <input
                            v-model="form.tahun_ajaran"
                            type="text"
                            placeholder="Contoh: 2026/2027"
                            class="ui-input"
                            required
                        />
                        <p v-if="form.errors.tahun_ajaran" class="ui-error">{{ form.errors.tahun_ajaran }}</p>
                    </div>

                    <div>
                        <label class="ui-label">Tingkat Kelas</label>
                        <select v-model="form.tingkat" class="ui-select" required>
                            <option value="" disabled>Pilih Tingkat</option>
                            <option value="X">Kelas X (Sepuluh)</option>
                            <option value="XI">Kelas XI (Sebelas)</option>
                            <option value="XII">Kelas XII (Dua Belas)</option>
                        </select>
                        <p v-if="form.errors.tingkat" class="ui-error">{{ form.errors.tingkat }}</p>
                    </div>

                    <div>
                        <label class="ui-label">Nominal Standar (Rp)</label>
                        <input
                            v-model="form.nominal_standar"
                            type="number"
                            placeholder="Contoh: 250000"
                            min="0"
                            step="1000"
                            class="ui-input"
                            required
                        />
                        <p v-if="form.errors.nominal_standar" class="ui-error">{{ form.errors.nominal_standar }}</p>
                    </div>

                    <div>
                        <label class="ui-label">Keterangan (Opsional)</label>
                        <textarea
                            v-model="form.keterangan"
                            rows="3"
                            placeholder="Catatan tambahan mengenai skema tarif..."
                            class="ui-textarea"
                        ></textarea>
                        <p v-if="form.errors.keterangan" class="ui-error">{{ form.errors.keterangan }}</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary-ui" @click="closeModal">Batal</button>
                        <button type="submit" :disabled="form.processing" class="btn-brand">
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Tarif') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus -->
        <div v-if="isDeleteModalOpen" class="modal-backdrop" @click.self="closeDeleteModal">
            <div class="modal-box">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">Hapus Usulan Tarif SPP?</h3>
                        <p class="modal-subtitle">Konfirmasi penghapusan draf skema tarif.</p>
                    </div>
                    <button type="button" class="modal-close" aria-label="Tutup modal" @click="closeDeleteModal">&times;</button>
                </div>

                <div class="modal-body">
                    <p class="text-sm text-ink-muted m-0">
                        Apakah Anda yakin ingin menghapus tarif SPP untuk
                        <strong class="text-ink">Kelas {{ itemToDelete?.tingkat }} ({{ itemToDelete?.tahun_ajaran }})</strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </p>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary-ui" @click="closeDeleteModal">Batal</button>
                        <button type="button" class="btn-danger-ui" @click="executeDelete">Ya, Hapus Tarif</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>