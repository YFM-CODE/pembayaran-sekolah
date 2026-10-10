<?

namespace App\Repositories;

use App\Interfaces\SiswaInterface;
use App\Models\Siswa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SiswaRepository implements SiswaInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Siswa::query()
            ->with(['kelas', 'user'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($filters['kelas_id'] ?? null, function ($query, $kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function cariBerdasarkanId(int $id): ?Siswa
    {
        return Siswa::find($id);
    }

    public function buat(array $data): Siswa
    {
        return Siswa::create($data);
    }

    public function perbarui(Siswa $siswa, array $data): bool
    {
        return $siswa->update($data);
    }

    public function hapus(Siswa $siswa): bool
    {
        return $siswa->delete();
    }

    public function perbaruiStatus(Siswa $siswa, string $status): bool
    {
        return $siswa->update(['status' => $status]);
    }

    public function tautkanUser(Siswa $siswa, int $userId): bool
    {
        return $siswa->update(['user_id' => $userId]);
    }

    public function memilikiTransaksiAtauTagihan(Siswa $siswa): bool
    {
        return $siswa->tagihanSpp()->exists() || $siswa->transaksiSpp()->exists();
    }
}
