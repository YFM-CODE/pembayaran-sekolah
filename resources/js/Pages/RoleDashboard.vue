<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    role: { type: String, required: true },
    ringkasan: { type: Array, default: () => [] },
    grafik: { type: Object, default: null },
    tagihan: { type: Array, default: () => [] },
});

const konfigurasi = {
    admin: {
        label: 'Administrator',
        sapaan: 'Kelola seluruh master data, pengaturan sistem, dan operasional SPP sekolah.',
        menu: [
            { judul: 'Tarif SPP', isi: 'Atur usulan nominal per tingkat dan tahun ajaran.', rute: 'admin.tarif-spp.index' },
            { judul: 'Program Keringanan', isi: 'Kelola beasiswa dan potongan biaya SPP.', rute: 'admin.keringanan-spp.index' },
            { judul: 'Tagihan Siswa', isi: 'Terbitkan tagihan bulanan dan pantau status bayar.', rute: 'admin.tagihan-spp.index' },
            { judul: 'Manajemen Pengguna', isi: 'Kelola akun admin, bendahara, staf TU, kepsek, komite, dan siswa.', rute: 'admin.users.index' },
            { judul: 'Master Data Kelas', isi: 'Kelola data rombongan belajar, tingkat (X, XI, XII), dan jurusan.', rute: 'admin.kelas.index' },
            { judul: 'Master Data Siswa', isi: 'Pendaftaran siswa baru, update status siswa, dan akun login.', rute: 'admin.siswa.index' },
        ],
    },
    staf_tu: {
        label: 'Staf Tata Usaha',
        sapaan: 'Layani pembayaran iuran di loket dan serahkan setoran kasir harian.',
        menu: [
            { judul: 'Loket Pembayaran', isi: 'Catat pembayaran siswa dan cetak kuitansi.', rute: 'tu.pembayaran-spp.index' },
            { judul: 'Setoran Kasir', isi: 'Tutup kasir harian dan serahkan uang tunai ke bendahara.', rute: 'tu.setoran-kasir.index' },
            { judul: 'Dispensasi Pembayaran', isi: 'Input pengajuan permohonan penundaan bayar.', rute: 'tu.dispensasi-spp.index' },
        ],
    },
    bendahara: {
        label: 'Bendahara Sekolah',
        sapaan: 'Verifikasi setoran uang fisik dan rekonsiliasi penerimaan kas.',
        menu: [
            { judul: 'Verifikasi Setoran', isi: 'Periksa fisik kas loket dan catat selisih setoran.', rute: 'bendahara.setoran-kasir.index' },
            { judul: 'Tagihan & Kas Masuk', isi: 'Generate tagihan massal dan monitoring arus kas.', rute: 'bendahara.tagihan-spp.index' },
        ],
    },
    kepala_sekolah: {
        label: 'Kepala Sekolah',
        sapaan: 'Tinjau dan proses permohonan yang menunggu persetujuan pimpinan.',
        menu: [
            { judul: 'Persetujuan Dispensasi', isi: 'Setujui atau tolak surat penundaan bayar tagihan.', rute: 'kepsek.dispensasi-spp.index' },
            { judul: 'Program Keringanan', isi: 'Pantau siswa penerima bantuan dan beasiswa.', rute: 'kepsek.keringanan-spp.index' },
            { judul: 'Laporan Rekapitulasi', isi: 'Rekapitulasi total penerimaan dan tunggakan.', rute: 'kepsek.laporan-spp.index' },
        ],
    },
    komite_sekolah: {
        label: 'Komite Sekolah',
        sapaan: 'Tinjau dan sahkan usulan tarif SPP melalui Berita Acara resmi.',
        menu: [
            { judul: 'Pengesahan Tarif SPP', isi: 'Tinjau usulan tarif dan simpan keputusan Berita Acara.', rute: 'komite.tarif-spp.index' },
        ],
    },
    siswa: {
        label: 'Siswa / Wali Murid',
        sapaan: 'Pantau kewajiban iuran SPP dan riwayat lembar kuitansi resmi.',
        menu: [
            { judul: 'Kartu Tagihan Saya', isi: 'Daftar tagihan bulanan dan status pelunasan.', rute: 'siswa.tagihan-saya.index' },
            { judul: 'Status Dispensasi', isi: 'Lihat progres persetujuan penundaan bayar.', rute: 'siswa.dispensasi-saya.index' },
        ],
    },
};

