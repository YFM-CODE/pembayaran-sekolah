<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: false },
    laravelVersion: String,
    phpVersion: String,
});

const aktif = ref(0);
const terlihat = ref(false);
const tabTampil = ref(true);
const jeda = ref(false);
const kurangiGerak = ref(false);
const bagian = ref(null);
const capKunci = ref(0);

const JEDA = 2000; // lama tiap tahap tampil (ms)
let timer = null;
let pengamat = null;

// Berputar sendiri, kecuali: kursor/fokus sedang di area alur, bagian tidak
// terlihat, tab tersembunyi, atau pengguna meminta gerakan dikurangi.
const sedangPutar = computed(
    () => terlihat.value && tabTampil.value && !jeda.value && !kurangiGerak.value,
);

const hentikan = () => {
    clearInterval(timer);
    timer = null;
};

watch(sedangPutar, (putar) => {
    hentikan();
    if (putar) {
        timer = setInterval(() => {
            aktif.value = (aktif.value + 1) % alur.length;
        }, JEDA);
    }
});

const ubahTab = () => {
    tabTampil.value = !document.hidden;
};

onMounted(() => {
    kurangiGerak.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.addEventListener('visibilitychange', ubahTab);
    if ('IntersectionObserver' in window && bagian.value) {
        pengamat = new IntersectionObserver(([e]) => {
            terlihat.value = e.isIntersecting;
        }, { threshold: 0.4 });
        pengamat.observe(bagian.value);
    }
});

onBeforeUnmount(() => {
    hentikan();
    pengamat?.disconnect();
    document.removeEventListener('visibilitychange', ubahTab);
});

// Direktif v-muncul: elemen tampil perlahan saat masuk layar
const vMuncul = {
    mounted(el, binding) {
        if (
            !('IntersectionObserver' in window) ||
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) return;
        el.classList.add('muncul-awal');
        el.style.setProperty('--tunda', `${(binding.value || 0) * 90}ms`);
        const io = new IntersectionObserver((entri) => {
            if (entri.some((e) => e.isIntersecting)) {
                el.classList.add('muncul-masuk');
                io.disconnect();
            }
        }, { threshold: 0.15 });
        io.observe(el);
    },
};

const alur = [
    { judul: 'Penyusunan usulan tarif', pelaku: 'Bendahara', isi: 'Bendahara menyusun usulan tarif SPP per tahun ajaran dan tingkat kelas ke dalam sistem.' },
    { judul: 'Persetujuan komite', pelaku: 'Komite Sekolah', isi: 'Komite Sekolah meninjau usulan, lalu mengesahkan tarif dengan mengunggah Berita Acara (BA) resmi.' },
    { judul: 'Penerbitan tagihan', pelaku: 'Bendahara', isi: 'Bendahara menjalankan generate tagihan massal, dan sistem otomatis menghitung potongan beasiswa atau keringanan.' },
    { judul: 'Pembayaran di loket', pelaku: 'Staf TU', isi: 'Staf TU memproses transaksi siswa di kasir dan mencetak bukti pembayaran, berupa struk thermal atau kuitansi A5.' },
    { judul: 'Verifikasi setoran kas', pelaku: 'Staf TU dan Bendahara', isi: 'Staf TU melakukan tutup kasir harian, kemudian Bendahara mencocokkan uang fisik dengan catatan sistem.' },
];

const peran = [
    { nama: 'Administrator', tugas: 'Mengelola akun pengguna, master kelas dan jurusan, data pokok siswa, serta tautan akun.' },
    { nama: 'Bendahara sekolah', tugas: 'Mengusulkan tarif SPP, mengelola beasiswa dan keringanan, menerbitkan tagihan bulanan, serta memverifikasi setoran kasir.' },
    { nama: 'Komite sekolah', tugas: 'Meninjau dan mengesahkan usulan tarif melalui dokumen Berita Acara.' },
    { nama: 'Staf tata usaha', tugas: 'Melayani transaksi di loket, mencetak bukti bayar, melakukan tutup kasir harian, dan meneruskan berkas dispensasi.' },
    { nama: 'Kepala sekolah', tugas: 'Meninjau dan memutuskan dispensasi penundaan jatuh tempo, serta memantau laporan keuangan.' },
    { nama: 'Siswa / wali murid', tugas: 'Memantau tagihan digital dan riwayat pembayaran, serta mengajukan dispensasi.' },
];
</script>

