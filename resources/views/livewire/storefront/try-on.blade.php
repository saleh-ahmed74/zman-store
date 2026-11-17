<section class="tryon-page">
    <p class="eyebrow">{{ __('store.discover') }}</p><h1>{{ __('store.try_on') }}</h1><p class="tryon-intro">{{ __('store.tryon_intro') }}</p>
    <div class="tryon-layout">
        <div class="tryon-mannequin" id="tryon-mannequin" aria-label="{{ __('store.tryon_preview') }}">
            <svg class="mannequin-body" viewBox="0 0 300 520" role="img" aria-label="{{ __('store.tryon_preview') }}">
                <circle cx="150" cy="54" r="34" fill="#caa88d"/><path d="M120 92 Q150 78 180 92 L195 280 L175 300 L176 490 L132 490 L130 302 L105 280 Z" fill="#caa88d"/><path d="M105 115 Q65 128 52 255 L79 263 L119 175 M195 115 Q235 128 248 255 L221 263 L181 175" fill="#caa88d"/>
            </svg>
            <div class="tryon-garment tryon-hoodie" style="--garment-color:{{ $hoodie?->colors->firstWhere('id', $hoodieColorId)?->hex ?? '#777777' }}">
                @if ($hoodie?->colors->firstWhere('id', $hoodieColorId)?->tryon_image_path)
                    <img src="{{ asset('uploads/'.$hoodie->colors->firstWhere('id', $hoodieColorId)->tryon_image_path) }}" alt="">
                @else
                    <svg viewBox="0 0 300 250" aria-hidden="true"><path d="M83 52 119 25h62l36 27 53 37-24 48-31-17v105H85V120l-31 17-24-48z" fill="var(--garment-color)"/><path d="M119 25q31 54 62 0M132 37q18 27 36 0" fill="none" stroke="#ffffff55" stroke-width="8"/></svg>
                @endif
            </div>
            <div class="tryon-garment tryon-pants" style="--garment-color:{{ $pantsProduct?->colors->firstWhere('id', $pantsColorId)?->hex ?? '#555555' }}">
                @if ($pantsProduct?->colors->firstWhere('id', $pantsColorId)?->tryon_image_path)
                    <img src="{{ asset('uploads/'.$pantsProduct->colors->firstWhere('id', $pantsColorId)->tryon_image_path) }}" alt="">
                @else
                    <svg viewBox="0 0 300 300" aria-hidden="true"><path d="M87 10h126l-7 112-34 165h-52l-20-130-19 130H29L70 119z" transform="translate(30 0)" fill="var(--garment-color)"/><path d="M117 20h66" stroke="#ffffff55" stroke-width="8"/></svg>
                @endif
            </div>
        </div>

        <div class="tryon-options">
            <section class="tryon-group"><h2>{{ __('store.hoodie') }}</h2><div class="tryon-products">@forelse ($hoodies as $item)<button type="button" class="tryon-product {{ $hoodieId === $item->id ? 'selected' : '' }}" title="{{ app()->getLocale() === 'ar' ? $item->name_ar : $item->name_en }}" aria-label="{{ app()->getLocale() === 'ar' ? $item->name_ar : $item->name_en }}" wire:click="chooseHoodie({{ $item->id }})">@if ($item->images->first())<img src="{{ asset('uploads/'.$item->images->first()->path) }}" alt="" loading="lazy">@else<span class="garment-mark">H</span>@endif</button>@empty<p class="empty-state">{{ __('store.no_tryon_products') }}</p>@endforelse</div>
                @if ($hoodie)<p class="selected-model">{{ app()->getLocale() === 'ar' ? $hoodie->name_ar : $hoodie->name_en }}</p>@endif
                <div class="swatch-row">@foreach ($hoodie?->colors ?? [] as $color)<button class="color-swatch {{ $hoodieColorId === $color->id ? 'selected' : '' }}" style="--swatch:{{ $color->hex }}" title="{{ app()->getLocale() === 'ar' ? $color->name_ar : $color->name_en }}" aria-label="{{ app()->getLocale() === 'ar' ? $color->name_ar : $color->name_en }}" wire:click="$set('hoodieColorId', {{ $color->id }})"></button>@endforeach</div>
                @if ($hoodie)<label class="tryon-size-label">{{ __('store.size') }}<select wire:model.live="hoodieSize">@foreach ($hoodie->sizes as $size)<option value="{{ $size->label }}">{{ $size->label }}</option>@endforeach</select></label>@endif
            </section>

            <section class="tryon-group"><h2>{{ __('store.pants') }}</h2><div class="tryon-products">@forelse ($pants as $item)<button type="button" class="tryon-product {{ $pantsId === $item->id ? 'selected' : '' }}" title="{{ app()->getLocale() === 'ar' ? $item->name_ar : $item->name_en }}" aria-label="{{ app()->getLocale() === 'ar' ? $item->name_ar : $item->name_en }}" wire:click="choosePants({{ $item->id }})">@if ($item->images->first())<img src="{{ asset('uploads/'.$item->images->first()->path) }}" alt="" loading="lazy">@else<span class="garment-mark">P</span>@endif</button>@empty<p class="empty-state">{{ __('store.no_tryon_products') }}</p>@endforelse</div>
                @if ($pantsProduct)<p class="selected-model">{{ app()->getLocale() === 'ar' ? $pantsProduct->name_ar : $pantsProduct->name_en }}</p>@endif
                <div class="swatch-row">@foreach ($pantsProduct?->colors ?? [] as $color)<button class="color-swatch {{ $pantsColorId === $color->id ? 'selected' : '' }}" style="--swatch:{{ $color->hex }}" title="{{ app()->getLocale() === 'ar' ? $color->name_ar : $color->name_en }}" aria-label="{{ app()->getLocale() === 'ar' ? $color->name_ar : $color->name_en }}" wire:click="$set('pantsColorId', {{ $color->id }})"></button>@endforeach</div>
                @if ($pantsProduct)<label class="tryon-size-label">{{ __('store.size') }}<select wire:model.live="pantsSize">@foreach ($pantsProduct->sizes as $size)<option value="{{ $size->label }}">{{ $size->label }}</option>@endforeach</select></label>@endif
            </section>

            @if ($hoodie && $pantsProduct)<div class="tryon-total"><span>{{ __('store.total') }}</span><strong>{{ number_format((float) $hoodie->base_price + (float) $pantsProduct->base_price, 0) }} {{ __('store.currency') }}</strong></div>@endif
            <button class="button button-dark tryon-add" type="button" wire:click="addSet" wire:loading.attr="disabled">{{ __('store.add_set_to_cart') }}</button>
        </div>
    </div>
</section>