const cfg = computed(() => konfigurasi[props.role]);
const page = usePage();
const nama = computed(() => page.props.auth?.user?.nama ?? page.props.auth?.user?.name ?? '');

const rupiah = (n) => 'Rp ' + Number(n ?? 0).toLocaleString('id-ID');
const nilai = (r) => (r.tipe === 'rupiah' ? rupiah(r.nilai) : r.nilai);
const BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const tanggal = (d) => (d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-');
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="dash-header">Dashboard {{ cfg?.label }}</h2>
        </template>

        <div class="dash">
            <div class="wrap">
                <p v-if="!cfg" class="empty">
                    Peran akun Anda ({{ role }}) belum memiliki konfigurasi dashboard. Hubungi Administrator.
                </p>

                <template v-else>
                    <section class="intro">
                        <h1>Selamat datang<span v-if="nama">, {{ nama }}</span>.</h1>
                        <p>{{ cfg.sapaan }}</p>
                    </section>

                    <!-- 1. Ringkasan Metrik Angka Utama -->
                    <section v-if="ringkasan.length" class="blok-first">
                        <h2>Ringkasan Keseluruhan</h2>
                        <div class="stats-grid">
                            <component
                                :is="r.url ? Link : 'div'"
                                v-for="r in ringkasan"
                                :key="r.label"
                                :href="r.url"
                                class="stat-card"
                                :class="{ 'stat-link': !!r.url }"
                            >
                                <div class="stat-content">
                                    <span class="stat-lbl">{{ r.label }}</span>
                                    <span class="stat-num">{{ nilai(r) }}</span>
                                    <span v-if="r.sub" class="stat-sub">{{ r.sub }}</span>
                                </div>
                                <span v-if="r.url" class="stat-arrow">&rarr;</span>
                            </component>
                        </div>
                    </section>

                    <!-- 2. Visualisasi Progres & Grafik (Khusus Admin) -->
                    <section v-if="role === 'admin' && grafik" class="blok">
                        <h2>Papan Analitik & Grafik SPP</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Progress Pelunasan SPP -->
                            <div class="p-5 bg-white border border-gray-200 rounded-lg shadow-sm">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-semibold text-sm text-gray-700">Tingkat Pelunasan Tagihan SPP</span>
                                    <span class="text-xs font-bold px-2 py-0.5 bg-green-100 text-green-800 rounded">{{ grafik.persentase_lunas }}% Lunas</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3 mb-3 overflow-hidden flex">
                                    <div class="bg-green-600 h-3" :style="{ width: grafik.persentase_lunas + '%' }"></div>
                                    <div class="bg-red-500 h-3" :style="{ width: (100 - grafik.persentase_lunas) + '%' }"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>Lunas: <strong>{{ grafik.lunas }}</strong> tagihan</span>
                                    <span>Belum Lunas: <strong>{{ grafik.belum_lunas }}</strong> tagihan</span>
                                </div>
                            </div>

                            <!-- Distribusi Status Siswa -->
                            <div class="p-5 bg-white border border-gray-200 rounded-lg shadow-sm">
                                <span class="font-semibold text-sm text-gray-700 block mb-2">Distribusi Status Siswa</span>
                                <div class="grid grid-cols-3 gap-2 text-center mt-3">
                                    <div class="bg-green-50 p-2 rounded border border-green-200">
                                        <div class="text-lg font-bold text-green-700">{{ grafik.siswa_aktif }}</div>
                                        <div class="text-xs text-gray-600">Aktif</div>
                                    </div>
                                    <div class="bg-blue-50 p-2 rounded border border-blue-200">
                                        <div class="text-lg font-bold text-blue-700">{{ grafik.siswa_lulus }}</div>
                                        <div class="text-xs text-gray-600">Lulus</div>
                                    </div>
                                    <div class="bg-gray-50 p-2 rounded border border-gray-200">
                                        <div class="text-lg font-bold text-gray-700">{{ grafik.siswa_keluar }}</div>
                                        <div class="text-xs text-gray-600">Keluar/Pindah</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 3. Khusus Siswa: Tagihan Belum Lunas -->
                    <section v-if="role === 'siswa'" class="blok">
                        <h2>Tagihan yang belum lunas</h2>
                        <p v-if="!tagihan.length" class="empty">Tidak ada tagihan yang belum lunas. Seluruh kewajiban telah terpenuhi.</p>
                        <ul v-else class="tagihan">
                            <li v-for="t in tagihan" :key="t.id">
                                <div>
                                    <strong>{{ t.siswa }}</strong>
                                    <span class="muted">SPP Bulan {{ BULAN[t.bulan - 1] }} {{ t.tahun }} &middot; Jatuh tempo {{ tanggal(t.jatuh_tempo) }}</span>
                                </div>
                                <span class="sisa">{{ rupiah(t.sisa_tagihan) }}</span>
                            </li>
                        </ul>
                    </section>

                    <!-- 4. Daftar Menu Navigasi Peran -->
                    <section class="blok">
                        <h2>Menu Akses</h2>
                        <nav class="menu" aria-label="Menu utama">
                            <component
                                :is="route().has(m.rute) ? Link : 'div'"
                                v-for="m in cfg.menu"
                                :key="m.rute"
                                :href="route().has(m.rute) ? route(m.rute) : undefined"
                                :class="['item', { off: !route().has(m.rute) }]"
                            >
                                <span class="item-body">
                                    <span class="item-title">{{ m.judul }}</span>
                                    <span class="item-desc">{{ m.isi }}</span>
                                </span>
                                <span class="item-act">{{ route().has(m.rute) ? 'Buka' : 'Segera hadir' }}</span>
                            </component>
                        </nav>
                    </section>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Schibsted+Grotesk:wght@400;600;800&display=swap');

.dash-header { font-family: 'Schibsted Grotesk', system-ui, sans-serif; font-size: 1.1rem; font-weight: 800; color: #12262b; margin: 0; }
.dash {
    --ink: #12262b; --muted: #55696d; --bg: #f8fafc; --line: #e2e8f0;
    --brand: #0e6b63;
    font-family: 'Schibsted Grotesk', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    color: var(--ink); background: var(--bg); line-height: 1.6;
    min-height: calc(100vh - 8rem); padding: 40px 24px;
}
.wrap { max-width: 960px; margin: 0 auto; }
.muted { color: var(--muted); }
.intro { margin-bottom: 28px; }
h1 { font-size: clamp(1.6rem, 3.2vw, 2.2rem); line-height: 1.2; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 8px; }
.intro p { margin: 0; color: var(--muted); max-width: 60ch; }
h2 { font-size: 1.15rem; margin: 0 0 14px; font-weight: 700; color: #1e293b; }
.blok-first { margin-top: 24px; }
.blok { margin-top: 36px; }

/* Grid Metrik */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 14px;
}
.stat-card {
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 8px;
    padding: 16px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.stat-link:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
}
.stat-content {
    display: flex;
    flex-direction: column;
}
.stat-lbl {
    color: var(--muted);
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.stat-num {
    font-size: 1.6rem;
    font-weight: 800;
    line-height: 1.2;
    margin-top: 2px;
    color: var(--ink);
}
.stat-sub {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 4px;
}
.stat-arrow {
    color: #94a3b8;
    font-weight: bold;
    font-size: 1.1rem;
}

.tagihan { list-style: none; margin: 0; padding: 0; border-top: 1px solid var(--line); }
.tagihan li { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 14px 0; border-bottom: 1px solid var(--line); }
.tagihan li > div { display: flex; flex-direction: column; }
.tagihan .muted { font-size: 0.85rem; }
.sisa { font-weight: 800; font-variant-numeric: tabular-nums; white-space: nowrap; color: #b3261e; }
.menu { display: grid; border-top: 1px solid var(--line); }
.item { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 18px 8px 18px 0; border-bottom: 1px solid var(--line); color: inherit; text-decoration: none; }
.item-body { display: flex; flex-direction: column; gap: 2px; }
.item-title { font-weight: 800; font-size: 1rem; }
.item-desc { color: var(--muted); max-width: 56ch; font-size: 0.88rem; }
.item-act { flex: none; padding: 6px 16px; border: 1px solid var(--line); border-radius: 6px; font-weight: 600; font-size: 0.85rem; }
a.item:hover .item-act { background: var(--brand); border-color: var(--brand); color: #fff; }
.item.off { opacity: 0.55; cursor: not-allowed; }
.empty { color: var(--muted); }

@media (max-width: 560px) {
    .dash { padding: 24px 16px; }
    .item { flex-direction: column; align-items: flex-start; gap: 12px; }
}
</style>