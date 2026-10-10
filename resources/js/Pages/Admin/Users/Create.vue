<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    roles: Array,
});

const form = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
    is_active: true,
});

const submit = () => {
    form.post(route('admin.users.store'));
};
</script>

<template>
    <Head title="Tambah Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Pengguna</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <input v-model="form.name" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="form.email" type="email" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Peran Akun</label>
                            <select v-model="form.role" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="" disabled>Pilih Peran</option>
                                <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                            </select>
                            <div v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                            <input v-model="form.password" type="password" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</div>
                        </div>

                        <div class="flex items-center">
                            <input id="is_active" v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600" />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">Akun Aktif</label>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <Link :href="route('admin.users.index')" class="rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</Link>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
