<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use App\Filament\Pages\Auth\CustomRequestPasswordReset;
use App\Filament\Pages\Auth\CustomResetPassword;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\FirstResponseSlaChart;
use App\Filament\Widgets\TicketCategoryChart;
use App\Filament\Widgets\TicketStatsWidget;
use App\Filament\Widgets\TicketStatusChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->passwordReset(CustomRequestPasswordReset::class, CustomResetPassword::class)
            ->renderHook('panels::body.end', fn () => view('filament.custom-login-style'))
            ->renderHook('panels::head.end', fn (): string => $this->renderAdminAssets())
            ->brandName('IT Helpdesk PTPN IV')
            ->colors([
                'primary' => Color::Green,
            ])
            ->darkMode(true)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets($this->getPanelWidgets())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function renderAdminAssets(): string
    {
        if (! request()->routeIs(
            'filament.admin.pages.dashboard',
            'filament.admin.resources.laporan-ticket.create',
            'filament.admin.resources.laporan-ticket.edit',
        )) {
            return '';
        }

        return Blade::render('@vite(["resources/css/app.css", "resources/js/app.js"])');
    }

    private function getPanelWidgets(): array
    {
        return [
            TicketStatsWidget::class,
            FirstResponseSlaChart::class,
            TicketStatusChart::class,
            TicketCategoryChart::class,
        ];
    }
}
