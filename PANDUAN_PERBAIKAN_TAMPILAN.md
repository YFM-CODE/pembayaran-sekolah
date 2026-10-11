# Panduan Perbaikan Tampilan UI/UX
## Sistem Informasi SPP Sekolah — Laravel, Inertia.js, Vue 3, Tailwind CSS

> **Tujuan:** menyatukan identitas visual, menyederhanakan navigasi, memperjelas alur kerja operasional, dan meningkatkan aksesibilitas serta responsivitas tanpa mengubah aturan bisnis atau hak akses aplikasi.
>
> **Ruang lingkup:** halaman Welcome/Login, layout autentikasi, sidebar/topbar, komponen Vue inti, dashboard per peran, loket pembayaran SPP, dan verifikasi setoran kasir.

---

## 1. Prinsip dan hasil yang dituju

1. **Konsisten:** gunakan Deep Teal sebagai warna utama; hapus sisa warna default Breeze yang tidak sesuai.
2. **Berorientasi tugas:** menu dan tombol utama harus menonjolkan pekerjaan yang paling sering dilakukan setiap peran.
3. **Terbaca:** gunakan hierarki tipografi, ruang antar-elemen, dan status yang mudah dipindai.
4. **Aman:** jangan mengubah middleware, policy, validasi, atau otorisasi hanya demi tampilan.
5. **Responsif dan aksesibel:** semua fungsi dapat digunakan dengan keyboard dan layar sentuh; status tidak dibedakan berdasarkan warna saja.
6. **Minim regresi:** migrasikan per halaman, jalankan pengujian setelah setiap fase, dan pertahankan perilaku bisnis yang sudah benar.

### Kriteria penerimaan umum

- [ ] Tidak ada warna `indigo-*` atau teks `gray-800` yang tertinggal pada UI aplikasi, kecuali ada alasan khusus yang terdokumentasi.
- [ ] Semua halaman internal memakai token warna dan komponen bersama.
- [ ] Navigasi desktop menggunakan sidebar; pada layar kecil sidebar menjadi drawer yang dapat dibuka/tutup.
- [ ] Menu yang tampil sesuai peran pengguna dan izin dari backend.
- [ ] Status pembayaran tetap terbaca tanpa mengandalkan warna saja.
- [ ] Form, modal, tabel, dan tombol dapat digunakan dengan keyboard serta layar sentuh.
- [ ] Alur pembayaran, verifikasi setoran, cetak kuitansi, dan laporan tidak berubah secara fungsional.

---

## 2. Audit awal sebelum mengubah kode

Lakukan audit pada branch khusus, lalu catat hasilnya sebelum mulai implementasi.

### 2.1 Temukan struktur proyek

Contoh perintah dari root proyek:

```bash
# Cari komponen dan halaman yang relevan
find resources/js -type f \( -name "*.vue" -o -name "*.js" -o -name "*.ts" \)

# Cari warna Breeze yang perlu diaudit
grep -RInE 'indigo-|gray-800|focus:ring-indigo|text-indigo' resources/js
```

Di Windows PowerShell, alternatifnya:

```powershell
Get-ChildItem resources/js -Recurse -Include *.vue,*.js,*.ts |
  Select-String -Pattern 'indigo-|gray-800|focus:ring-indigo|text-indigo'
```

Periksa setidaknya file berikut (lokasi dapat berbeda sesuai struktur proyek):

- `tailwind.config.js` atau `tailwind.config.ts`
- `resources/css/app.css`
- `resources/js/Layouts/AuthenticatedLayout.vue`
- `resources/js/Layouts/GuestLayout.vue`
- `resources/js/Components/PrimaryButton.vue`
- `resources/js/Components/SecondaryButton.vue`
- `resources/js/Components/NavLink.vue`
- `resources/js/Components/TextInput.vue`
- `resources/js/Components/Modal.vue`
- `resources/js/Pages/RoleDashboard.vue`
- `resources/js/Pages/PembayaranSpp/Index.vue`
- `resources/js/Pages/SetoranKasir/Index.vue`

> **Catatan:** sesuaikan nama dan lokasi file dengan proyek aktual. Jangan membuat ulang file yang sudah ada sebelum memeriksa implementasi dan pemakaiannya.

### 2.2 Catat baseline

- [ ] Ambil screenshot halaman Welcome, Login, dashboard setiap peran, loket kasir, dan setoran kasir.
- [ ] Catat menu yang tersedia untuk setiap peran dan sumber otorisasinya.
- [ ] Catat ukuran layar yang digunakan saat ini dan masalah yang ditemukan.
- [ ] Jalankan test suite yang tersedia sebelum perubahan dan simpan hasilnya.
- [ ] Buat branch, misalnya `feat/ui-refresh-deep-teal`.

---

## 3. Sistem desain: token warna dan tipografi

### 3.1 Token warna resmi

| Token | Nilai | Pemakaian |
|---|---|---|
| Primary | `#0e6b63` | Tombol utama, tautan aktif, ikon utama |
| Primary hover | `#0a4f49` | Hover/active tombol utama |
| Soft | `#eef8f5` | Latar elemen aktif, panel informasi |
| Border | `#d3dcdb` | Border input, kartu, pemisah |
| Focus ring | `#2f9e83` | Indikator fokus keyboard |
| Surface | `#ffffff` | Kartu, modal, panel |
| Page background | `#f7faf9` | Latar halaman internal |
| Text primary | `#183330` | Judul dan teks utama |
| Text secondary | `#5b6d69` | Deskripsi dan metadata |
| Success | `#18794e` | Status lunas/berhasil |
| Warning | `#946200` | Status sebagian/pending |
| Danger | `#b42318` | Belum bayar, gagal, tindakan destruktif |

**Aturan:** gunakan token semantik di atas, bukan memilih warna secara acak di setiap halaman. Pastikan kontras teks dan latar memadai; validasi kembali dengan alat pemeriksa kontras sebelum rilis.

