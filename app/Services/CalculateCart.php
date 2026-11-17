<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;

final class CalculateCart
{
    /**
     * @param  array<int, array{product_id: int, color_id: int, size_id: int, quantity?: int}>  $storedItems
     * @return array{items: array<int, array{index: int, product: Product, color: ProductColor, size: ProductSize, quantity: int, unit_price: float, line_total: float}>, subtotal: float, discount: float, free_shipping: bool, count: int}
     */
    public function calculate(array $storedItems): array
    {
        $items = [];
        $products = Product::query()->visible()->with(['category', 'colors', 'sizes', 'images'])
            ->whereIn('id', collect($storedItems)->pluck('product_id')->unique())
            ->get()->keyBy('id');
        $counts = collect($storedItems)->groupBy('product_id')->map(fn ($rows): int => $rows->sum(fn (array $row): int => max(1, min(20, (int) ($row['quantity'] ?? 1)))));

        foreach ($storedItems as $index => $stored) {
            $product = $products->get((int) $stored['product_id']);
            if ($product === null) {
                continue;
            }

            $color = $product->colors->firstWhere('id', (int) $stored['color_id']);
            $size = $product->sizes->firstWhere('id', (int) $stored['size_id']);
            if ($color === null || $size === null) {
                continue;
            }

            $quantity = max(1, min(20, (int) ($stored['quantity'] ?? 1)));
            $items[] = [
                'index' => $index,
                'product' => $product,
                'color' => $color,
                'size' => $size,
                'quantity' => $quantity,
                'unit_price' => (float) $product->base_price,
                'line_total' => round((float) $product->base_price * $quantity, 2),
            ];
        }

        $subtotal = round(array_sum(array_column($items, 'line_total')), 2);
        $discount = 0.0;
        foreach ($counts as $productId => $count) {
            $pieces = min(4, $count);
            if ($pieces < 2) {
                continue;
            }
            $offer = Offer::query()->where('is_active', true)->where('pieces', $pieces)
                ->where(fn ($query) => $query->whereNull('product_id')->orWhere('product_id', $productId))
                ->orderByRaw('product_id IS NULL')->first();
            $rate = ($offer === null ? 0.0 : (float) $offer->discount_percent) / 100;
            if ($rate > 0) {
                $productTotal = array_sum(array_map(fn (array $line): float => $line['product']->id === (int) $productId ? $line['line_total'] : 0, $items));
                $discount += $productTotal * $rate;
            }
        }

        $freeShipping = count($items) >= (int) app(StoreSettings::class)->get('store.shipping.free_shipping_from', 2)
            || collect($items)->contains(fn (array $item): bool => Offer::query()->where('is_active', true)->where('product_id', $item['product']->id)->where('free_shipping', true)->exists());

        return ['items' => $items, 'subtotal' => $subtotal, 'discount' => round($discount, 2), 'free_shipping' => $freeShipping, 'count' => array_sum(array_column($items, 'quantity'))];
    }
}
