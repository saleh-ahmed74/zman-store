<?php

declare(strict_types=1);

use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\ContentPage;
use App\Livewire\Storefront\Home;
use App\Livewire\Storefront\ProductPage;
use App\Livewire\Storefront\Shop;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/shop', Shop::class)->name('shop');
Route::get('/category/{slug}', Shop::class)->name('category');
Route::get('/products/{slug}', ProductPage::class)->name('product');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/about', ContentPage::class)->defaults('slug', 'about')->name('about');
Route::get('/page/{slug}', ContentPage::class)->name('page');
Route::get('/try-on', fn () => view('storefront.placeholder', ['heading' => __('store.try_on')]))->name('try-on');
Route::get('/checkout', fn () => view('storefront.placeholder', ['heading' => __('store.checkout')]))->name('checkout');
Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['ar', 'en'], true), 404);
    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale');
