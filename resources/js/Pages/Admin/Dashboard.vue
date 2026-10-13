<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    ringkasan: { type: Array, default: () => [] },
    grafik: { type: Object, default: null },
});

const page = usePage();
const nama = computed(() => page.props.auth?.user?.name ?? 'Administrator');
const rupiah = (n) => 'Rp ' + Number(n ?? 0).toLocaleString('id-ID');
const tanggal = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

// Kartu "Total Tunggakan SPP" dijadikan sorotan utama, sisanya jadi kartu metrik.
const tunggakan = computed(() => props.ringkasan.find((i) => i.tipe === 'rupiah'));
const kartu = computed(() => props.ringkasan.filter((i) => i.tipe !== 'rupiah'));

const kategoriKartu = (label) => {
    const map = {
        'Total Siswa Terdaftar': 'Data Pokok',
        'Master Rombel / Kelas': 'Akademik',
        'Pengguna Sistem': 'Otorisasi',
        'Skema Tarif SPP': 'Keuangan',
        'Program Keringanan': 'Beasiswa',
        'Tagihan Belum Lunas': 'Penagihan',
    };
    return map[label] || 'Ringkasan';
};

// Pintasan dikelompokkan sesuai rute di grup admin.* pada web.php.
const kelompok = [
    {
        judul: 'Master Data Akademik',
        item: [
            { teks: 'Tambah Siswa Baru', sub: 'Input biodata & akun siswa', rute: 'admin.siswa.create' },
            { teks: 'Daftar Master Siswa', sub: 'Kelola status & kelas siswa', rute: 'admin.siswa.index' },
            { teks: 'Tambah Kelas Baru', sub: 'Buka rombel tahun ajaran', rute: 'admin.kelas.create' },
            { teks: 'Daftar Master Kelas', sub: 'Tingkat X, XI, dan XII', rute: 'admin.kelas.index' },
        ],
    },
    {
        judul: 'Otorisasi & Pengguna',
        item: [
            { teks: 'Tambah Pengguna', sub: 'Buat akun petugas atau siswa', rute: 'admin.users.create' },
            { teks: 'Manajemen Pengguna', sub: 'Kelola peran, status & sandi', rute: 'admin.users.index' },
            { teks: 'Profil & Keamanan Saya', sub: 'Pengaturan akun administrator', rute: 'profile.edit' },
        ],
    },
    {
        judul: 'Keuangan & Tagihan SPP',
        item: [
            { teks: 'Skema Tarif SPP', sub: 'Susun tarif per tingkat kelas', rute: 'admin.tarif-spp.index' },
            { teks: 'Program Keringanan', sub: 'Beasiswa & SK potongan SPP', rute: 'admin.keringanan-spp.index' },
            { teks: 'Tagihan Bulanan Siswa', sub: 'Generate & pantau pelunasan', rute: 'admin.tagihan-spp.index' },
        ],
    },
];

const totalSiswa = computed(() => {
    const g = props.grafik;
    return g ? (g.siswa_aktif || 0) + (g.siswa_lulus || 0) + (g.siswa_keluar || 0) : 0;
});

