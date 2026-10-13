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

const roleLabel = (role) => {
    const map = {
        admin: 'Administrator',
        bendahara: 'Bendahara',
        kepsek: 'Kepala Sekolah',
        siswa: 'Siswa / Wali',
    };
    return map[role] || role;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah Pengguna" />

        <template #header>
            <div class="page-head">
                <div>
                    <div class="page-head-eyebrow">Manajemen Pengguna</div>
                    <h2 class="page-head-title">Tambah Pengguna Baru</h2>
                    <p class="page-head-sub">Buat akun baru untuk staf sekolah atau pengguna portal SPP.</p>
                </div>
                <div class="page-head-actions">
                    <Link :href="route('admin.users.index')" class="btn-secondary-ui">
                        &larr; Kembali ke Daftar
                    </Link>
                </div>
            </div>
        </template>

        <div class="admin-page">
            <div class="admin-wrap-narrow">
                <div class="form-panel">
                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="form-section-head">
                            <h3 class="form-section-title">Informasi & Kredensial Akun</h3>
                            <p class="form-section-sub">Pastikan alamat email aktif dan peran pengguna sesuai dengan tanggung jawabnya.</p>
                        </div>

                        <div>
                            <label class="ui-label">Nama Lengkap</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Masukkan nama lengkap pengguna"
                                class="ui-input"
                                required
                            />
                            <div v-if="form.errors.name" class="ui-error">{{ form.errors.name }}</div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="ui-label">Alamat Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="nama@sekolah.sch.id"
                                    class="ui-input"
                                    required
                                />
                                <div v-if="form.errors.email" class="ui-error">{{ form.errors.email }}</div>
                            </div>

                            <div>
                                <label class="ui-label">Peran Akun</label>
                                <select v-model="form.role" class="ui-select" required>
                                    <option value="" disabled>Pilih Peran</option>
                                    <option v-for="role in roles" :key="role" :value="role">
                                        {{ roleLabel(role) }}
                                    </option>
                                </select>
                                <div v-if="form.errors.role" class="ui-error">{{ form.errors.role }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Kata Sandi Awal</label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="Minimal 8 karakter"
                                class="ui-input"
                                required
                            />
                            <div v-if="form.errors.password" class="ui-error">{{ form.errors.password }}</div>
                        </div>

                        <div class="pt-1">
                            <label class="ui-check-row" for="is_active">
                                <input id="is_active" v-model="form.is_active" type="checkbox" />
                                <div>
                                    <div style="font-size: 0.875rem; font-weight: 700; color: var(--color-ink);">Aktifkan Akun Sekarang</div>
                                    <div style="font-size: 0.78125rem; color: var(--color-muted);">Pengguna yang aktif dapat langsung masuk ke dalam sistem.</div>
                                </div>
                            </label>
                        </div>

                        <div class="form-footer">
                            <Link :href="route('admin.users.index')" class="btn-secondary-ui">Batal</Link>
                            <button type="submit" :disabled="form.processing" class="btn-brand">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Pengguna' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

