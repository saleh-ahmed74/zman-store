<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use App\Models\Product;
use App\Services\StoreImage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public ?int $editingId = null;

    public string $slug = '';

    public string $name_ar = '';

    public string $name_en = '';

    public int $sort_order = 0;

    public bool $is_visible = true;

    public bool $showArchived = false;

    public ?TemporaryUploadedFile $image = null;

    public function edit(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);
        $this->editingId = $category->id;
        $this->slug = $category->slug;
        $this->name_ar = $category->name_ar;
        $this->name_en = $category->name_en;
        $this->sort_order = $category->sort_order;
        $this->is_visible = $category->is_visible;
        $this->image = null;
        $this->resetValidation();
    }

    public function save(StoreImage $images): void
    {
        $validated = $this->validate([
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('categories', 'slug')->ignore($this->editingId)],
            'name_ar' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_visible' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $oldImage = null;
        $category = DB::transaction(function () use ($validated, $images, &$oldImage): Category {
            $category = $this->editingId === null ? new Category : Category::query()->findOrFail($this->editingId);
            $oldImage = $category->image_path;
            $newImage = $this->image ? $images->save($this->image, 'categories', 1400) : $oldImage;
            unset($validated['image']);
            $category->fill([...$validated, 'image_path' => $newImage]);
            $category->save();

            return $category;
        });

        if ($this->image !== null && $oldImage !== null) {
            $images->delete($oldImage);
        }

        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: __('store.category_saved'));
    }

    public function archive(int $categoryId, bool $archiveProducts = false): void
    {
        $category = Category::query()->findOrFail($categoryId);
        $products = Product::query()->where('category_id', $category->id)->get();

        if ($products->isNotEmpty() && ! $archiveProducts) {
            $this->addError('archive', __('store.category_archive_has_products'));

            return;
        }

        DB::transaction(function () use ($category, $products): void {
            foreach ($products as $product) {
                $product->delete();
            }
            $category->delete();
        });

        $this->dispatch('toast', type: 'success', message: __('store.category_archived'));
    }

    public function restore(int $categoryId): void
    {
        Category::onlyTrashed()->findOrFail($categoryId)->restore();
        $this->dispatch('toast', type: 'success', message: __('store.category_restored'));
    }

    public function forceDelete(int $categoryId, StoreImage $images): void
    {
        $category = Category::onlyTrashed()->findOrFail($categoryId);
        foreach (Product::withTrashed()->with(['images', 'colors'])->where('category_id', $category->id)->get() as $product) {
            foreach ($product->images as $image) {
                $images->delete($image->path);
            }
            foreach ($product->colors as $color) {
                $images->delete($color->tryon_image_path);
            }
            $product->forceDelete();
        }
        $images->delete($category->image_path);
        $category->forceDelete();
        $this->dispatch('toast', type: 'success', message: __('store.category_deleted'));
    }

    public function showArchive(): void
    {
        $this->showArchived = ! $this->showArchived;
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'slug', 'name_ar', 'name_en', 'image']);
        $this->sort_order = 0;
        $this->is_visible = true;
        $this->resetValidation();
    }

    public function render(): View
    {
        $query = $this->showArchived ? Category::onlyTrashed() : Category::query();

        return view('livewire.admin.categories.index', [
            'categories' => $query->withCount('products')->orderBy('sort_order')->orderBy('id')->paginate(12),
        ])->layout('layouts.admin', ['title' => __('store.manage_categories')]);
    }
}
