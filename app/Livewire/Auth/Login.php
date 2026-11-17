<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Services\StoreSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function authenticate(): mixed
    {
        $this->validate([
            'email' => ['required', 'email:rfc', 'max:120'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);
        $key = 'login:'.Str::transliterate(Str::lower(trim($this->email)).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)])]);
        }

        RateLimiter::hit($key, 60);

        if (! Auth::attempt(['email' => Str::lower(trim($this->email)), 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        RateLimiter::clear($key);
        session()->regenerate();

        if (app(StoreSettings::class)->get('store.auth.require_email_verification', false) && ! Auth::user()?->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        session()->flash('toast', __('store.login_success'));

        return redirect()->intended(Auth::user()?->is_admin ? route('admin') : route('home'));
    }

    public function render(): View
    {
        return view('livewire.auth.login')->layout('layouts.store', ['title' => __('store.login')]);
    }
}
