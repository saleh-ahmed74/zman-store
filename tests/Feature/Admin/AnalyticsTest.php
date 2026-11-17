<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\User;
use App\Services\DashboardAnalytics;
use Illuminate\Support\Str;

it('aggregates expected, actual and cancelled revenue from MySQL orders', function (): void {
    $user = User::factory()->create();
    $makeOrder = function (string $status, float $total) use ($user): Order {
        return Order::query()->create([
            'user_id' => $user->id, 'number' => 'HM-'.strtoupper(bin2hex(random_bytes(5))), 'status' => $status,
            'customer_name' => $user->name, 'phone_country' => 'EG', 'phone_e164' => '+201062575484', 'locale' => 'ar',
            'address' => 'Cairo, Egypt', 'payment_method' => 'cod', 'payment_status' => 'unpaid',
            'subtotal' => $total, 'discount' => 0, 'shipping' => 0, 'total' => $total,
            'idempotency_key' => (string) Str::uuid(), 'placed_at' => now(),
            'delivered_at' => $status === 'delivered' ? now() : null,
        ]);
    };
    $makeOrder('pending', 700);
    $delivered = $makeOrder('delivered', 500);
    $makeOrder('cancelled', 600);
    $delivered->items()->create([
        'product_name_ar' => 'هودي', 'product_name_en' => 'Hoodie', 'color_name' => 'أسود', 'color_hex' => '#111111',
        'size' => 'M', 'unit_price' => 500, 'quantity' => 1, 'line_total' => 500,
    ]);

    $metrics = app(DashboardAnalytics::class)->forPeriod(now()->toDateString(), now()->toDateString(), false);

    expect($metrics['empty'])->toBeFalse()
        ->and($metrics['expected'])->toBe(700.0)
        ->and($metrics['actual'])->toBe(500.0)
        ->and($metrics['cancelled_loss'])->toBe(600.0)
        ->and($metrics['statuses'])->toMatchArray(['pending' => 1, 'delivered' => 1, 'cancelled' => 1])
        ->and($metrics['top_products'][0]->quantity)->toBe(1);
});
