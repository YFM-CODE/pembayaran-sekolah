<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    kelasList: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const tingkat = ref(props.filters.tingkat || '');

const handleFilter = () => {
    router.get(route('admin.kelas.index'), {
        search: search.value,
        tingkat: tingkat.value,
    }, { preserveState: true, replace: true });
};

const deleteKelas = (id) => {
    if (confirm('Yakin ingin menghapus kelas ini? Tindakan ini tidak dapat dibatalkan.')) {
        router.delete(route('admin.kelas.destroy', id));
    }
};
</script>

<template>
    <Head title="Master Data Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Master Data Kelas</h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">Kelola data rombongan belajar, tingkatan kelas, dan peminatan kejuruan.</p>
                </div>
                <Link
                    :href="route('admin.kelas.create')"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Kelas</span>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter Toolbar -->
            <div class="flex flex-col sm:flex-row gap-3 rounded-xl border border-brand-border bg-white p-4 shadow-xs">
                <div class="relative flex-1">
                    <input
                        v-model="search"
                        @input="handleFilter"
                        type="search"
                        placeholder="Cari nama kelas atau jurusan..."
                        class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                    />
                </div>
                <div class="w-full sm:w-56">
                    <select
                        v-model="tingkat"
                        @change="handleFilter"
                        class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                    >
                        <option value="">Semua Tingkat</option>
                        <option value="X">Kelas X</option>
                        <option value="XI">Kelas XI</option>
                        <option value="XII">Kelas XII</option>
                    </select>
                </div>
            </div>

            <!-- Table Card -->
            <div class="overflow-hidden rounded-xl border border-brand-border bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-sm">
                        <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold">Tingkat</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Nama Kelas</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Jurusan</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Jumlah Siswa</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            <tr
                                v-for="item in kelasList.data"
                                :key="item.id"
                                class="odd:bg-white even:bg-page/50 transition-colors hover:bg-brand-soft/40"
                            >
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span class="inline-flex rounded-md bg-brand-soft px-2.5 py-0.5 text-xs font-bold text-brand">
                                        {{ item.tingkat }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 font-semibold text-ink">
                                    {{ item.nama_kelas }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-ink-muted">
                                    {{ item.jurusan }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-ink font-medium tabular-nums">
                                    {{ item.siswa_count }} Siswa
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right space-x-3">
                                    <Link
                                        :href="route('admin.kelas.edit', item.id)"
                                        class="text-xs font-semibold text-brand hover:text-brand-hover hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteKelas(item.id)"
                                        class="text-xs font-semibold text-status-danger hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!kelasList.data || kelasList.data.length === 0">
                                <td colspan="5" class="px-5 py-8 text-center text-ink-muted">
                                    Belum ada data kelas yang terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="kelasList.links && kelasList.links.length > 3" class="flex items-center justify-between border-t border-brand-border bg-white px-5 py-3">
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="(link, i) in kelasList.links"
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
    </AuthenticatedLayout>
</template>
