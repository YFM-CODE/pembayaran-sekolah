<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');

const handleFilter = () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        role: roleFilter.value,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    search.value = '';
    roleFilter.value = '';
    handleFilter();
};

const toggleStatus = (id) => {
    if (confirm('Ubah status aktif pengguna ini?')) {
        router.patch(route('admin.users.toggle-status', id));
    }
};

const deleteUser = (user) => {
    if (confirm(`Yakin ingin menghapus pengguna "${user.name}"? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

const resetModal = ref(false);
const selectedUser = ref(null);
const resetForm = useForm({
    password: '',
    password_confirmation: '',
});

const openResetModal = (user) => {
    selectedUser.value = user;
    resetForm.reset();
    resetForm.clearErrors();
    resetModal.value = true;
};

const submitReset = () => {
    resetForm.post(route('admin.users.reset-password', selectedUser.value.id), {
        onSuccess: () => {
            resetModal.value = false;
            resetForm.reset();
        },
    });
};

const roleLabel = (role) => {
    const map = {
        admin: 'Administrator',
        bendahara: 'Bendahara',
        kepsek: 'Kepala Sekolah',
        siswa: 'Siswa / Wali',
    };
    return map[role] || role;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manajemen Pengguna" />

        <template #header>
            <div class="page-head">
                <div>
                    <div class="page-head-eyebrow">Administrasi Sistem</div>
                    <h2 class="page-head-title">Manajemen Pengguna</h2>
                    <p class="page-head-sub">Kelola akun akses portal untuk Administrator, Bendahara, Kepala Sekolah, dan Siswa.</p>
                </div>
                <div class="page-head-actions">
                    <Link :href="route('admin.users.create')" class="btn-brand">
                        + Tambah Pengguna
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
                            placeholder="Cari nama pengguna atau alamat email..."
                            class="ui-input"
                        />
                    </div>
                    <div class="filter-select">
                        <select
                            v-model="roleFilter"
                            @change="handleFilter"
                            class="ui-select"
                        >
                            <option value="">Semua Peran Akun</option>
                            <option v-for="role in roles" :key="role" :value="role">
                                {{ roleLabel(role) }}
                            </option>
                        </select>
                    </div>
                    <button
                        v-if="search || roleFilter"
                        type="button"
                        @click="resetFilter"
                        class="btn-secondary-ui"
                    >
                        Reset
                    </button>
                </div>

                <!-- Tabel Pengguna -->
                <div class="table-card">
                    <div class="table-scroll">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>Nama Pengguna</th>
                                    <th>Email Login</th>
                                    <th>Peran</th>
                                    <th>Status Akun</th>
                                    <th class="text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id">
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-ink);">{{ user.name }}</div>
                                    </td>
                                    <td style="color: var(--color-muted);">{{ user.email }}</td>
                                    <td>
                                        <span class="badge-pill badge-info">
                                            {{ roleLabel(user.role) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge-pill"
                                            :class="user.is_active ? 'badge-success' : 'badge-danger'"
                                        >
                                            <span class="badge-dot"></span>
                                            {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <button
                                                type="button"
                                                @click="toggleStatus(user.id)"
                                                class="act-btn act-btn-warn"
                                            >
                                                {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                            <button
                                                type="button"
                                                @click="openResetModal(user)"
                                                class="act-btn"
                                            >
                                                Reset Sandi
                                            </button>
                                            <Link
                                                :href="route('admin.users.edit', user.id)"
                                                class="act-btn act-btn-brand"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                v-if="user.id !== $page.props.auth.user.id"
                                                type="button"
                                                @click="deleteUser(user)"
                                                class="act-btn act-btn-danger"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!users.data || users.data.length === 0">
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-state-title">Tidak Ada Pengguna Ditemukan</div>
                                            <p class="empty-state-sub">Coba sesuaikan kata kunci pencarian atau tambah pengguna baru.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.links?.length > 3" class="pagination-bar">
                        <div class="pagination-info">
                            Menampilkan {{ users.from || 0 }}–{{ users.to || 0 }} dari {{ users.total || 0 }} pengguna
                        </div>
                        <div class="pagination-links">
                            <template v-for="(link, i) in users.links" :key="i">
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

        <!-- Modal Reset Kata Sandi -->
        <div v-if="resetModal" class="modal-backdrop" @click.self="resetModal = false">
            <div class="modal-box">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">Reset Kata Sandi</h3>
                        <p class="modal-subtitle">Atur ulang kata sandi untuk akun <strong>{{ selectedUser?.name }}</strong></p>
                    </div>
                    <button type="button" @click="resetModal = false" class="modal-close">&times;</button>
                </div>

                <form @submit.prevent="submitReset">
                    <div class="modal-body">
                        <div>
                            <label class="ui-label">Kata Sandi Baru</label>
                            <input
                                v-model="resetForm.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                required
                                class="ui-input"
                            />
                            <p v-if="resetForm.errors.password" class="ui-error">{{ resetForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="ui-label">Konfirmasi Kata Sandi Baru</label>
                            <input
                                v-model="resetForm.password_confirmation"
                                type="password"
                                placeholder="Ulangi kata sandi baru"
                                required
                                class="ui-input"
                            />
                            <p v-if="resetForm.errors.password_confirmation" class="ui-error">{{ resetForm.errors.password_confirmation }}</p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" @click="resetModal = false" class="btn-secondary-ui">
                            Batal
                        </button>
                        <button type="submit" :disabled="resetForm.processing" class="btn-brand">
                            {{ resetForm.processing ? 'Menyimpan...' : 'Simpan Sandi Baru' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>