<template>
    <Head title="Sistem Manajemen Pembayaran SPP" />

    <div class="page">
        <header class="bar">
            <span class="brand">
                <span class="brand-mark" aria-hidden="true"></span>
                SPP Sekolah
            </span>
            <nav v-if="canLogin">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-ghost">
                    Ke Dashboard
                </Link>
                <Link v-else :href="route('login')" class="btn btn-ghost">
                    Masuk
                </Link>
            </nav>
        </header>

        <main>
            <section class="hero">
                <div class="hero-text">
                    <h1>Setiap rupiah SPP tercatat, dari tarif sampai ke kas.</h1>
                    <p>
                        Sistem administrasi iuran sekolah yang menghubungkan komite, tata usaha,
                        bendahara, pimpinan, dan wali murid dalam satu alur pembukuan yang tertib dan akuntabel.
                    </p>
                    <div class="cta">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-solid">
                            Buka Dashboard
                        </Link>
                        <Link v-else :href="route('login')" class="btn btn-solid">
                            Masuk ke Sistem
                        </Link>
                        <a href="#alur" class="btn btn-ghost">Lihat alur pembayaran</a>
                    </div>
                </div>

                <aside
                    class="receipt"
                    aria-label="Contoh kuitansi pembayaran"
                    tabindex="0"
                    @mouseenter="capKunci++"
                    @focus="capKunci++"
                >
                    <div class="r-head">
                        <span class="r-title">Kuitansi pembayaran</span>
                        <span class="r-no">KW-2026-000418</span>
                    </div>
                    <dl>
                        <div>
                            <dt>Siswa</dt>
                            <dd>Aditya Pratama, XII RPL 1</dd>
                        </div>
                        <div>
                            <dt>Periode</dt>
                            <dd>Oktober 2026</dd>
                        </div>
                        <div>
                            <dt>Nominal standar</dt>
                            <dd class="num">Rp 250.000</dd>
                        </div>
                        <div class="baris-potongan">
                            <dt>Keringanan / beasiswa</dt>
                            <dd class="num minus">− Rp 75.000</dd>
                            <span class="catatan" aria-hidden="true">
                                Ditetapkan Bendahara lewat program beasiswa dan dihitung otomatis saat tagihan terbit.
                            </span>
                        </div>
                        <div class="total">
                            <dt>Jumlah dibayar</dt>
                            <dd class="num">Rp 175.000</dd>
                        </div>
                    </dl>
                    <div class="r-foot">
                        <span>Loket TU, tunai</span>
                        <span :key="capKunci" class="stamp" :class="{ ulang: capKunci > 0 }">LUNAS</span>
                    </div>
                </aside>
            </section>

            <section id="alur" class="section">
                <h2>Lima tahap dari penetapan tarif hingga setoran kas</h2>
                <div
                    ref="bagian"
                    class="alur-grid"
                    @mouseenter="jeda = true"
                    @mouseleave="jeda = false"
                    @focusin="jeda = true"
                    @focusout="jeda = false"
                >
                    <div class="kolom">
                        <ol class="alur">
                            <li
                                v-for="(a, i) in alur"
                                :key="a.judul"
                                :class="{ aktif: aktif === i }"
                                :aria-current="aktif === i ? 'step' : undefined"
                                tabindex="0"
                                @mouseenter="aktif = i"
                                @focus="aktif = i"
                                @click="aktif = i"
                            >
                                <span class="no">{{ i + 1 }}</span>
                                <div>
                                    <h3>{{ a.judul }}</h3>
                                    <p>{{ a.isi }}</p>
                                    <span class="jejak" aria-hidden="true">
                                        <span
                                            v-if="aktif === i && sedangPutar"
                                            class="progres"
                                            :style="{ animationDuration: JEDA + 'ms' }"
                                        ></span>
                                    </span>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <figure class="stage" aria-hidden="true">
                        <Transition name="ganti" mode="out-in">
                            <!-- 1. Usulan tarif -->
                            <svg v-if="aktif === 0" key="s0" class="ilus" viewBox="0 0 360 250">
                                <rect class="i-paper" x="70" y="20" width="220" height="210" rx="8" />
                                <path class="i-brand" d="M70 28a8 8 0 0 1 8-8h204a8 8 0 0 1 8 8v28H70z" />
                                <text class="i-tw" x="88" y="44">Usulan tarif SPP</text>
                                <g class="i-masuk" style="--d: 0.1s"><text class="i-tm" x="88" y="90">Kelas X</text></g>
                                <rect class="i-bar i-lebar" style="--d: 0.15s" x="190" y="80" width="80" height="12" rx="6" />
                                <line class="i-garis" x1="86" y1="104" x2="274" y2="104" />
                                <g class="i-masuk" style="--d: 0.5s"><text class="i-tm" x="88" y="128">Kelas XI</text></g>
                                <rect class="i-bar i-lebar" style="--d: 0.55s" x="190" y="118" width="80" height="12" rx="6" />
                                <line class="i-garis" x1="86" y1="142" x2="274" y2="142" />
                                <g class="i-masuk" style="--d: 0.9s"><text class="i-tm" x="88" y="166">Kelas XII</text></g>
                                <rect class="i-bar i-lebar" style="--d: 0.95s" x="190" y="156" width="80" height="12" rx="6" />
                                <line class="i-garis" x1="86" y1="180" x2="274" y2="180" />
                                <g class="i-pensil">
                                    <g transform="translate(246 200) rotate(-35)">
                                        <polygon class="i-ink" points="0,0 14,-6 14,6" />
                                        <rect class="i-brand" x="14" y="-6" width="44" height="12" />
                                        <rect class="i-tint" x="58" y="-6" width="10" height="12" rx="2" />
                                    </g>
                                </g>
                            </svg>

                            <!-- 2. Persetujuan komite -->
                            <svg v-else-if="aktif === 1" key="s1" class="ilus" viewBox="0 0 360 250">
                                <rect class="i-paper" x="80" y="20" width="200" height="212" rx="8" />
                                <text class="i-t i-tengah" x="180" y="52">Berita Acara</text>
                                <line class="i-garis" x1="100" y1="64" x2="260" y2="64" />
                                <rect class="i-bar i-lebar" style="--d: 0.15s" x="100" y="82" width="160" height="8" rx="4" />
                                <rect class="i-bar i-lebar" style="--d: 0.3s" x="100" y="102" width="160" height="8" rx="4" />
                                <rect class="i-bar i-lebar" style="--d: 0.45s" x="100" y="122" width="120" height="8" rx="4" />
                                <rect class="i-bar i-lebar" style="--d: 0.6s" x="100" y="142" width="140" height="8" rx="4" />
                                <line class="i-garis" x1="100" y1="204" x2="168" y2="204" />
                                <text class="i-tm" x="100" y="220">Ketua komite</text>
                                <g class="i-cap" style="--d: 1s">
                                    <circle class="i-cap-lingkar" cx="232" cy="188" r="30" />
                                    <circle class="i-cap-tipis" cx="232" cy="188" r="24" />
                                    <path class="i-cap-cek" d="M219 188l9 9 17-20" />
                                </g>
                            </svg>

                            <!-- 3. Penerbitan tagihan -->
                            <svg v-else-if="aktif === 2" key="s2" class="ilus" viewBox="0 0 360 250">
                                <g class="i-naik" style="--d: 0s">
                                    <rect class="i-paper" x="96" y="26" width="176" height="150" rx="8" transform="rotate(-5 184 101)" />
                                </g>
                                <g class="i-naik" style="--d: 0.15s">
                                    <rect class="i-paper" x="88" y="38" width="176" height="150" rx="8" transform="rotate(3 176 113)" />
                                </g>
                                <g class="i-naik" style="--d: 0.3s">
                                    <rect class="i-paper" x="82" y="30" width="196" height="196" rx="8" />
                                </g>
                                <g class="i-masuk" style="--d: 0.6s">
                                    <text class="i-t" x="102" y="58">Tagihan SPP</text>
                                    <text class="i-tm" x="102" y="76">Per siswa, per bulan</text>
                                </g>
                                <line class="i-garis" x1="100" y1="88" x2="260" y2="88" />
                                <g class="i-masuk" style="--d: 0.7s"><text class="i-tm" x="102" y="114">SPP bulanan</text></g>
                                <rect class="i-bar i-lebar" style="--d: 0.75s" x="196" y="105" width="64" height="10" rx="5" />
                                <g class="i-masuk" style="--d: 0.9s"><text class="i-tm" x="102" y="146">Keringanan</text></g>
                                <g class="i-pop" style="--d: 1s">
                                    <rect class="i-chip-bg" x="184" y="130" width="76" height="22" rx="11" />
                                    <text class="i-tb i-tengah" x="222" y="145">− potongan</text>
                                </g>
                                <line class="i-garis" x1="100" y1="168" x2="260" y2="168" />
                                <g class="i-masuk" style="--d: 1.2s"><text class="i-t" x="102" y="196">Total</text></g>
                                <rect class="i-ink i-lebar" style="--d: 1.25s" x="196" y="187" width="64" height="12" rx="6" />
                            </svg>

                            <!-- 4. Pembayaran di loket -->
                            <svg v-else-if="aktif === 3" key="s3" class="ilus" viewBox="0 0 360 250">
                                <defs>
                                    <clipPath id="klip-loket">
                                        <rect x="100" y="0" width="160" height="126" />
                                    </clipPath>
                                </defs>
                                <rect class="i-ink" x="40" y="176" width="280" height="54" rx="8" />
                                <g clip-path="url(#klip-loket)">
                                    <g class="i-struk">
                                        <rect class="i-paper" x="140" y="24" width="80" height="110" rx="3" />
                                        <rect class="i-bar" x="152" y="38" width="56" height="6" rx="3" />
                                        <rect class="i-bar" x="152" y="52" width="40" height="6" rx="3" />
                                        <rect class="i-bar" x="152" y="66" width="56" height="6" rx="3" />
                                        <line class="i-garis i-putus" x1="152" y1="82" x2="208" y2="82" />
                                        <rect class="i-ink" x="152" y="90" width="56" height="8" rx="4" />
                                        <g class="i-pop" style="--d: 1.1s">
                                            <text class="i-lunas" x="180" y="116" transform="rotate(-6 180 112)">LUNAS</text>
                                        </g>
                                    </g>
                                </g>
                                <rect class="i-paper" x="110" y="118" width="140" height="66" rx="8" />
                                <rect class="i-ink" x="126" y="124" width="108" height="6" rx="3" />
                                <circle class="i-brand i-kedip" cx="130" cy="160" r="5" />
                                <rect class="i-bar" x="144" y="156" width="40" height="8" rx="4" />
                                <text class="i-tm" x="238" y="166" text-anchor="end">Loket TU</text>
                            </svg>

                            <!-- 5. Verifikasi setoran -->
                            <svg v-else key="s4" class="ilus" viewBox="0 0 360 250">
                                <rect class="i-paper" x="24" y="40" width="130" height="170" rx="8" />
                                <text class="i-t i-tengah" x="89" y="66">Catatan sistem</text>
                                <line class="i-garis" x1="38" y1="78" x2="140" y2="78" />
                                <rect class="i-bar i-lebar" style="--d: 0.1s" x="40" y="94" width="98" height="8" rx="4" />
                                <rect class="i-bar i-lebar" style="--d: 0.25s" x="40" y="112" width="98" height="8" rx="4" />
                                <rect class="i-bar i-lebar" style="--d: 0.4s" x="40" y="130" width="98" height="8" rx="4" />
                                <line class="i-garis" x1="38" y1="156" x2="140" y2="156" />
                                <rect class="i-ink i-lebar" style="--d: 0.55s" x="40" y="168" width="98" height="12" rx="6" />

                                <rect class="i-paper" x="206" y="40" width="130" height="170" rx="8" />
                                <text class="i-t i-tengah" x="271" y="66">Uang fisik</text>
                                <line class="i-garis" x1="220" y1="78" x2="322" y2="78" />
                                <ellipse class="i-koin i-jatuh" style="--d: 0.2s" cx="271" cy="190" rx="32" ry="9" />
                                <ellipse class="i-koin i-jatuh" style="--d: 0.35s" cx="271" cy="178" rx="32" ry="9" />
                                <ellipse class="i-koin i-jatuh" style="--d: 0.5s" cx="271" cy="166" rx="32" ry="9" />
                                <ellipse class="i-koin i-jatuh" style="--d: 0.65s" cx="271" cy="154" rx="32" ry="9" />
                                <ellipse class="i-koin i-jatuh" style="--d: 0.8s" cx="271" cy="142" rx="32" ry="9" />

                                <g class="i-pop" style="--d: 1.1s"><circle class="i-brand" cx="180" cy="125" r="18" /></g>
                                <path class="i-cek" style="--d: 1.35s" d="M171 125l7 7 12-14" />
                            </svg>
                        </Transition>
                        <figcaption>
                            Dilakukan oleh <strong>{{ alur[aktif].pelaku }}</strong>
                        </figcaption>
                    </figure>
                </div>
            </section>

            <section class="section duo">
                <div v-muncul="0" class="panel">
                    <h2>Keringanan SPP</h2>
                    <p>
                        Bendahara membuat program potongan dan menetapkan siswa penerimanya per tahun ajaran.
                        Potongan, baik berupa persentase maupun nominal, otomatis diperhitungkan saat tagihan diterbitkan.
                    </p>
                </div>
                <div v-muncul="1" class="panel panel-alt">
                    <h2>Dispensasi pembayaran</h2>
                    <p>
                        Wali murid mengajukan penundaan jatuh tempo dengan melampirkan dokumen pendukung. Staf TU
                        meneruskan berkasnya, dan Kepala Sekolah yang memutuskan persetujuan.
                    </p>
                </div>
            </section>

            <section class="section">
                <h2>Setiap peran fokus pada kewenangan kerjanya</h2>
                <div class="peran">
                    <article v-for="(p, i) in peran" :key="p.nama" v-muncul="i % 3">
                        <h3>{{ p.nama }}</h3>
                        <p>{{ p.tugas }}</p>
                    </article>
                </div>
            </section>
        </main>

        <footer class="foot">
            <span>&copy; {{ new Date().getFullYear() }} Sistem Pembayaran SPP Sekolah. Seluruh hak cipta dilindungi.</span>
            <Link v-if="canLogin && !$page.props.auth.user" :href="route('login')">Masuk petugas</Link>
        </footer>
    </div>
