<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Governorate;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\User;
use App\Services\CalculateCart;
use App\Services\StoreSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PlaceOrder
{
    /** @param array{name: string, phone_country: string, phone: string, governorate_id: ?int, address: string, payment_method: string, payment_proof_path: ?string, idempotency_key: string} $data */
    public function handle(array $data, ?User $user): Order
    {
        return DB::transaction(function () use ($data, $user): Order {
            $existing = Order::query()->where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing !== null) {
                return $existing;
            }

            $cart = app(CalculateCart::class)->calculate(session('cart.items', []));
            abort_if($cart['items'] === [], 422, __('store.cart_empty'));

            $freeForAll = (bool) app(StoreSettings::class)->get('store.shipping.free_for_all', false);
            $governorate = $freeForAll ? null : Governorate::query()->where('is_active', true)->findOrFail($data['governorate_id']);
            $shipping = $freeForAll || $cart['free_shipping'] ? 0.0 : (float) $governorate->shipping_price;
            $payment = app(StoreSettings::class)->get('store.payment', []);
            $destination = match ($data['payment_method']) {
                'instapay' => (string) ($payment['instapay_url'] ?? ''),
                'vodafone_cash' => (string) ($payment['vodafone_cash'] ?? ''),
                default => null,
            };
            $total = round($cart['subtotal'] - $cart['discount'] + $shipping, 2);
            $order = Order::query()->create([
                'user_id' => $user?->id, 'number' => 'HM-'.Str::upper(Str::random(10)), 'status' => 'pending',
                'customer_name' => $data['name'], 'phone_country' => $data['phone_country'], 'phone_e164' => $data['phone'],
                'locale' => app()->getLocale(), 'governorate_id' => $governorate?->id,
                'governorate_name' => $governorate === null ? null : (app()->getLocale() === 'ar' ? $governorate->name_ar : $governorate->name_en),
                'address' => $data['address'], 'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_method'] === 'cod' ? 'unpaid' : 'awaiting_review',
                'payment_proof_path' => $data['payment_proof_path'], 'payment_destination' => $destination,
                'subtotal' => $cart['subtotal'], 'discount' => $cart['discount'], 'shipping' => $shipping, 'total' => $total,
                'idempotency_key' => $data['idempotency_key'], 'placed_at' => now(),
            ]);

            foreach ($cart['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id, 'product_name_ar' => $item['product']->name_ar,
                    'product_name_en' => $item['product']->name_en, 'color_name' => $item['color']->name_ar,
                    'color_hex' => $item['color']->hex, 'size' => $item['size']->label,
                    'unit_price' => $item['unit_price'], 'quantity' => $item['quantity'], 'line_total' => $item['line_total'],
                ]);
            }
            OrderStatusLog::query()->create(['order_id' => $order->id, 'to_status' => 'pending', 'changed_by' => $user?->id, 'note' => 'Order placed']);

            return $order;
        }, 3);
    }
}
