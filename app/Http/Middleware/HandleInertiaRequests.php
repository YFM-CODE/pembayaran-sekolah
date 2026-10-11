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
        if ($user && $roleValue === 'admin') {
            try {
                $badges['tagihan_belum_lunas'] = TagihanSpp::where('status', '!=', 'lunas')->count();
            } catch (\Throwable $e) {
                // Ignore if table not yet migrated
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
            ],
            'badges' => $badges,
        ];
    }
}