### 3.2 Konfigurasi Tailwind

Jika proyek menggunakan Tailwind CSS v3 dengan `tailwind.config.js`, tambahkan warna berikut di dalam `theme.extend` tanpa menimpa konfigurasi yang sudah ada:

```js
// tailwind.config.js
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          DEFAULT: '#0e6b63',
          hover: '#0a4f49',
          soft: '#eef8f5',
          border: '#d3dcdb',
          focus: '#2f9e83',
        },
        surface: '#ffffff',
        page: '#f7faf9',
        ink: {
          DEFAULT: '#183330',
          muted: '#5b6d69',
        },
        status: {
          success: '#18794e',
          warning: '#946200',
          danger: '#b42318',
        },
      },
      boxShadow: {
        panel: '0 2px 10px rgba(24, 51, 48, 0.06)',
      },
    },
  },
  plugins: [],
};
```

> **Penting:** contoh di atas untuk Tailwind v3. Periksa versi Tailwind proyek sebelum mengubah konfigurasi. Untuk Tailwind v4, gunakan pendekatan konfigurasi CSS-first (`@theme`) sesuai setup yang digunakan; jangan menyalin konfigurasi v3 secara membabi buta. Pertahankan plugin yang sudah ada.

Tambahkan utilitas dasar ke stylesheet utama bila sesuai:

```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  body {
    @apply bg-page text-ink antialiased;
  }

  :focus-visible {
    outline: 2px solid #2f9e83;
    outline-offset: 2px;
  }
}

@layer components {
  .ui-panel {
    @apply rounded-xl border border-brand-border bg-white shadow-panel;
  }

  .ui-page-title {
    @apply text-2xl font-semibold tracking-tight text-ink;
  }

  .ui-muted {
    @apply text-sm text-ink-muted;
  }
}
```

Jika stylesheet saat ini sudah memiliki direktif Tailwind, gabungkan aturan di atas—jangan menggandakan direktif.

### 3.3 Migrasi warna

| Sebelum | Sesudah | Catatan |
|---|---|---|
| `bg-indigo-500` | `bg-brand` | Aksi utama |
| `hover:bg-indigo-600` | `hover:bg-brand-hover` | Hover aksi utama |
| `text-indigo-600` | `text-brand` | Tautan/teks aktif |
| `focus:ring-indigo-500` | `focus:ring-brand-focus` | Fokus input |
| `text-gray-800` | `text-ink` | Teks utama |
| `border-gray-300` | `border-brand-border` | Border |
| `bg-gray-50` | `bg-page` atau `bg-brand-soft` | Pilih sesuai konteks |
| `text-gray-500` | `text-ink-muted` | Teks sekunder |

Jangan mengganti semua `gray-*` secara massal tanpa melihat konteks. Abu-abu netral yang tetap berguna untuk disabled state atau pembatas dapat dipertahankan bila memenuhi sistem desain dan aksesibilitas. Sasaran utama adalah menghapus inkonsistensi, khususnya `indigo-*` dan `gray-800`.

---

## 4. Arsitektur navigasi: sidebar vertikal + topbar mini

### 4.1 Struktur desktop

Gunakan layout dua area:

- **Sidebar kiri:** lebar sekitar `256px` pada desktop, latar putih, border kanan tipis, logo/nama sekolah di bagian atas.
- **Area konten:** mengambil sisa lebar layar, latar `page`.
- **Topbar mini:** tinggi sekitar `64px`, berisi tombol toggle sidebar, breadcrumb/judul halaman bila diperlukan, dan profil pengguna.
- **Konten halaman:** padding `24px` pada desktop, `16px` pada tablet, dan `12–16px` pada ponsel.

Sidebar sebaiknya tetap sederhana: ikon konsisten, label menu, status aktif yang jelas, dan jarak vertikal yang cukup. Hindari menampilkan semua menu dalam satu baris horizontal.

### 4.2 Pengelompokan menu

Susunan berikut adalah contoh arsitektur informasi. Sesuaikan dengan rute dan izin yang benar-benar tersedia di aplikasi.

| Grup | Contoh menu | Catatan |
|---|---|---|
| Ringkasan | Dashboard | Ringkasan sesuai peran |
| Master Data | Data Siswa, Data Kelas, Data Tarif SPP | Tampilkan hanya jika tersedia dan diizinkan |
| Transaksi Kasir | Pembayaran SPP, Riwayat Pembayaran | Aksi yang paling sering digunakan |
| Setoran Kasir | Setoran Saya, Verifikasi Setoran | Menu/verifikasi sesuai peran |
| Laporan | Laporan Pembayaran, Rekap Setoran | Jangan tampilkan jika tidak berizin |
| Akun | Profil, Keluar | Letakkan di bagian bawah atau topbar |

**Aturan otorisasi:** menyembunyikan menu di frontend hanya untuk UX. Backend tetap wajib memeriksa autentikasi, role, policy, dan permission pada setiap endpoint.

### 4.3 Badge counter

Badge dapat menunjukkan antrean yang memerlukan perhatian, misalnya `3 pending`. Ketentuan:

- Tampilkan hanya pada menu yang relevan bagi peran pengguna.
- Ambil jumlah dari data backend yang terotorisasi; jangan hardcode.
- Beri label yang dapat dibaca screen reader, misalnya `aria-label="3 setoran menunggu verifikasi"`.
- Jika jumlah nol, sembunyikan badge atau tampilkan nol hanya jika bermakna bagi alur kerja.
- Jangan menjadikan warna sebagai satu-satunya penanda status.

### 4.4 Contoh komponen sidebar Vue 3

Contoh ini memperlihatkan struktur dasar; ganti nama rute dengan nama rute Inertia/Laravel yang benar. Untuk aplikasi yang memakai Ziggy, gunakan `route('nama.rute')` setelah memastikan Ziggy tersedia.

