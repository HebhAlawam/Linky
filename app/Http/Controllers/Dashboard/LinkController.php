<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LinkController extends Controller
{
    private const FIXED_TYPES = [
        'whatsapp',
        'phone',
        'email',
        'map',
        'website',
    ];

    private const FORM_TYPES = [
        'whatsapp' => 'واتساب',
        'phone' => 'هاتف',
        'email' => 'بريد إلكتروني',
        'map' => 'خريطة',
        'website' => 'موقع إلكتروني',
        'instagram' => 'إنستغرام',
        'facebook' => 'فيسبوك',
        'tiktok' => 'تيك توك',
        'youtube' => 'يوتيوب',
        'telegram' => 'تلغرام',
        'snapchat' => 'سناب شات',
        'x' => 'X',
        'linkedin' => 'لينكدإن',
        'custom' => 'رابط مخصص',
    ];

    private const SOCIAL_TYPES = [
        'instagram',
        'facebook',
        'tiktok',
        'youtube',
        'telegram',
        'snapchat',
        'x',
        'linkedin',
    ];

    private const ICONS = [
        'whatsapp' => 'ti ti-brand-whatsapp',
        'phone' => 'ti ti-phone',
        'instagram' => 'ti ti-brand-instagram',
        'facebook' => 'ti ti-brand-facebook',
        'tiktok' => 'ti ti-brand-tiktok',
        'youtube' => 'ti ti-brand-youtube',
        'telegram' => 'ti ti-brand-telegram',
        'snapchat' => 'ti ti-brand-snapchat',
        'x' => 'ti ti-brand-x',
        'linkedin' => 'ti ti-brand-linkedin',
        'map' => 'ti ti-map-pin',
        'email' => 'ti ti-mail',
        'website' => 'ti ti-world',
        'custom' => 'ti ti-link',
        'social' => 'ti ti-share',
    ];

    public function index(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $links = Link::query()
            ->where('page_id', $page->id)
            ->orderBy('display_order')
            ->get();

        $typeLabels = $this->typeLabels();
        $fixedSlots = collect(self::FIXED_TYPES)->map(fn ($type) => [
            'type' => $type,
            'label' => self::FORM_TYPES[$type],
            'icon' => $this->iconFor($type),
            'link' => $links->first(fn (Link $link) => $link->type === $type),
        ]);
        $socialSlots = collect(self::SOCIAL_TYPES)->map(fn ($type) => [
            'type' => $type,
            'label' => self::FORM_TYPES[$type],
            'icon' => $this->iconFor($type),
            'link' => $links->first(fn (Link $link) => $this->formTypeFromLink($link) === $type),
        ]);
        $customLinks = $links->filter(fn (Link $link) => $this->formTypeFromLink($link) === 'custom')->values();

        return view('dashboard.links.index', compact('fixedSlots', 'socialSlots', 'customLinks', 'typeLabels'));
    }

    public function create(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $types = self::FORM_TYPES;
        $disabledTypes = $this->unavailableTypes($page);

        return view('dashboard.links.create', compact('types', 'disabledTypes'));
    }

    public function store(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        $data = $this->validateLink($request);
        $formType = $data['type'];
        $duplicateMessage = $this->duplicateMessage($page->id, $formType);

        if ($duplicateMessage) {
            return back()
                ->withErrors(['type' => $duplicateMessage])
                ->withInput();
        }

        if (! array_key_exists('display_order', $data) || $data['display_order'] === null) {
            $data['display_order'] = ((int) Link::query()->where('page_id', $page->id)->max('display_order')) + 1;
        }

        Link::query()->create([
            'page_id' => $page->id,
            'title' => $this->titlePayloadForStorage($data),
            'url' => $this->normalizeUrl($formType, $data['url']),
            'type' => $this->databaseType($formType),
            'icon' => $this->iconFor($formType),
            'display_order' => $data['display_order'],
            'clicks' => 0,
        ]);

        return redirect()
            ->route('dashboard.links.index')
            ->with('success', 'تم حفظ الرابط بنجاح.');
    }

    public function edit(Request $request, Link $link)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $link->page_id === (int) $page->id, 404);

        $selectedType = $this->formTypeFromLink($link);
        $types = self::FORM_TYPES;
        $disabledTypes = $this->unavailableTypes($page, $link);

        return view('dashboard.links.edit', compact('link', 'types', 'selectedType', 'disabledTypes'));
    }

    public function update(Request $request, Link $link)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $link->page_id === (int) $page->id, 404);

        $data = $this->validateLink($request);
        $formType = $data['type'];
        $duplicateMessage = $this->duplicateMessage($page->id, $formType, $link);

        if ($duplicateMessage) {
            return back()
                ->withErrors(['type' => $duplicateMessage])
                ->withInput();
        }

        $linkData = [
            'title' => $this->titlePayloadForStorage($data),
            'url' => $this->normalizeUrl($formType, $data['url']),
            'type' => $this->databaseType($formType),
            'icon' => $this->iconFor($formType),
        ];

        if (array_key_exists('display_order', $data) && $data['display_order'] !== null) {
            $linkData['display_order'] = $data['display_order'];
        }

        $link->update($linkData);

        return redirect()
            ->route('dashboard.links.index')
            ->with('success', 'تم حفظ التعديلات بنجاح.');
    }

    public function destroy(Request $request, Link $link)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return $this->missingPageRedirect();
        }

        abort_unless((int) $link->page_id === (int) $page->id, 404);

        $link->delete();

        return back()->with('success', 'تم حذف الرابط.');
    }

    public function reorder(Request $request)
    {
        $page = $this->currentPage($request);

        if (! $page) {
            return response()->json(['message' => 'أنشئ موقعك أولاً لتتمكن من إدارة روابط التواصل.'], 422);
        }

        $data = $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($data['items'] ?? [] as $item) {
            Link::query()
                ->where('page_id', $page->id)
                ->where('id', $item['id'])
                ->update(['display_order' => $item['order']]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function validateLink(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(array_keys(self::FORM_TYPES))],
            'title_ar' => ['required', 'string', 'max:100'],
            'title_en' => ['nullable', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:1000'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'type' => 'النوع',
            'title_ar' => 'العنوان بالعربي',
            'title_en' => 'العنوان بالإنكليزي',
            'url' => 'الرابط / الرقم',
            'display_order' => 'الترتيب',
        ]);
    }

    private function titlePayloadForStorage(array $data): string
    {
        return json_encode([
            'ar' => trim($data['title_ar']),
            'en' => filled($data['title_en'] ?? null) ? trim($data['title_en']) : null,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function currentPage(Request $request)
    {
        return $request->user()?->pages()->orderBy('id')->first();
    }

    private function missingPageRedirect()
    {
        return redirect()
            ->route('dashboard.my-website.index')
            ->with('error', 'أنشئ موقعك أولاً لتتمكن من إدارة روابط التواصل.');
    }

    private function databaseType(string $type): string
    {
        return in_array($type, self::SOCIAL_TYPES, true) ? 'social' : $type;
    }

    private function iconFor(string $type): string
    {
        return self::ICONS[$type] ?? self::ICONS['custom'];
    }

    private function typeLabels(): array
    {
        return array_merge(self::FORM_TYPES, ['social' => 'حساب اجتماعي']);
    }

    private function formTypeFromLink(Link $link): string
    {
        $iconMap = [
            'ti ti-brand-instagram' => 'instagram',
            'ti ti-brand-facebook' => 'facebook',
            'ti ti-brand-tiktok' => 'tiktok',
            'ti ti-brand-youtube' => 'youtube',
            'ti ti-brand-telegram' => 'telegram',
            'ti ti-brand-snapchat' => 'snapchat',
            'ti ti-brand-x' => 'x',
            'ti ti-brand-twitter' => 'x',
            'ti ti-brand-linkedin' => 'linkedin',
        ];

        if ($link->type === 'social' && $link->icon && isset($iconMap[$link->icon])) {
            return $iconMap[$link->icon];
        }

        return array_key_exists($link->type, self::FORM_TYPES) ? $link->type : 'custom';
    }

    private function unavailableTypes($page, ?Link $except = null): array
    {
        return Link::query()
            ->where('page_id', $page->id)
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->get()
            ->map(fn (Link $link) => $this->formTypeFromLink($link))
            ->filter(fn (string $type) => $type !== 'custom')
            ->unique()
            ->values()
            ->all();
    }

    private function duplicateMessage(int $pageId, string $formType, ?Link $except = null): ?string
    {
        if ($formType === 'custom') {
            return null;
        }

        $exists = Link::query()
            ->where('page_id', $pageId)
            ->when($except, fn ($query) => $query->whereKeyNot($except->id))
            ->get()
            ->contains(fn (Link $link) => $this->formTypeFromLink($link) === $formType);

        if (! $exists) {
            return null;
        }

        return in_array($formType, self::SOCIAL_TYPES, true)
            ? 'هذا الحساب الاجتماعي موجود مسبقًا، يمكنك تعديله بدل إضافة رابط جديد.'
            : 'هذا النوع من الروابط موجود مسبقًا، يمكنك تعديله بدل إضافة رابط جديد.';
    }

    private function normalizeUrl(string $type, string $url): string
    {
        $url = trim($url);

        return match ($type) {
            'whatsapp' => $this->normalizeWhatsapp($url),
            'phone' => $this->normalizePhone($url),
            'email' => $this->normalizeEmail($url),
            default => $this->normalizeWebUrl($url),
        };
    }

    private function normalizeWhatsapp(string $url): string
    {
        if (str_starts_with($url, 'https://wa.me/') || str_starts_with($url, 'http://wa.me/')) {
            return preg_replace('/^http:\/\//', 'https://', $url);
        }

        if (str_contains($url, 'whatsapp.com')) {
            return str_starts_with($url, 'http://') || str_starts_with($url, 'https://') ? $url : 'https://' . $url;
        }

        $phone = $this->phoneDigits($url);

        return $phone ? 'https://wa.me/' . $phone : $this->normalizeWebUrl($url);
    }

    private function normalizePhone(string $url): string
    {
        if (str_starts_with($url, 'tel:')) {
            return $url;
        }

        $phone = $this->phoneDigits($url);

        return $phone ? 'tel:' . $phone : $url;
    }

    private function normalizeEmail(string $url): string
    {
        if (str_starts_with($url, 'mailto:')) {
            return $url;
        }

        return str_contains($url, '@') ? 'mailto:' . $url : $this->normalizeWebUrl($url);
    }

    private function normalizeWebUrl(string $url): string
    {
        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $url)) {
            return $url;
        }

        return 'https://' . ltrim($url, '/');
    }

    private function phoneDigits(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value) ?: '';
    }
}
