<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');

const handleFilter = () => {
    router.get(route('admin.users.index'), {
        search: search.value,
        role: roleFilter.value,
    }, { preserveState: true, replace: true });
};

const toggleStatus = (id) => {
    if (confirm('Ubah status aktif pengguna ini?')) {
        router.patch(route('admin.users.toggle-status', id));
    }
};

const deleteUser = (user) => {
    if (confirm(`Yakin ingin menghapus pengguna "${user.name}"? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

const resetModal = ref(false);
const selectedUser = ref(null);
const resetForm = useForm({
    password: '',
    password_confirmation: '',
});

const openResetModal = (user) => {
    selectedUser.value = user;
    resetForm.reset();
    resetModal.value = true;
};

const submitReset = () => {
    resetForm.post(route('admin.users.reset-password', selectedUser.value.id), {
        onSuccess: () => {
            resetModal.value = false;
            resetForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Manajemen Pengguna</h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">Kelola akun akses sistem, hak peran, status keaktifan, dan kredensial.</p>
                </div>
                <Link
                    :href="route('admin.users.create')"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Pengguna</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter & Search Toolbar -->
            <div class="flex flex-col sm:flex-row gap-3 rounded-xl border border-brand-border bg-white p-4 shadow-xs">
                <div class="relative flex-1">
                    <input
                        v-model="search"
                        @input="handleFilter"
                        type="search"
                        placeholder="Cari nama atau email..."
                        class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                    />
                </div>
                <div class="w-full sm:w-56">
                    <select
                        v-model="roleFilter"
                        @change="handleFilter"
                        class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                    >
                        <option value="">Semua Peran</option>
                        <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                    </select>
                </div>
            </div>

            <!-- Users Table Card -->
            <div class="overflow-hidden rounded-xl border border-brand-border bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-sm">
                        <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold">Nama</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Email</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Peran</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="odd:bg-white even:bg-page/50 transition-colors hover:bg-brand-soft/40"
                            >
                                <td class="whitespace-nowrap px-5 py-3.5 font-medium text-ink">
                                    {{ user.name }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-ink-muted">
                                    {{ user.email }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span class="inline-flex rounded-full bg-brand-soft px-2.5 py-0.5 text-xs font-semibold capitalize text-brand">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span
                                        :class="user.is_active ? 'bg-status-success/15 text-status-success' : 'bg-status-danger/15 text-status-danger'"
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    >
                                        {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right space-x-2">
                                    <button
                                        @click="toggleStatus(user.id)"
                                        class="text-xs font-medium text-amber-700 hover:text-amber-900 hover:underline"
                                    >
                                        Ubah Status
                                    </button>
                                    <button
                                        @click="openResetModal(user)"
                                        class="text-xs font-medium text-brand hover:text-brand-hover hover:underline"
                                    >
                                        Reset Sandi
                                    </button>
                                    <Link
                                        :href="route('admin.users.edit', user.id)"
                                        class="text-xs font-medium text-brand hover:text-brand-hover hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="user.id !== $page.props.auth.user.id"
                                        @click="deleteUser(user)"
                                        class="text-xs font-medium text-status-danger hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!users.data || users.data.length === 0">
                                <td colspan="5" class="px-5 py-8 text-center text-ink-muted">
                                    Tidak ada data pengguna yang sesuai dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links && users.links.length > 3" class="flex items-center justify-between border-t border-brand-border bg-white px-5 py-3">
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="(link, i) in users.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                link.active
                                    ? 'bg-brand-soft text-brand font-semibold border-brand'
                                    : 'text-ink-muted border-brand-border hover:bg-page hover:text-ink',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            class="rounded-lg px-3 py-1.5 text-xs border transition-colors"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div v-if="resetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 backdrop-blur-xs p-4">
            <div class="w-full max-w-md rounded-2xl border border-brand-border bg-white p-6 shadow-xl">
                <h3 class="text-base font-bold text-ink mb-1">Reset Sandi Pengguna</h3>
                <p class="text-xs text-ink-muted mb-4">{{ selectedUser?.name }} ({{ selectedUser?.email }})</p>

                <form @submit.prevent="submitReset" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-ink mb-1">Sandi Baru</label>
                        <input
                            v-model="resetForm.password"
                            type="password"
                            required
                            class="w-full rounded-lg border border-brand-border px-3 py-2 text-sm text-ink focus:border-brand focus:ring-2 focus:ring-brand-focus"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink mb-1">Konfirmasi Sandi</label>
                        <input
                            v-model="resetForm.password_confirmation"
                            type="password"
                            required
                            class="w-full rounded-lg border border-brand-border px-3 py-2 text-sm text-ink focus:border-brand focus:ring-2 focus:ring-brand-focus"
                        />
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="resetModal = false"
                            class="rounded-lg border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-ink hover:bg-brand-soft"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="resetForm.processing"
                            class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-hover disabled:opacity-50"
                        >
                            Simpan Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>