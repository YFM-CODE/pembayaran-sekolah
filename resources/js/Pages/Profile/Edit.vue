<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: '',
    },
    infoAkun: {
        type: Object,
        default: () => ({}),
    },
    profilSiswa: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});

const activeSection = ref('identitas');

const inisial = computed(() => {
    const nama = (user.value.name || 'P').trim();
    const parts = nama.split(/\s+/);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return nama.slice(0, 2).toUpperCase();
});

const deskripsiPeran = computed(() => {
    const map = {
        admin: 'Mengelola akun pengguna, master kelas dan jurusan, data pokok siswa, serta pemeliharaan sistem SPP.',
        bendahara: 'Mengusulkan tarif SPP, mengelola beasiswa dan keringanan, menerbitkan tagihan bulanan, serta memverifikasi setoran kasir.',
        komite_sekolah: 'Meninjau dan mengesahkan usulan skema tarif SPP melalui dokumen Berita Acara (BA) resmi.',
        staf_tu: 'Melayani transaksi pembayaran di loket kasir, mencetak kuitansi, melakukan tutup kasir harian, dan meneruskan berkas dispensasi.',
        kepala_sekolah: 'Meninjau dan memutuskan permohonan dispensasi penundaan jatuh tempo serta memantau laporan keuangan sekolah.',
        siswa: 'Memantau tagihan SPP bulanan, riwayat pembayaran kuitansi resmi, serta status pengajuan dispensasi.',
    };
    return map[user.value.role] || 'Akses pengguna terdaftar pada Sistem Informasi SPP Sekolah.';
});

