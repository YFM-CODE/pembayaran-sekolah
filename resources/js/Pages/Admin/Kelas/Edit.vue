<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    kelas: Object,
});

const form = useForm({
    nama_kelas: props.kelas.nama_kelas,
    tingkat: props.kelas.tingkat,
    jurusan: props.kelas.jurusan,
});

const submit = () => {
    form.put(route('admin.kelas.update', props.kelas.id));
};
</script>

<template>
    <Head title="Edit Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Kelas</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tingkat</label>
                            <select v-model="form.tingkat" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="X">X (Sepuluh)</option>
                                <option value="XI">XI (Sebelas)</option>
                                <option value="XII">XII (Dua Belas)</option>
                            </select>
                            <div v-if="form.errors.tingkat" class="mt-1 text-xs text-red-600">{{ form.errors.tingkat }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                            <input v-model="form.nama_kelas" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.nama_kelas" class="mt-1 text-xs text-red-600">{{ form.errors.nama_kelas }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jurusan</label>
                            <input v-model="form.jurusan" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required />
                            <div v-if="form.errors.jurusan" class="mt-1 text-xs text-red-600">{{ form.errors.jurusan }}</div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <Link :href="route('admin.kelas.index')" class="rounded-md border px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</Link>
                            <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
