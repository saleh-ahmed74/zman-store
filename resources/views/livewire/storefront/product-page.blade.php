<article class="product-page">
    <nav class="breadcrumbs"><a href="{{ route('home') }}">{{ __('store.home') }}</a><span>/</span><a href="{{ route('category', $product->category->slug) }}">{{ app()->getLocale() === 'ar' ? $product->category->name_ar : $product->category->name_en }}</a><span>/</span><span>{{ app()->getLocale() === 'ar' ? $product->name_ar : $product->name_en }}</span></nav>
    <div class="product-detail">
        <div class="product-gallery">
            @forelse ($product->images as $image)
                <img src="{{ asset('uploads/'.$image->path) }}" alt="{{ app()->getLocale() === 'ar' ? $product->name_ar : $product->name_en }}" loading="lazy">
            @empty
                <div class="product-art product-art-large" aria-hidden="true"><span class="garment-mark">{{ $product->category->slug === 'hoodies' ? 'H' : 'P' }}</span></div>
            @endforelse
        </div>
        <div class="product-info">
            <p class="eyebrow">{{ app()->getLocale() === 'ar' ? $product->category->name_ar : $product->category->name_en }}</p>
            <h1>{{ app()->getLocale() === 'ar' ? $product->name_ar : $product->name_en }}</h1>
            <p class="product-price product-detail-price">{{ number_format((float) $product->base_price, 0) }} <small>{{ __('store.currency') }}</small></p>
            <p class="product-description">{{ app()->getLocale() === 'ar' ? $product->description_ar : $product->description_en }}</p>
            <h2>{{ __('store.choose_offer') }}</h2>
            <div class="offer-options">
                @foreach ([2, 3, 4] as $pieceCount)
                    @php($offer = $offers->get($pieceCount))
                    <label class="offer-option {{ $pieces === $pieceCount ? 'selected' : '' }}">
                        <input type="radio" wire:model.live="pieces" value="{{ $pieceCount }}">
                        <span><strong>{{ $pieceCount }} {{ __('store.pieces') }}</strong><small>{{ $offer && $offer->discount_percent > 0 ? $offer->discount_percent.'% '.__('store.discount') : __('store.base_offer') }}</small></span>
                        <b>{{ number_format((float) $product->base_price * $pieceCount * (1 - (float) ($offer?->discount_percent ?? 0) / 100), 0) }} {{ __('store.currency') }}</b>
                    </label>
                @endforeach
            </div>
            <p class="selection-note">{{ __('store.choose_each_piece') }}</p>
            @foreach (range(0, $pieces - 1) as $index)
                <fieldset class="piece-selector"><legend>{{ __('store.piece') }} {{ $index + 1 }}</legend>
                    <label>{{ __('store.color') }}<select wire:model="colors.{{ $index }}">@foreach ($product->colors as $color)<option value="{{ $color->id }}">{{ app()->getLocale() === 'ar' ? $color->name_ar : $color->name_en }}</option>@endforeach</select></label>
                    <label>{{ __('store.size') }}<select wire:model="sizes.{{ $index }}">@foreach ($product->sizes as $size)<option value="{{ $size->id }}">{{ $size->label }}</option>@endforeach</select></label>
                </fieldset>
            @endforeach
            @error('selections')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            <div class="product-actions"><button class="button button-outline" type="button" wire:click="addToCart">{{ __('store.add_to_cart') }}</button><button class="button button-dark" type="button" wire:click="addToCart(true)">{{ __('store.buy_offer') }}</button></div>
        </div>
    </div>
</article>