</template>

<style scoped>
/* Seluruh nilai memakai token dari resources/css/tokens.css */
.page {
    --gutter: var(--space-5);
    font-family: var(--font-sans);
    font-size: var(--text-base);
    color: var(--color-ink);
    background: var(--color-bg);
    min-height: 100vh;
    line-height: var(--leading-body);
    overflow-x: clip;
    -webkit-font-smoothing: antialiased;
}

.bar,
.hero,
.section,
.foot {
    max-width: var(--container);
    margin: 0 auto;
    padding-left: var(--gutter);
    padding-right: var(--gutter);
}

/* Header */
.bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--space-4);
    padding-bottom: var(--space-4);
    background: linear-gradient(var(--color-line), var(--color-line)) center bottom / calc(100% - 2 * var(--gutter)) 1px no-repeat;
}

.brand {
    display: inline-flex;
    align-items: center;
    gap: var(--space-3);
    color: var(--color-ink);
    font-weight: var(--weight-bold);
    font-size: var(--text-lg);
    letter-spacing: -0.01em;
    text-decoration: none;
}

.brand-mark {
    width: 22px;
    height: 22px;
    border-radius: 5px;
    background: var(--color-brand);
    box-shadow: inset 0 -6px 0 rgba(255, 255, 255, 0.22);
}

