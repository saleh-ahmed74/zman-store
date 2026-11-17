<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Home extends Component
{
    public function render(): View
    {
        return view('livewire.storefront.home', [
            'categories' => Category::query()->visible()->get(['id', 'slug', 'name_ar', 'name_en', 'image_path']),
            'bestSellers' => Product::query()->visible()->where('is_best_seller', true)
                ->with(['category:id,slug,name_ar,name_en', 'images:id,product_id,path,sort_order'])
                ->orderBy('sort_order')->limit(8)
                ->get(['id', 'category_id', 'slug', 'name_ar', 'name_en', 'base_price', 'is_best_seller']),
        ])->layout('layouts.store');
    }
}
