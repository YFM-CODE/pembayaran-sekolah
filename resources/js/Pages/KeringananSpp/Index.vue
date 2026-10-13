<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    programList: { type: Array, default: () => [] },
    daftarSiswa: { type: Object, default: () => ({ data: [] }) },
    listKelas: { type: Array, default: () => [] },
    listTahunAjaran: { type: Array, default: () => [] },
    tahunAjaranAktif: { type: String, default: '2026/2027' },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const role = computed(() => page.props.auth?.user?.role ?? 'admin');
const canManage = computed(() => ['admin', 'bendahara'].includes(role.value));

const routePrefix = computed(() => {
    if (role.value === 'bendahara') return 'bendahara.keringanan-spp';
    if (role.value === 'kepala_sekolah') return 'kepsek.keringanan-spp';
    return 'admin.keringanan-spp';
});

const activeTab = ref('program'); // 'program' | 'penetapan'

// Filter Siswa
const searchSiswa = ref(props.filters?.search || '');
const kelasFilter = ref(props.filters?.kelas_id || '');
const tahunAjaranSelect = ref(props.tahunAjaranAktif);

const applyFilterSiswa = () => {
    router.get(
        route(`${routePrefix.value}.index`),
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
        formProgram.put(route(`${routePrefix.value}.update`, editingProgramId.value), {
            preserveScroll: true,
            onSuccess: () => closeProgramModal(),
        });
    } else {
        formProgram.post(route(`${routePrefix.value}.store`), {
            preserveScroll: true,
            onSuccess: () => closeProgramModal(),
        });
    }
};

