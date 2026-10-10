<?php

namespace App\Providers;

use App\Interfaces\DispensasiSppInterface;
use App\Interfaces\KelasInterface;
use App\Interfaces\KeringananSppInterface;
use App\Interfaces\SetoranKasirInterface;
use App\Interfaces\SiswaInterface;
use App\Interfaces\TagihanSppInterface;
use App\Interfaces\TarifSppInterface;
use App\Interfaces\TransaksiSppInterface;
use App\Interfaces\UserInterface;
use App\Repositories\DispensasiSppRepository;
use App\Repositories\KelasRepository;
use App\Repositories\KeringananSppRepository;
use App\Repositories\SetoranKasirRepository;
use App\Repositories\SiswaRepository;
use App\Repositories\TagihanSppRepository;
use App\Repositories\TarifSppRepository;
use App\Repositories\TransaksiSppRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TarifSppInterface::class, TarifSppRepository::class);
        $this->app->bind(KeringananSppInterface::class, KeringananSppRepository::class);
        $this->app->bind(TagihanSppInterface::class, TagihanSppRepository::class);
        $this->app->bind(TransaksiSppInterface::class, TransaksiSppRepository::class);
        $this->app->bind(SetoranKasirInterface::class, SetoranKasirRepository::class);
        $this->app->bind(DispensasiSppInterface::class, DispensasiSppRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(KelasInterface::class, KelasRepository::class);
        $this->app->bind(SiswaInterface::class, SiswaRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Vite::prefetch(concurrency: 3);
        //
    }
}
