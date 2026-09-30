<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class PasswordResetResponse implements PasswordResetResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect()->route('login');
    }
}
