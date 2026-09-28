<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        if ($this->shouldForceHttps()) {
            URL::forceScheme('https');
        }

        $this->registerLivewireScriptRoute();
    }

    private function shouldForceHttps(): bool
    {
        if ($this->app->runningInConsole()) {
            return false;
        }

        $request = request();

        return $request->header('X-Forwarded-Proto') === 'https'
            || Str::startsWith($request->fullUrl(), 'https://')
            || ! $this->app->isLocal();
    }

    private function registerLivewireScriptRoute(): void
    {
        $prefix = trim((string) env('LIVEWIRE_URL_PREFIX', ''), '/');

        if ($prefix === '') {
            return;
        }

        Livewire::setScriptRoute(function ($handle) use ($prefix) {
            return Route::get("{$prefix}/livewire/livewire.js", $handle);
        });
    }
}
