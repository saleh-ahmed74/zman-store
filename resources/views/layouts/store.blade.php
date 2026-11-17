<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Hima') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script>
        (() => {
            const theme = localStorage.getItem('hima-theme');
            if (theme === 'dark') document.documentElement.dataset.theme = 'dark';
        })();
    </script>
</head>
<body>
    <header class="site-header">
        <div class="header-start">
            <button class="icon-button" type="button" aria-label="{{ __('store.menu') }}" onclick="document.getElementById('site-drawer').classList.toggle('is-open')">☰</button>
            <button class="icon-button theme-toggle" type="button" aria-label="{{ __('store.toggle_theme') }}" onclick="toggleTheme()"><span class="sun">☀</span><span class="moon">☾</span></button>
        </div>
        <a class="brand" href="{{ route('home') }}">{{ app(\App\Services\StoreSettings::class)->get('store.general.brand', 'Hima') }}</a>
        <div class="header-actions">
            @foreach (app(\App\Services\StoreSettings::class)->get('store.social.links', []) as $link)
                <a class="social-link" href="{{ $link['url'] }}" aria-label="{{ $link['label'] }}" target="_blank" rel="noopener">{{ strtoupper(substr($link['icon'] ?? $link['label'], 0, 2)) }}</a>
            @endforeach
            <a class="social-link whatsapp" href="https://wa.me/{{ app(\App\Services\StoreSettings::class)->get('store.general.whatsapp', '') }}" aria-label="WhatsApp" target="_blank" rel="noopener">WA</a>
            <a class="icon-button cart-link" href="{{ route('cart') }}" aria-label="{{ __('store.cart') }}">🛒</a>
        </div>
    </header>
    <div id="site-drawer" class="drawer" onclick="this.classList.remove('is-open')">
        <nav class="drawer-panel" onclick="event.stopPropagation()" aria-label="{{ __('store.menu') }}">
            <button class="drawer-close" type="button" aria-label="{{ __('store.close') }}" onclick="document.getElementById('site-drawer').classList.remove('is-open')">×</button>
            <a href="{{ route('home') }}">{{ __('store.home') }}</a>
            <a href="{{ route('shop') }}">{{ __('store.shop') }}</a>
            @foreach (\App\Models\Category::query()->visible()->get(['slug', 'name_ar', 'name_en']) as $category)
                <a href="{{ route('category', $category->slug) }}">{{ app()->getLocale() === 'ar' ? $category->name_ar : $category->name_en }}</a>
            @endforeach
            <a href="{{ route('try-on') }}">{{ __('store.try_on') }}</a>
            <a href="{{ route('about') }}">{{ __('store.about') }}</a>
            @auth
                <a href="{{ route('account.orders') }}">{{ __('store.my_orders') }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="drawer-action" type="submit">{{ __('store.logout') }}</button></form>
            @else
                <a href="{{ route('login') }}">{{ __('store.login') }}</a>
                <a href="{{ route('register') }}">{{ __('store.register') }}</a>
            @endauth
            <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}">{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a>
        </nav>
    </div>
    @if (session()->has('toast'))
        <div class="toast" role="status">{{ session('toast') }}</div>
    @endif
    <main class="page-content">{{ $slot }}</main>
    <footer class="site-footer">
        <a href="{{ route('about') }}">{{ __('store.about') }}</a>
        <span>© {{ now()->year }} {{ app(\App\Services\StoreSettings::class)->get('store.general.brand', 'Hima') }}</span>
    </footer>
    <script>
        function toggleTheme() {
            const root = document.documentElement;
            root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('hima-theme', root.dataset.theme);
        }
    </script>
    @livewireScripts
</body>
</html>
