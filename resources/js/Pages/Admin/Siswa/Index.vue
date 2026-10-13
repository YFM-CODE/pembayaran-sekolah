<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    siswaList: Object,
    kelasList: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const kelasFilter = ref(props.filters?.kelas_id || '');
const statusFilter = ref(props.filters?.status || '');

const handleFilter = () => {
    router.get(route('admin.siswa.index'), {
        search: search.value,
        kelas_id: kelasFilter.value,
        status: statusFilter.value,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    search.value = '';
    kelasFilter.value = '';
    statusFilter.value = '';
    handleFilter();
};

const showStatusModal = ref(false);
const selectedSiswa = ref(null);
const statusForm = useForm({
    status_siswa: '',
});

const openStatusModal = (siswa) => {
    selectedSiswa.value = siswa;
    statusForm.status_siswa = siswa.status_siswa || siswa.status || 'aktif';
    statusForm.clearErrors();
    showStatusModal.value = true;
};

const submitUpdateStatus = () => {
    statusForm.patch(route('admin.siswa.update-status', selectedSiswa.value.id), {
        onSuccess: () => {
            showStatusModal.value = false;
        },
    });
};

const deleteSiswa = (id) => {
    if (confirm('Hapus data siswa ini?')) {
        router.delete(route('admin.siswa.destroy', id));
    }
};

const statusBadgeClass = (status) => {
    const val = status || 'aktif';
    if (val === 'aktif') return 'badge-success';
    if (val === 'lulus') return 'badge-info';
    if (val === 'pindah') return 'badge-warning';
    if (val === 'keluar') return 'badge-danger';
    return 'badge-neutral';
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Data Siswa" />

        <template #header>
            <div class="page-head">
                <div>
                    <div class="page-head-eyebrow">Data Akademik</div>
                    <h2 class="page-head-title">Master Data Siswa</h2>
                    <p class="page-head-sub">Kelola profil akademik siswa, penempatan kelas, status keaktifan, dan tautan akun portal SPP.</p>
                </div>
                <div class="page-head-actions">
                    <Link :href="route('admin.siswa.create')" class="btn-brand">
                        + Tambah Siswa
                    </Link>
                </div>
            </div>
        </template>

        <div class="admin-page">
            <div class="admin-wrap">
                <!-- Filter Bar -->
                <div class="filter-card">
                    <div class="filter-search">
                        <input
                            v-model="search"
                            @input="handleFilter"
                            type="text"
                            placeholder="Cari NIS, NISN, atau nama lengkap siswa..."
                            class="ui-input"
                        />
                    </div>
                    <div class="filter-select">
                        <select
                            v-model="kelasFilter"
                            @change="handleFilter"
                            class="ui-select"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                        </select>
                    </div>
                    <div class="filter-select">
                        <select
                            v-model="statusFilter"
                            @change="handleFilter"
                            class="ui-select"
                        >
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="lulus">Lulus</option>
                            <option value="pindah">Pindah</option>
                            <option value="keluar">Keluar</option>
                        </select>
                    </div>
                    <button
                        v-if="search || kelasFilter || statusFilter"
                        type="button"
                        @click="resetFilter"
                        class="btn-secondary-ui"
                    >
                        Reset
                    </button>
                </div>

                <!-- Tabel Siswa -->
                <div class="table-card">
                    <div class="table-scroll">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>NIS / NISN</th>
                                    <th>Nama Lengkap</th>
                                    <th>Kelas</th>
                                    <th>Akun Portal</th>
                                    <th>Status Siswa</th>
                                    <th class="text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="siswa in siswaList.data" :key="siswa.id">
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-ink);">{{ siswa.nis }}</div>
                                        <div style="font-size: 0.75rem; color: var(--color-muted);">NISN: {{ siswa.nisn }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-ink);">{{ siswa.nama_lengkap }}</div>
                                    </td>
                                    <td>
                                        <span class="badge-pill badge-neutral">
                                            {{ siswa.kelas?.nama_kelas || '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span v-if="siswa.user" class="badge-pill badge-info" style="text-transform: none;">
                                            {{ siswa.user.email }}
                                        </span>
                                        <span v-else style="font-size: 0.78125rem; color: var(--color-muted); font-style: italic;">
                                            Belum Terhubung
                                        </span>
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            @click="openStatusModal(siswa)"
                                            class="badge-pill"
                                            :class="statusBadgeClass(siswa.status_siswa || siswa.status)"
                                            style="cursor: pointer;"
                                            title="Klik untuk mengubah status siswa"
                                        >
                                            <span class="badge-dot"></span>
                                            {{ siswa.status_siswa || siswa.status || 'aktif' }}
                                        </button>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <button
                                                type="button"
                                                @click="openStatusModal(siswa)"
                                                class="act-btn act-btn-warn"
                                            >
                                                Ubah Status
                                            </button>
                                            <Link
                                                :href="route('admin.siswa.edit', siswa.id)"
                                                class="act-btn act-btn-brand"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                type="button"
                                                @click="deleteSiswa(siswa.id)"
                                                class="act-btn act-btn-danger"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!siswaList.data || siswaList.data.length === 0">
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-title">Belum Ada Data Siswa</div>
                                            <p class="empty-state-sub">Silakan tambahkan siswa baru atau sesuaikan kata kunci dan filter pencarian.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="siswaList.links?.length > 3" class="pagination-bar">
                        <div class="pagination-info">
                            Menampilkan {{ siswaList.from || 0 }}–{{ siswaList.to || 0 }} dari {{ siswaList.total || 0 }} siswa
                        </div>
                        <div class="pagination-links">
                            <template v-for="(link, i) in siswaList.links" :key="i">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="page-link"
                                    :class="{ 'is-active': link.active }"
                                    v-html="link.label"
                                />
                                <span
                                    v-else
                                    class="page-link is-disabled"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ubah Status Siswa -->
        <div v-if="showStatusModal" class="modal-backdrop" @click.self="showStatusModal = false">
            <div class="modal-box" style="max-width: 26rem;">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">Ubah Status Siswa</h3>
                        <p class="modal-subtitle">
                            {{ selectedSiswa?.nama_lengkap }} &bull; NIS {{ selectedSiswa?.nis }}
                        </p>
                    </div>
                    <button type="button" @click="showStatusModal = false" class="modal-close">&times;</button>
                </div>

                <form @submit.prevent="submitUpdateStatus">
                    <div class="modal-body">
                        <div>
                            <label class="ui-label">Pilih Status Keaktifan</label>
                            <select
                                v-model="statusForm.status_siswa"
                                class="ui-select"
                                required
                            >
                                <option value="aktif">Aktif</option>
                                <option value="lulus">Lulus</option>
                                <option value="pindah">Pindah</option>
                                <option value="keluar">Keluar</option>
                            </select>
                            <div v-if="statusForm.errors.status_siswa" class="ui-error">
                                {{ statusForm.errors.status_siswa }}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            @click="showStatusModal = false"
                            class="btn-secondary-ui"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="statusForm.processing"
                            class="btn-brand"
                        >
                            {{ statusForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>