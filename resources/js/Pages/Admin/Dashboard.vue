<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    ringkasan: { type: Array, default: () => [] },
    grafik: { type: Object, default: null },
});

const page = usePage();
const nama = computed(() => page.props.auth?.user?.name ?? 'Admin');
const rupiah = (n) => 'Rp ' + Number(n ?? 0).toLocaleString('id-ID');
const tanggal = new Date().toLocaleDateString('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
});

// Kartu "Total Tunggakan SPP" dijadikan sorotan utama, sisanya jadi kartu biasa.
const tunggakan = computed(() => props.ringkasan.find((i) => i.tipe === 'rupiah'));
const kartu = computed(() => props.ringkasan.filter((i) => i.tipe !== 'rupiah'));

// Ikon (path heroicons outline) & warna aksen per kartu, dicocokkan lewat label.
const gaya = {
    'Total Siswa Terdaftar': { ikon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z', warna: 'bg-indigo-50 text-indigo-600' },
    'Master Rombel / Kelas': { ikon: 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5', warna: 'bg-sky-50 text-sky-600' },
    'Pengguna Sistem': { ikon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', warna: 'bg-violet-50 text-violet-600' },
    'Skema Tarif SPP': { ikon: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z', warna: 'bg-emerald-50 text-emerald-600' },
    'Program Keringanan': { ikon: 'M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z', warna: 'bg-amber-50 text-amber-600' },
    'Tagihan Belum Lunas': { ikon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', warna: 'bg-rose-50 text-rose-600' },
};
const gayaDefault = { ikon: 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z', warna: 'bg-slate-100 text-slate-600' };
const gayaKartu = (label) => gaya[label] ?? gayaDefault;

// Pintasan dikelompokkan sesuai rute di grup admin.* pada web.php.
const kelompok = [
    {
        judul: 'Master data',
        item: [
            { teks: 'Tambah siswa', rute: 'admin.siswa.create' },
            { teks: 'Daftar siswa', rute: 'admin.siswa.index' },
            { teks: 'Tambah kelas', rute: 'admin.kelas.create' },
            { teks: 'Daftar kelas', rute: 'admin.kelas.index' },
        ],
    },
    {
        judul: 'Pengguna',
        item: [
            { teks: 'Tambah pengguna', rute: 'admin.users.create' },
            { teks: 'Kelola akun & status', rute: 'admin.users.index' },
        ],
    },
    {
        judul: 'Keuangan SPP',
        item: [
            { teks: 'Buat tarif SPP', rute: 'admin.tarif-spp.create' },
            { teks: 'Atur keringanan', rute: 'admin.keringanan-spp.index' },
            { teks: 'Pantau tagihan', rute: 'admin.tagihan-spp.index' },
        ],
    },
];

const totalSiswa = computed(() => {
    const g = props.grafik;
    return g ? g.siswa_aktif + g.siswa_lulus + g.siswa_keluar : 0;
});
const lebar = (n) => (totalSiswa.value > 0 ? Math.round((n / totalSiswa.value) * 100) : 0);
const lunas = computed(() => Number(props.grafik?.persentase_lunas ?? 0));
</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-slate-800">Dashboard Administrator</h2>
        </template>

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Sambutan + sorotan tunggakan -->
                <section class="overflow-hidden rounded-2xl bg-slate-900 text-white shadow-lg">
                    <div class="grid gap-8 p-6 sm:p-8 lg:grid-cols-5">
                        <div class="flex flex-col justify-between lg:col-span-3">
                            <div>
                                <p class="text-sm text-slate-400">{{ tanggal }}</p>
                                <h1 class="mt-1 text-3xl font-black tracking-tight sm:text-4xl">Selamat datang, {{ nama }}.</h1>
                                <p class="mt-2 max-w-lg text-sm text-slate-300">
                                    Kelola data induk, akun pengguna, dan pantau pelunasan SPP dari satu tempat.
                                </p>
                            </div>

                            <div v-if="tunggakan" class="mt-8">
                                <p class="text-sm text-slate-400">{{ tunggakan.label }}</p>
                                <p class="mt-1 text-4xl font-black tabular-nums text-amber-300 sm:text-5xl">
                                    {{ rupiah(tunggakan.nilai) }}
                                </p>
                                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                                    <span class="text-slate-400">{{ tunggakan.sub }}</span>
                                    <Link
                                        v-if="tunggakan.url"
                                        :href="tunggakan.url"
                                        class="rounded-lg bg-white/10 px-3 py-1.5 font-semibold text-white ring-1 ring-white/20 transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300"
                                    >
                                        Lihat rincian tagihan
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Donut pelunasan -->
                        <div v-if="grafik" class="flex items-center justify-center gap-6 lg:col-span-2">
                            <div class="relative h-40 w-40 shrink-0">
                                <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90" role="img" :aria-label="`${lunas}% tagihan lunas`">
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#f43f5e" stroke-width="3.5" />
                                    <circle
                                        cx="18" cy="18" r="15.9155" fill="none" stroke="#10b981" stroke-width="3.5"
                                        stroke-linecap="butt"
                                        :stroke-dasharray="`${lunas} ${100 - lunas}`"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-3xl font-black tabular-nums">{{ lunas }}%</span>
                                    <span class="text-xs text-slate-400">lunas</span>
                                </div>
                            </div>
                            <ul class="space-y-3 text-sm">
                                <li class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-slate-300">Lunas</span>
                                    <strong class="tabular-nums">{{ grafik.lunas }}</strong>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                    <span class="text-slate-300">Belum lunas</span>
                                    <strong class="tabular-nums">{{ grafik.belum_lunas }}</strong>
                                </li>
                                <li class="border-t border-white/10 pt-2 text-slate-400">
                                    Total {{ grafik.total_tagihan }} tagihan
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Ringkasan sistem -->
                <section>
                    <h3 class="mb-3 text-base font-bold text-slate-800">Ringkasan sistem</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <component
                            :is="item.url ? Link : 'div'"
                            v-for="item in kartu"
                            :key="item.label"
                            :href="item.url"
                            class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                        >
                            <span :class="['flex h-11 w-11 shrink-0 items-center justify-center rounded-xl', gayaKartu(item.label).warna]">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="gayaKartu(item.label).ikon" />
                                </svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm text-slate-500">{{ item.label }}</span>
                                <span class="block text-2xl font-black tabular-nums text-slate-900">{{ item.nilai }}</span>
                                <span class="block truncate text-xs text-slate-400">{{ item.sub }}</span>
                            </span>
                        </component>
                    </div>
                </section>

                <div class="grid gap-6 lg:grid-cols-5">
                    <!-- Distribusi siswa -->
                    <section v-if="grafik" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                        <div class="mb-4 flex items-baseline justify-between">
                            <h3 class="text-base font-bold text-slate-800">Status siswa</h3>
                            <span class="text-sm text-slate-500">{{ totalSiswa }} siswa</span>
                        </div>

                        <div class="mb-5 flex h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="bg-emerald-500" :style="{ width: lebar(grafik.siswa_aktif) + '%' }"></div>
                            <div class="bg-sky-500" :style="{ width: lebar(grafik.siswa_lulus) + '%' }"></div>
                            <div class="bg-slate-400" :style="{ width: lebar(grafik.siswa_keluar) + '%' }"></div>
                        </div>

                        <dl class="space-y-3 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Aktif</dt>
                                <dd class="font-bold tabular-nums text-slate-900">{{ grafik.siswa_aktif }} <span class="font-normal text-slate-400">({{ lebar(grafik.siswa_aktif) }}%)</span></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span>Lulus</dt>
                                <dd class="font-bold tabular-nums text-slate-900">{{ grafik.siswa_lulus }} <span class="font-normal text-slate-400">({{ lebar(grafik.siswa_lulus) }}%)</span></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>Keluar / pindah</dt>
                                <dd class="font-bold tabular-nums text-slate-900">{{ grafik.siswa_keluar }} <span class="font-normal text-slate-400">({{ lebar(grafik.siswa_keluar) }}%)</span></dd>
                            </div>
                        </dl>
                    </section>

                    <!-- Pintasan -->
                    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-3">
                        <h3 class="mb-4 text-base font-bold text-slate-800">Pintasan</h3>
                        <div class="grid gap-5 sm:grid-cols-3">
                            <div v-for="grup in kelompok" :key="grup.judul">
                                <p class="mb-2 text-sm font-semibold text-slate-500">{{ grup.judul }}</p>
                                <ul class="space-y-1.5">
                                    <li v-for="l in grup.item" :key="l.rute">
                                        <Link
                                            :href="route(l.rute)"
                                            class="block rounded-lg bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                                        >
                                            {{ l.teks }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>