```vue
<!-- resources/js/Layouts/AppSidebar.vue -->
<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  collapsed: { type: Boolean, default: false },
});

const page = usePage();

const groups = computed(() => [
  {
    title: 'Ringkasan',
    items: [
      { label: 'Dashboard', href: '/dashboard', active: page.url.startsWith('/dashboard') },
    ],
  },
  {
    title: 'Transaksi Kasir',
    items: [
      { label: 'Pembayaran SPP', href: '/pembayaran-spp', active: page.url.startsWith('/pembayaran-spp') },
      { label: 'Setoran Kasir', href: '/setoran-kasir', active: page.url.startsWith('/setoran-kasir'), badge: null },
    ],
  },
  {
    title: 'Laporan',
    items: [
      { label: 'Laporan Pembayaran', href: '/laporan-pembayaran', active: page.url.startsWith('/laporan-pembayaran') },
    ],
  },
]);
</script>

<template>
  <aside
    class="flex h-full flex-col border-r border-brand-border bg-white"
    :class="collapsed ? 'w-20' : 'w-64'"
    aria-label="Navigasi utama"
  >
    <div class="flex h-16 items-center border-b border-brand-border px-5">
      <span class="font-bold text-brand" v-if="!collapsed">SPP Sekolah</span>
      <span class="font-bold text-brand" v-else aria-label="SPP Sekolah">SPP</span>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
      <section v-for="group in groups" :key="group.title">
        <h2 v-if="!collapsed" class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-ink-muted">
          {{ group.title }}
        </h2>

        <div class="space-y-1">
          <Link
            v-for="item in group.items"
            :key="item.href"
            :href="item.href"
            :aria-current="item.active ? 'page' : undefined"
            class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
            :class="item.active
              ? 'bg-brand-soft text-brand'
              : 'text-ink-muted hover:bg-page hover:text-ink'"
          >
            <span>{{ item.label }}</span>
            <span
              v-if="item.badge && !collapsed"
              class="rounded-full bg-status-warning/10 px-2 py-0.5 text-xs font-semibold text-status-warning"
              :aria-label="`${item.badge} item menunggu tindakan`"
            >
              {{ item.badge }}
            </span>
          </Link>
        </div>
      </section>
    </nav>
  </aside>
</template>
```

**Sebelum mengadopsi contoh:**

- [ ] Cocokkan URL dengan route aktual; lebih baik gunakan nama route yang sudah tersedia.
- [ ] Ganti `badge: null` dengan prop/data dari backend yang telah diotorisasi.
- [ ] Buat konfigurasi menu per peran, atau kirim daftar izin dari server; jangan menganggap seluruh pengguna boleh melihat semua menu.
- [ ] Jika sidebar dapat diciutkan, pastikan item tetap memiliki label aksesibel dan tooltip/label yang memadai.

### 4.5 Topbar mini dan layout responsif

Contoh struktur integrasi:

```vue
<!-- cuplikan struktur layout -->
<script setup>
import { ref } from 'vue';
import AppSidebar from '@/Layouts/AppSidebar.vue';

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
</script>

<template>
  <div class="min-h-screen bg-page md:flex">
    <!-- Drawer untuk layar kecil -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 md:hidden">
      <button
        class="absolute inset-0 bg-black/40"
        aria-label="Tutup navigasi"
        @click="sidebarOpen = false"
      />
      <div class="relative h-full w-72 max-w-[85vw] bg-white">
        <AppSidebar :collapsed="false" />
      </div>
    </div>

    <div class="hidden md:block">
      <AppSidebar :collapsed="sidebarCollapsed" />
    </div>

    <div class="min-w-0 flex-1">
      <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-brand-border bg-white/95 px-4 backdrop-blur md:px-6">
        <div class="flex items-center gap-3">
          <button class="rounded-lg p-2 text-ink hover:bg-brand-soft md:hidden"
                  aria-label="Buka navigasi" @click="sidebarOpen = true">
            ☰
          </button>
          <button class="hidden rounded-lg p-2 text-ink hover:bg-brand-soft md:inline-flex"
                  aria-label="Ciutkan navigasi" @click="sidebarCollapsed = !sidebarCollapsed">
            ⇔
          </button>
          <span class="text-sm text-ink-muted">Sistem Informasi SPP</span>
        </div>

        <div class="flex items-center gap-3">
          <!-- Isi dengan nama, avatar/inisial, dan menu profil aktual -->
          <span class="hidden text-sm font-medium text-ink sm:inline">Nama Pengguna</span>
        </div>
      </header>

      <main class="p-3 sm:p-4 lg:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
```

Ini merupakan kerangka implementasi, bukan layout siap-tempel tanpa penyesuaian. Untuk produksi, pastikan drawer mengunci scroll latar, menutup dengan tombol Escape, mengelola fokus keyboard, dan memiliki tombol/menu profil yang benar. Jika layout sidebar diciutkan, sinkronkan lebar area konten dengan status collapse, misalnya menggunakan CSS grid atau flex pada elemen induk.

---

## 5. Standardisasi komponen inti

Gunakan satu sumber gaya untuk seluruh halaman. Hindari mengulang kelas panjang yang sedikit berbeda di setiap file.

### 5.1 `PrimaryButton.vue`

**Spesifikasi:**

- Latar `brand`, teks putih, hover `brand-hover`.
- Focus ring terlihat dan konsisten.
- Disabled state jelas, tidak hanya bergantung pada perubahan warna.
- Mendukung `type`, `disabled`, dan atribut native lain.
- Tinggi target interaksi ideal minimal sekitar `40–44px`.

```vue
<script setup>
defineProps({
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
});
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
  >
    <slot />
  </button>
</template>
```

### 5.2 `SecondaryButton.vue`

