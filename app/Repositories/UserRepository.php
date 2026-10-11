<?

namespace App\Repositories;

use App\Enums\PeranPengguna;
use App\Interfaces\UserInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, function ($query, $role) {
                $query->where('role', $role);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function cariBerdasarkanId(int $id): ?User
    {
        return User::find($id);
    }

    public function buat(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $data['email_verified_at'] = now();

        return User::create($data);
    }

    public function perbarui(User $user, array $data): bool
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $user->update($data);
    }

    public function toggleStatus(User $user): bool
    {
        return $user->update([
            'is_active' => ! $user->is_active,
        ]);
    }

    public function resetPassword(User $user, string $passwordBaru): bool
    {
        return $user->update([
            'password' => Hash::make($passwordBaru),
        ]);
    }

    public function getPenggunaSiswaTersedia(?int $currentUserId = null): Collection
    {
        return User::where('role', PeranPengguna::SISWA->value)
            ->where(function ($query) use ($currentUserId) {
                $query->whereDoesntHave('siswa');
                if ($currentUserId) {
                    $query->orWhere('id', $currentUserId);
                }
            })
            ->select('id', 'name', 'email')
            ->get();
    }
}
