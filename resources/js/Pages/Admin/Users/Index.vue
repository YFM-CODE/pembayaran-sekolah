<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');

const handleFilter = () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        role: roleFilter.value,
    }, { preserveState: true, replace: true });
};

const toggleStatus = (id) => {
    if (confirm('Ubah status aktif pengguna ini?')) {
        router.patch(route('admin.users.toggle-status', id));
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
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Manajemen Pengguna</h2>
                <Link :href="route('admin.users.create')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Tambah Pengguna
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
                        placeholder="Cari nama atau email..."
                        class="w-full max-w-sm rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <select
                        v-model="roleFilter"
                        @change="handleFilter"
                        class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Semua Peran</option>
                        <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                    </select>
                </div>

                <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Nama</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Email</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Peran</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ user.name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ user.email }}</td>
                                <td class="px-6 py-4 text-gray-500 capitalize">{{ user.role }}</td>
                                <td class="px-6 py-4">
                                    <span :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                        {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button @click="toggleStatus(user.id)" class="text-xs text-yellow-600 hover:underline">Ubah Status</button>
                                    <button @click="openResetModal(user)" class="text-xs text-purple-600 hover:underline">Reset Sandi</button>
                                    <Link :href="route('admin.users.edit', user.id)" class="text-xs text-indigo-600 hover:underline">Edit</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex gap-1">
                            <Link
                                v-for="(link, i) in users.links"
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

        <div v-if="resetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="w-full max-w-md bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Reset Sandi: {{ selectedUser?.name }}</h3>
                <form @submit.prevent="submitReset" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-700">Sandi Baru</label>
                        <input v-model="resetForm.password" type="password" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm text-gray-700">Konfirmasi Sandi</label>
                        <input v-model="resetForm.password_confirmation" type="password" class="w-full rounded border-gray-300 text-sm" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="resetModal = false" class="px-4 py-2 text-sm text-gray-700 border rounded">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