**Spesifikasi:** latar putih, teks `ink`, border `brand-border`, hover `brand-soft`; digunakan untuk batal, kembali, atau aksi sekunder.

```vue
<script setup>
defineProps({
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
});
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    class="inline-flex min-h-10 items-center justify-center rounded-lg border border-brand-border bg-white px-4 py-2 text-sm font-semibold text-ink transition-colors hover:bg-brand-soft focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
  >
    <slot />
  </button>
</template>
```

### 5.3 `NavLink.vue`

**Spesifikasi:** tautan aktif memakai `bg-brand-soft` dan `text-brand`; tautan tidak aktif memakai teks sekunder dan hover lembut. Jangan memakai `indigo-*`.

```vue
<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  href: { type: String, required: true },
  active: { type: Boolean, default: false },
});

const classes = computed(() => props.active
  ? 'bg-brand-soft text-brand'
  : 'text-ink-muted hover:bg-page hover:text-ink');
</script>

<template>
  <Link
    :href="href"
    :aria-current="active ? 'page' : undefined"
    class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors"
    :class="classes"
  >
    <slot />
  </Link>
</template>
```

Jika komponen lama menerima prop `active` atau atribut tambahan dengan bentuk berbeda, pertahankan API yang sedang digunakan atau migrasikan semua pemanggil secara terencana.

### 5.4 `TextInput.vue`

**Spesifikasi:** border token, placeholder lembut, focus ring hijau, ukuran teks minimal `text-base` pada ponsel bila perlu mencegah zoom otomatis di beberapa browser, serta dukungan `disabled` dan `invalid`.

```vue
<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  type: { type: String, default: 'text' },
  invalid: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
  <input
    :type="type"
    :value="modelValue"
    :disabled="disabled"
    :aria-invalid="invalid ? 'true' : 'false'"
    @input="$emit('update:modelValue', $event.target.value)"
    class="block min-h-11 w-full rounded-lg border bg-white px-3 py-2 text-sm text-ink placeholder:text-ink-muted/80 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-brand-focus disabled:cursor-not-allowed disabled:bg-page disabled:opacity-70"
    :class="invalid ? 'border-status-danger focus:border-status-danger' : 'border-brand-border focus:border-brand'"
  />
</template>
```

Pastikan label terlihat dan terhubung melalui `for`/`id`. Pesan validasi harus diletakkan dekat input; jika ada error, gunakan `aria-describedby` yang menunjuk ke pesan tersebut.

### 5.5 `Modal.vue`

**Spesifikasi:**

- Overlay gelap transparan; panel putih dengan radius dan bayangan konsisten.
- Judul dan deskripsi jelas; tombol tutup yang berlabel.
- Tombol utama dan sekunder tidak memiliki bobot visual yang sama.
- Menutup lewat Escape dan klik overlay hanya jika aman bagi alur kerja.
- Mengunci scroll latar, mengelola fokus, dan mengembalikan fokus setelah modal ditutup.
- Modal konfirmasi transaksi harus menjelaskan dampak sebelum tindakan final.

Jika proyek sudah memakai komponen modal yang menangani focus trap dan aksesibilitas, pertahankan mekanisme itu dan ubah kelas visualnya saja. Hindari mengganti modal aksesibel dengan implementasi minimal yang kehilangan pengelolaan fokus.

Contoh kelas panel:

```html
<div class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-black/40 p-4">
  <section
    role="dialog"
    aria-modal="true"
    aria-labelledby="dialog-title"
    class="w-full max-w-lg rounded-2xl border border-brand-border bg-white p-6 shadow-xl"
  >
    <h2 id="dialog-title" class="text-lg font-semibold text-ink">Konfirmasi tindakan</h2>
    <p class="mt-2 text-sm text-ink-muted">Periksa kembali data sebelum melanjutkan.</p>
    <!-- Konten dan tombol -->
  </section>
</div>
```

Kode tersebut hanya contoh visual panel; bukan pengganti implementasi modal lengkap dengan focus trap, Escape, dan manajemen fokus.

### 5.6 Tabel data

**Standar tampilan:**

- Header: latar `brand-soft`, teks `ink`, ukuran kecil dan tebal, rata kiri kecuali angka.
- Baris: padding konsisten, border bawah tipis, zebra striping sangat halus.
- Hover baris: `hover:bg-brand-soft/60`.
- Nominal uang: rata kanan dan gunakan format Rupiah yang konsisten.
- Aksi per baris: gunakan label/tooltip yang jelas; jangan hanya ikon tanpa nama aksesibel.
- Mobile: bungkus tabel dalam `overflow-x-auto` atau ubah ke kartu jika jumlah kolom sangat banyak. Jangan memaksa teks menyusut sampai tidak terbaca.

