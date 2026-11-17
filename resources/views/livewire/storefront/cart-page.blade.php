<section class="cart-page">
    <p class="eyebrow">{{ __('store.your_selection') }}</p><h1>{{ __('store.cart') }}</h1>
    @if (count($cart['items']) === 0)
        <div class="empty-state"><p>{{ __('store.cart_empty') }}</p><a class="button button-dark" href="{{ route('shop') }}">{{ __('store.start_shopping') }}</a></div>
    @else
        <div class="cart-layout"><div class="cart-lines">
            @foreach ($cart['items'] as $item)
                <article class="cart-line"><span class="cart-thumb" style="--swatch:{{ $item['color']->hex }}">{{ $item['product']->category->slug === 'hoodies' ? 'H' : 'P' }}</span>
                    <div class="cart-line-info"><a href="{{ route('product', $item['product']->slug) }}"><strong>{{ app()->getLocale() === 'ar' ? $item['product']->name_ar : $item['product']->name_en }}</strong></a><small>{{ app()->getLocale() === 'ar' ? $item['color']->name_ar : $item['color']->name_en }} · {{ $item['size']->label }}</small></div>
                    <div class="quantity-control"><button type="button" wire:click="setQuantity({{ $item['index'] }}, {{ $item['quantity'] - 1 }})" aria-label="−">−</button><span>{{ $item['quantity'] }}</span><button type="button" wire:click="setQuantity({{ $item['index'] }}, {{ $item['quantity'] + 1 }})" aria-label="+">+</button></div>
                    <b>{{ number_format($item['line_total'], 0) }} {{ __('store.currency') }}</b><button type="button" class="remove-item" wire:click="remove({{ $item['index'] }})" aria-label="{{ __('store.remove') }}">×</button>
                </article>
            @endforeach
        </div><aside class="cart-summary"><h2>{{ __('store.order_summary') }}</h2><div><span>{{ __('store.subtotal') }}</span><b>{{ number_format($cart['subtotal'], 0) }} {{ __('store.currency') }}</b></div><div><span>{{ __('store.discount') }}</span><b>−{{ number_format($cart['discount'], 0) }} {{ __('store.currency') }}</b></div><div class="summary-total"><span>{{ __('store.total') }}</span><b>{{ number_format($cart['subtotal'] - $cart['discount'], 0) }} {{ __('store.currency') }}</b></div><a class="button button-dark" href="{{ route('checkout') }}">{{ __('store.checkout') }}</a></aside></div>
    @endif
</section>
