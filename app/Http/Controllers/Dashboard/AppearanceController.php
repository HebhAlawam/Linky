<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppearanceController extends Controller
{
    public function index(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $settings = $page->settings ?? [];
        $templates = config('linky.templates', []);
        $defaultTemplate = config('linky.default_template', 'restaurant1');
        $selectedTemplate = array_key_exists($page->template, $templates) ? $page->template : $defaultTemplate;

        return view('dashboard.appearance.index', compact('page', 'settings', 'templates', 'selectedTemplate'));
    }

    public function update(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $data = $request->validate([
            'primary_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'template' => ['required', Rule::in(array_keys(config('linky.templates', [])))],
            'address_ar' => ['nullable', 'string', 'max:1000'],
            'address_en' => ['nullable', 'string', 'max:1000'],
            'hours_ar' => ['nullable', 'string', 'max:2000'],
            'hours_en' => ['nullable', 'string', 'max:2000'],
            'hero_image_1' => ['nullable', 'image', 'max:2048'],
            'hero_image_2' => ['nullable', 'image', 'max:2048'],
            'hero_image_3' => ['nullable', 'image', 'max:2048'],
            'remove_hero_image_1' => ['nullable', 'boolean'],
            'remove_hero_image_2' => ['nullable', 'boolean'],
            'remove_hero_image_3' => ['nullable', 'boolean'],
        ], [], [
            'primary_color' => 'اللون الأساسي',
            'template' => 'القالب',
            'address_ar' => 'العنوان بالعربي',
            'address_en' => 'العنوان بالإنكليزي',
            'hours_ar' => 'مواعيد العمل بالعربي',
            'hours_en' => 'مواعيد العمل بالإنكليزي',
            'hero_image_1' => 'صورة الواجهة الأولى',
            'hero_image_2' => 'صورة الواجهة الثانية',
            'hero_image_3' => 'صورة الواجهة الثالثة',
        ]);

        $settings = $page->settings ?? [];
        $existingHeroImages = collect($settings['hero_images'] ?? [])->values();
        $heroImages = [];

        for ($slot = 1; $slot <= 3; $slot++) {
            $field = 'hero_image_' . $slot;
            $removeField = 'remove_hero_image_' . $slot;
            $existing = $existingHeroImages->get($slot - 1);
            $existingUrl = is_array($existing) ? ($existing['url'] ?? null) : (is_string($existing) ? $existing : null);

            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('appearance/hero', 'public');
                $heroImages[] = ['url' => asset('storage/' . $path)];
                continue;
            }

            if ($request->boolean($removeField)) {
                continue;
            }

            if (filled($existingUrl)) {
                $heroImages[] = ['url' => $existingUrl];
            }
        }

        $settings['primary_color'] = $data['primary_color'];
        $settings['address'] = $this->translations($data['address_ar'] ?? null, $data['address_en'] ?? null);
        $settings['hours'] = $this->translations($data['hours_ar'] ?? null, $data['hours_en'] ?? null);
        $settings['hero_images'] = $heroImages;

        $page->update([
            'settings' => $settings,
            'template' => $data['template'],
        ]);

        return redirect()
            ->route('dashboard.appearance.index')
            ->with('success', 'تم حفظ الإعدادات بنجاح.');
    }

    private function currentPage(Request $request)
    {
        return $request->user()?->pages()->orderBy('id')->first();
    }

    private function missingPageRedirect()
    {
        return redirect()
            ->route('dashboard.my-website.index')
            ->with('error', 'أنشئ موقعك أولاً لتتمكن من تعديل المظهر.');
    }

    private function translations(?string $ar, ?string $en): array
    {
        return collect([
            'ar' => $ar,
            'en' => $en,
        ])->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => filled($value))
            ->all();
    }
}
