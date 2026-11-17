<?php

declare(strict_types=1);

use App\Livewire\Admin\Categories\Index as CategoriesPage;
use App\Livewire\Admin\Products\Index as ProductsPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

function catalogAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['is_admin' => true])->save();

    return $user;
}

it('allows an administrator to create and archive categories', function (): void {
    $this->actingAs(catalogAdmin());

    Livewire::test(CategoriesPage::class)
        ->set('slug', 'hoodies')
        ->set('name_ar', 'هوديز')
        ->set('name_en', 'Hoodies')
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::query()->where('slug', 'hoodies')->firstOrFail();
    Livewire::test(CategoriesPage::class)->call('archive', $category->id);
    expect(Category::onlyTrashed()->whereKey($category->id)->exists())->toBeTrue();
});

it('allows an administrator to create a product with database-owned color, size and offer data', function (): void {
    $this->actingAs(catalogAdmin());
    $category = Category::query()->create(['slug' => 'hoodies', 'name_ar' => 'هوديز', 'name_en' => 'Hoodies']);

    Livewire::test(ProductsPage::class)
        ->set('category_id', $category->id)
        ->set('slug', 'classic-hoodie')
        ->set('name_ar', 'هودي كلاسيك')
        ->set('name_en', 'Classic Hoodie')
        ->set('base_price', '650')
        ->set('colorRows', [['name_ar' => 'أسود', 'name_en' => 'Black', 'hex' => '#111111']])
        ->set('sizeRows', ['M', 'L'])
        ->set('useCustomOffers', true)
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::query()->where('slug', 'classic-hoodie')->with(['colors', 'sizes', 'offers'])->firstOrFail();
    expect($product->colors)->toHaveCount(1)
        ->and($product->sizes)->toHaveCount(2)
        ->and($product->offers)->toHaveCount(3);
});

it('keeps catalog administration behind the administrator middleware', function (): void {
    $this->get(route('admin.categories'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get(route('admin.categories'))->assertForbidden();
});
