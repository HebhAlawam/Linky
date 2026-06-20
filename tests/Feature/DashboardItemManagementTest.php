<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\Page;
use App\Models\User;

function itemManagementContext(): array
{
    $user = User::factory()->create();
    $page = Page::query()->create([
        'user_id' => $user->id,
        'slug' => 'item-management-test',
        'title' => ['ar' => 'اختبار إدارة العناصر'],
        'type' => 'website',
        'template' => 'restaurant3',
        'settings' => [],
        'status' => 'published',
    ]);
    $category = Category::query()->create([
        'page_id' => $page->id,
        'name' => ['ar' => 'مشروبات'],
        'slug' => 'item-management-drinks',
    ]);

    return [$user, $page, $category];
}

test('a numeric discount without a currency label is accepted', function () {
    [$user, , $category] = itemManagementContext();

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'قهوة',
        'category_id' => $category->id,
        'price_ar' => '42500 ل.س.',
        'discount_price_ar' => '22000',
    ])->assertRedirect(route('dashboard.items.index'))
        ->assertSessionHasNoErrors();

    expect(Item::query()->sole()->price)->toBe([
        'ar' => '42500 ل.س.',
        'discount_price' => ['ar' => '22000'],
    ]);
});

test('a formatted discount with the same currency label is accepted', function () {
    [$user, , $category] = itemManagementContext();

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'قهوة مخفضة',
        'category_id' => $category->id,
        'price_ar' => '42500 ل.س.',
        'discount_price_ar' => '22000 ل.س.',
    ])->assertRedirect(route('dashboard.items.index'))
        ->assertSessionHasNoErrors();
});

test('different currency labels are ignored when numeric amounts are comparable', function () {
    [$user, , $category] = itemManagementContext();

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'سعر إنكليزي',
        'category_id' => $category->id,
        'price_en' => '42,500 SYP',
        'discount_price_en' => '40,000 USD',
    ])->assertRedirect(route('dashboard.items.index'))
        ->assertSessionHasNoErrors();

    expect(Item::query()->sole()->price)->toBe([
        'en' => '42,500 SYP',
        'discount_price' => ['en' => '40,000 USD'],
    ]);
});

test('Arabic Indic and Persian digits are normalized for comparison', function () {
    [$user, , $category] = itemManagementContext();

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'أرقام محلية',
        'category_id' => $category->id,
        'price_ar' => '٤٢٬٥٠٠ ل.س.',
        'discount_price_ar' => '۴۰٬۰۰۰',
    ])->assertRedirect(route('dashboard.items.index'))
        ->assertSessionHasNoErrors();
});

test('a discount equal to the base amount is rejected even without a currency label', function () {
    [$user, , $category] = itemManagementContext();

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'خصم مساو',
        'category_id' => $category->id,
        'price_ar' => '42500 ل.س.',
        'discount_price_ar' => '42500',
    ])->assertSessionHasErrors([
        'discount_price_ar' => 'يجب أن يكون السعر بعد الخصم أقل من السعر الأساسي.',
    ]);

    expect(Item::query()->count())->toBe(0);
});

test('a discount greater than the base amount is rejected', function () {
    [$user, , $category] = itemManagementContext();

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'خصم أكبر',
        'category_id' => $category->id,
        'price_ar' => '42500 ل.س.',
        'discount_price_ar' => '45000',
    ])->assertSessionHasErrors([
        'discount_price_ar' => 'يجب أن يكون السعر بعد الخصم أقل من السعر الأساسي.',
    ]);

    expect(Item::query()->count())->toBe(0);
});

test('a non-numeric base price with a discount is rejected', function () {
    [$user, , $category] = itemManagementContext();

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'سعر غير قابل للمقارنة',
        'category_id' => $category->id,
        'price_ar' => 'حسب الطلب',
        'discount_price_ar' => '22000',
    ])->assertSessionHasErrors([
        'discount_price_ar' => 'يجب إدخال سعر أساسي رقمي قبل إضافة سعر بعد الخصم.',
    ]);

    expect(Item::query()->count())->toBe(0);
});

test('a non-numeric discount price is rejected', function () {
    [$user, , $category] = itemManagementContext();

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'خصم غير رقمي',
        'category_id' => $category->id,
        'price_ar' => '42500 ل.س.',
        'discount_price_ar' => 'عرض خاص',
    ])->assertSessionHasErrors([
        'discount_price_ar' => 'يجب إدخال سعر بعد الخصم بصيغة رقمية قابلة للمقارنة.',
    ]);

    expect(Item::query()->count())->toBe(0);
});

test('removing discount prices preserves the original base price strings', function () {
    [$user, $page, $category] = itemManagementContext();
    $item = Item::query()->create([
        'page_id' => $page->id,
        'category_id' => $category->id,
        'title' => ['ar' => 'عنصر'],
        'slug' => 'discount-removal-item',
        'price' => [
            'ar' => '42500 ل.س.',
            'en' => '42,500 SYP',
            'discount_price' => [
                'ar' => '40000 ل.س.',
                'en' => '40,000 SYP',
            ],
        ],
    ]);

    $this->actingAs($user)->put(route('dashboard.items.update', $item), [
        'title_ar' => 'عنصر',
        'category_id' => $category->id,
        'price_ar' => '42500 ل.س.',
        'price_en' => '42,500 SYP',
        'discount_price_ar' => '',
        'discount_price_en' => '',
    ])->assertRedirect(route('dashboard.items.index'));

    expect($item->fresh()->price)->toBe([
        'ar' => '42500 ل.س.',
        'en' => '42,500 SYP',
    ]);
});
