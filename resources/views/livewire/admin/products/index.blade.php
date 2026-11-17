<section class="admin-page">
    <div class="admin-title-row"><div><p class="eyebrow">{{ __('store.admin') }}</p><h1>{{ __('store.manage_products') }}</h1></div><button type="button" class="button button-outline" wire:click="showArchive">{{ $showArchived ? __('store.active_products') : __('store.product_archive') }}</button></div>
    @if (! $showArchived)
        <form class="admin-form" wire:submit="save">
            <h2>{{ $editingId ? __('store.edit_product') : __('store.add_product') }}</h2>
            <div class="admin-form-grid">
                <label>{{ __('store.category') }}<select wire:model="category_id" required>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ app()->getLocale() === 'ar' ? $category->name_ar : $category->name_en }}</option>@endforeach</select>@error('category_id')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.slug') }}<input type="text" wire:model.blur="slug" maxlength="120" required>@error('slug')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.name_ar') }}<input type="text" wire:model.blur="name_ar" maxlength="160" dir="rtl" required>@error('name_ar')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.name_en') }}<input type="text" wire:model.blur="name_en" maxlength="160" dir="ltr" required>@error('name_en')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.price') }}<input type="number" wire:model.blur="base_price" min="0" step="0.01" required>@error('base_price')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.sort_order') }}<input type="number" wire:model.blur="sort_order" min="0" required>@error('sort_order')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.description_ar') }}<textarea wire:model.blur="description_ar" maxlength="5000" rows="3" dir="rtl"></textarea>@error('description_ar')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.description_en') }}<textarea wire:model.blur="description_en" maxlength="5000" rows="3" dir="ltr"></textarea>@error('description_en')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="admin-toggle"><input type="checkbox" wire:model="is_visible"> {{ __('store.visible') }}</label>
                <label class="admin-toggle"><input type="checkbox" wire:model="is_best_seller"> {{ __('store.best_seller_toggle') }}</label>
            </div>

            <div class="admin-repeaters">
                <div class="admin-repeater-heading"><h3>{{ __('store.colors') }}</h3><button type="button" class="button button-outline" wire:click="addColor">{{ __('store.add_color') }}</button></div>
                @foreach ($colorRows as $index => $color)
                    <div class="admin-color-row"><label>{{ __('store.name_ar') }}<input wire:model.blur="colorRows.{{ $index }}.name_ar" maxlength="80" required></label><label>{{ __('store.name_en') }}<input wire:model.blur="colorRows.{{ $index }}.name_en" maxlength="80" dir="ltr" required></label><label>{{ __('store.color_hex') }}<input wire:model.blur="colorRows.{{ $index }}.hex" pattern="^#[0-9a-fA-F]{6}$" maxlength="7" dir="ltr" required></label><button type="button" class="danger-action" aria-label="{{ __('store.remove') }}" wire:click="removeColor({{ $index }})">×</button></div>
                    @error("colorRows.$index.name_ar")<small class="form-error">{{ $message }}</small>@enderror @error("colorRows.$index.name_en")<small class="form-error">{{ $message }}</small>@enderror @error("colorRows.$index.hex")<small class="form-error">{{ $message }}</small>@enderror
                @endforeach
                <div class="admin-repeater-heading"><h3>{{ __('store.sizes') }}</h3><button type="button" class="button button-outline" wire:click="addSize">{{ __('store.add_size') }}</button></div>
                <div class="size-row-list">@foreach ($sizeRows as $index => $size)<label>{{ __('store.size') }} {{ $index + 1 }}<span><input wire:model.blur="sizeRows.{{ $index }}" maxlength="12" dir="ltr" required><button type="button" class="danger-action" wire:click="removeSize({{ $index }})" aria-label="{{ __('store.remove') }}">×</button></span></label>@endforeach</div>
                @error('sizeRows')<small class="form-error">{{ $message }}</small>@enderror @error('sizeRows.*')<small class="form-error">{{ $message }}</small>@enderror
            </div>

            <div class="admin-repeaters"><label class="admin-toggle"><input type="checkbox" wire:model.live="useCustomOffers"> {{ __('store.custom_offers') }}</label>
                @if ($useCustomOffers)
                    <div class="admin-form-grid">@foreach ([2, 3, 4] as $pieces)<label>{{ $pieces }} {{ __('store.pieces') }} · {{ __('store.discount_percent') }}<input type="number" wire:model.blur="offerDiscounts.{{ $pieces }}" min="0" max="90" step="0.01">@error("offerDiscounts.$pieces")<small class="form-error">{{ $message }}</small>@enderror</label>@endforeach</div>
                    <label class="admin-toggle"><input type="checkbox" wire:model="offerFreeShipping"> {{ __('store.offer_free_shipping') }}</label>
                @endif
            </div>

            <label class="admin-upload">{{ __('store.product_images') }}<input type="file" wire:model="imageFiles" accept="image/jpeg,image/png,image/webp" multiple>@error('imageFiles')<small class="form-error">{{ $message }}</small>@enderror</label>
            @foreach ($imageFiles as $file)<span class="upload-preview">{{ $file->getClientOriginalName() }}</span>@endforeach
            @error('imageFiles.*')<small class="form-error">{{ $message }}</small>@enderror
            @if ($editingId)<div class="existing-images"><h3>{{ __('store.current_images') }}</h3>@foreach ($products->firstWhere('id', $editingId)?->images ?? [] as $image)<figure><img src="{{ asset('uploads/'.$image->path) }}" alt=""><button type="button" wire:click="deleteImage({{ $image->id }})" wire:confirm="{{ __('store.confirm_remove_image') }}">{{ __('store.remove') }}</button></figure>@endforeach</div>@endif
            <div class="admin-actions"><button class="button button-dark" type="submit">{{ __('store.save') }}</button>@if ($editingId)<button class="button button-outline" type="button" wire:click="resetForm">{{ __('store.cancel') }}</button>@endif</div>
        </form>
    @endif
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>{{ __('store.product') }}</th><th>{{ __('store.category') }}</th><th>{{ __('store.price') }}</th><th>{{ __('store.visibility') }}</th><th>{{ __('store.actions') }}</th></tr></thead><tbody>
        @forelse ($products as $product)
            <tr><td><span class="admin-entity">@if ($product->images->first())<img src="{{ asset('uploads/'.$product->images->first()->path) }}" alt="" loading="lazy">@endif<strong>{{ app()->getLocale() === 'ar' ? $product->name_ar : $product->name_en }}</strong>@if ($product->is_best_seller)<small>{{ __('store.best_seller_toggle') }}</small>@endif</span></td>
                <td>{{ app()->getLocale() === 'ar' ? $product->category?->name_ar : $product->category?->name_en }}</td><td>{{ number_format((float) $product->base_price, 2) }} {{ __('store.currency') }}</td><td>{{ $product->is_visible ? __('store.visible') : __('store.hidden') }}</td>
                <td><div class="admin-row-actions">@if ($showArchived)<button type="button" wire:click="restore({{ $product->id }})">{{ __('store.restore') }}</button><button type="button" class="danger-action" wire:click="forceDelete({{ $product->id }})" wire:confirm="{{ __('store.confirm_delete_product') }}">{{ __('store.delete_forever') }}</button>@else<button type="button" wire:click="edit({{ $product->id }})">{{ __('store.edit') }}</button><button type="button" class="danger-action" wire:click="archive({{ $product->id }})" wire:confirm="{{ __('store.confirm_archive_product') }}">{{ __('store.archive') }}</button>@endif</div></td></tr>
        @empty<tr><td colspan="5" class="table-empty">{{ __('store.no_products') }}</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination-links">{{ $products->links() }}</div>
</section>
