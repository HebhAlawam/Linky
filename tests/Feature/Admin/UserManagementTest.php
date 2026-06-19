<?php

use App\Models\Page;
use App\Models\User;

function adminTestPage(User $user, string $slug = 'admin-test-page'): Page
{
    return Page::query()->create([
        'user_id' => $user->id,
        'slug' => $slug,
        'title' => ['ar' => 'موقع اختباري'],
        'type' => 'website',
        'template' => 'restaurant1',
        'settings' => [],
        'status' => 'published',
    ]);
}

test('only admins can access user management routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);

    $this->get(route('admin.users.index'))
        ->assertRedirect(route('login'));

    $this->actingAs($customer)
        ->get(route('admin.users.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('إدارة المستخدمين');
});

test('admin can view a user and suspend then reactivate the account', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'role' => 'customer',
        'business_name' => 'مطعم الاختبار',
    ]);
    adminTestPage($customer);

    $this->actingAs($admin)
        ->get(route('admin.users.show', $customer))
        ->assertOk()
        ->assertSee($customer->email)
        ->assertSee('مطعم الاختبار')
        ->assertSee('موقع اختباري');

    $this->actingAs($admin)
        ->patch(route('admin.users.suspend', $customer), [
            'suspended_reason' => 'مخالفة شروط الاستخدام',
        ])
        ->assertRedirect();

    $customer->refresh();

    expect($customer->status)->toBe('suspended')
        ->and($customer->suspended_at)->not->toBeNull()
        ->and($customer->suspended_reason)->toBe('مخالفة شروط الاستخدام');

    $this->actingAs($admin)
        ->patch(route('admin.users.activate', $customer))
        ->assertRedirect();

    $customer->refresh();

    expect($customer->status)->toBe('active')
        ->and($customer->suspended_at)->toBeNull()
        ->and($customer->suspended_reason)->toBeNull();
});

test('admin cannot suspend the currently authenticated admin account', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.users.suspend', $admin))
        ->assertStatus(422);

    expect($admin->fresh()->status)->toBe('active');
});

test('suspended users cannot access dashboard and their public pages return 404', function () {
    $customer = User::factory()->create([
        'role' => 'customer',
        'status' => 'suspended',
        'suspended_at' => now(),
    ]);
    $page = adminTestPage($customer, 'suspended-public-page');

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertForbidden()
        ->assertSee('تم إيقاف حسابك، يرجى التواصل مع إدارة Linky.');

    $this->get(route('public.show', $page->slug))
        ->assertNotFound();

    $customer->update([
        'status' => 'active',
        'suspended_at' => null,
    ]);

    $this->get(route('public.show', $page->slug))
        ->assertOk();
});

test('admin navigation link is visible only to admins', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('إدارة المستخدمين');

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('إدارة المستخدمين');
});