const lebar = (n) => (totalSiswa.value > 0 ? Math.round(((n || 0) / totalSiswa.value) * 100) : 0);
const lunas = computed(() => Number(props.grafik?.persentase_lunas ?? 0));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard Administrator" />

        <template #header>
            <div class="page-head">
                <div>
                    <span class="page-head-eyebrow">Pusat Kendali Sistem</span>
                    <h1 class="page-head-title">Dashboard Administrator</h1>
                    <p class="page-head-sub">
                        Kelola data induk siswa, rombongan belajar, akun pengguna, dan pantau kewajiban SPP secara terpusat.
                    </p>
                </div>
                <div class="page-head-actions">
                    <span class="date-chip">{{ tanggal }}</span>
                </div>
            </div>
        </template>

        <div class="admin-page">
            <div class="admin-wrap">
                <!-- 1. Hero Banner Sambutan & Sorotan Tunggakan -->
                <section class="hero-banner" aria-label="Ringkasan Utama Keuangan SPP">
                    <div class="hero-left">
                        <div class="hero-status-pill">
                            <span class="hero-dot" aria-hidden="true"></span>
                            <span>Sistem Administrasi SPP Aktif</span>
                        </div>
                        <h2 class="hero-title">Selamat datang kembali, {{ nama }}.</h2>
                        <p class="hero-desc">
                            Pantau realisasi pembayaran SPP, komposisi keaktifan siswa, serta kelola seluruh parameter master data sekolah dari satu halaman.
                        </p>

                        <div v-if="tunggakan" class="hero-highlight">
                            <span class="highlight-label">{{ tunggakan.label }}</span>
                            <div class="highlight-value">{{ rupiah(tunggakan.nilai) }}</div>
                            <div class="highlight-foot">
                                <span>{{ tunggakan.sub }}</span>
                                <Link
                                    v-if="tunggakan.url"
                                    :href="tunggakan.url"
                                    class="highlight-link"
                                >
                                    Buka Rincian Tagihan &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Donut Rasio Pelunasan -->
                    <div v-if="grafik" class="hero-right">
                        <span class="donut-title">Rasio Pelunasan Tagihan</span>
                        <div class="donut-body">
                            <div class="donut-wrap">
                                <svg viewBox="0 0 36 36" class="donut-svg" role="img" :aria-label="`${lunas}% tagihan lunas`">
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3.6" />
                                    <circle
                                        cx="18"
                                        cy="18"
                                        r="15.9155"
                                        fill="none"
                                        stroke="#6ee7b7"
                                        stroke-width="3.6"
                                        stroke-linecap="round"
                                        :stroke-dasharray="`${lunas} ${100 - lunas}`"
                                    />
                                </svg>
                                <div class="donut-center">
                                    <strong>{{ lunas }}%</strong>
                                    <span>Lunas</span>
                                </div>
                            </div>

                            <ul class="donut-legend">
                                <li>
                                    <span class="legend-dot dot-lunas"></span>
                                    <span class="legend-lbl">Tagihan Lunas</span>
                                    <strong>{{ grafik.lunas }}</strong>
                                </li>
                                <li>
                                    <span class="legend-dot dot-tunggak"></span>
                                    <span class="legend-lbl">Belum Lunas</span>
                                    <strong>{{ grafik.belum_lunas }}</strong>
                                </li>
                                <li class="legend-total">
                                    <span>Total Diterbitkan</span>
                                    <strong>{{ grafik.total_tagihan }} tagihan</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 2. Kartu Ringkasan Sistem -->
                <section class="section-block" aria-label="Metrik Master Data">
                    <div class="section-bar">
                        <div>
                            <h2 class="section-heading">Ringkasan Sistem &amp; Master Data</h2>
                            <p class="section-sub">Data aktual yang tercatat pada basis data sekolah saat ini.</p>
                        </div>
                    </div>

                    <div class="metric-grid">
                        <component
                            :is="item.url ? Link : 'div'"
                            v-for="item in kartu"
                            :key="item.label"
                            :href="item.url"
                            class="metric-card"
                            :class="{ 'is-Interactive': !!item.url, 'is-warning': item.label === 'Tagihan Belum Lunas' }"
                        >
                            <div class="metric-top">
                                <span class="metric-tag">{{ kategoriKartu(item.label) }}</span>
                                <span v-if="item.url" class="metric-arrow" aria-hidden="true">Kelola &rarr;</span>
                            </div>
                            <div class="metric-value">{{ item.nilai }}</div>
                            <div class="metric-label">{{ item.label }}</div>
                            <div class="metric-sub">{{ item.sub }}</div>
                        </component>
                    </div>
                </section>

                <!-- 3. Distribusi Siswa & Pintasan Operasional -->
                <div class="bottom-grid">
                    <!-- Distribusi Status Siswa -->
                    <section v-if="grafik" class="panel-card">
                        <div class="panel-head">
                            <div>
                                <h3 class="panel-title">Komposisi Status Siswa</h3>
                                <p class="panel-sub">Proporsi keaktifan siswa terdaftar</p>
                            </div>
                            <span class="badge-pill badge-success">{{ totalSiswa }} Siswa</span>
                        </div>

                        <div class="progress-track" aria-hidden="true">
                            <div class="bar-aktif" :style="{ width: lebar(grafik.siswa_aktif) + '%' }"></div>
                            <div class="bar-lulus" :style="{ width: lebar(grafik.siswa_lulus) + '%' }"></div>
                            <div class="bar-keluar" :style="{ width: lebar(grafik.siswa_keluar) + '%' }"></div>
                        </div>

                        <dl class="status-list">
                            <div class="status-row">
                                <dt>
                                    <span class="status-dot dot-aktif"></span>
                                    <span>Siswa Aktif</span>
                                </dt>
                                <dd>
                                    <strong>{{ grafik.siswa_aktif }}</strong>
                                    <span>({{ lebar(grafik.siswa_aktif) }}%)</span>
                                </dd>
                            </div>
                            <div class="status-row">
                                <dt>
                                    <span class="status-dot dot-lulus"></span>
                                    <span>Alumni / Lulus</span>
                                </dt>
                                <dd>
                                    <strong>{{ grafik.siswa_lulus }}</strong>
                                    <span>({{ lebar(grafik.siswa_lulus) }}%)</span>
                                </dd>
                            </div>
                            <div class="status-row">
                                <dt>
                                    <span class="status-dot dot-keluar"></span>
                                    <span>Pindah / Keluar</span>
                                </dt>
                                <dd>
                                    <strong>{{ grafik.siswa_keluar }}</strong>
                                    <span>({{ lebar(grafik.siswa_keluar) }}%)</span>
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <!-- Pintasan Menu Admin -->
                    <section class="panel-card">
                        <div class="panel-head">
                            <div>
                                <h3 class="panel-title">Pintasan Operasional Administrator</h3>
                                <p class="panel-sub">Akses cepat ke modul pengelolaan data pokok dan keuangan SPP</p>
                            </div>
                        </div>

                        <div class="shortcut-columns">
                            <div v-for="grup in kelompok" :key="grup.judul" class="shortcut-group">
                                <h4 class="shortcut-group-title">{{ grup.judul }}</h4>
                                <ul class="shortcut-list">
                                    <li v-for="l in grup.item" :key="l.teks">
                                        <Link :href="route(l.rute)" class="shortcut-item">
                                            <div>
                                                <strong class="shortcut-name">{{ l.teks }}</strong>
                                                <small class="shortcut-desc">{{ l.sub }}</small>
                                            </div>
                                            <span class="shortcut-go" aria-hidden="true">&rarr;</span>
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

