<script setup>
import { ref, computed } from 'vue'
import { useForm, usePage, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue' // Sesuaikan jika nama layout Anda AppLayout.vue
import axios from 'axios'

const props = defineProps({
  transaksi: Object,
  filters: Object,
})

const page = usePage()

// State Pencarian Siswa
const keyword = ref('')
const hasilPencarian = ref([])
const isMencari = ref(false)
const siswaTerpilih = ref(null)
const tagihanTerpilih = ref(null)

// Modal Kuitansi
const showModalKuitansi = ref(false)
const dataKuitansi = computed(() => page.props.flash?.kuitansi || null)

const form = useForm({
  tagihan_spp_id: null,
  jumlah_dibayar: '',
  metode_pembayaran: 'tunai',
  nomor_referensi_bank: '',
  catatan: '',
})

let searchTimeout = null
const onInputCari = () => {
  clearTimeout(searchTimeout)
  if (keyword.value.length < 2) {
    hasilPencarian.value = []
    return
  }

  isMencari.value = true
  searchTimeout = setTimeout(async () => {
    try {
      const res = await axios.get(route('tu.pembayaran-spp.cari-siswa'), {
        params: { q: keyword.value }
      })
      hasilPencarian.value = res.data
    } finally {
      isMencari.value = false
    }
  }, 350)
}

const pilihSiswa = (siswa) => {
  siswaTerpilih.value = siswa
  hasilPencarian.value = []
  keyword.value = `${siswa.nis} - ${siswa.nama_lengkap}`
  tagihanTerpilih.value = null
  form.reset()
}

const pilihTagihan = (tagihan) => {
  tagihanTerpilih.value = tagihan
  form.tagihan_spp_id = tagihan.id
  form.jumlah_dibayar = Number(tagihan.sisa_tagihan)
}

const submitBayar = () => {
  form.post(route('tu.pembayaran-spp.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showModalKuitansi.value = true
      form.reset()
      tagihanTerpilih.value = null
      
      if (siswaTerpilih.value) {
        axios.get(route('tu.pembayaran-spp.cari-siswa'), { params: { q: siswaTerpilih.value.nis } })
          .then(res => {
            const updated = res.data.find(s => s.id === siswaTerpilih.value.id)
            if (updated) siswaTerpilih.value = updated
          })
      }
    }
  })
}

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0)
const cetakKuitansi = () => window.print()
</script>

