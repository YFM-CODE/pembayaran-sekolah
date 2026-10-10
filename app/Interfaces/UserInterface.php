<?

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function cariBerdasarkanId(int $id): ?User;

    public function buat(array $data): User;

    public function perbarui(User $user, array $data): bool;

    public function toggleStatus(User $user): bool;

    public function resetPassword(User $user, string $passwordBaru): bool;

    public function getPenggunaSiswaTersedia(?int $currentUserId = null): Collection;
}