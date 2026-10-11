<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\TagihanSpp;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiswaDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $totalKewajiban = TagihanSpp::whereHas('siswa', fn ($q) => $q->where('user_id', $user->id))
            ->sum('sisa_tagihan');

        $tagihanList = TagihanSpp::whereHas('siswa', fn ($q) => $q->where('user_id', $user->id))
            ->where('sisa_tagihan', '>', 0)
            ->with('siswa:id,nama_lengkap')
            ->orderBy('jatuh_tempo')
            ->get();

        return Inertia::render('Siswa/Dashboard', [
            'totalKewajiban' => $totalKewajiban,
            'tagihanList' => $tagihanList,
        ]);
    }
}