const deleteProgram = (id) => {
    if (confirm('Yakin ingin menghapus program keringanan ini?')) {
        router.delete(route(`${routePrefix.value}.destroy`, id), { preserveScroll: true });
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
    formTetapkan.post(route(`${routePrefix.value}.tetapkan-siswa`), {
        preserveScroll: true,
        onSuccess: () => closeSiswaModal(),
    });
};

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Program & Penetapan Keringanan SPP" />

        <template #header>
            <div class="page-head">
                <div>
                    <span class="page-head-eyebrow">Kebijakan &amp; Beasiswa</span>
                    <h1 class="page-head-title">Program &amp; Penetapan Keringanan SPP</h1>
                    <p class="page-head-sub">
                        Kelola skema potongan biaya SPP dan tetapkan Surat Keputusan (SK) siswa penerima keringanan.
                    </p>
                </div>

                <div class="page-head-actions">
                    <div class="tab-switcher" role="tablist">
                        <button
                            type="button"
                            role="tab"
                            :aria-selected="activeTab === 'program'"
                            class="tab-btn"
                            :class="{ active: activeTab === 'program' }"
                            @click="activeTab = 'program'"
                        >
                            Master Program
                        </button>
                        <button
                            type="button"
                            role="tab"
                            :aria-selected="activeTab === 'penetapan'"
                            class="tab-btn"
                            :class="{ active: activeTab === 'penetapan' }"
                            @click="activeTab = 'penetapan'"
                        >
                            Penetapan Siswa
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div class="admin-page">
            <div class="admin-wrap">
                <!-- ================= TAB 1: MASTER PROGRAM ================= -->
                <section v-if="activeTab === 'program'" class="space-y-4">
                    <div class="subbar">
                        <span class="text-xs font-medium text-ink-muted">
                            Daftar skema keringanan dan bantuan biaya pendidikan yang terdaftar di sistem
                        </span>
                        <button
                            v-if="canManage"
                            type="button"
                            class="btn-brand"
                            @click="openCreateProgram"
                        >
                            + Tambah Program Keringanan
                        </button>
                    </div>

                    <div class="table-card">
                        <div class="table-scroll">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>Nama Program Bantuan</th>
                                        <th>Bentuk Potongan</th>
                                        <th>Nilai Potongan</th>
                                        <th>Penerima Aktif</th>
                                        <th>Status</th>
                                        <th v-if="canManage" class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in programList" :key="item.id">
                                        <td>
                                            <strong class="font-bold text-ink">{{ item.nama_program }}</strong>
                                            <p class="text-xs text-ink-muted mt-0.5 m-0">
                                                {{ item.syarat_ketentuan || 'Tidak ada catatan syarat khusus.' }}
                                            </p>
                                        </td>
                                        <td>
                                            <span class="badge-pill badge-neutral">
                                                {{ item.tipe_potongan === 'persentase' ? 'Persentase (%)' : 'Nominal Tetap (Rp)' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="font-bold tabular-nums text-brand">
                                                {{ item.tipe_potongan === 'persentase' ? `${item.nilai_potongan}%` : formatRupiah(item.nilai_potongan) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="font-semibold text-ink">{{ item.penerima_count || 0 }} Siswa</span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge-pill"
                                                :class="item.aktif ? 'badge-success' : 'badge-neutral'"
                                            >
                                                <span class="badge-dot"></span>
                                                {{ item.aktif ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </td>
                                        <td v-if="canManage" class="text-right">
                                            <div class="row-actions">
                                                <button type="button" class="act-btn act-btn-warn" @click="openEditProgram(item)">
                                                    Ubah
                                                </button>
                                                <button type="button" class="act-btn act-btn-danger" @click="deleteProgram(item.id)">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="programList.length === 0">
                                        <td :colspan="canManage ? 6 : 5">
                                            <div class="empty-state">
                                                <p class="empty-state-title">Belum Ada Master Program Keringanan</p>
                                                <p class="empty-state-sub">Tambahkan skema beasiswa atau potongan SPP terlebih dahulu.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ================= TAB 2: PENETAPAN SISWA ================= -->
                <section v-if="activeTab === 'penetapan'" class="space-y-4">
                    <div class="filter-card">
                        <div class="filter-search">
                            <input
                                v-model="searchSiswa"
                                type="search"
                                placeholder="Cari Nama Siswa atau NIS..."
                                class="ui-input"
                                @keyup.enter="applyFilterSiswa"
                            />
                        </div>
                        <div class="filter-select">
                            <select v-model="tahunAjaranSelect" class="ui-select" @change="applyFilterSiswa">
                                <option v-for="ta in listTahunAjaran" :key="ta" :value="ta">TA {{ ta }}</option>
                            </select>
                        </div>
                        <div class="filter-select">
                            <select v-model="kelasFilter" class="ui-select" @change="applyFilterSiswa">
                                <option value="">Semua Kelas</option>
                                <option v-for="k in listKelas" :key="k.id" :value="k.id">
                                    {{ k.tingkat }} - {{ k.nama_kelas }}
                                </option>
                            </select>
                        </div>
                        <button type="button" class="btn-secondary-ui" @click="applyFilterSiswa">
                            Terapkan Filter
                        </button>
                    </div>

                    <div class="table-card">
                        <div class="table-scroll">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>Identitas Siswa</th>
                                        <th>Kelas / Rombel</th>
                                        <th>Program Keringanan Aktif</th>
                                        <th>Nomor SK Bantuan</th>
                                        <th v-if="canManage" class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="s in (daftarSiswa?.data || [])" :key="s.id">
                                        <td>
                                            <strong class="font-bold text-ink">{{ s.nama_lengkap }}</strong>
                                            <div class="text-xs text-ink-muted font-mono">NIS: {{ s.nis }}</div>
                                        </td>
                                        <td>
                                            <span class="badge-pill badge-neutral">
                                                {{ s.kelas ? `${s.kelas.tingkat} - ${s.kelas.nama_kelas}` : '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                v-if="s.keringanan && s.keringanan.length > 0"
                                                class="badge-pill badge-success"
                                            >
                                                <span class="badge-dot"></span>
                                                {{ s.keringanan[0].keringanan?.nama_program }}
                                            </span>
                                            <span v-else class="text-xs text-ink-muted italic">
                                                Tanpa Keringanan (Tarif Reguler)
                                            </span>
                                        </td>
                                        <td>
                                            <span class="font-mono text-xs text-ink">
                                                {{ s.keringanan && s.keringanan.length > 0 ? (s.keringanan[0].nomor_sk || '-') : '-' }}
                                            </span>
                                        </td>
                                        <td v-if="canManage" class="text-right">
                                            <div class="row-actions">
                                                <button type="button" class="act-btn act-btn-brand" @click="openTetapkanModal(s)">
                                                    Atur SK Keringanan
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!daftarSiswa?.data || daftarSiswa.data.length === 0">
                                        <td :colspan="canManage ? 5 : 4">
                                            <div class="empty-state">
                                                <p class="empty-state-title">Data Siswa Tidak Ditemukan</p>
                                                <p class="empty-state-sub">Ubah kata kunci pencarian atau filter kelas di atas.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="daftarSiswa?.links?.length > 3" class="pagination-bar">
                            <span class="text-xs text-ink-muted">
                                Menampilkan daftar siswa tahun ajaran {{ tahunAjaranSelect }}
                            </span>
                            <div class="pagination-links">
                                <Link
                                    v-for="(link, i) in daftarSiswa.links"
                                    :key="i"
                                    :href="link.url || '#'"
                                    class="page-link"
                                    :class="{ active: link.active, disabled: !link.url }"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Modal Form Program Keringanan -->
        <div v-if="isProgramModalOpen" class="modal-backdrop" @click.self="closeProgramModal">
            <div class="modal-box">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">
                            {{ isEditingProgram ? 'Ubah Program Keringanan' : 'Tambah Program Keringanan' }}
                        </h3>
                        <p class="modal-subtitle">Konfigurasi skema potongan nominal atau persentase SPP.</p>
                    </div>
                    <button type="button" class="modal-close" aria-label="Tutup modal" @click="closeProgramModal">&times;</button>
                </div>

                <form @submit.prevent="submitProgram" class="modal-body">
                    <div>
                        <label class="ui-label">Nama Program</label>
                        <input
                            v-model="formProgram.nama_program"
                            type="text"
                            placeholder="Contoh: Beasiswa SKTM / Bantuan Yatim Piatu"
                            class="ui-input"
                            required
                        />
                        <p v-if="formProgram.errors.nama_program" class="ui-error">{{ formProgram.errors.nama_program }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="ui-label">Tipe Potongan</label>
                            <select v-model="formProgram.tipe_potongan" class="ui-select">
                                <option value="nominal">Nominal (Rp)</option>
                                <option value="persentase">Persentase (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="ui-label">Nilai Potongan</label>
                            <input
                                v-model="formProgram.nilai_potongan"
                                type="number"
                                :placeholder="formProgram.tipe_potongan === 'persentase' ? 'Misal: 50' : 'Misal: 100000'"
                                class="ui-input"
                                required
                            />
                        </div>
                    </div>
                    <p v-if="formProgram.errors.nilai_potongan" class="ui-error">{{ formProgram.errors.nilai_potongan }}</p>

                    <div>
                        <label class="ui-label">Syarat &amp; Ketentuan</label>
                        <textarea
                            v-model="formProgram.syarat_ketentuan"
                            rows="3"
                            placeholder="Dokumen pendukung yang wajib dilampirkan..."
                            class="ui-textarea"
                        ></textarea>
                    </div>

                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input v-model="formProgram.aktif" type="checkbox" class="h-4 w-4 rounded border-brand-border text-brand focus:ring-brand" />
                        <span class="text-sm font-medium text-ink">Program Aktif</span>
                    </label>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary-ui" @click="closeProgramModal">Batal</button>
                        <button type="submit" :disabled="formProgram.processing" class="btn-brand">
                            {{ formProgram.processing ? 'Menyimpan...' : 'Simpan Program' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Penetapan Siswa -->
        <div v-if="isSiswaModalOpen" class="modal-backdrop" @click.self="closeSiswaModal">
            <div class="modal-box">
                <div class="modal-header">
                    <div>
                        <h3 class="modal-title">Penetapan SK Keringanan Siswa</h3>
                        <p class="modal-subtitle">{{ selectedSiswa?.nama_lengkap }} &bull; NIS {{ selectedSiswa?.nis }}</p>
                    </div>
                    <button type="button" class="modal-close" aria-label="Tutup modal" @click="closeSiswaModal">&times;</button>
                </div>

                <form @submit.prevent="submitTetapkan" class="modal-body">
                    <div>
                        <label class="ui-label">Pilih Program Keringanan</label>
                        <select v-model="formTetapkan.keringanan_spp_id" class="ui-select">
                            <option value="">-- Hapus / Reguler (Tanpa Keringanan) --</option>
                            <option v-for="prog in programList.filter((p) => p.aktif)" :key="prog.id" :value="prog.id">
                                {{ prog.nama_program }} (Potongan: {{ prog.tipe_potongan === 'persentase' ? `${prog.nilai_potongan}%` : formatRupiah(prog.nilai_potongan) }})
                            </option>
                        </select>
                        <p v-if="formTetapkan.errors.keringanan_spp_id" class="ui-error">{{ formTetapkan.errors.keringanan_spp_id }}</p>
                    </div>

                    <div>
                        <label class="ui-label">Nomor Surat Keputusan (SK)</label>
                        <input
                            v-model="formTetapkan.nomor_sk"
                            type="text"
                            placeholder="Contoh: 421.3/088/SK-BEASISWA/2026"
                            class="ui-input"
                        />
                        <p v-if="formTetapkan.errors.nomor_sk" class="ui-error">{{ formTetapkan.errors.nomor_sk }}</p>
                    </div>

                    <div>
                        <label class="ui-label">Tahun Ajaran Berlaku</label>
                        <input
                            v-model="formTetapkan.tahun_ajaran"
                            type="text"
                            readonly
                            class="ui-input bg-page text-ink-muted cursor-not-allowed"
                        />
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary-ui" @click="closeSiswaModal">Batal</button>
                        <button type="submit" :disabled="formTetapkan.processing" class="btn-brand">
                            {{ formTetapkan.processing ? 'Menyimpan...' : 'Simpan SK Keringanan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.tab-switcher {
    display: inline-flex;
    padding: 4px;
    border-radius: var(--radius);
    background: var(--color-bg);
    border: var(--border);
    gap: 4px;
}

.tab-btn {
    height: 36px;
    padding: 0 14px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--color-muted);
    font-family: var(--font-sans);
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
    cursor: pointer;
    transition: all 0.15s ease;
}

.tab-btn:hover {
    color: var(--color-ink);
}

.tab-btn.active {
    background: var(--color-brand);
    color: #ffffff;
}

.subbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-3);
}

@media (max-width: 640px) {
    .tab-switcher {
        width: 100%;
    }

    .tab-btn {
        flex: 1;
    }

    .subbar .btn-brand {
        width: 100%;
    }
}
</style>