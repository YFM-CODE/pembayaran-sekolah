<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tagihan: Object,
    filters: Object,
    auth: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const isGenerateModalOpen = ref(false);

const generateForm = useForm({
    tahun_ajaran: '2026/2027',
    bulan: new Date().getMonth() + 1,
    tahun: new Date().getFullYear(),
    jatuh_tempo: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 10).toISOString().slice(0, 10),
});

watch([search, status], ([sVal, stVal]) => {
    router.get(
        route(route().current()),
        { search: sVal, status: stVal },
        { preserveState: true, replace: true }
    );
});

const submitGenerate = () => {
    generateForm.post(route('admin.tagihan-spp.generate-bulanan'), {
        onSuccess: () => (isGenerateModalOpen.value = false),
    });
};

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
</script>

<template>
    <Head title="Daftar Tagihan SPP" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Manajemen Tagihan SPP</h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">Pantau status kewajiban tagihan bulanan siswa dan terbitkan tagihan periode berjalan.</p>
                </div>
                <button
                    v-if="['admin', 'bendahara'].includes(props.auth?.user?.role)"
                    @click="isGenerateModalOpen = true"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Generate Tagihan Bulanan</span>
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter Toolbar -->
            <div class="flex flex-col sm:flex-row gap-3 rounded-xl border border-brand-border bg-white p-4 shadow-xs">
                <div class="relative flex-1">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Cari NIS, Nama Siswa, No Tagihan..."
                        class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink placeholder:text-ink-muted/80 shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                    />
                </div>
                <div class="w-full sm:w-56">
                    <select
                        v-model="status"
                        class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                    >
                        <option value="">Semua Status Tagihan</option>
                        <option value="belum_lunas">Belum Lunas</option>
                        <option value="sebagian">Sebagian</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
            </div>

            <!-- Table Card -->
            <div class="overflow-hidden rounded-xl border border-brand-border bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-sm">
                        <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold">No. Tagihan</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Identitas Siswa</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Periode SPP</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold">Total Tagihan</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold">Sisa Tagihan</th>
                                <th scope="col" class="px-5 py-3 text-center font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            <tr
                                v-for="item in props.tagihan.data"
                                :key="item.id"
                                class="odd:bg-white even:bg-page/50 transition-colors hover:bg-brand-soft/40"
                            >
                                <td class="whitespace-nowrap px-5 py-3.5 font-mono text-xs font-semibold text-brand">
                                    {{ item.nomor_tagihan }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span class="block font-semibold text-ink">{{ item.siswa?.nama_lengkap }}</span>
                                    <span class="block text-xs text-ink-muted">NIS: {{ item.siswa?.nis }} &middot; {{ item.siswa?.kelas?.nama_kelas || '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-ink-muted">
                                    Bulan {{ item.bulan }} / {{ item.tahun }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right font-semibold tabular-nums text-ink">
                                    {{ formatRupiah(item.total_harus_bayar) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right font-bold tabular-nums text-status-danger">
                                    {{ formatRupiah(item.sisa_tagihan) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                    <span
                                        :class="{
                                            'bg-status-success/15 text-status-success': item.status === 'lunas',
                                            'bg-status-warning/15 text-status-warning': item.status === 'sebagian',
                                            'bg-status-danger/15 text-status-danger': item.status === 'belum_lunas'
                                        }"
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    >
                                        {{ item.status.replace('_', ' ') }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="!props.tagihan.data || props.tagihan.data.length === 0">
                                <td colspan="6" class="px-5 py-8 text-center text-ink-muted">
                                    Tidak ada data tagihan yang sesuai dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Generate -->
        <div v-if="isGenerateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 backdrop-blur-xs p-4">
            <div class="w-full max-w-md rounded-2xl border border-brand-border bg-white p-6 shadow-xl">
                <h3 class="mb-1 text-base font-bold text-ink">Generate Tagihan Bulanan Massal</h3>
                <p class="mb-4 text-xs text-ink-muted">Terbitkan tagihan SPP untuk seluruh siswa aktif berdasarkan skema tarif dan program keringanan.</p>

                <form @submit.prevent="submitGenerate" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Tahun Ajaran</label>
                        <input
                            v-model="generateForm.tahun_ajaran"
                            type="text"
                            required
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Bulan (1-12)</label>
                            <input
                                v-model="generateForm.bulan"
                                type="number"
                                min="1"
                                max="12"
                                required
                                class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Tahun</label>
                            <input
                                v-model="generateForm.tahun"
                                type="number"
                                min="2020"
                                required
                                class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Tanggal Jatuh Tempo</label>
                        <input
                            v-model="generateForm.jatuh_tempo"
                            type="date"
                            required
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        />
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-brand-border">
                        <button
                            type="button"
                            @click="isGenerateModalOpen = false"
                            class="rounded-lg border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-ink hover:bg-brand-soft"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="generateForm.processing"
                            class="rounded-lg bg-brand px-5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus disabled:opacity-50"
                        >
                            Mulai Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
