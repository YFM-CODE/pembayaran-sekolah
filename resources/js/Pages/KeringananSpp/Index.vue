<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    keringanan: Object,
    filters: Object,
});

const isModalOpen = ref(false);
const editMode = ref(false);

const form = useForm({
    id: null,
    nama_program: '',
    tipe_potongan: 'persen',
    nilai_potongan: '',
    syarat_ketentuan: '',
    aktif: true,
});

const openModalCreate = () => {
    editMode.value = false;
    form.reset();
    isModalOpen.value = true;
};

const openModalEdit = (item) => {
    editMode.value = true;
    form.id = item.id;
    form.nama_program = item.nama_program;
    form.tipe_potongan = item.tipe_potongan;
    form.nilai_potongan = item.nilai_potongan;
    form.syarat_ketentuan = item.syarat_ketentuan || '';
    form.aktif = Boolean(item.aktif);
    isModalOpen.value = true;
};

const submit = () => {
    if (editMode.value) {
        form.put(route('admin.keringanan-spp.update', form.id), {
            onSuccess: () => (isModalOpen.value = false),
        });
    } else {
        form.post(route('admin.keringanan-spp.store'), {
            onSuccess: () => (isModalOpen.value = false),
        });
    }
};

const destroyItem = (id) => {
    if (confirm('Yakin ingin menghapus program keringanan ini?')) {
        router.delete(route('admin.keringanan-spp.destroy', id));
    }
};
</script>

<template>
    <Head title="Program Keringanan SPP" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">Program Keringanan / Beasiswa SPP</h1>
                    <p class="text-xs text-ink-muted sm:text-sm mt-0.5">Kelola skema bantuan pemotongan SPP, beasiswa prestasi, atau keringanan khusus.</p>
                </div>
                <button
                    @click="openModalCreate"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Program</span>
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <div class="overflow-hidden rounded-xl border border-brand-border bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-border text-sm">
                        <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
                            <tr>
                                <th scope="col" class="px-5 py-3 font-semibold">Nama Program</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Besaran Potongan</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Status Program</th>
                                <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-border">
                            <tr
                                v-for="item in props.keringanan.data"
                                :key="item.id"
                                class="odd:bg-white even:bg-page/50 transition-colors hover:bg-brand-soft/40"
                            >
                                <td class="whitespace-nowrap px-5 py-3.5 font-semibold text-ink">
                                    {{ item.nama_program }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 font-bold tabular-nums text-status-success">
                                    {{ item.tipe_potongan === 'persen' ? `${item.nilai_potongan}%` : `Rp ${Number(item.nilai_potongan).toLocaleString('id-ID')}` }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span
                                        :class="item.aktif ? 'bg-status-success/15 text-status-success' : 'bg-page text-ink-muted border border-brand-border'"
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    >
                                        {{ item.aktif ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right space-x-3">
                                    <button
                                        @click="openModalEdit(item)"
                                        class="text-xs font-semibold text-brand hover:text-brand-hover hover:underline"
                                    >
                                        Ubah
                                    </button>
                                    <button
                                        @click="destroyItem(item.id)"
                                        class="text-xs font-semibold text-status-danger hover:underline"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!props.keringanan.data || props.keringanan.data.length === 0">
                                <td colspan="4" class="px-5 py-8 text-center text-ink-muted">
                                    Belum ada data program keringanan yang tercatat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 backdrop-blur-xs p-4">
            <div class="w-full max-w-md rounded-2xl border border-brand-border bg-white p-6 shadow-xl">
                <h3 class="mb-1 text-base font-bold text-ink">
                    {{ editMode ? 'Ubah Program Keringanan' : 'Tambah Program Keringanan' }}
                </h3>
                <p class="mb-4 text-xs text-ink-muted">Tentukan jenis dan nilai potongan yang akan dipotongkan dari tagihan SPP.</p>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Nama Program</label>
                        <input
                            v-model="form.nama_program"
                            type="text"
                            placeholder="Contoh: Beasiswa Prestasi Akademik"
                            required
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Tipe Potongan</label>
                            <select
                                v-model="form.tipe_potongan"
                                class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            >
                                <option value="persen">Persentase (%)</option>
                                <option value="nominal">Nominal (Rp)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Nilai Potongan</label>
                            <input
                                v-model="form.nilai_potongan"
                                type="number"
                                min="1"
                                required
                                class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Syarat & Ketentuan</label>
                        <textarea
                            v-model="form.syarat_ketentuan"
                            rows="2"
                            placeholder="Keterangan kriteria penerima..."
                            class="w-full rounded-lg border border-brand-border bg-white px-3 py-2 text-sm text-ink shadow-xs focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
                        ></textarea>
                    </div>
                    <div class="flex items-center gap-2 pt-1">
                        <input
                            v-model="form.aktif"
                            id="aktif"
                            type="checkbox"
                            class="h-4 w-4 rounded border-brand-border text-brand focus:ring-brand-focus"
                        />
                        <label for="aktif" class="text-sm font-medium text-ink cursor-pointer">Program berstatus aktif</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-brand-border">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="rounded-lg border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-ink hover:bg-brand-soft"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-brand px-5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus disabled:opacity-50"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