```html
<div class="overflow-x-auto rounded-xl border border-brand-border bg-white">
  <table class="min-w-full divide-y divide-brand-border text-sm">
    <thead class="bg-brand-soft text-left text-xs uppercase tracking-wide text-ink">
      <tr>
        <th scope="col" class="px-4 py-3 font-semibold">Nama Siswa</th>
        <th scope="col" class="px-4 py-3 font-semibold">Periode</th>
        <th scope="col" class="px-4 py-3 text-right font-semibold">Tagihan</th>
        <th scope="col" class="px-4 py-3 font-semibold">Status</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-brand-border">
      <tr class="odd:bg-white even:bg-page/70 hover:bg-brand-soft/60">
        <td class="whitespace-nowrap px-4 py-3 font-medium text-ink">Nama Contoh</td>
        <td class="whitespace-nowrap px-4 py-3 text-ink-muted">Januari 2026</td>
        <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-ink">Rp 250.000</td>
        <td class="whitespace-nowrap px-4 py-3">
          <span class="inline-flex rounded-full bg-status-success/10 px-2.5 py-1 text-xs font-semibold text-status-success">
            Lunas
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

#### Standar status pembayaran

| Status | Tampilan | Makna |
|---|---|---|
| Lunas | Chip hijau + teks `Lunas` | Tagihan telah lunas |
| Sebagian | Chip kuning + teks `Sebagian` | Pembayaran belum memenuhi total tagihan |
| Belum Bayar | Chip merah + teks `Belum Bayar` | Belum ada pembayaran yang tercatat |

Jangan menentukan status hanya dari tampilan. Gunakan status/hasil perhitungan backend yang menjadi sumber kebenaran. Periksa kontras warna chip dan pertahankan teks status agar tetap jelas ketika dicetak atau dilihat oleh pengguna dengan gangguan persepsi warna.

---

## 6. Peningkatan halaman modul

## 6.1 Loket Kasir TU — `PembayaranSpp/Index.vue`

### Sasaran UX

Kasir harus bisa mencari siswa, melihat tagihan dan tunggakan, memasukkan pembayaran, memeriksa ringkasan, lalu menyelesaikan transaksi tanpa berpindah-pindah fokus secara berlebihan.

### Susunan halaman yang disarankan

1. **Header halaman:** judul “Pembayaran SPP”, deskripsi singkat, dan informasi tanggal/shift jika tersedia.
2. **Panel pencarian siswa:** pencarian berdasarkan NIS/NISN/nama sesuai dukungan backend; tampilkan hasil dengan identitas siswa dan kelas.
3. **Ringkasan siswa terpilih:** nama, NIS, kelas, total tagihan, total dibayar, dan sisa tagihan.
4. **Daftar periode/tagihan:** status tiap periode, nominal, dan tindakan yang sesuai.
5. **Form pembayaran:** periode/tagihan yang dibayar, nominal, metode pembayaran jika tersedia, dan catatan opsional.
6. **Ringkasan transaksi:** total tagihan dipilih, nominal yang akan dibayar, dan sisa setelah transaksi.
7. **Aksi utama:** tombol “Proses Pembayaran” paling menonjol; tombol cetak kuitansi tampil setelah transaksi berhasil.

### Contoh kerangka UI

```vue
<template>
  <div class="space-y-6">
    <header class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 class="ui-page-title">Pembayaran SPP</h1>
        <p class="ui-muted mt-1">Cari siswa dan catat pembayaran dengan teliti.</p>
      </div>
    </header>

    <section class="ui-panel p-4 sm:p-6">
      <label for="student-search" class="mb-2 block text-sm font-medium text-ink">
        Cari siswa
      </label>
      <input
        id="student-search"
        type="search"
        placeholder="Masukkan nama atau NIS siswa"
        class="min-h-11 w-full rounded-lg border border-brand-border px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus sm:max-w-xl"
      />
      <!-- Render hasil pencarian aktual dari backend di sini -->
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,1fr)]">
      <section class="ui-panel p-4 sm:p-6">
        <h2 class="text-base font-semibold text-ink">Tagihan siswa</h2>
        <!-- Tabel tagihan dan status aktual -->
      </section>

      <section class="ui-panel p-4 sm:p-6">
        <h2 class="text-base font-semibold text-ink">Rincian pembayaran</h2>
        <!-- Form terhubung ke state dan endpoint pembayaran yang sudah ada -->
        <div class="mt-5 flex flex-col gap-3">
          <button class="min-h-11 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus">
            Proses Pembayaran
          </button>
          <!-- Tampilkan tombol kuitansi hanya setelah pembayaran berhasil -->
        </div>
      </section>
    </div>
  </div>
</template>
```

Kerangka ini hanya untuk struktur visual. Sambungkan input, state, validasi, loading, dan submit ke implementasi yang sudah ada; jangan menambahkan transaksi tiruan atau endpoint baru tanpa audit backend.

### Perilaku yang wajib diperhatikan

- [ ] Tombol proses dinonaktifkan selama request berjalan untuk mencegah submit ganda.
- [ ] Validasi nominal dilakukan di frontend untuk kenyamanan dan tetap divalidasi di backend.
- [ ] Tampilkan error server di dekat bagian yang relevan; jangan hanya mengandalkan notifikasi yang cepat hilang.
- [ ] Setelah sukses, tampilkan ringkasan transaksi dan tombol **Cetak Kuitansi** dengan gaya utama/aksen yang mudah ditemukan.
- [ ] Cetak kuitansi hanya untuk transaksi yang telah berhasil dan memiliki referensi transaksi sah.
- [ ] Sediakan tampilan cetak yang bersih melalui print stylesheet atau halaman kuitansi yang memang sudah digunakan aplikasi.
- [ ] Konfirmasi sebelum membatalkan form yang sudah diisi bila tindakan tersebut berpotensi menghilangkan data.

Contoh tombol kuitansi setelah sukses:

```vue
<a
  v-if="paymentSucceeded && receiptUrl"
  :href="receiptUrl"
  target="_blank"
  rel="noopener"
  class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-status-success px-4 py-2.5 text-sm font-semibold text-white hover:brightness-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-focus focus-visible:ring-offset-2"
>
  Cetak Kuitansi
