<?php

use App\Models\Link;
use App\Models\Page;
use App\Models\User;

function linkManagementPage(User $user, string $slug = 'link-management-test'): Page
{
    return Page::query()->create([
        'user_id' => $user->id,
        'slug' => $slug,
        'title' => ['ar' => 'اختبار الروابط'],
        'type' => 'website',
        'template' => 'restaurant3',
        'settings' => [],
        'status' => 'published',
    ]);
}

test('primary whatsapp orders entry is stored as whatsapp and primary', function () {
    $user = User::factory()->create();
    linkManagementPage($user);

    $this->actingAs($user)->post(route('dashboard.links.store'), [
        'type' => 'whatsapp_orders',
        'url' => '963999111222',
    ])->assertRedirect(route('dashboard.links.index'))
        ->assertSessionHasNoErrors();

    $link = Link::query()->sole();

    expect($link->type)->toBe('whatsapp')
        ->and($link->is_primary)->toBeTrue()
        ->and($link->url)->toBe('https://wa.me/963999111222');
});

test('setting another whatsapp as orders clears prior primary for same page', function () {
    $user = User::factory()->create();
    $page = linkManagementPage($user);

    $primary = Link::query()->create([
        'page_id' => $page->id,
        'title' => json_encode(['ar' => 'واتساب'], JSON_UNESCAPED_UNICODE),
        'url' => 'https://wa.me/963999111222',
        'type' => 'whatsapp',
        'icon' => 'ti ti-brand-whatsapp',
        'display_order' => 1,
        'is_primary' => true,
    ]);
    $additional = Link::query()->create([
        'page_id' => $page->id,
        'title' => json_encode(['ar' => 'واتساب إضافي'], JSON_UNESCAPED_UNICODE),
        'url' => 'https://wa.me/963999333444',
        'type' => 'whatsapp',
        'icon' => 'ti ti-brand-whatsapp',
        'display_order' => 2,
        'is_primary' => false,
    ]);

    $this->actingAs($user)->put(route('dashboard.links.update', $additional), [
        'type' => 'whatsapp_orders',
        'url' => '963999333444',
    ])->assertRedirect(route('dashboard.links.index'))
        ->assertSessionHasNoErrors();

    expect($primary->fresh()->is_primary)->toBeFalse()
        ->and($additional->fresh()->is_primary)->toBeTrue();
});

test('multiple additional phone and whatsapp numbers are allowed and never primary', function () {
    $user = User::factory()->create();
    linkManagementPage($user);

    foreach ([
        ['type' => 'phone', 'title_ar' => 'قسم الفروج', 'url' => '0115922903'],
        ['type' => 'phone', 'title_ar' => 'قسم الوجبات', 'url' => '0115922904'],
        ['type' => 'whatsapp', 'title_ar' => 'واتساب إضافي 1', 'url' => '963999111333'],
        ['type' => 'whatsapp', 'title_ar' => 'واتساب إضافي 2', 'url' => '963999111444'],
    ] as $payload) {
        $this->actingAs($user)->post(route('dashboard.links.store'), $payload)
            ->assertRedirect(route('dashboard.links.index'))
            ->assertSessionHasNoErrors();
    }

    expect(Link::query()->where('type', 'phone')->count())->toBe(2)
        ->and(Link::query()->where('type', 'whatsapp')->count())->toBe(2)
        ->and(Link::query()->where('is_primary', true)->count())->toBe(0)
        ->and(Link::query()->get()->first(fn (Link $link) => $link->title_text === 'قسم الفروج')->url)->toBe('tel:0115922903');
});

test('cross page ownership protections remain intact', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $page = linkManagementPage($owner, 'owner-links');
    linkManagementPage($intruder, 'intruder-links');

    $link = Link::query()->create([
        'page_id' => $page->id,
        'title' => json_encode(['ar' => 'رقم خاص'], JSON_UNESCAPED_UNICODE),
        'url' => 'tel:0115922903',
        'type' => 'phone',
        'icon' => 'ti ti-phone',
        'display_order' => 1,
        'is_primary' => false,
    ]);

    $this->actingAs($intruder)
        ->get(route('dashboard.links.edit', $link))
        ->assertNotFound();

    $this->actingAs($intruder)
        ->put(route('dashboard.links.update', $link), [
            'type' => 'phone',
            'title_ar' => 'محاولة تعديل',
            'url' => '0115000000',
        ])->assertNotFound();

    expect($link->fresh()->url)->toBe('tel:0115922903');
});
