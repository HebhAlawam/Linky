<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $items = Item::with('category')
            ->where('page_id', $page->id)
            ->orderBy('display_order')
            ->get();

        return view('dashboard.items.index', compact('items'));
    }

    public function create(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $categories = $this->pageCategories($page->id)->get();

        return view('dashboard.items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $data = $this->validateItem($request, $page->id);

        if (! array_key_exists('display_order', $data) || $data['display_order'] === null) {
            $data['display_order'] = ((int) Item::query()->where('page_id', $page->id)->max('display_order')) + 1;
        }

        $itemData = [
            'page_id' => $page->id,
            'category_id' => $data['category_id'],
            'title' => $this->translationData($data['title_ar'], $data['title_en'] ?? null),
            'short_description' => $this->translationData($data['short_description_ar'] ?? null, $data['short_description_en'] ?? null),
            'description' => $this->translationData($data['description_ar'] ?? null, $data['description_en'] ?? null),
            'price' => $this->priceData($data['price_ar'] ?? null, $data['price_en'] ?? null),
            'image' => null,
            'is_featured' => $request->boolean('is_featured'),
            'is_visible' => $request->boolean('is_visible', true),
            'display_order' => $data['display_order'],
            'action_type' => 'internal',
            'external_url' => null,
            'clicks' => 0,
        ];

        if ($request->hasFile('image')) {
            $itemData['image'] = $request->file('image')->store('items', 'public');
        }

        Item::query()->create($itemData);

        return redirect()
            ->route('dashboard.items.index')
            ->with('success', 'تم حفظ العنصر بنجاح.');
    }

    public function edit(Request $request, Item $item)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $item->page_id === (int) $page->id, 404);

        $categories = $this->pageCategories($page->id)->get();

        return view('dashboard.items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, Item $item)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $item->page_id === (int) $page->id, 404);

        $data = $this->validateItem($request, $page->id);

        $itemData = [
            'category_id' => $data['category_id'],
            'title' => $this->translationData($data['title_ar'], $data['title_en'] ?? null),
            'short_description' => $this->translationData($data['short_description_ar'] ?? null, $data['short_description_en'] ?? null),
            'description' => $this->translationData($data['description_ar'] ?? null, $data['description_en'] ?? null),
            'price' => $this->priceData($data['price_ar'] ?? null, $data['price_en'] ?? null),
            'is_featured' => $request->boolean('is_featured'),
            'is_visible' => $request->boolean('is_visible', true),
            'action_type' => 'internal',
            'external_url' => null,
        ];

        if (array_key_exists('display_order', $data) && $data['display_order'] !== null) {
            $itemData['display_order'] = $data['display_order'];
        }

        if ($request->hasFile('image')) {
            $itemData['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($itemData);

        return redirect()
            ->route('dashboard.items.index')
            ->with('success', 'تم حفظ التعديلات بنجاح.');
    }

    public function destroy(Request $request, Item $item)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $item->page_id === (int) $page->id, 404);

        $item->delete();

        return back()->with('success', 'تم الحذف');
    }

    public function toggle(Request $request, Item $item)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $item->page_id === (int) $page->id, 404);

        $item->update([
            'is_visible' => ! $item->is_visible,
        ]);

        return back();
    }

    public function reorder(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return response()->json(['message' => 'أنشئ موقعك أولاً لتتمكن من إدارة العناصر.'], 422);
        }

        foreach ($request->input('items', []) as $item) {
            Item::query()
                ->where('page_id', $page->id)
                ->where('id', $item['id'] ?? null)
                ->update(['display_order' => $item['order'] ?? 0]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function validateItem(Request $request, int $pageId): array
    {
        return $request->validate([
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'short_description_ar' => ['nullable', 'string', 'max:500'],
            'short_description_en' => ['nullable', 'string', 'max:500'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('page_id', $pageId)),
            ],
            'price_ar' => ['nullable', 'string', 'max:255'],
            'price_en' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_featured' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'title_ar' => 'العنوان بالعربي',
            'title_en' => 'العنوان بالإنكليزي',
            'short_description_ar' => 'الوصف المختصر بالعربي',
            'short_description_en' => 'الوصف المختصر بالإنكليزي',
            'description_ar' => 'الوصف الكامل بالعربي',
            'description_en' => 'الوصف الكامل بالإنكليزي',
            'category_id' => 'التصنيف',
            'price_ar' => 'السعر بالعربي',
            'price_en' => 'السعر بالإنكليزي',
            'image' => 'الصورة',
            'is_featured' => 'مميز',
            'is_visible' => 'الظهور',
            'display_order' => 'الترتيب',
        ]);
    }

    private function pageCategories(int $pageId)
    {
        return Category::query()
            ->where('page_id', $pageId)
            ->orderBy('display_order');
    }

    private function currentPage(Request $request)
    {
        return $request->user()?->pages()->orderBy('id')->first();
    }

    private function missingPageRedirect()
    {
        return redirect()
            ->route('dashboard.my-website.index')
            ->with('error', 'أنشئ موقعك أولاً لتتمكن من إدارة العناصر.');
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

    private function priceData(?string $ar, ?string $en): ?array
    {
        $price = $this->translationData($ar, $en);

        return $price === [] ? null : $price;
    }
}
