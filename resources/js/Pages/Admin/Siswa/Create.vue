<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    kelasList: Array,
});

const form = useForm({
    nis: '',
    nisn: '',
    nama_lengkap: '',
    kelas_id: '',
    status_siswa: 'aktif',
    jenis_kelamin: 'L',
    no_telepon: '',
    alamat: '',
    email: '',
    password: '',
});

// Otomatis generate saran email siswa ketika NIS diisi
watch(() => form.nis, (newNis) => {
    if (newNis && !form.email) {
        form.email = `${newNis}@siswa.sekolah.sch.id`;
    }
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
                        <div class="md:col-span-2 border-b pb-2">
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Informasi Akademik & Pribadi</h3>
                        </div>

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
                            <label class="block text-sm font-medium text-gray-700">Status Awal</label>
                            <select v-model="form.status_siswa" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="aktif">Aktif</option>
                                <option value="lulus">Lulus</option>
                                <option value="pindah">Pindah</option>
                                <option value="keluar">Keluar</option>
                            </select>
                            <div v-if="form.errors.status_siswa" class="mt-1 text-xs text-red-600">{{ form.errors.status_siswa }}</div>
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

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Alamat</label>
                            <textarea v-model="form.alamat" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            <div v-if="form.errors.alamat" class="mt-1 text-xs text-red-600">{{ form.errors.alamat }}</div>
                        </div>

                        <!-- Bagian Kredensial Akun Login -->
                        <div class="md:col-span-2 border-b pt-4 pb-2">
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Pembuatan Akun Login Siswa</h3>
                            <p class="text-xs text-gray-500">Akun ini digunakan siswa atau wali murid untuk masuk ke portal SPP.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Akun Login</label>
                            <input v-model="form.email" type="email" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kata Sandi Awal</label>
                            <input v-model="form.password" type="password" placeholder="Kosongkan untuk default: password123" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" />
                            <div v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</div>
                        </div>

                        <div class="md:col-span-2 flex items-center justify-end gap-3 pt-4 border-t">
                            <Link :href="route('admin.siswa.index')" class="rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</Link>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Daftarkan Siswa & Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>