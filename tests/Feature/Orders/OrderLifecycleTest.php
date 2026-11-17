<?php

declare(strict_types=1);

use App\Actions\CancelOrder;
use App\Actions\RequestReturn;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

function makeLifecycleOrder(User $user, string $status, ?Carbon $deliveredAt = null): Order
{
    return Order::query()->create([
        'user_id' => $user->id, 'number' => 'HM-'.strtoupper(bin2hex(random_bytes(5))), 'status' => $status,
        'customer_name' => $user->name, 'phone_country' => 'EG', 'phone_e164' => '+201062575484', 'locale' => 'ar',
        'address' => 'Cairo, Egypt', 'payment_method' => 'cod', 'payment_status' => 'unpaid',
        'subtotal' => 650, 'discount' => 0, 'shipping' => 50, 'total' => 700,
        'idempotency_key' => (string) Str::uuid(), 'placed_at' => now(), 'delivered_at' => $deliveredAt,
    ]);
}

it('allows only the owning customer to cancel a pending order', function (): void {
    $user = User::factory()->create();
    $order = makeLifecycleOrder($user, 'pending');

    app(CancelOrder::class)->handle($order, $user);

    expect($order->fresh()->status)->toBe('cancelled')
        ->and(OrderStatusLog::query()->where('order_id', $order->id)->where('to_status', 'cancelled')->exists())->toBeTrue();
});

it('rejects cancelling a non-pending order', function (): void {
    $user = User::factory()->create();
    $order = makeLifecycleOrder($user, 'confirmed');

    expect(fn () => app(CancelOrder::class)->handle($order, $user))->toThrow(HttpException::class);
});

it('accepts a return within fourteen days of delivery and moves the order into review', function (): void {
    $user = User::factory()->create();
    $order = makeLifecycleOrder($user, 'delivered', now()->subDays(5));

    $request = app(RequestReturn::class)->handle($order, $user, 'The size does not fit.');

    expect($request)->toBeInstanceOf(ReturnRequest::class)
        ->and($order->fresh()->status)->toBe('return_requested');
});

it('rejects returns after the fourteen-day delivery window', function (): void {
    $user = User::factory()->create();
    $order = makeLifecycleOrder($user, 'delivered', now()->subDays(15));

    expect(fn () => app(RequestReturn::class)->handle($order, $user, 'The size does not fit.'))->toThrow(HttpException::class);
});
