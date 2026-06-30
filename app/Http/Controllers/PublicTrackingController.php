<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Link;
use App\Models\Page;
use App\Models\PageDailyStat;
use Illuminate\Http\JsonResponse;

class PublicTrackingController extends Controller
{
    public function link(Link $link): JsonResponse
    {
        $link->incrementClicks();

        return response()->json(['success' => true]);
    }

    public function itemOrder(Item $item): JsonResponse
    {
        $item->incrementClicks();

        return response()->json(['success' => true]);
    }

    public function orderAttempt(Page $page, string $channel): JsonResponse
    {
        abort_unless(in_array($channel, ['whatsapp', 'copy'], true), 422);
        abort_unless($page->status === 'published' && $page->user()->where('status', 'active')->exists(), 404);

        PageDailyStat::forToday($page->id)->recordOrderAttempt($channel);

        return response()->json(['success' => true]);
    }
}
