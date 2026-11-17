<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\StoreSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register(): mixed
    {
        $key = 'register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)])]);
        }
        RateLimiter::hit($key, 60);

        $this->name = trim($this->name);
        $validated = $this->validate([
            'name' => ['required', 'string', 'regex:/^\p{L}+(?:[ \'.-]\p{L}+)+$/u', 'min:3', 'max:60'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:120', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()->uncompromised()],
        ]);
        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'password' => $validated['password'],
            'locale' => app()->getLocale(),
        ]);

        Auth::login($user);
        session()->regenerate();

        if (app(StoreSettings::class)->get('store.auth.require_email_verification', false)) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice');
        }

        session()->flash('toast', __('store.register_success'));

        return redirect()->intended(route('home'));
    }

    public function render(): View
    {
        return view('livewire.auth.register')->layout('layouts.store', ['title' => __('store.register')]);
    }
}
