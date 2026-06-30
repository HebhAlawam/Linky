<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Link;
use App\Models\PageDailyStat;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->user()?->pages()->orderBy('id')->first();

        if (! $page) {
            return redirect()
                ->route('dashboard.my-website.index')
                ->with('error', 'أنشئ موقعك أولاً لتتمكن من عرض الإحصائيات.');
        }

        $today = now()->toDateString();
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $dailyStats = PageDailyStat::query()
            ->where('page_id', $page->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn ($stat) => $stat->date->toDateString());

        $lastSevenDays = collect(range(0, 6))->map(function ($offset) use ($startDate, $dailyStats) {
            $date = $startDate->copy()->addDays($offset);
            $key = $date->toDateString();
            $stat = $dailyStats->get($key);

            return [
                'date' => $date,
                'visits' => (int) ($stat?->visits ?? 0),
                'link_clicks' => (int) ($stat?->link_clicks ?? 0),
                'item_clicks' => (int) ($stat?->item_clicks ?? 0),
                'whatsapp_order_attempts' => (int) ($stat?->whatsapp_order_attempts ?? 0),
                'copied_order_attempts' => (int) ($stat?->copied_order_attempts ?? 0),
            ];
        });

        $whatsappOrderAttempts = (int) PageDailyStat::query()
            ->where('page_id', $page->id)
            ->sum('whatsapp_order_attempts');
        $copiedOrderAttempts = (int) PageDailyStat::query()
            ->where('page_id', $page->id)
            ->sum('copied_order_attempts');

        $summary = [
            'today_visits' => (int) PageDailyStat::query()
                ->where('page_id', $page->id)
                ->whereDate('date', $today)
                ->value('visits'),
            'last_7_visits' => (int) $lastSevenDays->sum('visits'),
            'total_visits' => (int) PageDailyStat::query()
                ->where('page_id', $page->id)
                ->sum('visits'),
            'link_clicks' => (int) Link::query()
                ->where('page_id', $page->id)
                ->sum('clicks'),
            'whatsapp_order_attempts' => $whatsappOrderAttempts,
            'copied_order_attempts' => $copiedOrderAttempts,
        ];

        $topItems = Item::query()
            ->with('category')
            ->where('page_id', $page->id)
            ->orderByDesc('clicks')
            ->limit(5)
            ->get()
            ->map(fn (Item $item) => [
                'title' => $this->dashboardText($item->title, 'عنصر بدون عنوان'),
                'category' => $item->category
                    ? $this->dashboardText($item->category->name, '—')
                    : '—',
                'clicks' => (int) $item->clicks,
            ]);

        $topLinks = Link::query()
            ->where('page_id', $page->id)
            ->orderByDesc('clicks')
            ->limit(5)
            ->get()
            ->map(fn (Link $link) => [
                'title' => $this->dashboardText($link->title, $this->linkTypeLabel($link->type)),
                'type' => $this->linkTypeLabel($link->type),
                'clicks' => (int) $link->clicks,
            ]);

        return view('dashboard.stats.index', compact('page', 'summary', 'lastSevenDays', 'topItems', 'topLinks'));
    }

    private function dashboardText(mixed $value, string $fallback = '—'): string
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return $fallback;
            }

            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->dashboardText($decoded, $fallback);
            }

            return $trimmed;
        }

        if (is_array($value)) {
            $translation = $this->filledString($value['ar'] ?? null)
                ?? $this->filledString($value['en'] ?? null);

            if ($translation) {
                return $translation;
            }

            foreach ($value as $item) {
                $translation = $this->filledString($item);

                if ($translation) {
                    return $translation;
                }
            }
        }

        return $fallback;
    }

    private function filledString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function linkTypeLabel(?string $type): string
    {
        return [
            'whatsapp' => 'واتساب',
            'phone' => 'هاتف',
            'email' => 'بريد إلكتروني',
            'map' => 'خريطة',
            'website' => 'موقع إلكتروني',
            'social' => 'حساب اجتماعي',
            'custom' => 'رابط مخصص',
        ][$type] ?? 'رابط';
    }
}
