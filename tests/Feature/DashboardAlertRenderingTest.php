<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;

function alertTestPage(User $user): Page
{
    return Page::query()->create([
        'user_id' => $user->id,
        'slug' => 'alert-test',
        'title' => ['ar' => 'اختبار التنبيهات'],
        'type' => 'website',
        'template' => 'restaurant3',
        'settings' => [],
        'status' => 'published',
    ]);
}

function alertTestHeic(string $name): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, 'unsupported-heic-content');
}

function assertSingleGlobalValidationAlert($test, $response, string $field): void
{
    $content = $response->getContent();

    $test->assertSame(1, substr_count($content, 'alert alert-danger alert-close-left'));
    $test->assertStringNotContainsString('يرجى مراجعة الحقول التالية', $content);
    $test->assertStringContainsString('invalid-feedback', $content);
    $test->assertStringContainsString('name="'.$field.'"', $content);
}

test('image validation uses one global alert and keeps field errors on dashboard forms', function () {
    app()->setLocale('ar');
    $user = User::factory()->create();
    $page = alertTestPage($user);
    $category = Category::query()->create([
        'page_id' => $page->id,
        'name' => ['ar' => 'تصنيف'],
        'slug' => 'category',
    ]);
    $item = Item::query()->create([
        'page_id' => $page->id,
        'category_id' => $category->id,
        'title' => ['ar' => 'عنصر'],
        'slug' => 'item',
    ]);

    $itemEdit = $this->actingAs($user)
        ->from(route('dashboard.items.edit', $item))
        ->followingRedirects()
        ->put(route('dashboard.items.update', $item), [
            'title_ar' => 'عنصر',
            'category_id' => $category->id,
            'image' => alertTestHeic('item.heic'),
        ])
        ->assertOk();
    assertSingleGlobalValidationAlert($this, $itemEdit, 'image');

    $categoryEdit = $this->actingAs($user)
        ->from(route('dashboard.categories.edit', $category))
        ->followingRedirects()
        ->put(route('dashboard.categories.update', $category), [
            'name_ar' => 'تصنيف',
            'image' => alertTestHeic('category.heic'),
        ])
        ->assertOk();
    assertSingleGlobalValidationAlert($this, $categoryEdit, 'image');

    $appearance = $this->actingAs($user)
        ->from(route('dashboard.appearance.index'))
        ->followingRedirects()
        ->put(route('dashboard.appearance.update'), [
            'primary_color' => '#00aa9a',
            'template' => 'restaurant3',
            'hero_image_1' => alertTestHeic('hero.heic'),
        ])
        ->assertOk();
    assertSingleGlobalValidationAlert($this, $appearance, 'hero_image_1');

    $website = $this->actingAs($user)
        ->from(route('dashboard.my-website.index'))
        ->followingRedirects()
        ->put(route('dashboard.my-website.update'), [
            'title_ar' => 'اختبار التنبيهات',
            'slug' => $page->slug,
            'status' => 'published',
            'logo' => alertTestHeic('logo.heic'),
        ])
        ->assertOk();
    assertSingleGlobalValidationAlert($this, $website, 'logo');
});

test('link validation and success messages are rendered once by partials alerts', function () {
    $user = User::factory()->create();
    alertTestPage($user);

    $linkCreate = $this->actingAs($user)
        ->from(route('dashboard.links.create'))
        ->followingRedirects()
        ->post(route('dashboard.links.store'), [])
        ->assertOk();
    $content = $linkCreate->getContent();

    expect(substr_count($content, 'alert alert-danger alert-close-left'))->toBe(1)
        ->and($content)->not->toContain('يرجى مراجعة الحقول التالية')
        ->and($content)->toContain('invalid-feedback');

    $successPage = $this->actingAs($user)
        ->followingRedirects()
        ->post(route('dashboard.categories.store'), [
            'name_ar' => 'تصنيف ناجح',
        ])
        ->assertOk();

    expect(substr_count($successPage->getContent(), 'alert alert-success alert-close-left'))->toBe(1);
});