<style scoped>
.date-chip {
    display: inline-flex;
    align-items: center;
    height: 38px;
    padding: 0 var(--space-3);
    border-radius: var(--radius);
    background: var(--color-bg);
    border: var(--border);
    color: var(--color-muted);
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
}

/* Hero Banner */
.hero-banner {
    background: linear-gradient(135deg, var(--color-brand) 0%, var(--color-brand-dark) 100%);
    color: #ffffff;
    border-radius: 12px;
    padding: var(--space-6);
    box-shadow: var(--shadow);
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
    gap: var(--space-6);
    align-items: center;
}

.hero-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
    margin-bottom: var(--space-3);
}

.hero-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #6ee7b7;
}

.hero-title {
    margin: 0 0 var(--space-2);
    font-size: clamp(1.5rem, 2.6vw, 2rem);
    font-weight: var(--weight-bold);
    letter-spacing: -0.02em;
    line-height: var(--leading-tight);
    color: #ffffff;
}

.hero-desc {
    margin: 0;
    font-size: var(--text-sm);
    color: rgba(255, 255, 255, 0.86);
    max-width: 58ch;
    line-height: var(--leading-body);
}

.hero-highlight {
    margin-top: var(--space-5);
    padding-top: var(--space-4);
    border-top: 1px solid rgba(255, 255, 255, 0.18);
}

.highlight-label {
    display: block;
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(255, 255, 255, 0.78);
}

.highlight-value {
    margin-top: 4px;
    font-size: clamp(1.75rem, 3vw, 2.35rem);
    font-weight: var(--weight-bold);
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
    color: #fde68a;
}

.highlight-foot {
    margin-top: var(--space-2);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: var(--space-3);
    font-size: var(--text-xs);
    color: rgba(255, 255, 255, 0.82);
}

.highlight-link {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-weight: var(--weight-bold);
    text-decoration: none;
    transition: background-color 0.15s ease;
}

.highlight-link:hover {
    background: rgba(255, 255, 255, 0.26);
}

/* Donut Box */
.hero-right {
    background: rgba(16, 36, 43, 0.24);
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 10px;
    padding: var(--space-5);
}

.donut-title {
    display: block;
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(255, 255, 255, 0.82);
    margin-bottom: var(--space-4);
}

.donut-body {
    display: flex;
    align-items: center;
    gap: var(--space-5);
}

.donut-wrap {
    position: relative;
    width: 128px;
    height: 128px;
    flex-shrink: 0;
}

.donut-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.donut-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.donut-center strong {
    font-size: 1.5rem;
    font-weight: var(--weight-bold);
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
}

.donut-center span {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.78);
}

.donut-legend {
    list-style: none;
    padding: 0;
    margin: 0;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
    font-size: var(--text-xs);
}

.donut-legend li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    flex-shrink: 0;
}

.dot-lunas {
    background: #6ee7b7;
}

.dot-tunggak {
    background: #fcd34d;
}

.legend-lbl {
    flex: 1;
    color: rgba(255, 255, 255, 0.85);
}

.legend-total {
    padding-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.16);
    color: rgba(255, 255, 255, 0.78);
}

/* Section & Metric Cards */
.section-bar {
    margin-bottom: var(--space-3);
}

