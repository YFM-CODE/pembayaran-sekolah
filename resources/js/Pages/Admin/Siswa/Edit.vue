<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    siswa: Object,
    kelasList: Array,
    users: Array,
});

const form = useForm({
    nis: props.siswa.nis,
    nisn: props.siswa.nisn,
    nama_lengkap: props.siswa.nama_lengkap,
    kelas_id: props.siswa.kelas_id,
    // Gunakan status_siswa agar terpilih otomatis
    status_siswa: props.siswa.status_siswa || props.siswa.status || 'aktif',
    jenis_kelamin: props.siswa.jenis_kelamin || 'L',
    no_telepon: props.siswa.no_telepon || '',
    alamat: props.siswa.alamat || '',
    user_id: props.siswa.user_id || '',
});

const submit = () => {
    form.put(route('admin.siswa.update', props.siswa.id));
};

// Fitur otomatisasi pembuatan akun bagi siswa ini
const buatAkunOtomatis = () => {
    if (confirm(`Buatkan akun login siswa otomatis untuk ${props.siswa.nama_lengkap}? Akun akan dibuat dengan email NIS (${props.siswa.nis}@siswa.sekolah.sch.id).`)) {
        router.post(route('admin.siswa.tautkan-user', props.siswa.id), {
            otomatis: true,
        });
    }
};
</script>

<template>
    <Head title="Edit Data Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Data Siswa</h2>
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
                                <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                            <div v-if="form.errors.kelas_id" class="mt-1 text-xs text-red-600">{{ form.errors.kelas_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
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

                        <!-- Bagian Tautkan Akun -->
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-gray-700">Tautkan Akun Pengguna</label>
                                <button
                                    v-if="!props.siswa.user_id"
                                    type="button"
                                    @click="buatAkunOtomatis"
                                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800"
                                >
                                    + Buat & Tautkan Akun Siswa Baru Otomatis
                                </button>
                            </div>
                            <select v-model="form.user_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">-- Belum Ditautkan --</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">
                                    {{ u.name }} ({{ u.email }})
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Pilih akun siswa yang sudah terdaftar di menu "Manajemen Pengguna", atau gunakan tombol di atas untuk membuatkannya secara instan.
                            </p>
                            <div v-if="form.errors.user_id" class="mt-1 text-xs text-red-600">{{ form.errors.user_id }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Alamat</label>
                            <textarea v-model="form.alamat" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            <div v-if="form.errors.alamat" class="mt-1 text-xs text-red-600">{{ form.errors.alamat }}</div>
                        </div>

                        <div class="md:col-span-2 flex items-center justify-end gap-3 pt-4 border-t">
                            <Link :href="route('admin.siswa.index')" class="rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</Link>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>