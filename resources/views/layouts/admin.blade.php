<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ?? __('store.admin') }} · Hima</title>
    @vite(['resources/css/app.css', 'resources/js/app.js']) @livewireStyles
    <script>(()=>{if(localStorage.getItem('hima-theme')==='dark')document.documentElement.dataset.theme='dark'})()</script>
</head>
<body class="admin-body">
    <header class="site-header admin-header"><a class="brand" href="{{ route('admin') }}">Hima Admin</a><div class="header-actions"><a class="text-link" href="{{ route('home') }}">{{ __('store.view_store') }}</a><button class="icon-button theme-toggle" type="button" aria-label="{{ __('store.toggle_theme') }}" onclick="toggleAdminTheme()"><span class="sun">☀</span><span class="moon">☾</span></button></div></header>
    <div class="admin-shell">
        <aside class="admin-sidebar" aria-label="{{ __('store.admin') }}">
            <a href="{{ route('admin') }}">{{ __('store.dashboard') }}</a>
            <a href="{{ route('admin.categories') }}">{{ __('store.manage_categories') }}</a>
            <a href="{{ route('admin.products') }}">{{ __('store.manage_products') }}</a>
            <a href="{{ route('home') }}">{{ __('store.view_store') }}</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">{{ __('store.logout') }}</button></form>
        </aside>
        <main class="admin-content">{{ $slot }}</main>
    </div>
    <script>function toggleAdminTheme(){const r=document.documentElement;r.dataset.theme=r.dataset.theme==='dark'?'light':'dark';localStorage.setItem('hima-theme',r.dataset.theme)}</script>
    @livewireScripts
</body>
</html>
