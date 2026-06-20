<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function paddedJpegUpload(string $name, int $bytes, int $width, int $height): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'linky-image-').'.jpg';
    $image = imagecreatetruecolor($width, $height);
    $background = imagecolorallocate($image, 32, 120, 180);
    imagefill($image, 0, 0, $background);

    for ($i = 0; $i < 250; $i++) {
        $color = imagecolorallocate($image, random_int(0, 255), random_int(0, 255), random_int(0, 255));
        imagefilledellipse(
            $image,
            random_int(0, $width),
            random_int(0, $height),
            random_int(20, max(20, intdiv($width, 3))),
            random_int(20, max(20, intdiv($height, 3))),
            $color,
        );
    }

    imagejpeg($image, $path, 96);
    imagedestroy($image);

    $currentSize = filesize($path);
    if ($currentSize < $bytes) {
        file_put_contents($path, str_repeat("\0", $bytes - $currentSize), FILE_APPEND);
    }
    clearstatcache(true, $path);

    return new UploadedFile($path, $name, 'image/jpeg', null, true);
}

function testPage(User $user): Page
{
    return Page::query()->create([
        'user_id' => $user->id,
        'slug' => 'image-test',
        'title' => ['ar' => 'اختبار الصور'],
        'type' => 'website',
        'template' => 'restaurant3',
        'settings' => [],
        'status' => 'published',
    ]);
}

test('a 4MB item photo is accepted and stored as a smaller webp', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $page = testPage($user);
    $category = Category::query()->create([
        'page_id' => $page->id,
        'name' => ['ar' => 'مشروبات'],
        'slug' => 'drinks',
    ]);
    $upload = paddedJpegUpload('phone-item.jpg', 4 * 1024 * 1024, 2400, 1800);
    $originalSize = $upload->getSize();

    $response = $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'عنصر بصورة',
        'category_id' => $category->id,
        'image' => $upload,
        'is_visible' => '1',
    ]);

    $response->assertRedirect(route('dashboard.items.index'));
    $item = Item::query()->sole();
    expect($item->image)->toEndWith('.webp');
    Storage::disk('public')->assertExists($item->image);

    $optimizedPath = Storage::disk('public')->path($item->image);
    [$width, $height] = getimagesize($optimizedPath);

    expect(filesize($optimizedPath))->toBeLessThan($originalSize)
        ->and(max($width, $height))->toBeLessThanOrEqual(1200);

    $this->actingAs($user)
        ->get(route('dashboard.items.index'))
        ->assertOk()
        ->assertSee($item->image_url, false);

    $this->get(route('public.show', $page->slug))
        ->assertOk()
        ->assertSee(str_replace('/', '\/', $item->image_url), false);
});

test('a 7MB hero photo is accepted optimized and limited to 1920 pixels wide', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $page = testPage($user);
    $upload = paddedJpegUpload('phone-hero.jpg', 7 * 1024 * 1024, 3000, 1800);
    $originalSize = $upload->getSize();

    $response = $this->actingAs($user)->put(route('dashboard.appearance.update'), [
        'primary_color' => '#00aa9a',
        'template' => 'restaurant3',
        'hero_image_1' => $upload,
    ]);

    $response->assertRedirect(route('dashboard.appearance.index'));
    $page->refresh();
    $url = $page->settings['hero_images'][0]['url'];
    $relativePath = ltrim(parse_url($url, PHP_URL_PATH), '/');
    $relativePath = preg_replace('#^storage/#', '', $relativePath);
    $optimizedPath = Storage::disk('public')->path($relativePath);
    [$width] = getimagesize($optimizedPath);

    expect($relativePath)->toEndWith('.webp')
        ->and(filesize($optimizedPath))->toBeLessThan($originalSize)
        ->and($width)->toBeLessThanOrEqual(1920);
});

