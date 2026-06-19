<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\WebImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $categories = Category::query()
            ->withCount('items')
            ->where('page_id', $page->id)
            ->orderBy('display_order')
            ->get();

        return view('dashboard.categories.index', compact('categories'));
    }

    public function create(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        return view('dashboard.categories.create');
    }

    public function store(Request $request, WebImageOptimizer $imageOptimizer)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $data = $this->validateCategory($request);

        $categoryData = [
            'page_id' => $page->id,
            'name' => $this->translationData($data['name_ar'], $data['name_en'] ?? null),
            'description' => $this->translationData($data['description_ar'] ?? null, $data['description_en'] ?? null),
            'slug' => $this->uniqueSlug($page->id, ($data['name_en'] ?? null) ?: $data['name_ar']),
            'image' => null,
            'is_visible' => $request->boolean('is_visible', true),
            'display_order' => ((int) Category::query()->where('page_id', $page->id)->max('display_order')) + 1,
        ];

        if ($request->hasFile('image')) {
            $categoryData['image'] = $imageOptimizer->store($request->file('image'), 'categories', maxLongestSide: 1000);
        }

        Category::query()->create($categoryData);

        return redirect()
            ->route('dashboard.categories.index')
            ->with('success', 'تم حفظ التصنيف بنجاح.');
    }

    public function edit(Request $request, Category $category)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $category->page_id === (int) $page->id, 404);

        return view('dashboard.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category, WebImageOptimizer $imageOptimizer)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $category->page_id === (int) $page->id, 404);

        $data = $this->validateCategory($request);

        $categoryData = [
            'name' => $this->translationData($data['name_ar'], $data['name_en'] ?? null),
            'description' => $this->translationData($data['description_ar'] ?? null, $data['description_en'] ?? null),
            'is_visible' => $request->boolean('is_visible', true),
        ];

        if (! $category->slug) {
            $categoryData['slug'] = $this->uniqueSlug($page->id, ($data['name_en'] ?? null) ?: $data['name_ar'], $category->id);
        }

        if ($request->hasFile('image')) {
            $categoryData['image'] = $imageOptimizer->store($request->file('image'), 'categories', maxLongestSide: 1000);
        }

        $category->update($categoryData);

        return redirect()
            ->route('dashboard.categories.index')
            ->with('success', 'تم حفظ التعديلات بنجاح.');
    }

    public function destroy(Request $request, Category $category)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $category->page_id === (int) $page->id, 404);

        $category->delete();

        return back()->with('success', 'تم حذف التصنيف.');
    }

    public function toggle(Request $request, Category $category)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $category->page_id === (int) $page->id, 404);

        $category->update([
            'is_visible' => ! $category->is_visible,
        ]);

        return back();
    }

    public function reorder(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return response()->json(['message' => 'أنشئ موقعك أولاً لتتمكن من إدارة التصنيفات.'], 422);
        }

        $data = $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['items'] ?? [] as $item) {
            Category::query()
                ->where('page_id', $page->id)
                ->where('id', $item['id'])
                ->update(['display_order' => $item['order']]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_visible' => ['nullable', 'boolean'],
        ], [], [
            'name_ar' => 'الاسم بالعربي',
            'name_en' => 'الاسم بالإنكليزي',
            'description_ar' => 'الوصف بالعربي',
            'description_en' => 'الوصف بالإنكليزي',
            'image' => 'الصورة',
            'is_visible' => 'الظهور',
        ]);
    }

    private function currentPage(Request $request)
    {
        return $request->user()?->pages()->orderBy('id')->first();
    }

    private function missingPageRedirect()
    {
        return redirect()
            ->route('dashboard.my-website.index')
            ->with('error', 'أنشئ موقعك أولاً لتتمكن من إدارة التصنيفات.');
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

    private function uniqueSlug(int $pageId, string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'category';
        $slug = $base;
        $counter = 2;

        while (Category::query()
            ->where('page_id', $pageId)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
