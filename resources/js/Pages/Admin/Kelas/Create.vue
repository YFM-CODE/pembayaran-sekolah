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
    <Head title="Tambah Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Tambah Kelas Baru</h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">Daftarkan rombongan belajar baru untuk tahun ajaran aktif.</p>
                </div>
                <Link
                    :href="route('admin.kelas.index')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-brand-border bg-white px-3.5 py-2 text-sm font-semibold text-ink hover:bg-brand-soft"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Daftar</span>
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <div class="rounded-xl border border-brand-border bg-white p-6 sm:p-8 shadow-xs">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Tingkat Kelas</label>
                        <select
                            v-model="form.tingkat"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        >
                            <option value="X">Kelas X (Sepuluh)</option>
                            <option value="XI">Kelas XI (Sebelas)</option>
                            <option value="XII">Kelas XII (Dua Belas)</option>
                        </select>
                        <div v-if="form.errors.tingkat" class="mt-1 text-xs text-status-danger">{{ form.errors.tingkat }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Nama Rombel / Kelas</label>
                        <input
                            v-model="form.nama_kelas"
                            placeholder="Misal: X RPL 1"
                            type="text"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        />
                        <div v-if="form.errors.nama_kelas" class="mt-1 text-xs text-status-danger">{{ form.errors.nama_kelas }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Jurusan / Kompetensi Keahlian</label>
                        <input
                            v-model="form.jurusan"
                            placeholder="Misal: Rekayasa Perangkat Lunak"
                            type="text"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        />
                        <div v-if="form.errors.jurusan" class="mt-1 text-xs text-status-danger">{{ form.errors.jurusan }}</div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-brand-border">
                        <Link
                            :href="route('admin.kelas.index')"
                            class="rounded-lg border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-ink hover:bg-brand-soft"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-brand px-5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus disabled:opacity-50"
                        >
                            Simpan Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