const formatTanggal = (isoString) => {
    if (!isoString) return 'Terdaftar di sistem';
    try {
        return new Date(isoString).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch {
        return isoString;
    }
};

const scrollToSection = (id, key) => {
    activeSection.value = key;
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Profil & Pengaturan Akun" />

        <template #header>
            <div class="header-bar">
                <div>
                    <span class="header-eyebrow">Pengaturan Akun</span>
                    <h1 class="header-title">Profil &amp; Keamanan Pengguna</h1>
                    <p class="header-subtitle">
                        Kelola identitas akun, kredensial email, dan keamanan kata sandi Anda pada Sistem SPP Sekolah.
                    </p>
                </div>
                <div class="header-actions">
                    <span class="status-badge">
                        <span class="status-dot" aria-hidden="true"></span>
                        {{ infoAkun?.is_active !== false ? 'Akun Aktif' : 'Akun Nonaktif' }}
                    </span>
                    <Link :href="route('dashboard')" class="btn-back">
                        &larr; Kembali ke Dashboard
                    </Link>
                </div>
            </div>
        </template>

        <div class="profile-page">
            <div class="profile-container">
                <!-- 1. KARTU IDENTITAS UTAMA (HERO BANNER) -->
                <section class="identity-hero" aria-label="Ringkasan Identitas Pengguna">
                    <div class="identity-main">
                        <div class="avatar-monogram" aria-hidden="true">
                            {{ inisial }}
                        </div>
                        <div class="identity-text">
                            <div class="identity-badges">
                                <span class="role-badge">
                                    {{ user.role_label || user.role }}
                                </span>
                                <span class="id-badge">
                                    ID #{{ String(user.id || infoAkun?.id || 1).padStart(4, '0') }}
                                </span>
                            </div>
                            <h2 class="identity-name">{{ user.name }}</h2>
                            <p class="identity-email">{{ user.email }}</p>
                        </div>
                    </div>

                    <dl class="identity-stats">
                        <div class="stat-box">
                            <dt>Status Akun</dt>
                            <dd>
                                <span class="stat-indicator" aria-hidden="true"></span>
                                {{ infoAkun?.is_active !== false ? 'Terverifikasi & Aktif' : 'Nonaktif' }}
                            </dd>
                        </div>
                        <div class="stat-box">
                            <dt>Terdaftar Sejak</dt>
                            <dd>{{ formatTanggal(infoAkun?.terdaftar_pada) }}</dd>
                        </div>
                        <div class="stat-box">
                            <dt>Pembaruan Terakhir</dt>
                            <dd>{{ formatTanggal(infoAkun?.diperbarui_pada) }}</dd>
                        </div>
                    </dl>
                </section>

                <!-- 2. TATA LETAK DUA KOLOM: SIDEBAR RINGKASAN & FORMULIR PENGATURAN -->
                <div class="profile-grid">
                    <!-- Kolom Kiri: Navigasi Cepat, Peran, & Data Akademik Siswa -->
                    <aside class="profile-sidebar">
                        <!-- Navigasi Cepat Bagian -->
                        <nav class="side-card" aria-label="Navigasi Pengaturan Profil">
                            <h3 class="side-card-title">Menu Pengaturan</h3>
                            <ul class="jump-list">
                                <li>
                                    <button
                                        type="button"
                                        class="jump-btn"
                                        :class="{ active: activeSection === 'identitas' }"
                                        @click="scrollToSection('informasi-profil', 'identitas')"
                                    >
                                        <span class="jump-num">01</span>
                                        <span class="jump-text">
                                            <strong>Informasi Profil</strong>
                                            <small>Nama lengkap &amp; alamat email</small>
                                        </span>
                                    </button>
                                </li>
                                <li>
                                    <button
                                        type="button"
                                        class="jump-btn"
                                        :class="{ active: activeSection === 'keamanan' }"
                                        @click="scrollToSection('keamanan-sandi', 'keamanan')"
                                    >
                                        <span class="jump-num">02</span>
                                        <span class="jump-text">
                                            <strong>Keamanan &amp; Sandi</strong>
                                            <small>Ubah kata sandi akun</small>
                                        </span>
                                    </button>
                                </li>
                                <li>
                                    <button
                                        type="button"
                                        class="jump-btn jump-danger"
                                        :class="{ active: activeSection === 'hapus' }"
                                        @click="scrollToSection('hapus-akun', 'hapus')"
                                    >
                                        <span class="jump-num">03</span>
                                        <span class="jump-text">
                                            <strong>Zona Berbahaya</strong>
                                            <small>Penghapusan akun permanen</small>
                                        </span>
                                    </button>
                                </li>
                            </ul>
                        </nav>

                        <!-- Kartu Data Induk Siswa (Tampil jika akun terhubung ke Siswa) -->
                        <section v-if="profilSiswa" class="side-card student-card" aria-label="Data Akademik Siswa">
                            <div class="student-head">
                                <span class="student-pill">Data Induk Siswa</span>
                                <span class="student-status">{{ profilSiswa.status_siswa || 'Aktif' }}</span>
                            </div>
                            <h3 class="side-card-title">{{ profilSiswa.nama_lengkap }}</h3>
                            <dl class="student-dl">
                                <div>
                                    <dt>Nomor Induk Siswa (NIS)</dt>
                                    <dd class="mono">{{ profilSiswa.nis || '-' }}</dd>
                                </div>
                                <div>
                                    <dt>NISN</dt>
                                    <dd class="mono">{{ profilSiswa.nisn || '-' }}</dd>
                                </div>
                                <div>
                                    <dt>Kelas / Rombel</dt>
                                    <dd>{{ profilSiswa.nama_kelas || '-' }}</dd>
                                </div>
                                <div v-if="profilSiswa.jurusan">
                                    <dt>Kompetensi / Jurusan</dt>
                                    <dd>{{ profilSiswa.jurusan }}</dd>
                                </div>
                            </dl>
                            <p class="student-footnote">
                                Data akademik dikelola oleh Staf TU / Operator Sekolah. Hubungi loket TU apabila terdapat koreksi NIS atau kelas.
                            </p>
                        </section>

                        <!-- Kartu Hak Akses & Panduan Keamanan -->
                        <section class="side-card role-card" aria-label="Informasi Hak Akses">
                            <span class="role-eyebrow">Lingkup Otorisasi</span>
                            <h3 class="side-card-title">{{ user.role_label || user.role }}</h3>
                            <p class="role-desc">
                                {{ deskripsiPeran }}
                            </p>
                            <div class="security-tips">
                                <strong>Tips Keamanan Akun:</strong>
                                <ul>
                                    <li>Perbarui kata sandi secara berkala setiap semester.</li>
                                    <li>Jangan membagikan kata sandi kepada pihak lain.</li>
                                    <li>Pastikan keluar (Log Out) setelah menggunakan perangkat bersama.</li>
                                </ul>
                            </div>
                        </section>
                    </aside>

                    <!-- Kolom Kanan: Formulir Utama -->
                    <div class="profile-main">
                        <div id="informasi-profil" class="form-card">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>

                        <div id="keamanan-sandi" class="form-card">
                            <UpdatePasswordForm />
                        </div>

                        <div id="hapus-akun" class="form-card form-card-danger">
                            <DeleteUserForm />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Header Halaman */
.header-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: var(--space-4);
    font-family: var(--font-sans);
}

.header-eyebrow {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--color-brand);
    margin-bottom: var(--space-1);
}

