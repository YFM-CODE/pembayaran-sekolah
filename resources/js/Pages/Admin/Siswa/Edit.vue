<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    siswa: Object,
    kelasList: Array,
});

const form = useForm({
    nis: props.siswa.nis,
    nisn: props.siswa.nisn,
    nama_lengkap: props.siswa.nama_lengkap,
    kelas_id: props.siswa.kelas_id,
    status_siswa: props.siswa.status_siswa || props.siswa.status || 'aktif',
    jenis_kelamin: props.siswa.jenis_kelamin || 'L',
    no_telepon: props.siswa.no_telepon || '',
    alamat: props.siswa.alamat || '',
    // Ambil email dari relasi user jika sudah ada
    email: props.siswa.user ? props.siswa.user.email : '',
    password: '',
});

const submit = () => {
    form.put(route('admin.siswa.update', props.siswa.id));
};
</script>

<template>
    <Head title="Edit Data Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Edit Data Siswa</h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">Perbarui biodata, rombel kelas, status keaktifan, atau kredensial akun.</p>
                </div>
                <Link
                    :href="route('admin.siswa.index')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm font-semibold text-ink hover:bg-brand-soft"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Daftar</span>
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-4xl">
            <div class="rounded-xl border border-brand-border bg-white p-6 sm:p-8 shadow-xs">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2 border-b border-brand-border pb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-brand">1. Informasi Pokok & Biodata</h2>
                        <p class="text-xs text-ink-muted mt-0.5">Perubahan NIS/NISN akan berpengaruh pada pencatatan tagihan.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Nomor Induk Siswa (NIS)</label>
                        <input
                            v-model="form.nis"
                            type="text"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        />
                        <div v-if="form.errors.nis" class="mt-1 text-xs text-status-danger">{{ form.errors.nis }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">NISN (Nasional)</label>
                        <input
                            v-model="form.nisn"
                            type="text"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        />
                        <div v-if="form.errors.nisn" class="mt-1 text-xs text-status-danger">{{ form.errors.nisn }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Nama Lengkap</label>
                        <input
                            v-model="form.nama_lengkap"
                            type="text"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        />
                        <div v-if="form.errors.nama_lengkap" class="mt-1 text-xs text-status-danger">{{ form.errors.nama_lengkap }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Rombongan Belajar / Kelas</label>
                        <select
                            v-model="form.kelas_id"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        >
                            <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                        </select>
                        <div v-if="form.errors.kelas_id" class="mt-1 text-xs text-status-danger">{{ form.errors.kelas_id }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Status Keaktifan</label>
                        <select
                            v-model="form.status_siswa"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        >
                            <option value="aktif">Aktif</option>
                            <option value="lulus">Lulus</option>
                            <option value="pindah">Pindah</option>
                            <option value="keluar">Keluar</option>
                        </select>
                        <div v-if="form.errors.status_siswa" class="mt-1 text-xs text-status-danger">{{ form.errors.status_siswa }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Jenis Kelamin</label>
                        <select
                            v-model="form.jenis_kelamin"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        >
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        <div v-if="form.errors.jenis_kelamin" class="mt-1 text-xs text-status-danger">{{ form.errors.jenis_kelamin }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Nomor Telepon / WhatsApp</label>
                        <input
                            v-model="form.no_telepon"
                            type="text"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        />
                        <div v-if="form.errors.no_telepon" class="mt-1 text-xs text-status-danger">{{ form.errors.no_telepon }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Alamat Tempat Tinggal</label>
                        <textarea
                            v-model="form.alamat"
                            rows="2"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        ></textarea>
                        <div v-if="form.errors.alamat" class="mt-1 text-xs text-status-danger">{{ form.errors.alamat }}</div>
                    </div>

                    <!-- Bagian Kredensial Akun Login -->
                    <div class="md:col-span-2 border-b border-brand-border pt-4 pb-3">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-brand">2. Kredensial Akun Login Siswa</h2>
                        <p class="text-xs text-ink-muted mt-0.5">Ubah email portal atau atur ulang kata sandi bila siswa/wali lupa sandi.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Email Akun Login</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="misal: siswa@sekolah.sch.id"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        />
                        <div v-if="form.errors.email" class="mt-1 text-xs text-status-danger">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Kata Sandi Baru (Opsional)</label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="Kosongkan jika tidak ingin mengubah sandi"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        />
                        <div v-if="form.errors.password" class="mt-1 text-xs text-status-danger">{{ form.errors.password }}</div>
                    </div>

                    <div class="md:col-span-2 flex items-center justify-end gap-3 pt-5 border-t border-brand-border">
                        <Link
                            :href="route('admin.siswa.index')"
                            class="rounded-lg border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-ink hover:bg-brand-soft"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-brand px-5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus disabled:opacity-50"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>