/* Tombol */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: var(--control-height);
    padding: 0 var(--space-5);
    border-radius: var(--radius);
    font-weight: var(--weight-medium);
    font-size: var(--text-sm);
    text-decoration: none;
    border: 1px solid transparent;
    transition: background-color 0.15s, border-color 0.15s, color 0.15s;
}

.btn-solid {
    background: var(--color-brand);
    color: #ffffff;
}

.btn-solid:hover {
    background: var(--color-brand-dark);
}

.btn-ghost {
    background: var(--color-surface);
    border-color: var(--color-line-strong);
    color: var(--color-ink);
}

.btn-ghost:hover {
    border-color: var(--color-brand);
    color: var(--color-brand);
}

.btn:focus-visible,
a:focus-visible {
    outline: 2px solid var(--color-brand);
    outline-offset: 2px;
}

/* Hero */
.hero {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
    gap: var(--space-7);
    align-items: center;
    padding-top: var(--space-8);
    padding-bottom: var(--space-9);
}

h1 {
    font-size: var(--text-display);
    line-height: var(--leading-tight);
    font-weight: var(--weight-bold);
    letter-spacing: -0.025em;
    margin: 0 0 var(--space-5);
    max-width: 18ch;
}

.hero-text p {
    color: var(--color-muted);
    font-size: var(--text-lg);
    max-width: 52ch;
    margin: 0 0 var(--space-6);
}

