<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Models\Category;
use App\Models\Offer;
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

    public int $category_id = 0;

    public string $slug = '';

    public string $name_ar = '';

    public string $name_en = '';

    public string $description_ar = '';

    public string $description_en = '';

    public string $base_price = '';

    public bool $is_visible = true;

    public bool $is_best_seller = false;

    public int $sort_order = 0;

    public bool $showArchived = false;

    /** @var array<int, array{name_ar: string, name_en: string, hex: string}> */
    public array $colorRows = [];

    /** @var array<int, string> */
    public array $sizeRows = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $imageFiles = [];

    public bool $useCustomOffers = false;

    /** @var array<int, string> */
    public array $offerDiscounts = [2 => '0', 3 => '13', 4 => '15'];

    public bool $offerFreeShipping = true;

    public function mount(): void
    {
        $this->resetForm();
    }

    public function addColor(): void
    {
        if (count($this->colorRows) < 12) {
            $this->colorRows[] = ['name_ar' => '', 'name_en' => '', 'hex' => '#111111'];
        }
    }

    public function removeColor(int $index): void
    {
        if (count($this->colorRows) > 1) {
            unset($this->colorRows[$index]);
            $this->colorRows = array_values($this->colorRows);
        }
    }

    public function addSize(): void
    {
        if (count($this->sizeRows) < 20) {
            $this->sizeRows[] = '';
        }
    }

    public function removeSize(int $index): void
    {
        if (count($this->sizeRows) > 1) {
            unset($this->sizeRows[$index]);
            $this->sizeRows = array_values($this->sizeRows);
        }
    }

    public function edit(int $productId): void
    {
        $product = Product::query()->with(['colors', 'sizes', 'images', 'offers'])->findOrFail($productId);
        $this->editingId = $product->id;
        $this->category_id = $product->category_id;
        $this->slug = $product->slug;
        $this->name_ar = $product->name_ar;
        $this->name_en = $product->name_en;
        $this->description_ar = $product->description_ar ?? '';
        $this->description_en = $product->description_en ?? '';
        $this->base_price = (string) $product->base_price;
        $this->is_visible = $product->is_visible;
        $this->is_best_seller = $product->is_best_seller;
        $this->sort_order = $product->sort_order;
        $this->colorRows = $product->colors->map(fn ($color): array => ['name_ar' => $color->name_ar, 'name_en' => $color->name_en, 'hex' => $color->hex])->all();
        $this->sizeRows = $product->sizes->pluck('label')->all();
        $customOffers = $product->offers->keyBy('pieces');
        $this->useCustomOffers = $customOffers->isNotEmpty();
        foreach ([2, 3, 4] as $pieces) {
            $customOffer = $customOffers->get($pieces);
            $this->offerDiscounts[$pieces] = $customOffer === null
                ? (string) [2 => 0, 3 => 13, 4 => 15][$pieces]
                : (string) $customOffer->discount_percent;
        }
        $this->offerFreeShipping = $customOffers->isNotEmpty() ? (bool) $customOffers->first()->free_shipping : true;
        $this->imageFiles = [];
        $this->resetValidation();
    }

    public function save(StoreImage $images): void
    {
        $this->description_ar = trim($this->description_ar);
        $this->description_en = trim($this->description_en);
        $validated = $this->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('products', 'slug')->ignore($this->editingId)],
            'name_ar' => ['required', 'string', 'max:160'],
            'name_en' => ['required', 'string', 'max:160'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_visible' => ['boolean'],
            'is_best_seller' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'colorRows' => ['required', 'array', 'min:1', 'max:12'],
            'colorRows.*.name_ar' => ['required', 'string', 'max:80'],
            'colorRows.*.name_en' => ['required', 'string', 'max:80'],
            'colorRows.*.hex' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sizeRows' => ['required', 'array', 'min:1', 'max:20'],
            'sizeRows.*' => ['required', 'string', 'alpha_dash', 'max:12', 'distinct'],
            'imageFiles' => ['array', 'max:10'],
            'imageFiles.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'useCustomOffers' => ['boolean'],
            'offerDiscounts' => ['array'],
            'offerDiscounts.2' => ['required', 'numeric', 'min:0', 'max:90'],
            'offerDiscounts.3' => ['required', 'numeric', 'min:0', 'max:90'],
            'offerDiscounts.4' => ['required', 'numeric', 'min:0', 'max:90'],
            'offerFreeShipping' => ['boolean'],
        ]);

        $product = DB::transaction(function () use ($validated, $images): Product {
            $product = $this->editingId === null ? new Product : Product::query()->with('colors')->findOrFail($this->editingId);
            $product->fill([
                'category_id' => $validated['category_id'],
                'slug' => $validated['slug'],
                'name_ar' => $validated['name_ar'],
                'name_en' => $validated['name_en'],
                'description_ar' => $validated['description_ar'],
                'description_en' => $validated['description_en'],
                'base_price' => $validated['base_price'],
                'is_visible' => $validated['is_visible'],
                'is_best_seller' => $validated['is_best_seller'],
                'sort_order' => $validated['sort_order'],
            ]);
            $product->save();

            if ($this->editingId !== null) {
                foreach ($product->colors as $color) {
                    $images->delete($color->tryon_image_path);
                }
                $product->colors()->delete();
                $product->sizes()->delete();
            }

            foreach ($validated['colorRows'] as $order => $color) {
                $product->colors()->create([...$color, 'sort_order' => $order]);
            }
            foreach ($validated['sizeRows'] as $order => $label) {
                $product->sizes()->create(['label' => strtoupper($label), 'sort_order' => $order]);
            }

            if ($validated['useCustomOffers']) {
                foreach ([2, 3, 4] as $pieces) {
                    Offer::query()->updateOrCreate(
                        ['product_id' => $product->id, 'pieces' => $pieces],
                        ['discount_percent' => $validated['offerDiscounts'][$pieces], 'free_shipping' => $validated['offerFreeShipping'], 'is_active' => true],
                    );
                }
            } else {
                Offer::query()->where('product_id', $product->id)->delete();
            }

            foreach ($this->imageFiles as $image) {
                $product->images()->create(['path' => $images->save($image, 'products', 1800), 'sort_order' => $product->images()->count()]);
            }

            return $product;
        });

        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: __('store.product_saved'));
    }

    public function deleteImage(int $imageId, StoreImage $images): void
    {
        $image = $this->currentProduct()->images()->findOrFail($imageId);
        $images->delete($image->path);
        $image->delete();
    }

    public function archive(int $productId): void
    {
        Product::query()->findOrFail($productId)->delete();
        $this->dispatch('toast', type: 'success', message: __('store.product_archived'));
    }

    public function restore(int $productId): void
    {
        Product::onlyTrashed()->findOrFail($productId)->restore();
        $this->dispatch('toast', type: 'success', message: __('store.product_restored'));
    }

    public function forceDelete(int $productId, StoreImage $images): void
    {
        $product = Product::onlyTrashed()->with(['images', 'colors'])->findOrFail($productId);
        foreach ($product->images as $image) {
            $images->delete($image->path);
        }
        foreach ($product->colors as $color) {
            $images->delete($color->tryon_image_path);
        }
        $product->forceDelete();
        $this->dispatch('toast', type: 'success', message: __('store.product_deleted'));
    }

    public function showArchive(): void
    {
        $this->showArchived = ! $this->showArchived;
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'slug', 'name_ar', 'name_en', 'description_ar', 'description_en', 'base_price', 'imageFiles']);
        $this->category_id = (int) (Category::query()->orderBy('sort_order')->value('id') ?? 0);
        $this->is_visible = true;
        $this->is_best_seller = false;
        $this->sort_order = 0;
        $this->colorRows = [['name_ar' => '', 'name_en' => '', 'hex' => '#111111']];
        $this->sizeRows = ['M', 'L', 'XL', '2XL'];
        $this->useCustomOffers = false;
        $this->offerDiscounts = [2 => '0', 3 => '13', 4 => '15'];
        $this->offerFreeShipping = true;
        $this->resetValidation();
    }

    public function render(): View
    {
        $query = $this->showArchived ? Product::onlyTrashed() : Product::query();

        return view('livewire.admin.products.index', [
            'products' => $query->with(['category:id,name_ar,name_en', 'images:id,product_id,path,sort_order'])->orderBy('sort_order')->orderByDesc('created_at')->paginate(12),
            'categories' => Category::query()->orderBy('sort_order')->get(['id', 'name_ar', 'name_en']),
        ])->layout('layouts.admin', ['title' => __('store.manage_products')]);
    }

    private function currentProduct(): Product
    {
        abort_if($this->editingId === null, 404);

        return Product::query()->findOrFail($this->editingId);
    }
}
