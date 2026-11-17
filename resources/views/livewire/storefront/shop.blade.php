<section class="shop-page">
    <div class="section-heading shop-heading"><div><p class="eyebrow">{{ __('store.discover') }}</p><h1>{{ $heading }}</h1></div></div>
    <div class="shop-controls">
        <label class="search-control"><span class="sr-only">{{ __('store.search') }}</span><input type="search" maxlength="60" wire:model.live.debounce.300ms="search" placeholder="{{ __('store.search') }}"></label>
        <select aria-label="{{ __('store.sort') }}" wire:model.live="sort">
            <option value="new">{{ __('store.sort_new') }}</option><option value="best">{{ __('store.sort_best') }}</option>
            <option value="price_asc">{{ __('store.sort_low') }}</option><option value="price_desc">{{ __('store.sort_high') }}</option>
        </select>
        <input type="number" min="0" inputmode="numeric" wire:model.live.debounce.300ms="minPrice" placeholder="{{ __('store.min_price') }}" aria-label="{{ __('store.min_price') }}">
        <input type="number" min="0" inputmode="numeric" wire:model.live.debounce.300ms="maxPrice" placeholder="{{ __('store.max_price') }}" aria-label="{{ __('store.max_price') }}">
    </div>
    <nav class="category-chips" aria-label="{{ __('store.categories') }}">
        <a class="category-chip {{ $category === '' ? 'active' : '' }}" href="{{ route('shop') }}">{{ __('store.all') }}</a>
        @foreach ($categories as $item)
            <a class="category-chip {{ $category === $item->slug ? 'active' : '' }}" href="{{ route('category', $item->slug) }}">{{ app()->getLocale() === 'ar' ? $item->name_ar : $item->name_en }}</a>
        @endforeach
    </nav>
    @if ($products->isEmpty())
        <div class="empty-state">{{ __('store.no_products') }}</div>
    @else
        <div class="product-grid">
            @foreach ($products as $product)
                <a class="product-card" href="{{ route('product', $product->slug) }}">
                    <span class="product-art" aria-hidden="true">
                        @if ($product->images->first()) <img src="{{ asset('uploads/'.$product->images->first()->path) }}" alt="" loading="lazy">
                        @else <span class="garment-mark">{{ $product->category->slug === 'hoodies' ? 'H' : 'P' }}</span> @endif
                    </span>
                    <span class="product-name">{{ app()->getLocale() === 'ar' ? $product->name_ar : $product->name_en }}</span>
                    <span class="product-price">{{ number_format((float) $product->base_price, 0) }} <small>{{ __('store.currency') }}</small></span>
                </a>
            @endforeach
        </div>
        <div class="pagination-links">{{ $products->links() }}</div>
    @endif
</section>
