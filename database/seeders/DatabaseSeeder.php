<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'hoodies' => Category::query()->updateOrCreate(['slug' => 'hoodies'], ['name_ar' => 'هوديز', 'name_en' => 'Hoodies', 'sort_order' => 1, 'is_visible' => true]),
            'pants' => Category::query()->updateOrCreate(['slug' => 'pants'], ['name_ar' => 'بناطيل', 'name_en' => 'Pants', 'sort_order' => 2, 'is_visible' => true]),
        ];

        $products = [
            ['hoodies', 'oversized-hoodie', 'هوديز أوفر سايز', 'Oversized Hoodie', 650, true, [['أسود', 'Black', '#1b1b1b'], ['رمادي', 'Gray', '#8d8d8d'], ['بيج', 'Beige', '#d9c9ae']]],
            ['hoodies', 'classic-zip-hoodie', 'هوديز زيب كلاسيك', 'Classic Zip Hoodie', 720, true, [['كحلي', 'Navy', '#1f2a44'], ['أسود', 'Black', '#1b1b1b'], ['زيتي', 'Olive', '#4b5a3a']]],
            ['hoodies', 'cropped-hoodie', 'هوديز كروب', 'Cropped Hoodie', 590, false, [['وردي', 'Pink', '#e8b4bc'], ['أبيض', 'White', '#f1f1ee'], ['بنفسجي', 'Purple', '#6b5b95']]],
            ['hoodies', 'basic-sweatshirt', 'سويت شيرت بيسك', 'Basic Sweatshirt', 540, false, [['رمادي', 'Gray', '#9a9a9a'], ['أسود', 'Black', '#1b1b1b'], ['أحمر', 'Red', '#a8321f']]],
            ['pants', 'cargo-pants', 'بنطلون كارجو', 'Cargo Pants', 780, true, [['زيتي', 'Olive', '#4b5a3a'], ['أسود', 'Black', '#1b1b1b'], ['بيج', 'Beige', '#c9b891']]],
            ['pants', 'cotton-joggers', 'جوغر قطن', 'Cotton Joggers', 560, true, [['رمادي', 'Gray', '#8d8d8d'], ['أسود', 'Black', '#1b1b1b'], ['كحلي', 'Navy', '#1f2a44']]],
            ['pants', 'wide-leg-pants', 'بنطلون واسع', 'Wide-Leg Pants', 690, false, [['بيج', 'Beige', '#d9c9ae'], ['أسود', 'Black', '#1b1b1b'], ['بني', 'Brown', '#6b4a35']]],
            ['pants', 'straight-jeans', 'جينز سترايت', 'Straight Jeans', 850, false, [['أزرق', 'Blue', '#4a6a94'], ['أسود', 'Black', '#222222'], ['رمادي', 'Gray', '#7d8288']]],
        ];

        foreach ($products as [$category, $slug, $nameAr, $nameEn, $price, $bestSeller, $colors]) {
            $product = Product::query()->updateOrCreate(['slug' => $slug], [
                'category_id' => $categories[$category]->id,
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'description_ar' => 'خامة مريحة وقصة عصرية تناسب يومك.',
                'description_en' => 'Comfortable fabric and a modern everyday fit.',
                'base_price' => $price,
                'is_visible' => true,
                'is_best_seller' => $bestSeller,
            ]);

            foreach ($colors as $sortOrder => [$colorAr, $colorEn, $hex]) {
                $product->colors()->updateOrCreate(['name_en' => $colorEn], ['name_ar' => $colorAr, 'hex' => $hex, 'sort_order' => $sortOrder]);
            }

            foreach (['M', 'L', 'XL', '2XL'] as $sortOrder => $size) {
                $product->sizes()->updateOrCreate(['label' => $size], ['sort_order' => $sortOrder]);
            }
        }

        foreach ([[2, 0], [3, 13], [4, 15]] as [$pieces, $discount]) {
            DB::table('offers')->updateOrInsert(['product_id' => null, 'pieces' => $pieces], ['discount_percent' => $discount, 'free_shipping' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }

        foreach ([
            ['cairo', 'القاهرة', 'Cairo', 50, true], ['giza', 'الجيزة', 'Giza', 50, false],
            ['alexandria', 'الإسكندرية', 'Alexandria', 60, false], ['dakahlia', 'الدقهلية', 'Dakahlia', 75, false],
            ['sharqia', 'الشرقية', 'Sharqia', 75, false], ['other', 'محافظات أخرى', 'Other governorates', 90, false],
        ] as $sortOrder => [$slug, $nameAr, $nameEn, $price, $isDefault]) {
            DB::table('governorates')->updateOrInsert(['slug' => $slug], ['name_ar' => $nameAr, 'name_en' => $nameEn, 'shipping_price' => $price, 'is_active' => true, 'sort_order' => $sortOrder, 'is_default' => $isDefault, 'created_at' => now(), 'updated_at' => now()]);
        }

        foreach ([
            ['store.general', ['brand' => 'Hima', 'whatsapp' => '201000000000', 'currency' => 'EGP'], 'general'],
            ['store.hero', ['title_ar' => 'هوديز وبناطيل بقصّة مودرن', 'title_en' => 'Modern hoodies and pants', 'subtitle_ar' => 'قطع مريحة وخامات تدوم، توصلك لحد باب البيت.', 'subtitle_en' => 'Comfortable pieces made to last, delivered to your door.'], 'home'],
            ['store.shipping', ['free_for_all' => false, 'free_shipping_from' => 2], 'shipping'],
            ['store.payment', ['enabled' => ['cod' => true, 'instapay' => true, 'vodafone_cash' => true], 'instapay_url' => 'https://ipn.eg/S/salehahmed4774/instapay/8Fx1KB', 'vodafone_cash' => '01062575484'], 'payment'],
            ['store.social', ['links' => [['label' => 'Instagram', 'url' => 'https://instagram.com/', 'icon' => 'instagram'], ['label' => 'Facebook', 'url' => 'https://facebook.com/', 'icon' => 'facebook'], ['label' => 'TikTok', 'url' => 'https://tiktok.com/', 'icon' => 'tiktok']]], 'social'],
        ] as [$key, $value, $group]) {
            StoreSetting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }

        foreach ([
            ['terms', 'الشروط والأحكام', 'Terms & Conditions', 'يرجى مراجعة الشروط قبل إتمام الطلب.', 'Please review the terms before placing an order.'],
            ['returns', 'الاستبدال والاسترجاع', 'Returns & Exchanges', 'يمكنك طلب الاستبدال أو الإرجاع خلال ١٤ يومًا من الاستلام.', 'Returns or exchanges can be requested within 14 days of delivery.'],
            ['shipping', 'سياسة الشحن', 'Shipping Policy', 'تختلف تكلفة الشحن حسب المحافظة.', 'Shipping costs vary by governorate.'],
            ['privacy', 'سياسة الخصوصية', 'Privacy Policy', 'نستخدم بياناتك لتنفيذ طلبك فقط.', 'We use your details only to fulfill your order.'],
            ['about', 'من نحن', 'About us', 'Hima براند مصري للهوديز والبناطيل بتصميمات مريحة وعصرية.', 'Hima is an Egyptian hoodie and pants brand with comfortable modern designs.'],
        ] as [$slug, $titleAr, $titleEn, $bodyAr, $bodyEn]) {
            DB::table('pages')->updateOrInsert(['slug' => $slug], ['title_ar' => $titleAr, 'title_en' => $titleEn, 'body_ar' => $bodyAr, 'body_en' => $bodyEn, 'created_at' => now(), 'updated_at' => now()]);
        }

        if (filled(env('ADMIN_EMAIL')) && filled(env('ADMIN_PASSWORD'))) {
            User::query()->updateOrCreate(['email' => env('ADMIN_EMAIL')], [
                'name' => env('ADMIN_NAME', 'Store Admin'), 'password' => Hash::make((string) env('ADMIN_PASSWORD')),
                'locale' => 'ar', 'is_admin' => true,
            ]);
        }
    }
}
