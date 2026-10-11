<?php

namespace App\Http\Middleware;

use App\Enums\PeranPengguna;
use App\Models\TagihanSpp;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $role = $user?->role;
        $roleValue = $role instanceof PeranPengguna ? $role->value : (string) ($role ?? 'siswa');
        $roleLabel = $role instanceof PeranPengguna
            ? $role->label()
            : (PeranPengguna::tryFrom((string) $role)?->label() ?? 'Siswa');

        $badges = [];
        $siswaData = null;

        if ($user && $roleValue === 'admin') {
            try {
                $badges['tagihan_belum_lunas'] = TagihanSpp::where('status', '!=', 'lunas')->count();
            } catch (\Throwable $e) {
                // Ignore if table not yet migrated
            }
        } elseif ($user && $roleValue === 'siswa') {
            try {
                $badges['tagihan_siswa_belum_lunas'] = TagihanSpp::whereHas('siswa', fn ($q) => $q->where('user_id', $user->id))
                    ->where('status', '!=', 'lunas')
                    ->count();

                $siswaModel = $user->siswa()->with('kelas')->first();
                if ($siswaModel) {
                    $siswaData = [
                        'id' => $siswaModel->id,
                        'nama_lengkap' => $siswaModel->nama_lengkap,
                        'nis' => $siswaModel->nis,
                        'nisn' => $siswaModel->nisn,
                        'kelas' => $siswaModel->kelas?->nama_kelas ?? '-',
                        'status_siswa' => $siswaModel->status_siswa,
                    ];
                }
            } catch (\Throwable $e) {
                // Ignore if relation or table not yet migrated
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $roleValue,
                    'role_label' => $roleLabel,
                ] : null,
                'siswa' => $siswaData,
            ],
            'badges' => $badges,
        ];
    }
}