.section-heading {
    margin: 0;
    font-size: var(--text-lg);
    font-weight: var(--weight-bold);
    color: var(--color-ink);
}

.section-sub {
    margin: 2px 0 0;
    font-size: var(--text-xs);
    color: var(--color-muted);
}

.metric-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-4);
}

.metric-card {
    background: var(--color-surface);
    border: var(--border);
    border-left: 4px solid var(--color-brand);
    border-radius: 10px;
    padding: var(--space-5);
    box-shadow: var(--shadow);
    text-decoration: none;
    color: var(--color-ink);
    display: flex;
    flex-direction: column;
    transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

.metric-card.is-warning {
    border-left-color: #d97706;
}

.metric-card.is-Interactive:hover {
    transform: translateY(-2px);
    border-color: var(--color-brand);
    box-shadow: var(--shadow-lift);
}

.metric-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-2);
    margin-bottom: var(--space-3);
}

.metric-tag {
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    background: var(--color-brand-tint);
    color: var(--color-brand-dark);
    font-size: 0.7rem;
    font-weight: var(--weight-bold);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.is-warning .metric-tag {
    background: #fef3c7;
    color: #92400e;
}

.metric-arrow {
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
    color: var(--color-brand);
}

.metric-value {
    font-size: 1.85rem;
    font-weight: var(--weight-bold);
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
    color: var(--color-ink);
}

.metric-label {
    margin-top: 6px;
    font-size: var(--text-sm);
    font-weight: var(--weight-bold);
    color: var(--color-ink);
}

.metric-sub {
    margin-top: 2px;
    font-size: var(--text-xs);
    color: var(--color-muted);
}

/* Bottom Grid */
.bottom-grid {
    display: grid;
    grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.4fr);
    gap: var(--space-5);
    align-items: start;
}

.panel-card {
    background: var(--color-surface);
    border: var(--border);
    border-radius: 10px;
    padding: var(--space-5);
    box-shadow: var(--shadow);
}

.panel-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-3);
    padding-bottom: var(--space-3);
    margin-bottom: var(--space-4);
    border-bottom: var(--border);
}

.panel-title {
    margin: 0;
    font-size: var(--text-base);
    font-weight: var(--weight-bold);
    color: var(--color-ink);
}

.panel-sub {
    margin: 2px 0 0;
    font-size: var(--text-xs);
    color: var(--color-muted);
}

.progress-track {
    display: flex;
    height: 12px;
    border-radius: 999px;
    background: var(--color-bg);
    border: var(--border);
    overflow: hidden;
    margin-bottom: var(--space-4);
}

.bar-aktif {
    background: var(--color-brand);
}

.bar-lulus {
    background: #0284c7;
}

.bar-keluar {
    background: #94a3b8;
}

.status-list {
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: var(--text-sm);
    padding-bottom: var(--space-2);
    border-bottom: 1px dashed var(--color-line);
}

.status-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.status-row dt {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--color-ink);
    font-weight: var(--weight-medium);
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
}

.dot-aktif {
    background: var(--color-brand);
}

.dot-lulus {
    background: #0284c7;
}

.dot-keluar {
    background: #94a3b8;
}

.status-row dd {
    margin: 0;
    font-variant-numeric: tabular-nums;
}

.status-row dd span {
    margin-left: 4px;
    font-size: var(--text-xs);
    color: var(--color-muted);
}

/* Shortcuts */
.shortcut-columns {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-4);
}

.shortcut-group-title {
    margin: 0 0 var(--space-2);
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-brand);
}

.shortcut-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.shortcut-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 10px 12px;
    border-radius: var(--radius);
    background: var(--color-bg);
    border: var(--border);
    text-decoration: none;
    color: var(--color-ink);
    transition: background-color 0.15s ease, border-color 0.15s ease;
}

.shortcut-item:hover {
    background: var(--color-brand-tint);
    border-color: var(--color-brand);
}

.shortcut-name {
    display: block;
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
    color: var(--color-ink);
}

.shortcut-desc {
    display: block;
    font-size: 0.72rem;
    color: var(--color-muted);
    margin-top: 1px;
}

.shortcut-go {
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
    color: var(--color-brand);
}

@media (max-width: 1024px) {
    .hero-banner,
    .bottom-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .shortcut-columns {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .hero-banner {
        padding: var(--space-5) var(--space-4);
    }

    .donut-body {
        flex-direction: column;
        align-items: flex-start;
    }

    .donut-legend {
        width: 100%;
    }

    .metric-grid,
    .shortcut-columns {
        grid-template-columns: minmax(0, 1fr);
    }
}
</style>