test('an item image above 5MB is rejected with the Arabic size message', function () {
    Storage::fake('public');
    app()->setLocale('ar');
    $user = User::factory()->create();
    $page = testPage($user);
    $category = Category::query()->create([
        'page_id' => $page->id,
        'name' => ['ar' => 'مشروبات'],
        'slug' => 'drinks',
    ]);

    $response = $this->actingAs($user)
        ->from(route('dashboard.items.create'))
        ->post(route('dashboard.items.store'), [
            'title_ar' => 'صورة كبيرة',
            'category_id' => $category->id,
            'image' => paddedJpegUpload('too-large.jpg', 6 * 1024 * 1024, 1600, 1200),
        ]);

    $response->assertRedirect(route('dashboard.items.create'))
        ->assertSessionHasErrors([
            'image' => 'حجم الصورة كبير جدًا. الحد الأقصى المسموح هو 5MB.',
        ]);
    expect(Item::query()->count())->toBe(0);
});

test('an SVG logo is stored unchanged', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="80"><rect width="120" height="80" fill="#00aa9a"/></svg>';
    $upload = UploadedFile::fake()->createWithContent('logo.svg', $svg);

    $response = $this->actingAs($user)->post(route('dashboard.my-website.store'), [
        'title_ar' => 'مطعم SVG',
        'slug' => 'svg-restaurant',
        'status' => 'published',
        'logo' => $upload,
    ]);

    $response->assertRedirect(route('dashboard.my-website.index'));
    $page = Page::query()->sole();

    expect($page->logo)->toEndWith('.svg')
        ->and(Storage::disk('public')->get($page->logo))->toBe($svg);
});

test('an item stores localized discount prices inside the existing price JSON', function () {
    $user = User::factory()->create();
    $page = testPage($user);
    $category = Category::query()->create([
        'page_id' => $page->id,
        'name' => ['ar' => 'مشروبات'],
        'slug' => 'discount-drinks',
    ]);

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'قهوة مخفضة',
        'category_id' => $category->id,
        'price_ar' => '15000',
        'price_en' => '5',
        'discount_price_ar' => '12000',
        'discount_price_en' => '4',
    ])->assertRedirect(route('dashboard.items.index'));

    expect(Item::query()->sole()->price)->toBe([
        'ar' => '15000',
        'en' => '5',
        'discount_price' => [
            'ar' => '12000',
            'en' => '4',
        ],
    ]);
});

test('an equal or higher discount price is rejected', function () {
    app()->setLocale('ar');
    $user = User::factory()->create();
    $page = testPage($user);
    $category = Category::query()->create([
        'page_id' => $page->id,
        'name' => ['ar' => 'مشروبات'],
        'slug' => 'invalid-discount-drinks',
    ]);

    $this->actingAs($user)
        ->from(route('dashboard.items.create'))
        ->post(route('dashboard.items.store'), [
            'title_ar' => 'خصم غير صالح',
            'category_id' => $category->id,
            'price_ar' => '15000',
            'price_en' => '5',
            'discount_price_ar' => '15000',
            'discount_price_en' => '6',
        ])
        ->assertRedirect(route('dashboard.items.create'))
        ->assertSessionHasErrors([
            'discount_price_ar' => 'يجب أن يكون السعر بعد الخصم أقل من السعر الأساسي.',
            'discount_price_en' => 'يجب أن يكون السعر بعد الخصم أقل من السعر الأساسي.',
        ]);

    expect(Item::query()->count())->toBe(0);
});

test('removing discount prices keeps base prices and other JSON keys intact', function () {
    $user = User::factory()->create();
    $page = testPage($user);
    $category = Category::query()->create([
        'page_id' => $page->id,
        'name' => ['ar' => 'مشروبات'],
        'slug' => 'remove-discount-drinks',
    ]);
    $item = Item::query()->create([
        'page_id' => $page->id,
        'category_id' => $category->id,
        'title' => ['ar' => 'عنصر'],
        'slug' => 'discount-removal-item',
        'price' => [
            'ar' => '15000',
            'en' => '5',
            'tax_note' => 'preserve-me',
            'discount_price' => [
                'ar' => '12000',
                'en' => '4',
            ],
        ],
    ]);

    $this->actingAs($user)->put(route('dashboard.items.update', $item), [
        'title_ar' => 'عنصر',
        'category_id' => $category->id,
        'price_ar' => '15000',
        'price_en' => '5',
        'discount_price_ar' => '',
        'discount_price_en' => '',
    ])->assertRedirect(route('dashboard.items.index'));

    expect($item->fresh()->price)->toBe([
        'ar' => '15000',
        'en' => '5',
        'tax_note' => 'preserve-me',
    ]);
});
