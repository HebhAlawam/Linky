<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request, string $slug): RedirectResponse
    {
        $page = $request->user()?->pages()
            ->where('slug', $slug)
            ->firstOrFail();

        return redirect()->route('public.show', $page->slug);
    }
}
