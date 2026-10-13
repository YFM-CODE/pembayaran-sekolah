<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

defineProps({
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
    <AuthenticatedLayout>
        <Head title="Tambah Siswa Baru" />

        <template #header>
            <div class="page-head">
                <div>
                    <div class="page-head-eyebrow">Master Data Siswa</div>
                    <h2 class="page-head-title">Tambah Siswa Baru</h2>
                    <p class="page-head-sub">Daftarkan data akademik siswa sekaligus membuat akun login portal SPP.</p>
                </div>
                <div class="page-head-actions">
                    <Link :href="route('admin.siswa.index')" class="btn-secondary-ui">
                        &larr; Kembali ke Daftar
                    </Link>
                </div>
            </div>
        </template>

        <div class="admin-page">
            <div class="admin-wrap-narrow">
                <div class="form-panel">
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2 form-section-head">
                            <h3 class="form-section-title">Informasi Akademik & Pribadi</h3>
                            <p class="form-section-sub">Masukkan identitas resmi siswa sesuai buku induk sekolah dan penempatan kelasnya.</p>
                        </div>

                        <div>
                            <label class="ui-label">Nomor Induk Siswa (NIS)</label>
                            <input
                                v-model="form.nis"
                                type="text"
                                placeholder="Contoh: 2026001"
                                class="ui-input"
                                required
                            />
                            <div v-if="form.errors.nis" class="ui-error">{{ form.errors.nis }}</div>
                        </div>

                        <div>
                            <label class="ui-label">NISN</label>
                            <input
                                v-model="form.nisn"
                                type="text"
                                placeholder="10 digit nomor induk nasional"
                                class="ui-input"
                                required
                            />
                            <div v-if="form.errors.nisn" class="ui-error">{{ form.errors.nisn }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">Nama Lengkap Siswa</label>
                            <input
                                v-model="form.nama_lengkap"
                                type="text"
                                placeholder="Masukkan nama lengkap sesuai ijazah"
                                class="ui-input"
                                required
                            />
                            <div v-if="form.errors.nama_lengkap" class="ui-error">{{ form.errors.nama_lengkap }}</div>
                        </div>

                        <div>
                            <label class="ui-label">Kelas</label>
                            <select v-model="form.kelas_id" class="ui-select" required>
                                <option value="" disabled>Pilih Kelas</option>
                                <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                            <div v-if="form.errors.kelas_id" class="ui-error">{{ form.errors.kelas_id }}</div>
                        </div>

                        <div>
                            <label class="ui-label">Status Awal</label>
                            <select v-model="form.status_siswa" class="ui-select" required>
                                <option value="aktif">Aktif</option>
                                <option value="lulus">Lulus</option>
                                <option value="pindah">Pindah</option>
                                <option value="keluar">Keluar</option>
                            </select>
                            <div v-if="form.errors.status_siswa" class="ui-error">{{ form.errors.status_siswa }}</div>
                        </div>

                        <div>
                            <label class="ui-label">Jenis Kelamin</label>
                            <select v-model="form.jenis_kelamin" class="ui-select">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            <div v-if="form.errors.jenis_kelamin" class="ui-error">{{ form.errors.jenis_kelamin }}</div>
                        </div>

                        <div>
                            <label class="ui-label">Nomor Telepon / WhatsApp Wali</label>
                            <input
                                v-model="form.no_telepon"
                                type="text"
                                placeholder="Contoh: 081234567890"
                                class="ui-input"
                            />
                            <div v-if="form.errors.no_telepon" class="ui-error">{{ form.errors.no_telepon }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">Alamat Tempat Tinggal</label>
                            <textarea
                                v-model="form.alamat"
                                rows="2"
                                placeholder="Alamat lengkap domisili siswa"
                                class="ui-textarea"
                            ></textarea>
                            <div v-if="form.errors.alamat" class="ui-error">{{ form.errors.alamat }}</div>
                        </div>

                        <!-- Bagian Kredensial Akun Login -->
                        <div class="md:col-span-2 form-section-head pt-3">
                            <h3 class="form-section-title">Pembuatan Akun Login Siswa</h3>
                            <p class="form-section-sub">Akun ini digunakan siswa atau wali murid untuk memantau tagihan dan riwayat SPP.</p>
                        </div>

                        <div>
                            <label class="ui-label">Email Akun Login</label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="nis@siswa.sekolah.sch.id"
                                class="ui-input"
                                required
                            />
                            <div v-if="form.errors.email" class="ui-error">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="ui-label">Kata Sandi Awal</label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="Kosongkan untuk sandi bawaan: password123"
                                class="ui-input"
                            />
                            <div v-if="form.errors.password" class="ui-error">{{ form.errors.password }}</div>
                        </div>

                        <div class="md:col-span-2 form-footer">
                            <Link :href="route('admin.siswa.index')" class="btn-secondary-ui">Batal</Link>
                            <button type="submit" :disabled="form.processing" class="btn-brand">
                                {{ form.processing ? 'Mendaftarkan...' : 'Daftarkan Siswa & Akun' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>