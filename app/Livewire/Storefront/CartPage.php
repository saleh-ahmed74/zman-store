<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Services\CalculateCart;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CartPage extends Component
{
    public function remove(int $index): void
    {
        $items = session('cart.items', []);
        unset($items[$index]);
        session(['cart.items' => array_values($items)]);
    }

    public function render(): View
    {
        $cart = app(CalculateCart::class)->calculate(session('cart.items', []));

        return view('livewire.storefront.cart-page', compact('cart'))->layout('layouts.store');
    }
}
