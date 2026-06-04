<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Link;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DemoRestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'demo@linky.test'],
            [
                'name' => 'Linky Demo',
                'password' => Hash::make('password'),
            ]
        );

        $pageAttributes = [
            'user_id' => $user->id,
            'title' => [
                'ar' => 'الساقية',
                'en' => 'Al Saqia',
            ],
            'slogan' => [
                'ar' => 'قهوة ومذاق يومي بتجربة سهلة وسريعة',
                'en' => 'Daily coffee and bites with a simple quick experience',
            ],
            'logo' => null,
            'type' => 'website',
            'template' => 'restaurant3',
            'domain' => null,
            'settings' => [
                'primary_color' => '#0fb5a5',
                'address' => [
                    'ar' => 'التل - الشارع الرئيسي',
                    'en' => 'Al Tal - Main Street',
                ],
                'hours' => [
                    'ar' => 'يوميًا من 9 صباحًا حتى 12 ليلًا',
                    'en' => 'Daily from 9 AM to 12 AM',
                ],
                'hero_images' => [
                    // إذا القوالب عندك تدعم روابط خارجية للهيرو، خليها.
                    // إذا لا، استبدليها بمسارات local داخل storage/public.
                    'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=1600&q=80',
                    'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1600&q=80',
                ],
            ],
            'status' => 'published',
        ];

        if (Schema::hasColumn('pages', 'seo_title')) {
            $pageAttributes['seo_title'] = [
                'ar' => 'الساقية | منيو قهوة ومشروبات',
                'en' => 'Al Saqia | Coffee and Drinks Menu',
            ];
        }

        if (Schema::hasColumn('pages', 'seo_description')) {
            $pageAttributes['seo_description'] = [
                'ar' => 'منيو تجريبي لمقهى على منصة لينكي.',
                'en' => 'Demo cafe menu on Linky platform.',
            ];
        }

        $page = Page::query()->updateOrCreate(
            ['slug' => 'demo-restaurant'],
            $pageAttributes
        );

        /*
         * تنظيف بيانات الديمو حتى لا تتكرر عند إعادة تشغيل السييدر.
         * نحذف العناصر أولاً لتجنب مشاكل العلاقات.
         */
        Item::query()->where('page_id', $page->id)->delete();
        Category::query()->where('page_id', $page->id)->delete();
        Link::query()->where('page_id', $page->id)->delete();
$page->links()->delete();
$links = [
    [
        'title' => 'واتساب',
        'title_ar' => 'واتساب',
        'title_en' => 'WhatsApp',
        'url' => '+96395869575',
        'icon' => 'ti ti-brand-whatsapp',
        'type' => 'whatsapp',
        'display_order' => 1,
        'clicks' => 0,
    ],
    [
        'title' => 'هاتف',
        'title_ar' => 'هاتف',
        'title_en' => 'Phone',
        'url' => '+96395869575',
        'icon' => 'ti ti-phone',
        'type' => 'phone',
        'display_order' => 2,
        'clicks' => 0,
    ],
    [
        'title' => 'الموقع على الخريطة',
        'title_ar' => 'الموقع على الخريطة',
        'title_en' => 'Location',
        'url' => 'https://maps.google.com',
        'icon' => 'ti ti-map-pin',
        'type' => 'map',
        'display_order' => 3,
        'clicks' => 0,
    ],
    [
        'title' => 'إنستغرام',
        'title_ar' => 'إنستغرام',
        'title_en' => 'Instagram',
        'url' => 'https://instagram.com/linky.demo',
        'icon' => 'ti ti-brand-instagram',
        'type' => 'social',
        'display_order' => 4,
        'clicks' => 0,
    ],
    [
        'title' => 'فيسبوك',
        'title_ar' => 'فيسبوك',
        'title_en' => 'Facebook',
        'url' => 'https://facebook.com/linky.demo',
        'icon' => 'ti ti-brand-facebook',
        'type' => 'social',
        'display_order' => 5,
        'clicks' => 0,
    ],
    [
        'title' => 'قناة العروض',
        'title_ar' => 'قناة العروض',
        'title_en' => 'Offers Channel',
        'url' => 'https://t.me/linky_demo',
        'icon' => 'ti ti-brand-telegram',
        'type' => 'custom',
        'display_order' => 6,
        'clicks' => 1,
    ],
];

