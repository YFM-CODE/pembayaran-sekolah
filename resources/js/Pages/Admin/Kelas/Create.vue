<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nama_kelas: '',
    tingkat: 'X',
    jurusan: '',
});

const submit = () => {
    form.post(route('admin.kelas.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah Kelas" />

        <template #header>
            <div class="page-head">
                <div>
                    <div class="page-head-eyebrow">Master Data Kelas</div>
                    <h2 class="page-head-title">Tambah Kelas Baru</h2>
                    <p class="page-head-sub">Tambahkan rombongan belajar baru beserta tingkat dan kompetensi keahlian.</p>
                </div>
                <div class="page-head-actions">
                    <Link :href="route('admin.kelas.index')" class="btn-secondary-ui">
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
                            <h3 class="form-section-title">Identitas Rombongan Belajar</h3>
                            <p class="form-section-sub">Pastikan penamaan kelas seragam untuk mempermudah pengelompokan tagihan SPP.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="ui-label">Tingkat Kelas</label>
                                <select v-model="form.tingkat" class="ui-select" required>
                                    <option value="X">X (Sepuluh)</option>
                                    <option value="XI">XI (Sebelas)</option>
                                    <option value="XII">XII (Dua Belas)</option>
                                </select>
                                <div v-if="form.errors.tingkat" class="ui-error">{{ form.errors.tingkat }}</div>
                            </div>

                            <div>
                                <label class="ui-label">Nama Kelas</label>
                                <input
                                    v-model="form.nama_kelas"
                                    placeholder="Misal: X RPL 1"
                                    type="text"
                                    class="ui-input"
                                    required
                                />
                                <div v-if="form.errors.nama_kelas" class="ui-error">{{ form.errors.nama_kelas }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Jurusan / Kompetensi Keahlian</label>
                            <input
                                v-model="form.jurusan"
                                placeholder="Misal: Rekayasa Perangkat Lunak"
                                type="text"
                                class="ui-input"
                                required
                            />
                            <div v-if="form.errors.jurusan" class="ui-error">{{ form.errors.jurusan }}</div>
                        </div>

                        <div class="form-footer">
                            <Link :href="route('admin.kelas.index')" class="btn-secondary-ui">Batal</Link>
                            <button type="submit" :disabled="form.processing" class="btn-brand">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Kelas' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

