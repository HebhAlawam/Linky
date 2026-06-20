<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Services\WebImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

    public function store(Request $request, WebImageOptimizer $imageOptimizer)
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
            'price' => $this->priceData(
                $data['price_ar'] ?? null,
                $data['price_en'] ?? null,
                $data['discount_price_ar'] ?? null,
                $data['discount_price_en'] ?? null,
            ),
            'image' => null,
            'is_featured' => $request->boolean('is_featured'),
            'is_visible' => $request->boolean('is_visible', true),
            'display_order' => $data['display_order'],
            'action_type' => 'internal',
            'external_url' => null,
            'clicks' => 0,
        ];

        if ($request->hasFile('image')) {
            $itemData['image'] = $imageOptimizer->store($request->file('image'), 'items', maxLongestSide: 1200);
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

    public function update(Request $request, Item $item, WebImageOptimizer $imageOptimizer)
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
            'price' => $this->priceData(
                $data['price_ar'] ?? null,
                $data['price_en'] ?? null,
                $data['discount_price_ar'] ?? null,
                $data['discount_price_en'] ?? null,
                $item->price,
            ),
            'is_featured' => $request->boolean('is_featured'),
            'is_visible' => $request->boolean('is_visible', true),
            'action_type' => 'internal',
            'external_url' => null,
        ];

        if (array_key_exists('display_order', $data) && $data['display_order'] !== null) {
            $itemData['display_order'] = $data['display_order'];
        }

        if ($request->hasFile('image')) {
            $itemData['image'] = $imageOptimizer->store($request->file('image'), 'items', maxLongestSide: 1200);
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
        $validator = Validator::make($request->all(), [
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
            'discount_price_ar' => ['nullable', 'string', 'max:255'],
            'discount_price_en' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
            'discount_price_ar' => 'السعر بعد الخصم بالعربي',
            'discount_price_en' => 'السعر بعد الخصم بالإنكليزي',
            'image' => 'الصورة',
            'is_featured' => 'مميز',
            'is_visible' => 'الظهور',
            'display_order' => 'الترتيب',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach (['ar', 'en'] as $locale) {
                $discountField = "discount_price_{$locale}";
                $baseField = "price_{$locale}";

                if ($validator->errors()->has($discountField) || $validator->errors()->has($baseField)) {
                    continue;
                }

                $discount = trim((string) $request->input($discountField, ''));

                if ($discount === '') {
                    continue;
                }

                $base = trim((string) $request->input($baseField, ''));

                if ($base === '') {
                    $validator->errors()->add(
                        $discountField,
                        'يجب إدخال سعر أساسي رقمي قبل إضافة سعر بعد الخصم.',
                    );

                    continue;
                }

                $parsedBase = $this->parseComparablePrice($base);

                if ($parsedBase === null) {
                    $validator->errors()->add(
                        $discountField,
                        'يجب إدخال سعر أساسي رقمي قبل إضافة سعر بعد الخصم.',
                    );

                    continue;
                }

                $parsedDiscount = $this->parseComparablePrice($discount);

                if ($parsedDiscount === null) {
                    $validator->errors()->add(
                        $discountField,
                        'يجب إدخال سعر بعد الخصم بصيغة رقمية قابلة للمقارنة.',
                    );

                    continue;
                }

                if ($parsedDiscount >= $parsedBase) {
                    $validator->errors()->add(
                        $discountField,
                        'يجب أن يكون السعر بعد الخصم أقل من السعر الأساسي.',
                    );
                }
            }
        });

        return $validator->validate();
    }

    private function parseComparablePrice(string $value): ?float
    {
        $value = strtr(trim($value), [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);

        if (! preg_match(
            '/(?<!\d)\d+(?:[\s\x{00A0},،٬.٫]\d+)*(?!\d)/u',
            $value,
            $matches,
            PREG_OFFSET_CAPTURE,
        )) {
            return null;
        }

        [$numericText, $offset] = $matches[0];
        $remainingText = substr($value, 0, $offset).substr($value, $offset + strlen($numericText));

        if (preg_match('/\d/u', $remainingText)) {
            return null;
        }

        $numericText = preg_replace('/[\s\x{00A0}]+/u', '', $numericText);
        $numericText = strtr($numericText, ['،' => ',', '٬' => ',', '٫' => '.']);
        $normalizedAmount = $this->normalizePriceAmount($numericText);

        if ($normalizedAmount === null || ! is_numeric($normalizedAmount)) {
            return null;
        }

        $amount = (float) $normalizedAmount;

        if (! is_finite($amount)) {
            return null;
        }

        return $amount;
    }

    private function normalizePriceAmount(string $value): ?string
    {
        if (preg_match('/^\d+$/', $value)) {
            return $value;
        }

        $commaPosition = strrpos($value, ',');
        $dotPosition = strrpos($value, '.');

        if ($commaPosition !== false && $dotPosition !== false) {
            $decimalSeparator = $commaPosition > $dotPosition ? ',' : '.';
            $thousandsSeparator = $decimalSeparator === ',' ? '.' : ',';
            [$integer, $decimal] = explode($decimalSeparator, $value, 2);

            if (! preg_match('/^\d{1,3}(?:'.preg_quote($thousandsSeparator, '/').'\d{3})*$/', $integer)
                || ! preg_match('/^\d+$/', $decimal)) {
                return null;
            }

            return str_replace($thousandsSeparator, '', $integer).'.'.$decimal;
        }

        $separator = $commaPosition !== false ? ',' : '.';
        $parts = explode($separator, $value);

        if (count($parts) === 2 && strlen($parts[1]) <= 2) {
            return preg_match('/^\d+$/', $parts[0]) && preg_match('/^\d+$/', $parts[1])
                ? $parts[0].'.'.$parts[1]
                : null;
        }

        if (! preg_match('/^\d{1,3}(?:'.preg_quote($separator, '/').'\d{3})+$/', $value)) {
            return null;
        }

        return str_replace($separator, '', $value);
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

    private function priceData(
        ?string $ar,
        ?string $en,
        mixed $discountAr = null,
        mixed $discountEn = null,
        mixed $existing = null,
    ): ?array
    {
        $price = is_array($existing) ? $existing : [];

        foreach (['ar' => $ar, 'en' => $en] as $locale => $value) {
            $value = is_string($value) ? trim($value) : $value;

            if (filled($value)) {
                $price[$locale] = $value;
            } else {
                unset($price[$locale]);
            }
        }

        $discountPrice = is_array($price['discount_price'] ?? null)
            ? $price['discount_price']
            : [];

        foreach (['ar' => $discountAr, 'en' => $discountEn] as $locale => $value) {
            $value = is_scalar($value) ? trim((string) $value) : '';

            if ($value !== '') {
                $discountPrice[$locale] = $value;
            } else {
                unset($discountPrice[$locale]);
            }
        }

        $hasLocalizedDiscount = collect(['ar', 'en'])
            ->contains(fn ($locale) => is_scalar($discountPrice[$locale] ?? null) && filled($discountPrice[$locale]));

        if ($hasLocalizedDiscount) {
            $price['discount_price'] = $discountPrice;
        } else {
            unset($price['discount_price']);
        }

        return $price === [] ? null : $price;
    }
}
