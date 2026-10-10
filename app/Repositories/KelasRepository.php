<?

namespace App\Repositories;

use App\Interfaces\KelasInterface;
use App\Models\Kelas;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class KelasRepository implements KelasInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Kelas::query()
            ->withCount('siswa')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_kelas', 'like', "%{$search}%")
                        ->orWhere('jurusan', 'like', "%{$search}%");
                });
            })
            ->when($filters['tingkat'] ?? null, function ($query, $tingkat) {
                $query->where('tingkat', $tingkat);
            })
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function cariBerdasarkanId(int $id): ?Kelas
    {
        return Kelas::find($id);
    }

    public function semua(): Collection
    {
        return Kelas::select('id', 'nama_kelas', 'tingkat', 'jurusan')->get();
    }

    public function buat(array $data): Kelas
    {
        return Kelas::create($data);
    }

    public function perbarui(Kelas $kelas, array $data): bool
    {
        return $kelas->update($data);
    }

    public function hapus(Kelas $kelas): bool
    {
        return $kelas->delete();
    }

    public function memilikiSiswa(Kelas $kelas): bool
    {
        return $kelas->siswa()->exists();
    }
}
