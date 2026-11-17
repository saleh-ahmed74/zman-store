<?php

declare(strict_types=1);

use App\Livewire\Admin\Categories\Index as AdminCategories;
use App\Livewire\Admin\Products\Index as AdminProducts;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\ContentPage;
use App\Livewire\Storefront\Home;
use App\Livewire\Storefront\ProductPage;
use App\Livewire\Storefront\Shop;
use App\Services\StoreSettings;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

Route::get('/login', Login::class)->middleware('guest')->name('login');
Route::get('/register', Register::class)->middleware('guest')->name('register');
Route::get('/forgot-password', ForgotPassword::class)->middleware('guest')->name('password.request');
Route::get('/reset-password/{token}', ResetPassword::class)->middleware('guest')->name('password.reset');
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');
Route::get('/email/verify', fn () => view('storefront.verify-email'))->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->flash('toast', __('store.email_verified'));

    return redirect()->route('home');
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    if (! $request->user()->hasVerifiedEmail()) {
        $request->user()->sendEmailVerificationNotification();
    }

    return back()->with('status', __('store.verification_sent'));
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
Route::middleware('auth')->get('/account/orders', function (Request $request) {
    if (app(StoreSettings::class)->get('store.auth.require_email_verification', false)
        && ! $request->user()->hasVerifiedEmail()) {
        return redirect()->route('verification.notice');
    }

    return view('storefront.placeholder', ['heading' => __('store.my_orders')]);
})->name('account.orders');
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function (): void {
    Route::view('/', 'storefront.admin-placeholder')->name('admin');
    Route::get('/categories', AdminCategories::class)->name('admin.categories');
    Route::get('/products', AdminProducts::class)->name('admin.products');
});
