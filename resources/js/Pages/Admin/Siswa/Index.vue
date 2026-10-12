<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    siswaList: Object,
    kelasList: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const kelasFilter = ref(props.filters.kelas_id || '');
const statusFilter = ref(props.filters.status || '');

const handleFilter = () => {
    router.get(route('admin.siswa.index'), {
        search: search.value,
        kelas_id: kelasFilter.value,
        status: statusFilter.value,
    }, { preserveState: true, replace: true });
};

// Modal & Form Update Status
const showStatusModal = ref(false);
const selectedSiswa = ref(null);
const statusForm = useForm({
    status_siswa: '',
});

const openStatusModal = (siswa) => {
    selectedSiswa.value = siswa;
    statusForm.status_siswa = siswa.status_siswa || siswa.status || 'aktif';
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
    if (confirm('Hapus data siswa ini? Tindakan ini tidak dapat dibatalkan.')) {
        router.delete(route('admin.siswa.destroy', id));
    }
};
</script>

<template>
    <Head title="Master Data Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Master Data Siswa</h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">Kelola identitas siswa terdaftar, NIS/NISN, penempatan rombel, dan status.</p>
                </div>
                <Link
                    :href="route('admin.siswa.create')"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Siswa</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter Toolbar -->
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 rounded-xl border border-brand-border bg-white p-4 shadow-xs">
                <div class="relative flex-1 min-w-[240px]">
                    <input
                        v-model="search"
                        @input="handleFilter"
                        type="search"
                        placeholder="Cari NIS, NISN, atau Nama Siswa..."
                        class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                    />
                </div>
                <div class="w-full sm:w-48">
                    <select
                        v-model="kelasFilter"
                        @change="handleFilter"
                        class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                    >
                        <option value="">Semua Kelas</option>
                        <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                    </select>
                </div>
                <div class="w-full sm:w-44">
                    <select
                        v-model="statusFilter"
                        @change="handleFilter"
                        class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                    >
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="pindah">Pindah</option>
                        <option value="keluar">Keluar</option>
                    </select>
                </div>
            </div>

            <!-- Table Card -->
            <div class="overflow-hidden rounded-xl border border-brand-border bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-sm">
                        <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold">NIS / NISN</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Nama Lengkap</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Kelas</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Akun Terhubung</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            <tr
                                v-for="siswa in siswaList.data"
                                :key="siswa.id"
                                class="odd:bg-white even:bg-page/50 transition-colors hover:bg-brand-soft/40"
                            >
                                <td class="whitespace-nowrap px-5 py-3.5 text-ink">
                                    <span class="block font-medium font-mono text-xs">{{ siswa.nis }}</span>
                                    <span class="block text-xs text-ink-muted font-mono">{{ siswa.nisn || '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 font-semibold text-ink">
                                    {{ siswa.nama_lengkap }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-ink-muted">
                                    <span class="inline-flex rounded-md bg-page px-2 py-0.5 text-xs font-medium text-ink border border-brand-border/60">
                                        {{ siswa.kelas?.nama_kelas || '-' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-ink-muted">
                                    <span v-if="siswa.user" class="inline-flex rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-semibold text-brand">
                                        {{ siswa.user.email }}
                                    </span>
                                    <span v-else class="text-xs text-ink-muted/70 italic">Belum Ada Akun</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <button
                                        type="button"
                                        @click="openStatusModal(siswa)"
                                        :class="{
                                            'bg-status-success/15 text-status-success': (siswa.status_siswa || siswa.status) === 'aktif',
                                            'bg-sky-50 text-sky-700': (siswa.status_siswa || siswa.status) === 'lulus',
                                            'bg-status-warning/15 text-status-warning': (siswa.status_siswa || siswa.status) === 'pindah',
                                            'bg-status-danger/15 text-status-danger': (siswa.status_siswa || siswa.status) === 'keluar',
                                            'bg-page text-ink-muted': !(siswa.status_siswa || siswa.status),
                                        }"
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize transition hover:brightness-95"
                                        title="Klik untuk ubah status"
                                    >
                                        <span>{{ siswa.status_siswa || siswa.status || 'Tidak Diketahui' }}</span>
                                        <svg class="h-3 w-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right space-x-2">
                                    <button
                                        @click="openStatusModal(siswa)"
                                        class="text-xs font-semibold text-amber-700 hover:text-amber-900 hover:underline"
                                    >
                                        Status
                                    </button>
                                    <Link
                                        :href="route('admin.siswa.edit', siswa.id)"
                                        class="text-xs font-semibold text-brand hover:text-brand-hover hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteSiswa(siswa.id)"
                                        class="text-xs font-semibold text-status-danger hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!siswaList.data || siswaList.data.length === 0">
                                <td colspan="6" class="px-5 py-8 text-center text-ink-muted">
                                    Belum ada data siswa yang cocok dengan kriteria pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="siswaList.links && siswaList.links.length > 3" class="flex items-center justify-between border-t border-brand-border bg-white px-5 py-3">
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="(link, i) in siswaList.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                link.active
                                    ? 'bg-brand-soft text-brand font-semibold border-brand'
                                    : 'text-ink-muted border-brand-border hover:bg-page hover:text-ink',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            class="rounded-lg px-3 py-1.5 text-xs border transition-colors"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ubah Status Siswa -->
        <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 backdrop-blur-xs p-4">
            <div class="w-full max-w-sm rounded-2xl border border-brand-border bg-white p-6 shadow-xl">
                <h3 class="text-base font-bold text-ink">Ubah Status Siswa</h3>
                <p class="mt-1 text-xs text-ink-muted">
                    {{ selectedSiswa?.nama_lengkap }} (NIS: {{ selectedSiswa?.nis }})
                </p>

                <form @submit.prevent="submitUpdateStatus" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Pilih Status Baru</label>
                        <select
                            v-model="statusForm.status_siswa"
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        >
                            <option value="aktif">Aktif</option>
                            <option value="lulus">Lulus</option>
                            <option value="pindah">Pindah</option>
                            <option value="keluar">Keluar</option>
                        </select>
                        <div v-if="statusForm.errors.status_siswa" class="mt-1 text-xs text-status-danger">
                            {{ statusForm.errors.status_siswa }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-brand-border">
                        <button
                            type="button"
                            @click="showStatusModal = false"
                            class="rounded-lg border border-brand-border bg-white px-3.5 py-1.5 text-xs font-semibold text-ink hover:bg-brand-soft"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="statusForm.processing"
                            class="rounded-lg bg-brand px-4 py-1.5 text-xs font-semibold text-white shadow-xs transition hover:bg-brand-hover disabled:opacity-50"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>