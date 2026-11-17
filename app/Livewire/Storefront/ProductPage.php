<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ProductPage extends Component
{
    public string $slug;

    public int $pieces = 2;

    /** @var array<int, int|null> */
    public array $colors = [];

    /** @var array<int, int|null> */
    public array $sizes = [];

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $product = $this->product();
        foreach (range(0, 3) as $index) {
            $this->colors[$index] = $product->colors->first()?->id;
            $this->sizes[$index] = $product->sizes->first()?->id;
        }
    }

    public function updatedPieces(): void
    {
        if (! in_array($this->pieces, [2, 3, 4], true)) {
            $this->pieces = 2;
        }
    }

    public function addToCart(bool $checkout = false): RedirectResponse
    {
        $product = $this->product();
        $validColors = $product->colors->modelKeys();
        $validSizes = $product->sizes->modelKeys();

        foreach (range(0, $this->pieces - 1) as $index) {
            if (! in_array((int) ($this->colors[$index] ?? 0), $validColors, true)
                || ! in_array((int) ($this->sizes[$index] ?? 0), $validSizes, true)) {
                throw ValidationException::withMessages(['selections' => __('store.choose_each_piece')]);
            }
        }

        $cart = session('cart.items', []);
        foreach (range(0, $this->pieces - 1) as $index) {
            $cart[] = ['product_id' => $product->id, 'color_id' => (int) $this->colors[$index], 'size_id' => (int) $this->sizes[$index], 'quantity' => 1];
        }
        session(['cart.items' => $cart]);
        session()->flash('toast', __('store.added_to_cart'));

        return $checkout ? redirect()->route('checkout') : redirect()->route('cart');
    }

    public function render(): View
    {
        $product = $this->product();
        $offers = Offer::query()->where('is_active', true)->whereIn('pieces', [2, 3, 4])
            ->where(fn ($query) => $query->whereNull('product_id')->orWhere('product_id', $product->id))
            ->orderBy('pieces')->get()->keyBy('pieces');

        return view('livewire.storefront.product-page', compact('product', 'offers'))->layout('layouts.store');
    }

    private function product(): Product
    {
        return Product::query()->visible()->with(['category', 'colors', 'sizes', 'images'])
            ->where('slug', $this->slug)->firstOrFail();
    }
}
