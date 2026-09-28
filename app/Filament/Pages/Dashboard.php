<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public static function canAccess(): bool
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        return $user->hasPermission('dashboard.view');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manageUsers')
                ->label('Manage User')
                ->icon('heroicon-o-users')
                ->color('gray')
                ->url(UserResource::getUrl())
                ->visible(fn (): bool => Auth::user()?->hasPermission('user.view') ?? false),
        ];
    }
}
