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
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Tambah Pengguna</h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">Daftarkan akun petugas baru untuk mengakses sistem.</p>
                </div>
                <Link
                    :href="route('admin.users.index')"
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
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Nama Lengkap</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            placeholder="Contoh: Budi Santoso"
                            required
                        />
                        <div v-if="form.errors.name" class="mt-1 text-xs text-status-danger">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Alamat Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            placeholder="budi@sekolah.sch.id"
                            required
                        />
                        <div v-if="form.errors.email" class="mt-1 text-xs text-status-danger">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Peran Akun</label>
                        <select
                            v-model="form.role"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            required
                        >
                            <option value="" disabled>Pilih Peran Sistem</option>
                            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                        </select>
                        <div v-if="form.errors.role" class="mt-1 text-xs text-status-danger">{{ form.errors.role }}</div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1.5">Kata Sandi</label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full rounded-lg border border-brand-border bg-white px-3.5 py-2.5 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            placeholder="Minimal 8 karakter"
                            required
                        />
                        <div v-if="form.errors.password" class="mt-1 text-xs text-status-danger">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-brand-border text-brand focus:ring-brand-focus"
                        />
                        <label for="is_active" class="text-sm font-medium text-ink cursor-pointer">
                            Akun langsung aktif dan dapat masuk ke sistem
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5 border-t border-brand-border">
                        <Link
                            :href="route('admin.users.index')"
                            class="rounded-lg border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-ink hover:bg-brand-soft"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-brand px-5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus disabled:opacity-50"
                        >
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
