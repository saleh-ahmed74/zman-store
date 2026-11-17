<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">{{ __('store.brand_line') }}</p>
        <h1>{{ app()->getLocale() === 'ar' ? app(\App\Services\StoreSettings::class)->get('store.hero.title_ar') : app(\App\Services\StoreSettings::class)->get('store.hero.title_en') }}</h1>
        <p class="hero-subtitle">{{ app()->getLocale() === 'ar' ? app(\App\Services\StoreSettings::class)->get('store.hero.subtitle_ar') : app(\App\Services\StoreSettings::class)->get('store.hero.subtitle_en') }}</p>
        <a class="button button-dark" href="{{ route('shop') }}">{{ __('store.shop_now') }} <span aria-hidden="true">↗</span></a>
    </div>
    <div class="hero-visual" aria-hidden="true"><span class="hero-orbit orbit-one"></span><span class="hero-orbit orbit-two"></span><span class="hero-word">HIMA</span></div>
</section>

<section class="section-block">
    <div class="section-heading"><div><p class="eyebrow">{{ __('store.discover') }}</p><h2>{{ __('store.categories') }}</h2></div><a class="text-link" href="{{ route('shop') }}">{{ __('store.shop') }} <span aria-hidden="true">↗</span></a></div>
    <div class="category-grid">
        @foreach ($categories as $category)
            <a class="category-card category-{{ $loop->iteration % 2 ? 'warm' : 'cool' }}" href="{{ route('category', $category->slug) }}">
                @if ($category->image_path)
                    <img src="{{ asset('uploads/'.$category->image_path) }}" alt="" loading="lazy">
                @else
                    <span class="category-art" aria-hidden="true"><span></span></span>
                @endif
                <span class="category-name">{{ app()->getLocale() === 'ar' ? $category->name_ar : $category->name_en }} <span aria-hidden="true">↗</span></span>
            </a>
        @endforeach
    </div>
</section>

<section class="section-block best-sellers">
    <div class="section-heading"><div><p class="eyebrow">{{ __('store.customer_favorites') }}</p><h2>{{ __('store.best_sellers') }}</h2></div><a class="text-link" href="{{ route('shop', ['sort' => 'best']) }}">{{ __('store.view_all') }} <span aria-hidden="true">↗</span></a></div>
    @if ($bestSellers->isEmpty())
        <p class="empty-state">{{ __('store.coming_soon') }}</p>
    @else
        <div class="product-grid">
            @foreach ($bestSellers as $product)
                <a class="product-card" href="{{ route('product', $product->slug) }}">
                    <span class="product-art" aria-hidden="true">
                        @if ($product->images->first())
                            <img src="{{ asset('uploads/'.$product->images->first()->path) }}" alt="" loading="lazy">
                        @else
                            <span class="garment-mark">{{ $product->category->slug === 'hoodies' ? 'H' : 'P' }}</span>
                        @endif
                    </span>
                    <span class="product-name">{{ app()->getLocale() === 'ar' ? $product->name_ar : $product->name_en }}</span>
                    <span class="product-price">{{ number_format((float) $product->base_price, 0) }} <small>{{ __('store.currency') }}</small></span>
                </a>
            @endforeach
        </div>
    @endif
</section>