.header-title {
    font-size: var(--text-2xl);
    font-weight: var(--weight-bold);
    letter-spacing: -0.02em;
    line-height: var(--leading-tight);
    color: var(--color-ink);
    margin: 0;
}

.header-subtitle {
    margin: var(--space-1) 0 0;
    font-size: var(--text-sm);
    color: var(--color-muted);
}

.header-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: var(--space-3);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    height: 38px;
    padding: 0 var(--space-3);
    border-radius: 999px;
    background: var(--color-brand-tint);
    border: 1px solid #b8d8d4;
    color: var(--color-brand-dark);
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: var(--color-brand);
}

.btn-back {
    display: inline-flex;
    align-items: center;
    height: 38px;
    padding: 0 var(--space-3);
    border-radius: var(--radius);
    border: var(--border);
    background: var(--color-surface);
    color: var(--color-ink);
    font-size: var(--text-xs);
    font-weight: var(--weight-medium);
    text-decoration: none;
    transition: background-color 0.15s ease, border-color 0.15s ease;
}

.btn-back:hover {
    background: var(--color-bg);
    border-color: var(--color-line-strong);
}

/* Area Utama Halaman Profil */
.profile-page {
    padding: var(--space-6) var(--space-4) var(--space-8);
    font-family: var(--font-sans);
    color: var(--color-ink);
}

.profile-container {
    max-width: var(--container-app);
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
}

/* 1. Kartu Hero Identitas */
.identity-hero {
    background: linear-gradient(135deg, var(--color-brand) 0%, var(--color-brand-dark) 100%);
    color: #ffffff;
    border-radius: 12px;
    padding: var(--space-6);
    box-shadow: var(--shadow);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-6);
    position: relative;
    overflow: hidden;
}

.identity-hero::after {
    content: '';
    position: absolute;
    right: -40px;
    top: -40px;
    width: 220px;
    height: 220px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.05);
    pointer-events: none;
}

.identity-main {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    min-width: 0;
}

.avatar-monogram {
    width: 68px;
    height: 68px;
    border-radius: 14px;
    background: #ffffff;
    color: var(--color-brand-dark);
    font-size: var(--text-xl);
    font-weight: var(--weight-bold);
    letter-spacing: -0.02em;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    box-shadow: inset 0 -5px 0 var(--color-brand-tint);
}

.identity-text {
    min-width: 0;
}

.identity-badges {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: var(--space-2);
    margin-bottom: var(--space-2);
}

.role-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px var(--space-3);
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    font-size: var(--text-xs);
    font-weight: var(--weight-bold);
    letter-spacing: 0.02em;
}

.id-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px var(--space-2);
    border-radius: var(--radius-sm);
    background: rgba(16, 36, 43, 0.28);
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.75rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.identity-name {
    margin: 0;
    font-size: var(--text-xl);
    font-weight: var(--weight-bold);
    letter-spacing: -0.015em;
    line-height: var(--leading-snug);
    color: #ffffff;
    overflow-wrap: anywhere;
}

.identity-email {
    margin: 2px 0 0;
    font-size: var(--text-sm);
    color: rgba(255, 255, 255, 0.82);
    overflow-wrap: anywhere;
}

.identity-stats {
    margin: 0;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, auto));
    gap: var(--space-5);
    padding: var(--space-4) var(--space-5);
    border-radius: var(--radius);
    background: rgba(16, 36, 43, 0.22);
    border: 1px solid rgba(255, 255, 255, 0.16);
}

.stat-box dt {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: rgba(255, 255, 255, 0.72);
    margin-bottom: 4px;
}

