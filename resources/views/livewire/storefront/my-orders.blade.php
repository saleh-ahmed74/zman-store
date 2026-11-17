<section class="orders-page"><p class="eyebrow">{{ __('store.my_orders') }}</p><h1>{{ __('store.my_orders') }}</h1>
    @forelse ($orders as $order)
        <article class="order-card"><header><div><strong>{{ $order->number }}</strong><small>{{ $order->placed_at->format('Y-m-d H:i') }}</small></div><span class="order-status">{{ __('store.status_'.$order->status) }}</span></header>
            <div class="order-items">@foreach ($order->items as $item)<div><span>{{ app()->getLocale() === 'ar' ? $item->product_name_ar : $item->product_name_en }} · {{ $item->color_name }} · {{ $item->size }} × {{ $item->quantity }}</span><b>{{ number_format((float) $item->line_total, 0) }} {{ __('store.currency') }}</b></div>@endforeach</div>
            <footer><span>{{ __('store.payment_'.$order->payment_method) }} · {{ __('store.payment_status_'.$order->payment_status) }}</span><strong>{{ number_format((float) $order->total, 0) }} {{ __('store.currency') }}</strong></footer>
            @if ($order->status === 'pending')<button class="text-action danger-action" type="button" wire:click="cancel({{ $order->id }})" wire:confirm="{{ __('store.confirm_cancel_order') }}">{{ __('store.cancel_order') }}</button>@endif
            @if ($order->status === 'delivered' && $order->delivered_at?->gte(now()->subDays(14)))
                @if ($returningOrderId === $order->id)
                    <form class="return-form" wire:submit="requestReturn"><textarea wire:model="returnReason" rows="3" maxlength="2000" placeholder="{{ __('store.return_reason') }}"></textarea>@error('returnReason')<small class="field-error">{{ $message }}</small>@enderror<button type="submit" class="button button-dark">{{ __('store.submit_return') }}</button><button type="button" class="text-action" wire:click="$set('returningOrderId', null)">{{ __('store.cancel') }}</button></form>
                @else<button class="text-action" type="button" wire:click="$set('returningOrderId', {{ $order->id }})">{{ __('store.request_return') }}</button>@endif
            @elseif ($order->status === 'return_requested')<small>{{ __('store.return_requested') }}</small>@endif
        </article>
    @empty<div class="empty-state">{{ __('store.no_orders') }} <a href="{{ route('shop') }}">{{ __('store.shop_now') }}</a></div>@endforelse
    {{ $orders->links() }}
</section>
