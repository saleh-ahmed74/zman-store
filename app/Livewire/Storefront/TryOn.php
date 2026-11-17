<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class TryOn extends Component
{
    public ?int $hoodieId = null;

    public ?int $pantsId = null;

    public ?int $hoodieColorId = null;

    public ?int $pantsColorId = null;

    public string $hoodieSize = '';

    public string $pantsSize = '';

    public function mount(): void
    {
        $hoodie = $this->products('hoodies')->first();
        $pants = $this->products('pants')->first();
        $this->chooseHoodie($hoodie?->id);
        $this->choosePants($pants?->id);
    }

    public function chooseHoodie(?int $productId): void
    {
        $product = $productId === null ? null : $this->products('hoodies')->firstWhere('id', $productId);
        abort_if($productId !== null && $product === null, 404);
        $this->hoodieId = $product?->id;
        $this->hoodieColorId = $product?->colors->first()?->id;
        $this->hoodieSize = $product === null ? '' : $product->sizes->firstOrFail()->label;
    }

    public function choosePants(?int $productId): void
    {
        $product = $productId === null ? null : $this->products('pants')->firstWhere('id', $productId);
        abort_if($productId !== null && $product === null, 404);
        $this->pantsId = $product?->id;
        $this->pantsColorId = $product?->colors->first()?->id;
        $this->pantsSize = $product === null ? '' : $product->sizes->firstOrFail()->label;
    }

    public function addSet(): void
    {
        $hoodie = $this->selectedProduct('hoodies', $this->hoodieId);
        $pants = $this->selectedProduct('pants', $this->pantsId);
        abort_if($hoodie === null || $pants === null, 422, __('store.tryon_choose_products'));

        $hoodieColor = $hoodie->colors->firstWhere('id', $this->hoodieColorId);
        $pantsColor = $pants->colors->firstWhere('id', $this->pantsColorId);
        abort_if($hoodieColor === null || $pantsColor === null, 422, __('store.tryon_choose_colors'));
        abort_unless($hoodie->sizes->contains('label', $this->hoodieSize) && $pants->sizes->contains('label', $this->pantsSize), 422, __('store.tryon_choose_sizes'));

        $cart = session('cart.items', []);
        $cart[] = ['product_id' => $hoodie->id, 'color_id' => $hoodieColor->id, 'size_id' => $hoodie->sizes->firstWhere('label', $this->hoodieSize)->id, 'quantity' => 1];
        $cart[] = ['product_id' => $pants->id, 'color_id' => $pantsColor->id, 'size_id' => $pants->sizes->firstWhere('label', $this->pantsSize)->id, 'quantity' => 1];
        session(['cart.items' => $cart]);
        session()->flash('toast', __('store.added_to_cart'));
        $this->dispatch('cart-added', count: count($cart));
        $this->dispatch('toast', type: 'success', message: __('store.added_to_cart'));
    }

    public function render(): View
    {
        $hoodies = $this->products('hoodies');
        $pants = $this->products('pants');

        return view('livewire.storefront.try-on', [
            'hoodies' => $hoodies,
            'pants' => $pants,
            'hoodie' => $hoodies->firstWhere('id', $this->hoodieId),
            'pantsProduct' => $pants->firstWhere('id', $this->pantsId),
        ])->layout('layouts.store', ['title' => __('store.try_on')]);
    }

    /** @return Collection<int, Product> */
    private function products(string $categorySlug): Collection
    {
        return Product::query()->visible()->with(['category', 'colors', 'sizes', 'images'])
            ->whereHas('category', fn ($query) => $query->where('slug', $categorySlug))
            ->orderBy('sort_order')->orderBy('id')->get();
    }

    private function selectedProduct(string $categorySlug, ?int $productId): ?Product
    {
        if ($productId === null) {
            return null;
        }

        return $this->products($categorySlug)->firstWhere('id', $productId);
    }
}