foreach ($links as $link) {
    $payload = [
        'page_id' => $page->id,
        'title' => $link['title'],
        'url' => $link['url'],
        'icon' => $link['icon'],
        'type' => $link['type'],
        'display_order' => $link['display_order'],
        'clicks' => $link['clicks'],
    ];

    if (Schema::hasColumn('links', 'title_ar')) {
        $payload['title_ar'] = $link['title_ar'];
    }

    if (Schema::hasColumn('links', 'title_en')) {
        $payload['title_en'] = $link['title_en'];
    }

    Link::query()->create($payload);
}

        $categories = [
            [
                'name' => ['ar' => 'مشروبات', 'en' => 'Drinks'],
                'slug' => 'drinks',
                'description' => [
                    'ar' => 'مشروبات ساخنة وباردة لكل الأوقات.',
                    'en' => 'Hot and cold drinks for every time.',
                ],
                'image' => null,
                'items' => [
                    [
                        'title' => ['ar' => 'قهوة تركية', 'en' => 'Turkish Coffee'],
                        'slug' => 'turkish-coffee',
                        'short_description' => [
                            'ar' => 'قهوة تركية بطعم غني ومذاق أصيل.',
                            'en' => 'Rich traditional Turkish coffee.',
                        ],
                        'description' => [
                            'ar' => 'قهوة تركية محضّرة بعناية لتناسب بداية يومك.',
                            'en' => 'Carefully prepared Turkish coffee for your day.',
                        ],
                        'price' => ['ar' => '25000 ل.س', 'en' => ' 25,000 SYP'],
                        'is_featured' => true,
                        'clicks' => 2,
                    ],
                    [
                        'title' => ['ar' => 'لاتيه مثلج', 'en' => 'Iced Latte'],
                        'slug' => 'iced-latte',
                        'short_description' => [
                            'ar' => 'إسبريسو بارد مع حليب كريمي وثلج.',
                            'en' => 'Cold espresso with creamy milk and ice.',
                        ],
                        'description' => [
                            'ar' => 'مشروب منعش لمحبي القهوة الباردة.',
                            'en' => 'A refreshing drink for iced coffee lovers.',
                        ],
                        'price' => ['ar' => '35000 ل.س', 'en' => ' 35,000 SYP'],
                        'is_featured' => true,
                        'clicks' => 1,
                    ],
                    [
                        'title' => ['ar' => 'ليمون ونعناع', 'en' => 'Mint Lemonade'],
                        'slug' => 'mint-lemonade',
                        'short_description' => [
                            'ar' => 'ليمون طازج مع نعناع منعش.',
                            'en' => 'Fresh lemonade with mint.',
                        ],
                        'description' => [
                            'ar' => 'مشروب بارد ومنعش مناسب للصيف.',
                            'en' => 'A cold refreshing summer drink.',
                        ],
                        'price' => ['ar' => '27500 ل.س', 'en' => ' 27,500 SYP'],
                        'is_featured' => false,
                        'clicks' => 0,
                    ],
                ],
            ],
            [
                'name' => ['ar' => 'سندويش', 'en' => 'Sandwiches'],
                'slug' => 'sandwiches',
                'description' => [
                    'ar' => 'سندويشات خفيفة وسريعة.',
                    'en' => 'Quick and light sandwiches.',
                ],
                'image' => null,
                'items' => [
                    [
                        'title' => ['ar' => 'سندويش دجاج', 'en' => 'Chicken Sandwich'],
                        'slug' => 'chicken-sandwich',
                        'short_description' => [
                            'ar' => 'دجاج متبل مع خضار وصوص خاص.',
                            'en' => 'Seasoned chicken with vegetables and sauce.',
                        ],
                        'description' => [
                            'ar' => 'سندويش مناسب لوجبة سريعة خلال اليوم.',
                            'en' => 'A quick sandwich for any time of day.',
                        ],
                        'price' => ['ar' => '50000 ل.س', 'en' => ' 50,000 SYP'],
                        'is_featured' => true,
                        'clicks' => 3,
                    ],
                    [
                        'title' => ['ar' => 'كرواسون جبنة', 'en' => 'Cheese Croissant'],
                        'slug' => 'cheese-croissant',
                        'short_description' => [
                            'ar' => 'كرواسون طازج محشو بالجبنة.',
                            'en' => 'Fresh croissant filled with cheese.',
                        ],
                        'description' => [
                            'ar' => 'اختيار خفيف مع القهوة أو المشروبات.',
                            'en' => 'A light choice with coffee or drinks.',
                        ],
                        'price' => ['ar' => '22500 ل.س', 'en' => ' 22,500 SYP'],
                        'is_featured' => false,
                        'clicks' => 0,
                    ],
                ],
            ],
            [
                'name' => ['ar' => 'حلويات', 'en' => 'Desserts'],
                'slug' => 'desserts',
                'description' => [
                    'ar' => 'حلويات خفيفة مع القهوة.',
                    'en' => 'Light desserts with coffee.',
                ],
                'image' => null,
                'items' => [
                    [
                        'title' => ['ar' => 'تشيز كيك', 'en' => 'Cheesecake'],
                        'slug' => 'cheesecake',
                        'short_description' => [
                            'ar' => 'تشيز كيك كريمي مع صوص الفواكه.',
                            'en' => 'Creamy cheesecake with fruit sauce.',
                        ],
                        'description' => [
                            'ar' => 'حلوى مميزة لمحبي الطعم الكريمي.',
                            'en' => 'A creamy dessert for sweet lovers.',
                        ],
                        'price' => ['ar' => '42500 ل.س', 'en' => ' 42,500 SYP'],
                        'is_featured' => false,
                        'clicks' => 1,
                    ],
                ],
            ],
        ];

        foreach ($categories as $categoryIndex => $categoryData) {
            $items = $categoryData['items'];
            unset($categoryData['items']);

            $category = Category::query()->create($categoryData + [
                'page_id' => $page->id,
                'display_order' => $categoryIndex + 1,
                'is_visible' => true,
            ]);

            foreach ($items as $itemIndex => $itemData) {
                Item::query()->create($itemData + [
                    'page_id' => $page->id,
                    'category_id' => $category->id,
                    'action_type' => 'internal',
                    'external_url' => null,
                    'image' => null,
                    'is_visible' => true,
                    'display_order' => $itemIndex + 1,
                ]);
            }
        }
    }
}