.cta {
    display: flex;
    gap: var(--space-3);
    flex-wrap: wrap;
}

/* Kuitansi */
.receipt {
    position: relative;
    background: var(--color-surface);
    border: var(--border);
    border-bottom: 0;
    border-radius: var(--radius) var(--radius) 0 0;
    padding: var(--space-5) var(--space-5) var(--space-5);
    margin-bottom: var(--space-2);
    transform: rotate(1deg);
    box-shadow: var(--shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: muncul 0.5s ease-out backwards;
}

/* Tepi bergerigi di bagian bawah kertas */
.receipt::after {
    content: '';
    position: absolute;
    left: -1px;
    right: -1px;
    bottom: -8px;
    height: 8px;
    background:
        linear-gradient(135deg, var(--color-surface) 33.33%, transparent 33.33%) 0 0 / 16px 16px,
        linear-gradient(225deg, var(--color-surface) 33.33%, transparent 33.33%) 0 0 / 16px 16px;
    background-repeat: repeat-x;
    filter: drop-shadow(0 1px 0 var(--color-line));
}

.r-head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: var(--space-3);
    padding-bottom: var(--space-4);
    border-bottom: 1px dashed var(--color-line-strong);
}

.r-title {
    font-weight: var(--weight-bold);
    font-size: var(--text-sm);
}

.r-no {
    color: var(--color-muted);
    font-size: var(--text-xs);
}

dl {
    margin: var(--space-3) 0 0;
}

