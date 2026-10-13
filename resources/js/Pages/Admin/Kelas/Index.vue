<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    kelasList: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const tingkat = ref(props.filters?.tingkat || '');

const handleFilter = () => {
    router.get(route('admin.kelas.index'), {
        search: search.value,
        tingkat: tingkat.value,
    }, { preserveState: true, replace: true });
};

const resetFilter = () => {
    search.value = '';
    tingkat.value = '';
    handleFilter();
};

const deleteKelas = (id) => {
    if (confirm('Yakin ingin menghapus kelas ini?')) {
        router.delete(route('admin.kelas.destroy', id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Data Kelas" />

        <template #header>
            <div class="page-head">
                <div>
                    <div class="page-head-eyebrow">Data Akademik</div>
                    <h2 class="page-head-title">Master Data Kelas</h2>
                    <p class="page-head-sub">Kelola daftar rombongan belajar, tingkat kelas, dan kompetensi keahlian (jurusan).</p>
                </div>
                <div class="page-head-actions">
                    <Link :href="route('admin.kelas.create')" class="btn-brand">
                        + Tambah Kelas
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
                            placeholder="Cari nama kelas atau jurusan..."
                            class="ui-input"
                        />
                    </div>
                    <div class="filter-select">
                        <select
                            v-model="tingkat"
                            @change="handleFilter"
                            class="ui-select"
                        >
                            <option value="">Semua Tingkat Kelas</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                    <button
                        v-if="search || tingkat"
                        type="button"
                        @click="resetFilter"
                        class="btn-secondary-ui"
                    >
                        Reset
                    </button>
                </div>

                <!-- Tabel Kelas -->
                <div class="table-card">
                    <div class="table-scroll">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>Tingkat</th>
                                    <th>Nama Kelas</th>
                                    <th>Jurusan / Kompetensi</th>
                                    <th>Jumlah Siswa</th>
                                    <th class="text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in kelasList.data" :key="item.id">
                                    <td>
                                        <span class="badge-pill badge-info">
                                            Tingkat {{ item.tingkat }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-ink);">{{ item.nama_kelas }}</div>
                                    </td>
                                    <td style="color: var(--color-muted);">{{ item.jurusan }}</td>
                                    <td>
                                        <span class="badge-pill badge-neutral">
                                            {{ item.siswa_count || 0 }} Siswa
                                        </span>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <Link
                                                :href="route('admin.kelas.edit', item.id)"
                                                class="act-btn act-btn-brand"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                type="button"
                                                @click="deleteKelas(item.id)"
                                                class="act-btn act-btn-danger"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!kelasList.data || kelasList.data.length === 0">
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-state-title">Belum Ada Data Kelas</div>
                                            <p class="empty-state-sub">Silakan tambahkan data kelas baru atau sesuaikan filter pencarian.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="kelasList.links?.length > 3" class="pagination-bar">
                        <div class="pagination-info">
                            Menampilkan {{ kelasList.from || 0 }}–{{ kelasList.to || 0 }} dari {{ kelasList.total || 0 }} kelas
                        </div>
                        <div class="pagination-links">
                            <template v-for="(link, i) in kelasList.links" :key="i">
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
    </AuthenticatedLayout>
</template>

