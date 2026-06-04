<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Page;
use App\Models\Link;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MainSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        // 1. إنشاء مستخدم (صاحب الحساب)
        $user = User::create([
            'name'     => 'عبدالله محمد',
            'email'    => 'demo@example.com',
            'password' => Hash::make('password'), // كلمة المرور: password
        ]);

        // 2. إنشاء صفحة هبوط (Page) للمستخدم
        $page = Page::create([
            'user_id'          => $user->id,
            'slug'             => 'delicious-burger',
            'slogan'           => 'أفضل البرجر في المدينة',
            'title'            => 'برجر السعادة - Happiest Burger',
            'logo'             => 'storage/seed/logo.png',
            'type'             => 'website',
            'template'         => 'modern-dark',
            'domain'           => 'burger.test',
            'settings'         => json_encode(['theme' => 'dark', 'font' => 'Tajawal']),
            'status'        => 'draft',
            'settings_version' => 1,
        ]);

        // 3. إضافة روابط التواصل الاجتماعي (Links)
        $socialLinks = [
            ['title' => 'Instagram', 'url' => 'https://instagram.com', 'icon' => 'instagram-icon', 'type' => 'social'],
            ['title' => 'Snapchat', 'url' => 'https://snapchat.com', 'icon' => 'snap-icon', 'type' => 'social'],
            ['title' => 'تواصل معنا', 'url' => 'https://wa.me/9665000000', 'icon' => 'whatsapp-icon', 'type' => 'contact'],
        ];

        foreach ($socialLinks as $index => $linkData) {
            Link::create(array_merge($linkData, [
                'page_id'       => $page->id,
                'display_order' => $index,
            ]));
        }

        // 4. إضافة تصنيفات المنيو (Categories)
        $categories = [
            ['name' => 'الوجبات الأكثر مبيعاً', 'slug' => 'best-sellers'],
            ['name' => 'برجر دجاج', 'slug' => 'chicken-burger'],
            ['name' => 'المقبلات', 'slug' => 'appetizers'],
        ];

        foreach ($categories as $catIndex => $catData) {
            $category = Category::create([
                'page_id'       => $page->id,
                'name'          => $catData['name'],
                'slug'          => $catData['slug'],
                'display_order' => $catIndex,
                'is_visible'    => true,
                'image'          => "storage/seed/category{$catIndex}.png",
            ]);

            // 5. إضافة منتجات (Items) داخل كل تصنيف
            for ($i = 1; $i <= 2; $i++) {
                Item::create([
                    'page_id'           => $page->id,
                    'category_id'       => $category->id,
                    'title'             => "{$catData['name']} - منتج {$i}",
                    'slug'              => Str::slug($catData['slug'] . "-item-{$i}"),
                    'short_description' => 'وصف مختصر وسريع يظهر في القائمة الرئيسية.',
                    'description'       => 'هذا هو الوصف الكامل للمنتج، يحتوي على المكونات، السعرات الحرارية، وأي تفاصيل إضافية تهم العميل.',
                    'action_type'       => 'internal',
                    'price'             => rand(25, 80),
                    'image'             => "storage/seed/item{$i}.jpg",
                    'is_visible'        => true,
                    'display_order'     => $i,
                    'clicks'            => rand(0, 500),
                ]);
            }
        }
    }
}