</a>
```

`paymentSucceeded` dan `receiptUrl` di atas adalah nama ilustratif. Gunakan state dan URL yang benar dari aplikasi, dan pastikan backend mengotorisasi akses ke kuitansi.

## 6.2 Setoran Kasir — `SetoranKasir/Index.vue`

### Sasaran UX

Bendahara perlu membandingkan jumlah uang fisik dengan catatan sistem, memahami selisih, dan memutuskan tindakan dengan bukti serta konteks yang cukup.

### Layout verifikasi

- **Header modal:** “Verifikasi Setoran” beserta nomor/referensi setoran dan nama kasir.
- **Dua panel sejajar:** “Menurut Sistem” dan “Uang Fisik”.
- **Ringkasan perhitungan:** jumlah sistem, jumlah fisik, selisih, dan status verifikasi.
- **Input bukti/catatan:** alasan selisih atau catatan verifikasi jika alur bisnis mengizinkan.
- **Aksi:** “Konfirmasi Setoran” dan “Kembalikan untuk Koreksi”/“Tolak” sesuai istilah serta aturan bisnis yang benar.
- **Riwayat:** tampilkan siapa yang memverifikasi dan kapan, jika datanya tersedia.

### Contoh panel perbandingan

```vue
<div class="grid gap-4 sm:grid-cols-2">
  <section class="rounded-xl border border-brand-border bg-page p-4">
    <p class="text-sm font-medium text-ink-muted">Menurut Sistem</p>
    <p class="mt-2 text-2xl font-bold tabular-nums text-ink">Rp 2.500.000</p>
    <p class="mt-1 text-xs text-ink-muted">Berdasarkan transaksi tercatat</p>
  </section>

  <section class="rounded-xl border border-brand-border bg-white p-4">
    <label for="physical-amount" class="text-sm font-medium text-ink">Uang Fisik</label>
    <input
      id="physical-amount"
      inputmode="numeric"
      class="mt-2 min-h-11 w-full rounded-lg border border-brand-border px-3 py-2 text-right tabular-nums focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand-focus"
      aria-describedby="physical-amount-help"
    />
    <p id="physical-amount-help" class="mt-1 text-xs text-ink-muted">
      Masukkan total uang yang dihitung secara fisik.
    </p>
  </section>
</div>

<section class="mt-4 rounded-xl border border-status-warning/30 bg-status-warning/5 p-4">
  <p class="text-sm font-medium text-ink">Selisih</p>
  <p class="mt-1 text-xl font-bold tabular-nums text-status-warning">Dihitung dari data aktual</p>
  <p class="mt-1 text-xs text-ink-muted">
    Periksa ulang hitungan dan catatan sebelum menyelesaikan verifikasi.
  </p>
</section>
```

Angka pada cuplikan di atas adalah contoh ilustratif, bukan data transaksi nyata. Dalam aplikasi, seluruh nominal dan selisih harus dihitung dari state aktual, diformat sebagai Rupiah, dan diverifikasi kembali oleh backend.

### Perilaku verifikasi

- [ ] Nilai sistem ditampilkan sebagai referensi yang tidak bisa diedit oleh pemeriksa.
- [ ] Nilai fisik dapat diedit sesuai izin dan status setoran.
- [ ] Selisih dihitung otomatis untuk membantu pengguna; backend tetap menjadi sumber keputusan akhir.
- [ ] Jika ada selisih, minta catatan/alasan bila aturan bisnis menetapkannya.
- [ ] Konfirmasi akhir menampilkan nominal dan dampak tindakan dengan jelas.
- [ ] Cegah konfirmasi ganda dan tampilkan status loading.
- [ ] Setelah berhasil, perbarui daftar/status setoran tanpa membuat data tampak sukses sebelum server mengonfirmasi.
- [ ] Pastikan modal memiliki fokus keyboard, dukungan Escape sesuai risiko tindakan, dan konfirmasi sebelum menutup jika ada perubahan belum disimpan.

Jangan menetapkan bahwa selisih otomatis menyebabkan penolakan atau persetujuan tanpa merujuk aturan bisnis yang berlaku.

## 6.3 Dashboard peran — `RoleDashboard.vue`

### Susunan dashboard

1. Judul dan sapaan seperlunya, sesuai peran.
2. Kartu metrik utama (3–4 kartu yang relevan, bukan semua angka yang tersedia).
3. Grafik atau ringkasan tren bila data mendukung dan mudah ditafsirkan.
4. Tabel aktivitas terbaru atau antrean yang perlu ditindaklanjuti.
5. Tombol akses cepat ke tugas utama pengguna.

### Standar kartu metrik

- Latar putih, border `brand-border`, radius `xl`, shadow halus.
- Label singkat, angka besar, penjelasan periode/data.
- Ikon opsional dan sederhana; jangan gunakan dekorasi yang mengganggu.
- Gunakan warna status hanya untuk makna yang jelas.
- Susun responsif: 1 kolom di ponsel, 2 di tablet, 3–4 di desktop sesuai ruang.

```vue
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4">
  <section class="ui-panel p-5">
    <p class="text-sm font-medium text-ink-muted">Pembayaran Hari Ini</p>
    <p class="mt-3 text-2xl font-bold tabular-nums text-ink">—</p>
    <p class="mt-1 text-xs text-ink-muted">Total dari transaksi yang berhasil</p>
  </section>

  <section class="ui-panel p-5">
    <p class="text-sm font-medium text-ink-muted">Setoran Menunggu</p>
    <p class="mt-3 text-2xl font-bold tabular-nums text-ink">—</p>
    <p class="mt-1 text-xs text-ink-muted">Perlu verifikasi sesuai kewenangan</p>
  </section>
