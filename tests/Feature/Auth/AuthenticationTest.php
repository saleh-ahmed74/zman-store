<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function (): void {
    Http::fake();
});

it('registers a customer without granting admin access', function (): void {
    Livewire::test(Register::class)
        ->set('name', 'Saleh Ahmed')
        ->set('email', 'saleh@example.com')
        ->set('password', 'Strong-password-2046!')
        ->set('password_confirmation', 'Strong-password-2046!')
        ->call('register')
        ->assertRedirect(route('home'));

    expect(User::query()->where('email', 'saleh@example.com')->firstOrFail()->is_admin)->toBeFalse();
    $this->assertAuthenticated();
});

it('logs a customer in and rotates the session id', function (): void {
    User::query()->create([
        'name' => 'Saleh Ahmed',
        'email' => 'saleh@example.com',
        'password' => Hash::make('Strong-password-2046!'),
    ]);
    $oldSessionId = session()->getId();

    Livewire::test(Login::class)
        ->set('email', 'saleh@example.com')
        ->set('password', 'Strong-password-2046!')
        ->call('authenticate')
        ->assertRedirect(route('home'));

    $this->assertAuthenticated();
    expect(session()->getId())->not->toBe($oldSessionId);
});

it('uses a generic message when login credentials are invalid', function (): void {
    Livewire::test(Login::class)
        ->set('email', 'missing@example.com')
        ->set('password', 'wrong-password')
        ->call('authenticate')
        ->assertHasErrors(['email']);

    $this->assertGuest();
});

it('protects the admin area from customer accounts', function (): void {
    $customer = User::query()->create([
        'name' => 'Saleh Ahmed',
        'email' => 'saleh@example.com',
        'password' => Hash::make('Strong-password-2046!'),
    ]);

    $this->actingAs($customer)->get(route('admin'))->assertForbidden();
});