.stat-box dd {
    margin: 0;
    font-size: var(--text-sm);
    font-weight: var(--weight-bold);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.stat-indicator {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #6ee7b7;
}

/* 2. Grid 2 Kolom */
.profile-grid {
    display: grid;
    grid-template-columns: 320px minmax(0, 1fr);
    gap: var(--space-6);
    align-items: start;
}

.profile-sidebar {
    display: flex;
    flex-direction: column;
    gap: var(--space-5);
    position: sticky;
    top: var(--space-5);
}

.side-card {
    background: var(--color-surface);
    border: var(--border);
    border-radius: 10px;
    padding: var(--space-5);
    box-shadow: var(--shadow);
}

.side-card-title {
    margin: 0 0 var(--space-3);
    font-size: var(--text-base);
    font-weight: var(--weight-bold);
    color: var(--color-ink);
    letter-spacing: -0.01em;
}

/* Menu Lompat Bagian */
.jump-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.jump-btn {
    width: 100%;
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
    padding: var(--space-3);
    border-radius: var(--radius);
    border: 1px solid transparent;
    background: var(--color-bg);
    color: var(--color-ink);
    font-family: inherit;
    text-align: left;
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease;
}

.jump-btn:hover {
    background: var(--color-brand-tint);
    border-color: #b8d8d4;
}

.jump-btn.active {
    background: var(--color-brand-tint);
    border-color: var(--color-brand);
    box-shadow: inset 3px 0 0 var(--color-brand);
}

.jump-btn.jump-danger:hover,
.jump-btn.jump-danger.active {
    background: #fdf2f2;
    border-color: #f9d5d5;
    box-shadow: inset 3px 0 0 var(--color-danger);
}

.jump-num {
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    color: var(--color-brand);
    background: var(--color-surface);
    border: var(--border);
    border-radius: var(--radius-sm);
    padding: 2px 6px;
    flex-shrink: 0;
}

.jump-danger .jump-num {
    color: var(--color-danger);
}

.jump-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.jump-text strong {
    font-size: var(--text-sm);
    font-weight: var(--weight-bold);
    color: var(--color-ink);
}

.jump-text small {
    font-size: var(--text-xs);
    color: var(--color-muted);
}

/* Kartu Data Induk Siswa */
.student-card {
    border-top: 4px solid var(--color-brand);
}

.student-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-2);
    margin-bottom: var(--space-2);
}

.student-pill {
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-brand);
}

.student-status {
    padding: 2px var(--space-2);
    border-radius: 999px;
    background: var(--color-brand-tint);
    color: var(--color-brand-dark);
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    text-transform: capitalize;
}

.student-dl {
    margin: var(--space-3) 0;
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    padding-top: var(--space-3);
    border-top: var(--border);
}

.student-dl > div {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: var(--space-2);
}

.student-dl dt {
    font-size: var(--text-xs);
    color: var(--color-muted);
}

.student-dl dd {
    margin: 0;
    font-size: var(--text-sm);
    font-weight: var(--weight-bold);
    color: var(--color-ink);
    text-align: right;
}

.student-dl dd.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.student-footnote {
    margin: var(--space-3) 0 0;
    padding-top: var(--space-3);
    border-top: var(--border);
    font-size: var(--text-xs);
    color: var(--color-muted);
    line-height: 1.5;
}

/* Kartu Peran & Tips Keamanan */
.role-eyebrow {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: var(--weight-bold);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-brand);
    margin-bottom: var(--space-1);
}

.role-desc {
    margin: 0 0 var(--space-4);
    font-size: var(--text-sm);
    color: var(--color-muted);
    line-height: var(--leading-body);
}

.security-tips {
    padding: var(--space-3);
    border-radius: var(--radius);
    background: var(--color-brand-tint);
    border: 1px solid #b8d8d4;
    font-size: var(--text-xs);
    color: var(--color-brand-dark);
}

.security-tips strong {
    display: block;
    margin-bottom: var(--space-1);
    font-weight: var(--weight-bold);
}

.security-tips ul {
    margin: 0;
    padding-left: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: 4px;
}

/* Kolom Kanan: Formulir */
.profile-main {
    display: flex;
    flex-direction: column;
    gap: var(--space-6);
    min-width: 0;
}

.form-card {
    background: var(--color-surface);
    border: var(--border);
    border-radius: 10px;
    padding: var(--space-6);
    box-shadow: var(--shadow);
    scroll-margin-top: var(--space-5);
}

.form-card-danger {
    border-color: #f9d5d5;
    border-left: 4px solid var(--color-danger);
}

/* Responsif: Tablet & Layar Menengah */
@media (max-width: 1024px) {
    .profile-page {
        padding-inline: var(--space-5);
    }

    .profile-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .profile-sidebar {
        position: static;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: var(--space-4);
    }
}

@media (max-width: 768px) {
    .identity-hero {
        flex-direction: column;
        align-items: stretch;
        padding: var(--space-5);
    }

    .identity-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: var(--space-3);
    }
}

/* Responsif: Ponsel */
@media (max-width: 540px) {
    .profile-page {
        padding: var(--space-4) var(--space-3) var(--space-7);
    }

    .header-bar {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
        justify-content: space-between;
    }

    .identity-main {
        flex-direction: column;
        align-items: flex-start;
    }

    .avatar-monogram {
        width: 56px;
        height: 56px;
        font-size: var(--text-lg);
    }

    .identity-stats {
        grid-template-columns: minmax(0, 1fr);
        padding: var(--space-3) var(--space-4);
    }

    .profile-sidebar {
        grid-template-columns: minmax(0, 1fr);
    }

    .form-card {
        padding: var(--space-5) var(--space-4);
    }
}
</style>
