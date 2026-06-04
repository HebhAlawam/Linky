<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\QrCodeCardBuilder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QrCodeController extends Controller
{
    public function download(Request $request, QrCodeCardBuilder $qrCodeCardBuilder): Response
    {
        $page = $request->user()?->pages()->orderBy('id')->first();

        if (! $page) {
            return redirect()
                ->route('dashboard.my-website.index')
                ->with('error', 'أنشئ موقعك أولاً لتتمكن من تحميل رمز QR.');
        }

        $publicUrl = url('/' . $page->slug);
        $svg = $qrCodeCardBuilder->build($page, $publicUrl);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="linky-' . $page->slug . '-qr-card.svg"',
        ]);
    }
}
