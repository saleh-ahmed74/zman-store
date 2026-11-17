<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $email = '';

    public function sendLink(): void
    {
        $key = 'forgot:'.Str::lower(trim($this->email)).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)])]);
        }
        RateLimiter::hit($key, 60);
        $this->validate(['email' => ['required', 'email:rfc,dns', 'max:120']]);
        Password::sendResetLink(['email' => $this->email]);

        session()->flash('status', __('store.reset_link_sent'));
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password')->layout('layouts.store', ['title' => __('store.forgot_password')]);
    }
}
