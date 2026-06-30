<?php

use App\Models\Item;
use App\Models\Page;
use App\Models\PageDailyStat;
use App\Models\User;
use Illuminate\Support\Carbon;

function orderAttemptPage(User $user, string $slug = 'order-attempt-test'): Page
{
    return Page::query()->create([
        'user_id' => $user->id,
        'slug' => $slug,
        'title' => ['ar' => 'اختبار محاولات الطلب'],
        'type' => 'website',
        'template' => 'restaurant3',
        'settings' => [],
        'status' => 'published',
    ]);
}

function orderAttemptItem(Page $page, string $slug = 'test-item'): Item
{
    return Item::query()->create([
        'page_id' => $page->id,
        'slug' => $slug,
        'title' => ['ar' => 'صنف اختبار'],
        'price' => ['ar' => '1000 ل.س.'],
        'is_visible' => true,
        'display_order' => 1,
    ]);
}

beforeEach(function () {
    Carbon::setTestNow('2026-06-30 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

test('whatsapp order attempt increments only whatsapp attempts', function () {
    $page = orderAttemptPage(User::factory()->create());

    $this->post(route('track.order-attempt', [$page, 'whatsapp']))
        ->assertOk()
        ->assertJson(['success' => true]);

    $stat = PageDailyStat::query()->where('page_id', $page->id)->sole();

    expect($stat->date->toDateString())->toBe('2026-06-30')
        ->and($stat->whatsapp_order_attempts)->toBe(1)
        ->and($stat->copied_order_attempts)->toBe(0)
        ->and($stat->item_clicks)->toBe(0);
});

test('copy order attempt increments only copied attempts', function () {
    $page = orderAttemptPage(User::factory()->create());

    $this->post(route('track.order-attempt', [$page, 'copy']))
        ->assertOk()
        ->assertJson(['success' => true]);

    $stat = PageDailyStat::query()->where('page_id', $page->id)->sole();

    expect($stat->date->toDateString())->toBe('2026-06-30')
        ->and($stat->whatsapp_order_attempts)->toBe(0)
        ->and($stat->copied_order_attempts)->toBe(1)
        ->and($stat->item_clicks)->toBe(0);
});

test('order attempt counters are scoped to the correct page and day', function () {
    $firstPage = orderAttemptPage(User::factory()->create(), 'first-order-attempt-page');
    $secondPage = orderAttemptPage(User::factory()->create(), 'second-order-attempt-page');

    $this->post(route('track.order-attempt', [$firstPage, 'whatsapp']))->assertOk();
    $this->post(route('track.order-attempt', [$secondPage, 'copy']))->assertOk();

    $firstStat = PageDailyStat::query()->where('page_id', $firstPage->id)->sole();
    $secondStat = PageDailyStat::query()->where('page_id', $secondPage->id)->sole();

    expect($firstStat->date->toDateString())->toBe('2026-06-30')
        ->and($firstStat->whatsapp_order_attempts)->toBe(1)
        ->and($firstStat->copied_order_attempts)->toBe(0)
        ->and($secondStat->date->toDateString())->toBe('2026-06-30')
        ->and($secondStat->whatsapp_order_attempts)->toBe(0)
        ->and($secondStat->copied_order_attempts)->toBe(1);
});

test('invalid order attempt channels are rejected', function () {
    $page = orderAttemptPage(User::factory()->create());

    $this->post(route('track.order-attempt', [$page, 'email']))
        ->assertStatus(422);

    expect(PageDailyStat::query()->where('page_id', $page->id)->exists())->toBeFalse();
});

test('existing item order tracking increments item interactions without order attempt counters', function () {
    $page = orderAttemptPage(User::factory()->create());
    $firstItem = orderAttemptItem($page, 'first-item');
    $secondItem = orderAttemptItem($page, 'second-item');

    foreach ([$firstItem, $secondItem] as $item) {
        $this->post(route('track.item-order', $item))->assertOk();
    }

    $stat = PageDailyStat::query()->where('page_id', $page->id)->sole();

    expect($firstItem->fresh()->clicks)->toBe(1)
        ->and($secondItem->fresh()->clicks)->toBe(1)
        ->and($stat->item_clicks)->toBe(2)
        ->and($stat->whatsapp_order_attempts)->toBe(0)
        ->and($stat->copied_order_attempts)->toBe(0);
});

test('dashboard totals show whatsapp and copied order attempts', function () {
    $user = User::factory()->create();
    $page = orderAttemptPage($user);

    PageDailyStat::query()->create([
        'page_id' => $page->id,
        'date' => '2026-06-29',
        'whatsapp_order_attempts' => 2,
        'copied_order_attempts' => 3,
    ]);
    PageDailyStat::query()->create([
        'page_id' => $page->id,
        'date' => '2026-06-30',
        'whatsapp_order_attempts' => 4,
        'copied_order_attempts' => 5,
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard.stats.index'))
        ->assertOk()
        ->assertSee('طلبات عبر واتساب')
        ->assertSee('طلبات تم نسخها')
        ->assertDontSee('إجمالي محاولات الطلب');

    expect($response->viewData('summary')['whatsapp_order_attempts'])->toBe(6)
        ->and($response->viewData('summary')['copied_order_attempts'])->toBe(8)
        ->and($response->viewData('summary'))->not->toHaveKey('total_order_attempts');
});
