<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PageDailyStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $page = $user->pages()->orderBy('id')->first();

        $stats = [
            'categories' => 0,
            'items' => 0,
            'links' => 0,
            'today_visits' => 0,
            'total_visits' => 0,
        ];

        if ($page) {
            $stats = [
                'categories' => $page->categories()->count(),
                'items' => $page->items()->count(),
                'links' => $page->links()->count(),
                'today_visits' => (int) PageDailyStat::query()
                    ->where('page_id', $page->id)
                    ->whereDate('date', now()->toDateString())
                    ->value('visits'),
                'total_visits' => (int) PageDailyStat::query()
                    ->where('page_id', $page->id)
                    ->sum('visits'),
            ];
        }

        return view('dashboard', [
            'page' => $page,
            'stats' => $stats,
            'hasAppearanceRoute' => Route::has('dashboard.appearance.index'),
        ]);
    }
}