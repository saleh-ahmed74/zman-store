<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Shop extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sort = 'new';

    #[Url]
    public string $category = '';

    #[Url(as: 'min')]
    public string $minPrice = '';

    #[Url(as: 'max')]
    public string $maxPrice = '';

    public function mount(?string $slug = null): void
    {
        if ($slug !== null) {
            $this->category = Category::query()->visible()->where('slug', $slug)->firstOrFail()->slug;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['sort', 'category', 'minPrice', 'maxPrice'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $locale = app()->getLocale();
        $products = Product::query()->visible()
            ->with(['category:id,slug,name_ar,name_en', 'images:id,product_id,path,sort_order'])
            ->when($this->category !== '', fn (Builder $query) => $query->whereHas('category', fn (Builder $categories) => $categories->where('slug', $this->category)))
            ->when(trim($this->search) !== '', fn (Builder $query) => $query->where(fn (Builder $names) => $names
                ->where('name_ar', 'like', '%'.trim($this->search).'%')
                ->orWhere('name_en', 'like', '%'.trim($this->search).'%')))
            ->when($this->minPrice !== '' && ctype_digit($this->minPrice), fn (Builder $query) => $query->where('base_price', '>=', (int) $this->minPrice))
            ->when($this->maxPrice !== '' && ctype_digit($this->maxPrice), fn (Builder $query) => $query->where('base_price', '<=', (int) $this->maxPrice));

        match ($this->sort) {
            'best' => $products->orderByDesc('is_best_seller')->orderByDesc('created_at'),
            'price_asc' => $products->orderBy('base_price'),
            'price_desc' => $products->orderByDesc('base_price'),
            default => $products->orderByDesc('created_at'),
        };

        return view('livewire.storefront.shop', [
            'products' => $products->cursorPaginate(16),
            'categories' => Category::query()->visible()->get(['slug', 'name_ar', 'name_en']),
            'heading' => $this->category !== ''
                ? (Category::query()->visible()->where('slug', $this->category)->value($locale === 'ar' ? 'name_ar' : 'name_en') ?? __('store.shop'))
                : __('store.shop'),
        ])->layout('layouts.store');
    }
}
