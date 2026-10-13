<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tagihan: Object,
    filters: Object,
    auth: Object,
});

const page = usePage();
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const isGenerateModalOpen = ref(false);

const namaBulan = [
    '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const generateForm = useForm({
    tahun_ajaran: '2026/2027',
    bulan: new Date().getMonth() + 1,
    tahun: new Date().getFullYear(),
    jatuh_tempo: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 10).toISOString().slice(0, 10),
});

watch([search, status], ([sVal, stVal]) => {
    router.get(
        route(route().current()),
        { search: sVal, status: stVal },
        { preserveState: true, replace: true }
    );
});

const resetFilter = () => {
    search.value = '';
    status.value = '';
};

const submitGenerate = () => {
    const role = page.props.auth.user.role;
    const targetRoute = role === 'bendahara'
        ? 'bendahara.tagihan-spp.generate-bulanan'
        : 'admin.tagihan-spp.generate-bulanan';

    generateForm.post(route(targetRoute), {
        preserveScroll: true,
        onSuccess: () => {
            isGenerateModalOpen.value = false;
        },
    });
};

const formatRupiah = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Daftar Tagihan SPP" />

        <template #header>
            <div class="page-head">
                <div>
                    <div class="page-head-eyebrow">Operasional Keuangan</div>
                    <h2 class="page-head-title">Manajemen Tagihan SPP</h2>
                    <p class="page-head-sub">Pantau status tagihan siswa dan terbitkan tagihan SPP bulanan secara otomatis.</p>
                </div>
                <div class="page-head-actions">
                    <button
                        v-if="['admin', 'bendahara'].includes($page.props.auth.user.role)"
                        type="button"
                        @click="isGenerateModalOpen = true"
                        class="btn-brand"
                    >
                        + Generate Tagihan Bulanan
                    </button>
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
                            type="text"
                            placeholder="Cari NIS, nama siswa, atau nomor tagihan..."
                            class="ui-input"
                        />
                    </div>
                    <div class="filter-select">
                        <select v-model="status" class="ui-select">
                            <option value="">Semua Status Pembayaran</option>
                            <option value="belum_bayar">Belum Bayar</option>
                            <option value="belum_lunas">Belum Lunas / Sebagian</option>
                            <option value="lunas">Lunas</option>
                        </select>
                    </div>
                    <button
                        v-if="search || status"
                        type="button"
                        @click="resetFilter"
                        class="btn-secondary-ui"
                    >
                        Reset
                    </button>
                </div>

                <!-- Tabel Tagihan -->
                <div class="table-card">
                    <div class="table-scroll">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>Siswa & Kelas</th>
                                    <th>Periode</th>
                                    <th>Nominal Asli</th>
                                    <th>Potongan</th>
                                    <th>Total Tagihan</th>
                                    <th>Sisa Tagihan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in (props.tagihan?.data || props.tagihan || [])" :key="item.id">
                                    <td>
                                        <div style="font-weight: 700; color: var(--color-ink);">{{ item.siswa?.nama_lengkap || '-' }}</div>
                                        <div style="font-size: 0.78125rem; color: var(--color-muted); margin-top: 0.125rem;">
                                            NIS: {{ item.siswa?.nis || '-' }} &bull;
                                            {{ item.siswa?.kelas ? `${item.siswa.kelas.tingkat} ${item.siswa.kelas.nama_kelas}` : '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--color-ink);">
                                            {{ namaBulan[item.bulan] || `Bulan ${item.bulan}` }} {{ item.tahun }}
                                        </div>
                                        <div v-if="item.nomor_tagihan" style="font-size: 0.75rem; color: var(--color-muted);">
                                            {{ item.nomor_tagihan }}
                                        </div>
                                    </td>
                                    <td style="color: var(--color-muted);">{{ formatRupiah(item.nominal_asli) }}</td>
                                    <td>
                                        <span v-if="item.nominal_potongan > 0" style="font-weight: 600; color: var(--color-brand);">
                                            -{{ formatRupiah(item.nominal_potongan) }}
                                        </span>
                                        <span v-else style="color: var(--color-muted);">-</span>
                                    </td>
                                    <td style="font-weight: 700; color: var(--color-ink);">
                                        {{ formatRupiah(item.nominal_tagihan ?? item.total_harus_bayar) }}
                                    </td>
                                    <td>
                                        <span
                                            :style="{
                                                fontWeight: 700,
                                                color: item.sisa_tagihan > 0 ? '#a1342c' : 'var(--color-brand)',
                                            }"
                                        >
                                            {{ formatRupiah(item.sisa_tagihan) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge-pill"
                                            :class="{
                                                'badge-success': item.status === 'lunas',
                                                'badge-warning': ['sebagian', 'belum_lunas'].includes(item.status),
                                                'badge-danger': item.status === 'belum_bayar',
                                            }"
                                        >
                                            <span class="badge-dot"></span>
                                            {{ (item.status || '').replace('_', ' ') }}
                                        </span>
                                    </td>
                                </tr>

                                <tr v-if="!(props.tagihan?.data?.length || props.tagihan?.length)">
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-state-title">Belum Ada Tagihan SPP</div>
                                            <p class="empty-state-sub">Silakan gunakan tombol "Generate Tagihan Bulanan" atau ubah filter pencarian Anda.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.tagihan?.links?.length > 3" class="pagination-bar">
                        <div class="pagination-info">
                            Menampilkan {{ props.tagihan.from || 0 }}–{{ props.tagihan.to || 0 }} dari {{ props.tagihan.total || 0 }} tagihan
                        </div>
                        <div class="pagination-links">
                            <template v-for="(link, i) in props.tagihan.links" :key="i">
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

        <!-- Modal Generate Tagihan Bulanan -->
        <div v-if="isGenerateModalOpen" class="modal-backdrop" @click.self="isGenerateModalOpen = false">
            <div class="modal-box">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">Generate Tagihan Bulanan</h3>
                        <p class="modal-subtitle">Terbitkan tagihan SPP untuk seluruh siswa aktif pada periode terpilih.</p>
                    </div>
                    <button type="button" @click="isGenerateModalOpen = false" class="modal-close">&times;</button>
                </div>

                <form @submit.prevent="submitGenerate">
                    <div class="modal-body">
                        <div>
                            <label class="ui-label">Tahun Ajaran</label>
                            <input
                                v-model="generateForm.tahun_ajaran"
                                type="text"
                                placeholder="2026/2027"
                                required
                                class="ui-input"
                            />
                            <p v-if="generateForm.errors.tahun_ajaran" class="ui-error">{{ generateForm.errors.tahun_ajaran }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="ui-label">Bulan Tagihan (1–12)</label>
                                <select v-model="generateForm.bulan" required class="ui-select">
                                    <option v-for="m in 12" :key="m" :value="m">{{ m }} - {{ namaBulan[m] }}</option>
                                </select>
                                <p v-if="generateForm.errors.bulan" class="ui-error">{{ generateForm.errors.bulan }}</p>
                            </div>
                            <div>
                                <label class="ui-label">Tahun</label>
                                <input
                                    v-model="generateForm.tahun"
                                    type="number"
                                    min="2020"
                                    required
                                    class="ui-input"
                                />
                                <p v-if="generateForm.errors.tahun" class="ui-error">{{ generateForm.errors.tahun }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Tanggal Jatuh Tempo</label>
                            <input
                                v-model="generateForm.jatuh_tempo"
                                type="date"
                                required
                                class="ui-input"
                            />
                            <p v-if="generateForm.errors.jatuh_tempo" class="ui-error">{{ generateForm.errors.jatuh_tempo }}</p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" @click="isGenerateModalOpen = false" class="btn-secondary-ui">
                            Batal
                        </button>
                        <button type="submit" :disabled="generateForm.processing" class="btn-brand">
                            {{ generateForm.processing ? 'Memproses...' : 'Mulai Generate' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>