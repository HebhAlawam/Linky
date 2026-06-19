<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageDailyStat;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()
            ->published()
            ->whereHas('user', fn ($query) => $query->where('status', 'active'))
            ->where('slug', $slug)
            ->with([
                'visibleCategories' => fn ($query) => $query
                    ->orderBy('display_order')
                    ->orderBy('id'),
                'visibleCategories.visibleItems' => fn ($query) => $query
                    ->reorder()
                    ->orderByDesc('is_featured')
                    ->orderBy('display_order')
                    ->orderBy('id'),
                'links' => fn ($query) => $query->orderBy('display_order'),
            ])
            ->firstOrFail();

        PageDailyStat::forToday($page->id)->recordVisit();
        $templateKey = $page->template ?: config('linky.default_template', 'restaurant1');
        $template = config('linky.templates.' . $templateKey)
            ?: config('linky.templates.' . config('linky.default_template', 'restaurant1'));
        $view = $template['view'] ?? 'templates.website.dark.restaurant1';

        $pageData = [
            'page' => [
                'id' => $page->id,
                'slug' => $page->slug,
                'title' => $page->translationPayload('title'),
                'slogan' => $page->translationPayload('slogan'),
                'logo_url' => $page->logo_url,
                'type' => $page->type,
                'template' => $page->template,
                'settings' => $page->settings ?? [],
            ],
            'categories' => $page->visibleCategories->map(fn ($category) => [
                'id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->translationPayload('name'),
                'description' => $category->translationPayload('description'),
                'image_url' => $category->image_url,
                'items' => $category->visibleItems->map(fn ($item) => [
                    'id' => $item->id,
                    'slug' => $item->slug,
                    'title' => $item->translationPayload('title'),
                    'short_description' => $item->translationPayload('short_description'),
                    'description' => $item->translationPayload('description'),
                    'price' => $item->price,
                    'image_url' => $item->image_url,
                    'is_featured' => $item->is_featured,
                    'display_order' => $item->display_order,
                    'url' => $item->url,
                ])->values(),
            ])->values(),
            'links' => $page->links->map(fn ($link) => [
                'id' => $link->id,
                'title' => $link->title_payload,
                'url' => $link->url,
                'icon' => $link->icon,
                'type' => $link->type,
            ])->values(),
        ];

        $publicMeta = $this->publicMeta($page, $pageData);

        return view($view, compact('page', 'pageData', 'publicMeta'));
    }

    private function publicMeta(Page $page, array $pageData): array
    {
        $title = $this->translatedText($pageData['page']['title'] ?? null, 'Linky');
        $description = $this->translatedText($pageData['page']['slogan'] ?? null, 'موقع مصغر ومنيو QR عبر Linky');

        return [
            'title' => $title,
            'description' => $description,
            'image' => $this->metaImage($page, $pageData),
            'url' => url('/' . $page->slug),
            'type' => 'website',
        ];
    }

    private function metaImage(Page $page, array $pageData): string
    {
        foreach ($this->heroImages($pageData['page']['settings']['hero_images'] ?? []) as $image) {
            $url = $this->absolutePublicUrl($image);

            if ($url && $this->publicAssetExists($url)) {
                return $url;
            }
        }

        if ($page->logo && $this->publicAssetExists($page->logo_url)) {
            return $page->logo_url;
        }

        $fallback = asset('templates/previews/restaurant1-long.png');

        if ($this->publicAssetExists($fallback)) {
            return $fallback;
        }

        return asset('images/defaults/logo.svg');
    }

    private function heroImages(mixed $images): array
    {
        if (! is_array($images)) {
            return [];
        }

        return collect($images)
            ->map(function ($image) {
                if (is_string($image)) {
                    return trim($image);
                }

                if (is_array($image)) {
                    return trim((string) ($image['url'] ?? $image['image_url'] ?? $image['src'] ?? ''));
                }

                return '';
            })
            ->filter()
            ->values()
            ->all();
    }

    private function translatedText(mixed $value, string $fallback): string
    {
        if (is_string($value)) {
            return trim($value) ?: $fallback;
        }

        if (is_array($value)) {
            return trim((string) ($value['ar'] ?? ''))
                ?: trim((string) ($value['en'] ?? ''))
                ?: trim((string) collect($value)->filter()->first())
                ?: $fallback;
        }

        return $fallback;
    }

    private function absolutePublicUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '' || Str::contains($url, '/dashboard/')) {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        if (Str::startsWith($url, '/')) {
            return url($url);
        }

        return asset($url);
    }

    private function publicAssetExists(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return Str::startsWith($url, ['http://', 'https://']);
        }

        return file_exists(public_path(ltrim($path, '/')))
            || Str::startsWith($url, ['http://', 'https://']) && ! Str::contains($url, url('/'));
    }
}