dl div {
    display: flex;
    justify-content: space-between;
    gap: var(--space-4);
    padding: var(--space-2) 0;
    font-size: var(--text-sm);
}

dt {
    color: var(--color-muted);
}

dd {
    margin: 0;
    min-width: 0;
    overflow-wrap: anywhere;
    text-align: right;
    font-weight: var(--weight-medium);
}

.num {
    font-feature-settings: 'tnum' 0;
}

.minus {
    color: var(--color-brand);
}

dl .total {
    border-radius: 0;
    border-top: 2px solid var(--color-ink);
    margin-top: var(--space-3);
    padding-top: var(--space-3);
    font-size: var(--text-base);
}

dl .total dt {
    color: var(--color-ink);
    font-weight: 700;
}

dl .total dd {
    font-weight: var(--weight-bold);
}

dl div {
    margin-inline: calc(var(--space-2) * -1);
    padding-inline: var(--space-2);
    border-radius: var(--radius-sm);
    transition: background-color 0.15s;
}

dl div:hover {
    background: var(--color-brand-tint);
}

.catatan {
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 16px);
    z-index: 2;
    padding: var(--space-2) var(--space-3);
    background: var(--color-ink);
    color: #ffffff;
    font-size: var(--text-xs);
    font-weight: var(--weight-regular);
    line-height: 1.4;
    text-align: left;
    border-radius: var(--radius-sm);
    opacity: 0;
    transform: translateY(4px);
    pointer-events: none;
    transition: opacity 0.15s, transform 0.15s;
}

.baris-potongan:hover .catatan {
    opacity: 1;
    transform: none;
}

.receipt:hover,
.receipt:focus {
    transform: rotate(0deg) translateY(-6px);
    box-shadow: var(--shadow-lift);
}

.receipt:focus-visible {
    outline: 2px solid var(--color-brand);
    outline-offset: 6px;
}

.r-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--space-4);
    color: var(--color-muted);
    font-size: var(--text-xs);
}

.stamp {
    color: var(--color-danger);
    border: 2px solid var(--color-danger);
    border-radius: var(--radius-sm);
    padding: 2px var(--space-3);
    font-weight: var(--weight-bold);
    font-size: var(--text-sm);
    letter-spacing: 0.08em;
    transform: rotate(-5deg);
    opacity: 0.9;
    animation: cap-masuk 0.45s 0.7s cubic-bezier(0.2, 1.4, 0.4, 1) backwards;
}

.stamp.ulang {
    animation-delay: 0s;
}

@keyframes cap-masuk {
    from {
        opacity: 0;
        transform: scale(1.7) rotate(-14deg);
    }
    to {
        opacity: 0.9;
        transform: rotate(-5deg);
    }
}

@keyframes muncul {
    from {
        opacity: 0;
        transform: translateY(12px) rotate(1deg);
    }
    to {
        opacity: 1;
        transform: rotate(1deg);
    }
}

/* Seksi umum */
.section {
    padding-top: var(--space-7);
    padding-bottom: var(--space-7);
    background: linear-gradient(var(--color-line), var(--color-line)) center top / calc(100% - 2 * var(--gutter)) 1px no-repeat;
}

h2 {
    font-size: var(--text-2xl);
    line-height: var(--leading-snug);
    font-weight: var(--weight-bold);
    letter-spacing: -0.015em;
    margin: 0 0 var(--space-6);
    max-width: 28ch;
}

h3 {
    margin: 0 0 var(--space-1);
    font-size: var(--text-base);
    font-weight: 700;
}

.alur p,
.peran p,
.panel p {
    margin: 0;
    color: var(--color-muted);
    font-size: var(--text-sm);
    max-width: 62ch;
}

/* Alur */
.alur-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 0.9fr);
    gap: var(--space-7);
    align-items: start;
}

.alur {
    list-style: none;
    margin: 0;
    padding: 0;
}

.alur li {
    display: flex;
    gap: var(--space-5);
    padding: var(--space-5) 0;
    border-top: var(--border);
    cursor: pointer;
}

.alur li:first-child {
    border-top: 0;
    padding-top: 0;
}

.alur li:focus-visible {
    outline: 2px solid var(--color-brand);
    outline-offset: 4px;
    border-radius: var(--radius-sm);
}

.alur h3 {
    transition: color 0.15s;
}

.alur li.aktif h3 {
    color: var(--color-brand);
}

.no {
    flex: none;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--color-brand-tint);
    color: var(--color-brand-dark);
    border: 1px solid var(--color-brand);
    display: grid;
    place-items: center;
    font-weight: 700;
    font-size: var(--text-sm);
    font-variant-numeric: tabular-nums;
    transition: background-color 0.15s, color 0.15s;
}