<template>
  <Head title="Loket Pembayaran SPP" />

  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
      
      <!-- Judul Halaman -->
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Loket Pembayaran SPP</h1>
      </div>

      <!-- Grid Formulir Kasir -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Kolom Kiri: Cari & Pilih Tagihan Siswa -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Cari Siswa (NIS / Nama)</label>
            <div class="relative">
              <input 
                v-model="keyword" 
                @input="onInputCari"
                type="text" 
                placeholder="Ketik minimal 2 karakter..." 
                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
              />
              
              <!-- Dropdown Hasil -->
              <ul v-if="hasilPencarian.length" class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-100">
                <li 
                  v-for="s in hasilPencarian" 
                  :key="s.id" 
                  @click="pilihSiswa(s)"
                  class="p-3 hover:bg-indigo-50/60 cursor-pointer flex justify-between items-center text-sm transition"
                >
                  <div>
                    <span class="font-bold text-gray-900">{{ s.nama_lengkap }}</span> ({{ s.nis }})
                    <div class="text-xs text-gray-500">Kelas: {{ s.kelas?.nama_kelas || '-' }}</div>
                  </div>
                  <span class="text-xs bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full font-semibold border border-indigo-100">
                    {{ s.tagihan_spp?.length || 0 }} Tagihan Aktif
                  </span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Tabel Tagihan Siswa -->
          <div v-if="siswaTerpilih" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
              <div>
                <h3 class="font-bold text-gray-900 text-base">{{ siswaTerpilih.nama_lengkap }}</h3>
                <p class="text-xs text-gray-500">NIS: {{ siswaTerpilih.nis }} | Kelas: {{ siswaTerpilih.kelas?.nama_kelas || '-' }}</p>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/80 text-gray-500 text-xs uppercase tracking-wider font-semibold">
                  <tr>
                    <th class="p-3">Periode Tagihan</th>
                    <th class="p-3 text-right">Tagihan</th>
                    <th class="p-3 text-right">Terbayar</th>
                    <th class="p-3 text-right">Sisa</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr 
                    v-for="t in siswaTerpilih.tagihan_spp" 
                    :key="t.id"
                    :class="{'bg-indigo-50/50': tagihanTerpilih?.id === t.id}"
                  >
                    <td class="p-3 font-medium text-gray-900">Bulan {{ t.bulan }} / {{ t.tahun }}</td>
                    <td class="p-3 text-right text-gray-600">{{ formatRupiah(t.nominal_tagihan) }}</td>
                    <td class="p-3 text-right text-emerald-600 font-medium">{{ formatRupiah(t.total_terbayar) }}</td>
                    <td class="p-3 text-right font-bold text-rose-600">{{ formatRupiah(t.sisa_tagihan) }}</td>
                    <td class="p-3 text-center">
                      <span 
                        class="text-xs px-2.5 py-0.5 rounded-full font-semibold uppercase"
                        :class="t.status === 'sebagian' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                      >
                        {{ t.status }}
                      </span>
                    </td>
                    <td class="p-3 text-center">
                      <button 
                        @click="pilihTagihan(t)"
                        type="button" 
                        class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition"
                      >
                        Pilih
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!siswaTerpilih.tagihan_spp?.length">
                    <td colspan="6" class="p-6 text-center text-sm text-gray-500">Tidak ada tagihan tertunggak untuk siswa ini.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Eksekusi Kasir -->
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-4 h-fit">
          <h3 class="font-bold text-gray-900 text-base border-b pb-3">Eksekusi Kasir</h3>

          <div v-if="!tagihanTerpilih" class="text-sm text-gray-400 text-center py-12">
            Pilih tagihan dari tabel di sebelah kiri untuk memproses pembayaran.
          </div>

          <form v-else @submit.prevent="submitBayar" class="space-y-4">
            <div class="p-3 bg-gray-50 rounded-lg space-y-1.5 text-xs">
              <div class="flex justify-between text-gray-600">
                <span>Periode:</span>
                <span class="font-bold text-gray-900">Bulan {{ tagihanTerpilih.bulan }} / {{ tagihanTerpilih.tahun }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Sisa Tagihan:</span>
                <span class="font-bold text-rose-600">{{ formatRupiah(tagihanTerpilih.sisa_tagihan) }}</span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah Bayar (Rp)</label>
              <input 
                v-model="form.jumlah_dibayar" 
                type="number" 
                :max="tagihanTerpilih.sisa_tagihan"
                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
              />
              <div v-if="form.errors.jumlah_dibayar" class="text-xs text-rose-600 mt-1">{{ form.errors.jumlah_dibayar }}</div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Pembayaran</label>
              <select 
                v-model="form.metode_pembayaran" 
                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
              >
                <option value="tunai">Tunai</option>
                <option value="transfer">Transfer Bank</option>
              </select>
            </div>

            <div v-if="form.metode_pembayaran === 'transfer'">
              <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Referensi Transfer</label>
              <input 
                v-model="form.nomor_referensi_bank" 
                type="text" 
                placeholder="Kode unik / No. Rekening"
                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
              />
              <div v-if="form.errors.nomor_referensi_bank" class="text-xs text-rose-600 mt-1">{{ form.errors.nomor_referensi_bank }}</div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan (Opsional)</label>
              <textarea 
                v-model="form.catatan" 
                rows="2" 
                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
              ></textarea>
            </div>

            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold rounded-lg text-sm transition shadow-sm"
            >
              {{ form.processing ? 'Memproses...' : 'Simpan Pembayaran' }}
            </button>
          </form>
        </div>

      </div>

      <!-- Tabel Riwayat Transaksi -->
      <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-4">
        <h3 class="font-bold text-gray-900 text-base">Riwayat Transaksi Terkini</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-gray-50/80 text-gray-500 text-xs uppercase tracking-wider font-semibold">
              <tr>
                <th class="p-3">No. Kuitansi</th>
                <th class="p-3">Siswa</th>
                <th class="p-3">Periode</th>
                <th class="p-3 text-right">Nominal</th>
                <th class="p-3 text-center">Metode</th>
                <th class="p-3">Waktu</th>
                <th class="p-3">Petugas</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="trx in props.transaksi?.data" :key="trx.id">
                <td class="p-3 font-semibold text-indigo-600">{{ trx.nomor_kuitansi }}</td>
                <td class="p-3 text-gray-900">{{ trx.tagihan?.siswa?.nama_lengkap || '-' }}</td>
                <td class="p-3 text-gray-600">Bulan {{ trx.tagihan?.bulan }}/{{ trx.tagihan?.tahun }}</td>
                <td class="p-3 text-right font-bold text-emerald-600">{{ formatRupiah(trx.jumlah_dibayar) }}</td>
                <td class="p-3 text-center">
                  <span class="text-xs px-2 py-0.5 rounded font-medium uppercase bg-gray-100 text-gray-700">
                    {{ trx.metode_pembayaran }}
                  </span>
                </td>
                <td class="p-3 text-gray-500 text-xs">{{ trx.waktu_transaksi }}</td>
                <td class="p-3 text-gray-500 text-xs">{{ trx.petugas?.name || '-' }}</td>
              </tr>
              <tr v-if="!props.transaksi?.data?.length">
                <td colspan="7" class="p-6 text-center text-sm text-gray-400">Belum ada riwayat transaksi yang tercatat.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Modal Kuitansi Pembayaran -->
    <div v-if="showModalKuitansi && dataKuitansi" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl print:m-0 print:p-0 print:shadow-none">
        <div class="text-center border-b pb-3">
          <h2 class="text-lg font-bold text-gray-900">KUITANSI PEMBAYARAN SPP</h2>
          <p class="text-xs text-gray-500 font-mono">{{ dataKuitansi.nomor_kuitansi }}</p>
        </div>

        <div class="space-y-2 text-xs text-gray-700">
          <div class="flex justify-between"><span>Tanggal:</span> <span>{{ dataKuitansi.waktu_transaksi }}</span></div>
          <div class="flex justify-between"><span>Nama Siswa:</span> <span class="font-semibold text-gray-900">{{ dataKuitansi.nama_siswa }}</span></div>
          <div class="flex justify-between"><span>NIS / Kelas:</span> <span>{{ dataKuitansi.nis }} / {{ dataKuitansi.kelas }}</span></div>
          <div class="flex justify-between"><span>Periode Tagihan:</span> <span>Bulan {{ dataKuitansi.bulan }} / {{ dataKuitansi.tahun }}</span></div>
          <div class="flex justify-between"><span>Metode Bayar:</span> <span class="uppercase font-semibold">{{ dataKuitansi.metode_pembayaran }}</span></div>
          <div class="flex justify-between border-t pt-2 font-bold text-sm">
            <span>Jumlah Dibayar:</span>
            <span class="text-emerald-600">{{ formatRupiah(dataKuitansi.jumlah_dibayar) }}</span>
          </div>
          <div class="flex justify-between text-gray-500">
            <span>Sisa Tagihan:</span>
            <span>{{ formatRupiah(dataKuitansi.sisa_tagihan) }} ({{ dataKuitansi.status_tagihan }})</span>
          </div>
          <div class="flex justify-between text-gray-500 pt-1">
            <span>Petugas Loket:</span>
            <span>{{ dataKuitansi.petugas }}</span>
          </div>
        </div>

        <div class="flex gap-2 pt-3 border-t print:hidden">
          <button @click="cetakKuitansi" class="flex-1 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition">Cetak Kuitansi</button>
          <button @click="showModalKuitansi = false" class="py-2 px-4 border border-gray-300 text-gray-700 text-xs rounded-lg hover:bg-gray-50 transition">Tutup</button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template> 