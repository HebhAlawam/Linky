<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MyWebsiteController extends Controller
{
    private const TEMPLATE = 'restaurant1';


    private const USER_STATUSES = [
        'draft' => 'مسودة',
        'published' => 'منشور',
    ];

    public function index(Request $request): View
    {
        $page = $this->currentPage($request);

        return view('dashboard.my-website.index', [
            'page' => $page,
            'isEdit' => (bool) $page,
            'statuses' => self::USER_STATUSES,
            'isSuspended' => $page?->status === 'suspended',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->currentPage($request)) {
            return redirect()
                ->route('dashboard.my-website.index')
                ->with('error', 'لديك موقع مرتبط بحسابك بالفعل. يمكنك تعديل بياناته من هنا.');
        }

        $data = $this->validatedData($request);

        $pageData = [
            'user_id' => $request->user()->id,
            'slug' => $data['slug'],
            'title' => $this->translationData($data['title_ar'], $data['title_en'] ?? null),
            'slogan' => $this->translationData($data['slogan_ar'] ?? null, $data['slogan_en'] ?? null),
            'logo' => '',
            'type' => 'website',
            'template' => self::TEMPLATE,
            'domain' => null,
            'settings' => [],
            'status' => $data['status'] ?? 'published',
            'settings_version' => 1,
        ];

        if ($request->hasFile('logo')) {
            $pageData['logo'] = $request->file('logo')->store('page-logos', 'public');
        }

        Page::query()->create($pageData);

        return redirect()
            ->route('dashboard.my-website.index')
            ->with('success', 'تم إنشاء الموقع بنجاح. يمكنك الآن إضافة التصنيفات والعناصر والروابط.');
    }

    public function update(Request $request): RedirectResponse
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return redirect()
                ->route('dashboard.my-website.index')
                ->with('error', 'لا توجد صفحة مرتبطة بحسابك بعد. أنشئ الصفحة أولاً.');
        }

        $data = $this->validatedData($request, $page);

        $pageData = [
            'slug' => $data['slug'],
            'title' => $this->translationData($data['title_ar'], $data['title_en'] ?? null),
            'slogan' => $this->translationData($data['slogan_ar'] ?? null, $data['slogan_en'] ?? null),
            'type' => $page->type ?: 'website',
            'template' => $page->template ?: self::TEMPLATE,
            'status' => $page->status === 'suspended' ? 'suspended' : ($data['status'] ?? 'published'),
            'settings' => $page->settings ?? [],
        ];

        if ($request->hasFile('logo')) {
            $pageData['logo'] = $request->file('logo')->store('page-logos', 'public');
        }

        $page->update($pageData);

        return redirect()
            ->route('dashboard.my-website.index')
            ->with('success', 'تم حفظ التعديلات بنجاح.');
    }

    private function validatedData(Request $request, ?Page $page = null): array
    {
        $request->merge([
            'slug' => Str::slug((string) $request->input('slug')),
            'status' => $page?->status === 'suspended' ? 'published' : $request->input('status', 'published'),
        ]);

        return $request->validate([
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'slogan_ar' => ['nullable', 'string', 'max:500'],
            'slogan_en' => ['nullable', 'string', 'max:500'],
            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages', 'slug')->ignore($page?->id),
            ],
            'logo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(array_keys(self::USER_STATUSES))],
        ], [
            'slug.regex' => 'رابط الموقع يجب أن يحتوي على أحرف إنجليزية صغيرة وأرقام وشرطات فقط.',
        ], [
            'title_ar' => 'اسم الموقع بالعربي',
            'title_en' => 'اسم الموقع بالإنكليزي',
            'slogan_ar' => 'السلوغن بالعربي',
            'slogan_en' => 'السلوغن بالإنكليزي',
            'slug' => 'رابط الموقع',
            'logo' => 'الشعار',
            'status' => 'حالة النشر',
        ]);
    }

    private function currentPage(Request $request): ?Page
    {
        return $request->user()->pages()->orderBy('id')->first();
    }

    private function translationData(?string $ar, ?string $en): array
    {
        return collect([
            'ar' => $ar,
            'en' => $en,
        ])->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => filled($value))
            ->all();
    }
}
