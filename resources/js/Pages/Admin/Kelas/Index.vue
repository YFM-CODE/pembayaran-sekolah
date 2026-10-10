<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    kelasList: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const tingkat = ref(props.filters.tingkat || '');

const handleFilter = () => {
    router.get(route('admin.kelas.index'), {
        search: search.value,
        tingkat: tingkat.value,
    }, { preserveState: true, replace: true });
};

const deleteKelas = (id) => {
    if (confirm('Yakin ingin menghapus kelas ini?')) {
        router.delete(route('admin.kelas.destroy', id));
    }
};
</script>

<template>
    <Head title="Master Data Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Master Data Kelas</h2>
                <Link :href="route('admin.kelas.create')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Tambah Kelas
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <div class="flex gap-4 bg-white p-4 shadow sm:rounded-lg">
                    <input
                        v-model="search"
                        @input="handleFilter"
                        type="text"
                        placeholder="Cari kelas atau jurusan..."
                        class="w-full max-w-sm rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <select
                        v-model="tingkat"
                        @change="handleFilter"
                        class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Tingkat</option>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>

                <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Tingkat</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Nama Kelas</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Jurusan</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Jumlah Siswa</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="item in kelasList.data" :key="item.id">
                                <td class="px-6 py-4 font-semibold text-gray-700">{{ item.tingkat }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ item.nama_kelas }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ item.jurusan }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ item.siswa_count }} Siswa</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <Link :href="route('admin.kelas.edit', item.id)" class="text-xs text-indigo-600 hover:underline">Edit</Link>
                                    <button @click="deleteKelas(item.id)" class="text-xs text-red-600 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex gap-1">
                            <Link
                                v-for="(link, i) in kelasList.links"
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
    </AuthenticatedLayout>
</template>
