<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    totalKewajiban: Number,
    tagihanList: Array,
});

const page = usePage();
const nama = computed(() => page.props.auth?.user?.name ?? 'Siswa');
const rupiah = (n) => 'Rp ' + Number(n ?? 0).toLocaleString('id-ID');
const BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const tanggal = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-');
</script>

<template>
    <Head title="Dashboard Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-gray-800">Portal Siswa / Wali Murid</h2>
        </template>

        <div class="py-10 bg-slate-50 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Sambutan & Total Kewajiban -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-xl font-black text-slate-800">Halo, {{ nama }}</h1>
                        <p class="text-xs text-slate-500 mt-1">Pantau status pembayaran iuran SPP sekolah secara berkala.</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Total Sisa Tagihan</span>
                        <div class="text-2xl font-black text-rose-600 mt-1">{{ rupiah(totalKewajiban) }}</div>
                    </div>
                </div>

                <!-- Tagihan yang belum dibayar -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-4">Tagihan Menunggu Pembayaran</h3>

                    <div v-if="!tagihanList.length" class="text-center py-8 text-sm text-slate-500">
                        Alhamdulillah, semua tagihan SPP Anda telah lunas.
                    </div>

                    <div v-else class="divide-y divide-slate-100">
                        <div v-for="t in tagihanList" :key="t.id" class="py-4 flex justify-between items-center">
                            <div>
                                <strong class="text-sm text-slate-800 block">SPP Bulan {{ BULAN[t.bulan - 1] }} {{ t.tahun }}</strong>
                                <span class="text-xs text-slate-400">Jatuh tempo: {{ tanggal(t.jatuh_tempo) }}</span>
                            </div>
                            <span class="font-bold text-rose-600">{{ rupiah(t.sisa_tagihan) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>