.alur li.aktif .no {
    background: var(--color-brand);
    color: #ffffff;
}

.kolom {
    min-width: 0;
}

.alur li {
    -webkit-tap-highlight-color: transparent;
}

.jejak {
    display: block;
    height: 3px;
    margin-top: var(--space-3);
    overflow: hidden;
}

.progres {
    display: block;
    height: 100%;
    background: var(--color-brand);
    transform-origin: left center;
    animation: progres linear both;
}

@keyframes progres {
    from { transform: scaleX(0); }
    to { transform: scaleX(1); }
}

.muncul-awal {
    opacity: 0;
    transform: translateY(14px);
}

.muncul-awal.muncul-masuk {
    opacity: 1;
    transform: none;
    transition: opacity 0.5s var(--tunda, 0s) ease-out, transform 0.5s var(--tunda, 0s) ease-out;
}

/* Panggung ilustrasi */
.stage {
    user-select: none;
    position: sticky;
    top: var(--space-5);
    margin: 0;
    padding: var(--space-4);
    background: var(--color-surface);
    border: var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.stage svg {
    display: block;
    width: 100%;
    height: auto;
}

.stage figcaption {
    margin-top: var(--space-3);
    padding-top: var(--space-3);
    border-top: var(--border);
    color: var(--color-muted);
    font-size: var(--text-sm);
}

.stage figcaption strong {
    color: var(--color-ink);
}

.ganti-enter-active,
.ganti-leave-active {
    transition: opacity 0.12s ease;
}

.ganti-enter-from,
.ganti-leave-to {
    opacity: 0;
}

/* Gaya elemen ilustrasi */
.ilus .i-paper { fill: var(--color-surface); stroke: var(--color-line-strong); stroke-width: 1.5; }
.ilus .i-tint { fill: var(--color-brand-tint); }
.ilus .i-bar { fill: var(--color-brand-tint); fill: color-mix(in srgb, var(--color-brand) 22%, var(--color-surface)); }
.ilus .i-brand { fill: var(--color-brand); }
.ilus .i-ink { fill: var(--color-ink); }
.ilus .i-garis { stroke: var(--color-line); stroke-width: 1.5; fill: none; }
.ilus .i-putus { stroke-dasharray: 4 3; stroke: var(--color-line-strong); }
.ilus .i-t { fill: var(--color-ink); font-size: 15px; font-weight: 800; }
.ilus .i-tw { fill: #ffffff; font-size: 15px; font-weight: 800; }
.ilus .i-tm { fill: var(--color-muted); font-size: 13px; font-weight: 600; }
.ilus .i-tb { fill: var(--color-brand-dark); font-size: 11px; font-weight: 800; }
.ilus .i-tengah { text-anchor: middle; }
.ilus .i-lunas { fill: var(--color-danger); font-size: 12px; font-weight: 800; letter-spacing: 0.08em; text-anchor: middle; }
.ilus .i-chip-bg { fill: var(--color-brand-tint); stroke: var(--color-brand); stroke-width: 1.5; }
.ilus .i-koin { fill: var(--color-brand-tint); stroke: var(--color-brand); stroke-width: 2; }
.ilus .i-cap-lingkar { fill: var(--color-surface); stroke: var(--color-brand); stroke-width: 3; }
.ilus .i-cap-tipis { fill: none; stroke: var(--color-brand); stroke-width: 1; }
.ilus .i-cap-cek { fill: none; stroke: var(--color-brand); stroke-width: 4; stroke-linecap: round; stroke-linejoin: round; }
.ilus .i-cek { fill: none; stroke: #ffffff; stroke-width: 3.5; stroke-linecap: round; stroke-linejoin: round; }

/* Animasi ilustrasi: tiap elemen masuk berurutan lewat jeda --d, dipadatkan oleh --laju */
.ilus {
    --laju: 0.55;
}

.ilus .i-lebar { transform-box: fill-box; transform-origin: left center; animation: i-lebar 0.4s calc(var(--d, 0s) * var(--laju)) cubic-bezier(0.2, 0.8, 0.2, 1) both; }
.ilus .i-masuk { animation: i-masuk 0.4s calc(var(--d, 0s) * var(--laju)) ease-out both; }
.ilus .i-naik { animation: i-naik 0.4s calc(var(--d, 0s) * var(--laju)) ease-out both; }
.ilus .i-pop { transform-box: fill-box; transform-origin: center; animation: i-pop 0.4s calc(var(--d, 0s) * var(--laju)) cubic-bezier(0.2, 1.4, 0.4, 1) both; }
.ilus .i-jatuh { animation: i-jatuh 0.4s calc(var(--d, 0s) * var(--laju)) ease-out both; }
.ilus .i-cap { transform-box: fill-box; transform-origin: center; animation: i-cap 0.45s calc(var(--d, 0s) * var(--laju)) cubic-bezier(0.2, 1.4, 0.4, 1) both; }
.ilus .i-pensil { animation: i-tulis 0.8s 0.1s ease-in-out both; }
.ilus .i-struk { animation: i-keluar 0.7s ease-out both; }
.ilus .i-kedip { animation: i-kedip 1.2s ease-in-out infinite; }
.ilus .i-cek { stroke-dasharray: 32; stroke-dashoffset: 32; animation: i-gambar 0.4s calc(var(--d, 0.3s) * var(--laju)) ease-out forwards; }

@keyframes i-lebar { from { transform: scaleX(0); } to { transform: scaleX(1); } }
@keyframes i-masuk { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
@keyframes i-naik { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: none; } }
@keyframes i-pop { from { opacity: 0; transform: scale(0.6); } to { opacity: 1; transform: scale(1); } }
@keyframes i-jatuh { from { opacity: 0; transform: translateY(-26px); } to { opacity: 1; transform: none; } }
@keyframes i-cap { from { opacity: 0; transform: scale(1.6) rotate(-12deg); } to { opacity: 1; transform: scale(1) rotate(-8deg); } }
@keyframes i-tulis { from { transform: translateY(-112px); } to { transform: none; } }
@keyframes i-keluar { from { transform: translateY(110px); } to { transform: none; } }
@keyframes i-kedip { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
@keyframes i-gambar { to { stroke-dashoffset: 0; } }

/* Keringanan dan dispensasi */
.duo {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-5);
}

.panel {
    padding: var(--space-5) var(--space-5) var(--space-6);
    background: var(--color-surface);
    border: var(--border);
    border-top: 3px solid var(--color-brand);
    border-radius: var(--radius);
}

.panel-alt {
    border-top-color: var(--color-ink);
}

.panel h2 {
    font-size: var(--text-xl);
    margin-bottom: var(--space-3);
}

/* Peran */
.peran {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    border-top: var(--border);
}

.peran article {
    padding: var(--space-5) var(--space-5) var(--space-5) 0;
    border-bottom: var(--border);
}

/* Footer */
.foot {
    display: flex;
    justify-content: space-between;
    gap: var(--space-4);
    padding-top: var(--space-5);
    padding-bottom: var(--space-7);
    color: var(--color-muted);
    font-size: var(--text-sm);
    background: linear-gradient(var(--color-line), var(--color-line)) center top / calc(100% - 2 * var(--gutter)) 1px no-repeat;
}

.foot a {
    color: var(--color-brand);
    font-weight: var(--weight-medium);
    text-decoration: none;
}

.foot a:hover {
    text-decoration: underline;
}

/* Responsif */
@media (max-width: 860px) {
    .hero {
        grid-template-columns: minmax(0, 1fr);
        gap: var(--space-6);
        padding-top: var(--space-6);
        padding-bottom: var(--space-7);
    }

    .duo {
        grid-template-columns: 1fr;
    }

    .peran {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 540px) {
    .peran {
        grid-template-columns: 1fr;
    }

    .peran article {
        padding-right: 0;
    }

    .foot {
        flex-direction: column;
    }

    .page {
        --gutter: var(--space-4);
    }

    .hero {
        padding-top: var(--space-5);
    }

    .cta .btn {
        flex: 1 1 100%;
    }

    .receipt {
        padding: var(--space-4);
    }

    .panel {
        padding: var(--space-4) var(--space-4) var(--space-5);
    }
}

@media (max-width: 1024px) {
    .hero {
        gap: var(--space-6);
    }
}

@media (max-width: 860px) {
    .receipt {
        width: 100%;
        max-width: 440px;
        margin-inline: auto;
    }
}

@media (max-width: 860px) {
    .alur-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: var(--space-5);
    }

    .stage {
        order: -1;
        top: 0;
        z-index: 5;
        padding: var(--space-3);
        border-radius: 0 0 var(--radius) var(--radius);
    }

    .stage svg {
        max-height: 190px;
    }

    .stage figcaption {
        display: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    .receipt,
    .stamp {
        animation: none;
        transition: none;
    }

    .ilus * {
        animation: none !important;
    }

    .ilus .i-cek {
        stroke-dashoffset: 0;
    }

    .ganti-enter-active,
    .ganti-leave-active {
        transition: none;
    }

    .receipt:hover,
    .receipt:focus {
        transform: rotate(1deg);
    }
}
</style>

<style>
html {
    scroll-behavior: smooth;
}

@media (prefers-reduced-motion: reduce) {
    html {
        scroll-behavior: auto;
    }
}
</style>