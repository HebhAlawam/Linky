<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Link;
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
}
