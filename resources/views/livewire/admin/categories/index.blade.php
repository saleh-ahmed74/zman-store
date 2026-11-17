<section class="admin-page">
    <div class="admin-title-row"><div><p class="eyebrow">{{ __('store.admin') }}</p><h1>{{ __('store.manage_categories') }}</h1></div><button type="button" class="button button-outline" wire:click="showArchive">{{ $showArchived ? __('store.active_categories') : __('store.category_archive') }}</button></div>
    @if (! $showArchived)
        <form class="admin-form" wire:submit="save">
            <h2>{{ $editingId ? __('store.edit_category') : __('store.add_category') }}</h2>
            <div class="admin-form-grid">
                <label>{{ __('store.slug') }}<input type="text" wire:model.blur="slug" maxlength="120" required>@error('slug')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.name_ar') }}<input type="text" wire:model.blur="name_ar" maxlength="120" dir="rtl" required>@error('name_ar')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.name_en') }}<input type="text" wire:model.blur="name_en" maxlength="120" dir="ltr" required>@error('name_en')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label>{{ __('store.sort_order') }}<input type="number" min="0" wire:model.blur="sort_order" required>@error('sort_order')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="admin-upload">{{ __('store.category_image') }}<input type="file" wire:model="image" accept="image/jpeg,image/png,image/webp">@error('image')<small class="form-error">{{ $message }}</small>@enderror</label>
                <label class="admin-toggle"><input type="checkbox" wire:model="is_visible"> {{ __('store.visible') }}</label>
            </div>
            <div class="admin-actions"><button class="button button-dark" type="submit">{{ __('store.save') }}</button>@if ($editingId)<button class="button button-outline" type="button" wire:click="resetForm">{{ __('store.cancel') }}</button>@endif</div>
        </form>
        @error('archive')<p class="form-error" role="alert">{{ $message }}</p>@enderror
    @endif
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>{{ __('store.category') }}</th><th>{{ __('store.slug') }}</th><th>{{ __('store.product_count') }}</th><th>{{ __('store.visibility') }}</th><th>{{ __('store.actions') }}</th></tr></thead><tbody>
        @forelse ($categories as $category)
            <tr>
                <td><span class="admin-entity">@if ($category->image_path)<img src="{{ asset('uploads/'.$category->image_path) }}" alt="" loading="lazy">@endif<strong>{{ app()->getLocale() === 'ar' ? $category->name_ar : $category->name_en }}</strong></span></td>
                <td dir="ltr">{{ $category->slug }}</td><td>{{ $category->products_count }}</td>
                <td>{{ $category->is_visible ? __('store.visible') : __('store.hidden') }}</td>
                <td><div class="admin-row-actions">
                    @if ($showArchived)
                        <button type="button" wire:click="restore({{ $category->id }})">{{ __('store.restore') }}</button>
                        <button type="button" class="danger-action" wire:click="forceDelete({{ $category->id }})" wire:confirm="{{ __('store.confirm_delete_category') }}">{{ __('store.delete_forever') }}</button>
                    @else
                        <button type="button" wire:click="edit({{ $category->id }})">{{ __('store.edit') }}</button>
                        <button type="button" class="danger-action" wire:click="archive({{ $category->id }})" wire:confirm="{{ __('store.confirm_archive_category') }}">{{ __('store.archive') }}</button>
                        @if ($category->products_count > 0)<button type="button" class="danger-action" wire:click="archive({{ $category->id }}, true)" wire:confirm="{{ __('store.confirm_archive_products') }}">{{ __('store.archive_with_products') }}</button>@endif
                    @endif
                </div></td>
            </tr>
        @empty<tr><td colspan="5" class="table-empty">{{ __('store.no_categories') }}</td></tr>@endforelse
    </tbody></table></div>
    <div class="pagination-links">{{ $categories->links() }}</div>
</section>
