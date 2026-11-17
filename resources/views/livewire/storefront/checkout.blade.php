<section class="checkout-page">
    <p class="eyebrow">{{ __('store.order_summary') }}</p><h1>{{ __('store.checkout') }}</h1>
    @if ($placedOrderNumber)
        <div class="empty-state"><h2>{{ __('store.order_placed') }}</h2><p>{{ __('store.order_number') }}: <strong>{{ $placedOrderNumber }}</strong></p><a class="button button-dark" href="{{ route('home') }}">{{ __('store.home') }}</a></div>
    @elseif (count($cart['items']) === 0)
        <div class="empty-state"><p>{{ __('store.cart_empty') }}</p><a class="button button-dark" href="{{ route('shop') }}">{{ __('store.start_shopping') }}</a></div>
    @else
        <div class="checkout-layout">
            <form class="checkout-form" wire:submit="placeOrder">
                <section class="checkout-section"><h2>{{ __('store.delivery_details') }}</h2>
                    <label>{{ __('store.full_name') }}<input type="text" wire:model="name" autocomplete="name" required maxlength="120"></label>@error('name')<small class="field-error">{{ $message }}</small>@enderror
                    <label>{{ __('store.phone') }}<div class="phone-input"><select wire:model="phoneCountry" aria-label="{{ __('store.country_code') }}"><option value="EG">EG +20</option></select><input type="tel" wire:model="phone" autocomplete="tel-national" inputmode="tel" placeholder="01xxxxxxxxx" required></div></label>@error('phone')<small class="field-error">{{ $message }}</small>@enderror
                    @if (! $freeForAll)
                        <label>{{ __('store.governorate') }}<select wire:model.live="governorateId" required>@foreach ($governorates as $governorate)<option value="{{ $governorate->id }}">{{ app()->getLocale() === 'ar' ? $governorate->name_ar : $governorate->name_en }} — {{ number_format((float) $governorate->shipping_price, 0) }} {{ __('store.currency') }}</option>@endforeach</select></label>@error('governorateId')<small class="field-error">{{ $message }}</small>@enderror
                    @else <p class="free-shipping-note">{{ __('store.free_shipping_all') }}</p> @endif
                    <label>{{ __('store.address') }}<textarea wire:model="address" autocomplete="street-address" rows="3" maxlength="200" required></textarea></label>@error('address')<small class="field-error">{{ $message }}</small>@enderror
                </section>
                <section class="checkout-section"><h2>{{ __('store.payment_method') }}</h2>
                    <div class="payment-options">
                        @foreach (['cod' => '💵', 'instapay' => '↗', 'vodafone_cash' => '📱'] as $method => $icon)
                            @if ($payment['enabled'][$method] ?? false)<label class="payment-option {{ $paymentMethod === $method ? 'selected' : '' }}"><input type="radio" value="{{ $method }}" wire:model.live="paymentMethod"><span class="payment-icon" aria-hidden="true">{{ $icon }}</span><span>{{ __('store.payment_'.$method) }}</span></label>@endif
                        @endforeach
                    </div>@error('paymentMethod')<small class="field-error">{{ $message }}</small>@enderror
                    @if ($paymentMethod === 'instapay')<div class="payment-instructions"><p>{{ __('store.transfer_amount') }}: <strong>{{ number_format($cart['subtotal'] - $cart['discount'] + $shipping, 2) }} {{ __('store.currency') }}</strong></p><a href="{{ $payment['instapay_url'] ?? '#' }}" target="_blank" rel="noopener">{{ __('store.pay_instapay') }}</a></div>@endif
                    @if ($paymentMethod === 'vodafone_cash')<div class="payment-instructions"><p>{{ __('store.send_to_vodafone') }}: <strong dir="ltr">{{ $payment['vodafone_cash'] ?? '' }}</strong></p><p>{{ __('store.transfer_amount') }}: <strong>{{ number_format($cart['subtotal'] - $cart['discount'] + $shipping, 2) }} {{ __('store.currency') }}</strong></p></div>@endif
                    @if (in_array($paymentMethod, ['instapay', 'vodafone_cash'], true))
                        <label>{{ __('store.payment_proof') }}<input type="file" wire:model="paymentProof" accept="image/jpeg,image/png,image/webp" required></label>@error('paymentProof')<small class="field-error">{{ $message }}</small>@enderror
                        @if ($paymentProof)<img class="proof-preview" src="{{ $paymentProof->temporaryUrl() }}" alt="{{ __('store.payment_proof') }}">@endif
                    @endif
                </section>
                <label class="policy-consent"><input type="checkbox" wire:model="acceptedPolicies"><span>{{ __('store.accept_policies') }} @foreach ($policies as $policy)<a href="{{ route('page', $policy->slug) }}" target="_blank" rel="noopener">{{ app()->getLocale() === 'ar' ? $policy->title_ar : $policy->title_en }}</a>@endforeach</span></label>@error('acceptedPolicies')<small class="field-error">{{ __('store.accept_policies_required') }}</small>@enderror
                <button class="button button-dark" type="submit" wire:loading.attr="disabled">{{ __('store.confirm_order') }}</button>
            </form>
            <aside class="cart-summary checkout-summary"><h2>{{ __('store.order_summary') }}</h2>
                @foreach ($cart['items'] as $item)<div class="checkout-item"><span>{{ app()->getLocale() === 'ar' ? $item['product']->name_ar : $item['product']->name_en }} × {{ $item['quantity'] }}</span><b>{{ number_format($item['line_total'], 0) }}</b></div>@endforeach
                <div><span>{{ __('store.subtotal') }}</span><b>{{ number_format($cart['subtotal'], 0) }} {{ __('store.currency') }}</b></div><div><span>{{ __('store.discount') }}</span><b>−{{ number_format($cart['discount'], 0) }} {{ __('store.currency') }}</b></div><div><span>{{ __('store.shipping') }}</span><b>{{ $cart['free_shipping'] || $freeForAll ? __('store.free') : number_format($shipping, 0).' '.__('store.currency') }}</b></div><div class="summary-total"><span>{{ __('store.total') }}</span><b>{{ number_format($cart['subtotal'] - $cart['discount'] + $shipping, 0) }} {{ __('store.currency') }}</b></div>
            </aside>
        </div>
    @endif
</section>
