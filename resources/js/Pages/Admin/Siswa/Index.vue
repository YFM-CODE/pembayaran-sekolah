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
    if (confirm('Hapus data siswa ini?')) {
        router.delete(route('admin.siswa.destroy', id));
    }
};
</script>

<template>
    <Head title="Master Data Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Master Data Siswa</h2>
                <Link :href="route('admin.siswa.create')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Tambah Siswa
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <div class="flex flex-wrap gap-4 bg-white p-4 shadow sm:rounded-lg">
                    <input
                        v-model="search"
                        @input="handleFilter"
                        type="text"
                        placeholder="Cari NIS, NISN, atau Nama Siswa..."
                        class="w-full max-w-sm rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <select
                        v-model="kelasFilter"
                        @change="handleFilter"
                        class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Kelas</option>
                        <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                    </select>
                    <select
                        v-model="statusFilter"
                        @change="handleFilter"
                        class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="pindah">Pindah</option>
                        <option value="keluar">Keluar</option>
                    </select>
                </div>

                <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">NIS / NISN</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Nama Lengkap</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Kelas</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Akun Terhubung</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="siswa in siswaList.data" :key="siswa.id">
                                <td class="px-6 py-4 text-gray-900">
                                    <div>{{ siswa.nis }}</div>
                                    <div class="text-xs text-gray-400">{{ siswa.nisn }}</div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ siswa.nama_lengkap }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ siswa.kelas?.nama_kelas || '-' }}</td>
                                <td class="px-6 py-4 text-gray-500">
                                    <span v-if="siswa.user" class="text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded border border-green-200">
                                        {{ siswa.user.email }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400 italic">Belum Ada Akun</span>
                                </td>
                                <td class="px-6 py-4">
                                    <!-- Badge status yang dapat diklik untuk cepat ubah status -->
                                    <button
                                        type="button"
                                        @click="openStatusModal(siswa)"
                                        :class="{
                                            'bg-green-100 text-green-800 border-green-200 hover:bg-green-200': (siswa.status_siswa || siswa.status) === 'aktif',
                                            'bg-blue-100 text-blue-800 border-blue-200 hover:bg-blue-200': (siswa.status_siswa || siswa.status) === 'lulus',
                                            'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-200': (siswa.status_siswa || siswa.status) === 'pindah',
                                            'bg-red-100 text-red-800 border-red-200 hover:bg-red-200': (siswa.status_siswa || siswa.status) === 'keluar',
                                            'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-200': !(siswa.status_siswa || siswa.status),
                                        }"
                                        class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize transition"
                                        title="Klik untuk ubah status"
                                    >
                                        {{ siswa.status_siswa || siswa.status || 'Tidak Diketahui' }}
                                        <svg class="h-3 w-3 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button @click="openStatusModal(siswa)" class="text-xs text-amber-600 hover:underline">Ubah Status</button>
                                    <Link :href="route('admin.siswa.edit', siswa.id)" class="text-xs text-indigo-600 hover:underline">Edit</Link>
                                    <button @click="deleteSiswa(siswa.id)" class="text-xs text-red-600 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex gap-1">
                            <Link
                                v-for="(link, i) in siswaList.links"
                                :key="i"
                                :href="link.url || '#'"
                                :class="{'bg-indigo-50 text-indigo-600': link.active, 'text-gray-500': !link.active}"
                                class="rounded px-3 py-1 text-sm border"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ubah Status Siswa -->
        <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-base font-semibold text-gray-900">Ubah Status Siswa</h3>
                <p class="mt-1 text-xs text-gray-500">
                    {{ selectedSiswa?.nama_lengkap }} (NIS: {{ selectedSiswa?.nis }})
                </p>

                <form @submit.prevent="submitUpdateStatus" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Pilih Status Baru</label>
                        <select
                            v-model="statusForm.status_siswa"
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="aktif">Aktif</option>
                            <option value="lulus">Lulus</option>
                            <option value="pindah">Pindah</option>
                            <option value="keluar">Keluar</option>
                        </select>
                        <div v-if="statusForm.errors.status_siswa" class="mt-1 text-xs text-red-600">
                            {{ statusForm.errors.status_siswa }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button
                            type="button"
                            @click="showStatusModal = false"
                            class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="statusForm.processing"
                            class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>