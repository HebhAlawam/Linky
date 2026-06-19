<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function unsupportedImageTestPage(User $user): Page
{
    return Page::query()->create([
        'user_id' => $user->id,
        'slug' => 'format-test',
        'title' => ['ar' => 'اختبار الصيغ'],
        'type' => 'website',
        'template' => 'restaurant3',
        'settings' => [],
        'status' => 'published',
    ]);
}

function fakeHeicUpload(string $name = 'iphone-photo.heic'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, 'unsupported-heic-content');
}

function supportedRasterUpload(string $format): UploadedFile
{
    $extension = strtolower($format);
    $path = tempnam(sys_get_temp_dir(), 'linky-supported-').'.'.$extension;
    $image = imagecreatetruecolor(320, 240);
    $color = imagecolorallocate($image, 20, 140, 180);
    imagefill($image, 0, 0, $color);

    match ($extension) {
        'png' => imagepng($image, $path),
        'webp' => imagewebp($image, $path, 85),
        default => imagejpeg($image, $path, 85),
    };

    imagedestroy($image);

    return new UploadedFile(
        $path,
        'supported.'.$extension,
        mime_content_type($path),
        null,
        true,
    );
}

test('HEIC uploads show clear Arabic supported-format messages for every image field', function () {
    Storage::fake('public');
    app()->setLocale('ar');
    $user = User::factory()->create();
    $page = unsupportedImageTestPage($user);
    $category = Category::query()->create([
        'page_id' => $page->id,
        'name' => ['ar' => 'تصنيف'],
        'slug' => 'category',
    ]);

    $normalMessage = 'صيغة الصورة غير مدعومة. الصيغ المدعومة هي: JPG, PNG, WEBP.';
    $logoMessage = 'صيغة الشعار غير مدعومة. الصيغ المدعومة هي: JPG, PNG, WEBP, SVG.';

    $this->actingAs($user)->post(route('dashboard.items.store'), [
        'title_ar' => 'عنصر',
        'category_id' => $category->id,
        'image' => fakeHeicUpload('item.heic'),
    ])->assertSessionHasErrors(['image' => $normalMessage]);

    $this->actingAs($user)->post(route('dashboard.categories.store'), [
        'name_ar' => 'تصنيف جديد',
        'image' => fakeHeicUpload('category.heic'),
    ])->assertSessionHasErrors(['image' => $normalMessage]);

    $this->actingAs($user)->put(route('dashboard.appearance.update'), [
        'primary_color' => '#00aa9a',
        'template' => 'restaurant3',
        'hero_image_1' => fakeHeicUpload('hero.heic'),
    ])->assertSessionHasErrors(['hero_image_1' => $normalMessage]);

    $this->actingAs($user)->put(route('dashboard.my-website.update'), [
        'title_ar' => 'اختبار الصيغ',
        'slug' => $page->slug,
        'status' => 'published',
        'logo' => fakeHeicUpload('logo.heic'),
    ])->assertSessionHasErrors(['logo' => $logoMessage]);
});

test('supported PNG and WEBP uploads remain accepted', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $page = unsupportedImageTestPage($user);

    $this->actingAs($user)->post(route('dashboard.categories.store'), [
        'name_ar' => 'تصنيف PNG',
        'image' => supportedRasterUpload('png'),
    ])->assertRedirect(route('dashboard.categories.index'));

    $category = Category::query()->sole();
    expect($category->image)->toEndWith('.webp');
    Storage::disk('public')->assertExists($category->image);

    $this->actingAs($user)->put(route('dashboard.appearance.update'), [
        'primary_color' => '#00aa9a',
        'template' => 'restaurant3',
        'hero_image_1' => supportedRasterUpload('webp'),
    ])->assertRedirect(route('dashboard.appearance.index'));

    $page->refresh();
    expect($page->settings['hero_images'][0]['url'])->toEndWith('.webp');
});