</div>
```

Angka `—` sengaja digunakan sebagai placeholder. Hubungkan metrik ke data backend yang sudah ada; jangan mengisi angka contoh di produksi. Kartu dan menu harus disaring berdasarkan peran dan izin yang berlaku.

---

## 7. Tabel perbandingan sebelum dan sesudah

| Area | Sebelum | Sesudah yang ditargetkan |
|---|---|---|
| Warna | Deep Teal bercampur `indigo-*` dan `gray-800` | Token `brand`, `ink`, `surface`, dan `status` konsisten |
| Navigasi | Tujuh menu admin berjejer di navbar | Sidebar dengan grup, status aktif, badge relevan, dan topbar mini |
| Tombol | Ukuran, hover, dan fokus berbeda antarhalaman | Primary/Secondary seragam, disabled dan focus state jelas |
| Input | Border/focus mengikuti gaya default Breeze | Border token, focus ring hijau, error dekat input |
| Tabel | Header dan status sulit dipindai | Header konsisten, zebra halus, nominal rata kanan, chip status |
| Loket kasir | Form dan aksi dapat terasa padat | Pencarian, tagihan, form, ringkasan, dan kuitansi terstruktur |
| Setoran kasir | Verifikasi fisik vs sistem kurang tegas | Dua panel perbandingan, selisih jelas, konfirmasi terkontrol |
| Dashboard | Kartu tidak seragam dengan layout baru | Metrik konsisten, prioritas per peran, responsif |
| Mobile | Navbar horizontal berpotensi overflow | Drawer sidebar, tabel yang dapat digulir, target sentuh memadai |

---

## 8. Rencana aksi empat fase

## Fase 1 — Konfigurasi Token Tailwind & Global Components

**Tujuan:** membuat fondasi visual sebelum migrasi halaman.

- [x] Konfirmasi versi Tailwind dan pola konfigurasi proyek.
- [x] Tambahkan token warna Deep Teal dan warna semantik.
- [x] Perbarui stylesheet global dengan warna latar, tipografi, focus ring, dan utilitas bersama.
- [x] Refactor `PrimaryButton.vue`.
- [x] Refactor `SecondaryButton.vue`.
- [x] Refactor `NavLink.vue`.
- [x] Refactor `TextInput.vue`.
- [x] Audit dan perbarui styling `Modal.vue` tanpa menghilangkan fitur aksesibilitas.
- [x] Cari seluruh penggunaan `indigo-*`, `gray-800`, dan focus ring lama; migrasikan berdasarkan konteks.
- [x] Uji halaman Welcome/Login agar desain yang sudah bagus tidak rusak.
- [x] Jalankan lint, type check, build, dan test yang tersedia.

**Definition of Done:**

- [x] Komponen dasar memakai token warna yang sama.
- [x] State hover, focus, disabled, error, dan loading telah diperiksa.
- [x] Tidak ada regresi visual penting pada Welcome/Login.

## Fase 2 — Implementasi Sidebar & Layout Baru

**Tujuan:** mengganti navbar padat dengan navigasi yang terstruktur.

- [x] Buat atau refactor komponen `AppSidebar.vue` (diimplementasikan sebagai `AdminSidebar.vue` untuk Admin layout).
- [x] Buat topbar mini dengan toggle, identitas pengguna, dan menu profil.
- [x] Kelompokkan menu menjadi Ringkasan, Master Data, Keuangan SPP, dan Pengaturan sesuai fitur aktual.
- [x] Pastikan menu yang tampil sesuai peran dan permission.
- [x] Integrasikan badge counter dari data backend yang terotorisasi (`badges.tagihan_belum_lunas`).
- [x] Tambahkan active state untuk rute saat ini.
- [x] Implementasikan collapse untuk desktop jika dibutuhkan.
- [x] Implementasikan drawer mobile dan tombol buka/tutup.
- [x] Pastikan fokus keyboard, label aksesibel, Escape, dan scroll drawer ditangani.
- [x] Uji seluruh rute utama untuk setiap peran.

**Definition of Done:**

- [x] Tidak ada overflow navbar pada viewport umum.
- [x] Semua menu yang berizin dapat dijangkau dan rute aktif ditandai.
- [x] Menu yang tidak berizin tidak ditampilkan, dan backend tetap memblokir akses langsung.

## Fase 3 — Migrasi Halaman Modul Operasional

**Tujuan:** memperbaiki alur kerja yang paling kritis bagi transaksi.

- [x] Migrasikan `Admin/Dashboard.vue` ke kartu metrik, chart tren, status siswa, dan spacing baru.
- [x] Migrasikan antarmuka Siswa (`Siswa/Dashboard.vue`, `Siswa/TagihanSaya.vue`, `DispensasiSpp/Index.vue`) ke sistem desain Deep Teal, kartu metrik, status badge, dan layout responsif.
- [x] Integrasikan `SiswaSidebar.vue` dan drawer mobile pada `AuthenticatedLayout.vue`.
- [x] Implementasikan cetak kuitansi resmi (`CetakKuitansiController.php` & template `kuitansi-spp.blade.php`) dengan otorisasi hak akses siswa.
- [ ] Migrasikan `PembayaranSpp/Index.vue` ke layout pencarian, tagihan, form, dan ringkasan (Role Kasir TU - Fase selanjutnya).
- [ ] Perjelas state loading, error validasi, sukses, dan pencegahan submit ganda pada pembayaran.
- [ ] Tampilkan tombol cetak kuitansi hanya setelah transaksi berhasil.
- [x] Perbarui tabel data dan status chip untuk seluruh modul terkait Admin (Users, Kelas, Siswa, Tarif SPP, Keringanan SPP, Tagihan SPP).
- [ ] Perbaiki `SetoranKasir/Index.vue` dengan panel fisik vs sistem dan selisih (Role Bendahara - Fase selanjutnya).
- [ ] Pastikan alasan/catatan selisih mengikuti aturan bisnis yang ada.
- [ ] Pastikan modal verifikasi memiliki konfirmasi, loading, dan feedback server.
- [x] Periksa format Rupiah, tanggal, periode SPP, serta teks status pada Admin dashboard dan modul master data.
- [x] Jalankan test pembayaran, setoran, laporan, dan otorisasi yang tersedia.

**Definition of Done:**

- [ ] Alur pembayaran berhasil dari awal sampai cetak kuitansi.
- [ ] Verifikasi setoran memperlihatkan nominal dan selisih dengan benar.
- [x] Tidak ada aturan bisnis atau otorisasi yang berubah hanya karena refactor UI.

## Fase 4 — Uji Responsivitas Mobile & Tablet

**Tujuan:** memastikan UI dapat digunakan pada layar kecil tanpa kehilangan fungsi.

- [ ] Uji viewport sekitar `360px`, `390px`, `768px`, `1024px`, dan desktop lebar.
- [ ] Pastikan drawer sidebar tidak menutupi konten secara permanen dan dapat ditutup.
- [ ] Pastikan form kasir tidak memerlukan scroll horizontal halaman.
- [ ] Uji tabel: gunakan overflow terkontrol atau tampilan kartu untuk data kompleks.
- [ ] Pastikan tombol utama mudah disentuh dan tidak terlalu berdekatan.
- [ ] Uji keyboard-only: Tab, Shift+Tab, Enter, Space, Escape sesuai konteks.
- [ ] Uji fokus terlihat dan urutan fokus logis pada modal.
- [ ] Periksa kontras teks, badge, placeholder, disabled state, dan error.
- [ ] Uji zoom browser hingga 200% dan pastikan konten penting masih dapat digunakan.
- [ ] Bandingkan screenshot sebelum/sesudah untuk halaman utama.
- [ ] Lakukan smoke test semua peran dan alur operasional.
- [ ] Dokumentasikan masalah yang belum selesai dan buat tindak lanjut.

**Definition of Done:**

- [ ] Tidak ada elemen penting terpotong atau keluar dari viewport.
- [ ] Fungsi inti tetap dapat digunakan di ponsel dan tablet.
- [ ] Semua isu kritis aksesibilitas dan regresi fungsional telah diperbaiki atau dicatat dengan mitigasi yang jelas.

---

## 9. Strategi pengujian dan validasi

### 9.1 Pemeriksaan teknis

Gunakan script yang tersedia di `package.json`. Contoh umum (nama script dapat berbeda):

```bash
npm run lint
npm run build
npm run test
```

Jangan menganggap seluruh perintah tersedia; periksa script proyek terlebih dahulu. Jalankan juga test backend Laravel yang relevan, terutama untuk pembayaran, setoran, dan izin.

### 9.2 Skenario manual minimum

- [ ] Login dan logout tetap bekerja.
- [ ] Setiap peran hanya melihat navigasi yang sesuai.
- [ ] Mengakses URL terlarang secara langsung tetap ditolak backend.
- [ ] Pencarian siswa menampilkan hasil yang benar.
- [ ] Pembayaran invalid menampilkan pesan yang dapat dipahami.
- [ ] Submit pembayaran ganda dicegah atau ditangani secara aman.
- [ ] Pembayaran berhasil menampilkan kuitansi transaksi yang benar.
- [ ] Setoran tanpa selisih dan dengan selisih mengikuti aturan bisnis.
- [ ] Modal tidak kehilangan fokus dan tidak menutup tanpa sengaja saat proses penting.
- [ ] Dashboard tidak menampilkan angka placeholder di produksi.
- [ ] Tampilan mobile tidak merusak alur kasir atau verifikasi.
- [ ] Print kuitansi tidak menampilkan sidebar, topbar, atau tombol navigasi yang tidak diperlukan.

### 9.3 Rekomendasi urutan migrasi

Migrasikan satu halaman pada satu waktu:

1. Token dan komponen global.
2. Layout/sidebar pada halaman internal yang risikonya rendah.
3. Dashboard.
4. Tabel dan halaman master data.
5. Loket kasir.
6. Setoran kasir dan modal verifikasi.
7. Pengujian regresi menyeluruh.

Lakukan review visual dan fungsional setiap kali satu bagian selesai. Hindari perubahan seluruh aplikasi melalui penggantian teks massal tanpa pemeriksaan.

---

## 10. Panduan implementasi dan pemeliharaan

- **Gunakan komponen bersama:** perubahan tombol atau input dilakukan sekali, lalu diverifikasi pada seluruh pemakai.
- **Jangan menaruh logika bisnis di komponen visual:** status pembayaran, hak akses, nominal, dan hasil verifikasi harus berasal dari sumber backend yang benar.
- **Jaga kontrak Inertia:** pertahankan nama prop, event, route, dan bentuk response yang sudah dipakai sampai migrasi terencana dilakukan.
- **Hindari hardcode data operasional:** badge pending, kartu metrik, nama pengguna, nominal, dan URL kuitansi harus menggunakan data aplikasi.
- **Jangan mengandalkan warna saja:** sertakan teks, ikon yang bermakna, atau label untuk status.
- **Gunakan ikon secara hemat:** pilih satu pustaka ikon yang sudah terpasang; jangan menambahkan dependency hanya untuk dekorasi kecil tanpa kebutuhan.
- **Hormati preferensi gerakan:** bila ada transisi, pertimbangkan `motion-reduce` untuk pengguna yang mengurangi animasi.
- **Periksa print style:** kuitansi perlu berfokus pada informasi transaksi, bukan elemen navigasi aplikasi.

## 11. Checklist rilis akhir

- [ ] Seluruh empat fase selesai atau sisa pekerjaan terdokumentasi.
- [ ] Token warna diterapkan konsisten.
- [ ] Sidebar/topbar berfungsi pada desktop, tablet, dan ponsel.
- [ ] Menu dan badge mengikuti izin serta data aktual.
- [ ] Komponen inti memiliki hover, focus, disabled, dan error state.
- [ ] Tabel, nominal, dan status pembayaran terbaca.
- [ ] Loket kasir dan cetak kuitansi lulus pengujian.
- [ ] Verifikasi setoran fisik vs sistem lulus pengujian.
- [ ] Dashboard menggunakan data backend, bukan placeholder.
- [ ] Test otomatis dan smoke test manual lulus.
- [ ] Screenshot sebelum/sesudah disimpan untuk review.
- [ ] Perubahan ditinjau developer lain dan siap masuk ke branch utama.

---

**Prinsip akhir:** utamakan konsistensi, kejelasan, dan keandalan transaksi. UI yang menarik harus membantu staf sekolah menyelesaikan pekerjaan dengan lebih cepat dan mengurangi kesalahan, bukan sekadar menambah dekorasi.
