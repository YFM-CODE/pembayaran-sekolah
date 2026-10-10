<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    kelasList: Array,
    users: Array,
});

const form = useForm({
    nis: '',
    nisn: '',
    nama_lengkap: '',
    kelas_id: '',
    status: 'aktif',
    jenis_kelamin: 'L',
    no_telepon: '',
    alamat: '',
    user_id: '',
});

const submit = () => {
    form.post(route('admin.siswa.store'));
};
</script>

<template>
    <Head title="Tambah Siswa Baru" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Siswa Baru</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">NIS</label>
                            <input v-model="form.nis" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.nis" class="mt-1 text-xs text-red-600">{{ form.errors.nis }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">NISN</label>
                            <input v-model="form.nisn" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.nisn" class="mt-1 text-xs text-red-600">{{ form.errors.nisn }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <input v-model="form.nama_lengkap" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.nama_lengkap" class="mt-1 text-xs text-red-600">{{ form.errors.nama_lengkap }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select v-model="form.kelas_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="" disabled>Pilih Kelas</option>
                                <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                            <div v-if="form.errors.kelas_id" class="mt-1 text-xs text-red-600">{{ form.errors.kelas_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                            <select v-model="form.jenis_kelamin" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <div v-if="form.errors.jenis_kelamin" class="mt-1 text-xs text-red-600">{{ form.errors.jenis_kelamin }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                            <input v-model="form.no_telepon" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" />
                            <div v-if="form.errors.no_telepon" class="mt-1 text-xs text-red-600">{{ form.errors.no_telepon }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tautkan Akun Pengguna</label>
                            <select v-model="form.user_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-- Belum Ditautkan --</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                            </select>
                            <div v-if="form.errors.user_id" class="mt-1 text-xs text-red-600">{{ form.errors.user_id }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Alamat</label>
                            <textarea v-model="form.alamat" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            <div v-if="form.errors.alamat" class="mt-1 text-xs text-red-600">{{ form.errors.alamat }}</div>
                        </div>

                        <div class="md:col-span-2 flex items-center justify-end gap-3 pt-4 border-t">
                            <Link :href="route('admin.siswa.index')" class="rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</Link>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
