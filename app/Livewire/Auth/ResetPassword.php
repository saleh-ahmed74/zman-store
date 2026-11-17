<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ResetPassword extends Component
{
    public string $token;

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function resetPassword(): mixed
    {
        $validated = $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email:rfc,dns', 'max:120'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()->uncompromised()],
        ]);

        $status = Password::reset($validated, function ($user, string $password): void {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordResetEvent($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        session()->flash('status', __('store.password_reset'));

        return redirect()->route('login');
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password')->layout('layouts.store', ['title' => __('store.reset_password')]);
    }
}
