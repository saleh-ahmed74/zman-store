<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Services\CalculateCart;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CartPage extends Component
{
    /** @param array<int, array{quantity?: int}> $items */
    private function cartCount(array $items): int
    {
        return array_sum(array_map(static fn (array $item): int => max(1, (int) ($item['quantity'] ?? 1)), $items));
    }

    public function setQuantity(int $index, int $quantity): void
    {
        $items = session('cart.items', []);
        if (! isset($items[$index])) {
            return;
        }

        $items[$index]['quantity'] = max(1, min(20, $quantity));
        session(['cart.items' => $items]);
        $this->dispatch('cart-updated', count: $this->cartCount($items));
    }

    public function remove(int $index): void
    {
        $items = session('cart.items', []);
        unset($items[$index]);
        $items = array_values($items);
        session(['cart.items' => $items]);
        $this->dispatch('cart-updated', count: $this->cartCount($items));
    }

    public function render(): View
    {
        $cart = app(CalculateCart::class)->calculate(session('cart.items', []));

        return view('livewire.storefront.cart-page', compact('cart'))->layout('layouts.store');
    }
}
