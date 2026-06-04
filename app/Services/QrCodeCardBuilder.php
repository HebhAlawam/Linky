<?php

namespace App\Services;

use App\Models\Page;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Str;

class QrCodeCardBuilder
{
    public function build(Page $page, string $publicUrl): string
    {
        $settings = $page->settings ?? [];
        $primaryColor = $this->validHexColor($settings['primary_color'] ?? null) ?? '#c94c4c';
        $title = $page->title_text ?: 'Linky';
        $subtitle = $page->type === 'website' ? 'امسح لعرض الموقع' : 'امسح لعرض المنيو';
        $subtitleEn = $page->type === 'website' ? 'Scan to open the website' : 'Scan to open the menu';
        $qrSvg = $this->buildQrSvg($publicUrl);
        $logo = $this->logoDataUri($page);
        $displayUrl = $this->shortUrl($publicUrl);
        $hasLogo = $logo !== null;

        $accentLineY = $hasLogo ? 154 : 112;
        $titleY = $hasLogo ? 194 : 154;
        $subtitleY = $hasLogo ? 234 : 194;
        $subtitleEnY = $hasLogo ? 264 : 224;

        $logoMarkup = $hasLogo
            ? '<circle cx="360" cy="84" r="52" fill="#ffffff" stroke="#e5e7eb" stroke-width="2"/>
  <circle cx="360" cy="84" r="51" fill="none" stroke="#ffffff" stroke-width="3"/>
  <image href="' . $this->escape($logo) . '" x="320" y="44" width="80" height="80" preserveAspectRatio="xMidYMid meet" />'
            : '';

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="720" height="960" viewBox="0 0 720 960" role="img" aria-label="Linky QR code">
  <defs>
    <filter id="cardShadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="18" stdDeviation="22" flood-color="#111827" flood-opacity="0.14"/>
    </filter>
    <filter id="logoShadow" x="-30%" y="-30%" width="160%" height="160%">
      <feDropShadow dx="0" dy="8" stdDeviation="8" flood-color="#111827" flood-opacity="0.10"/>
    </filter>
  </defs>
  <rect width="720" height="960" fill="#f4f6f8"/>
  <rect x="56" y="48" width="608" height="864" rx="42" fill="#ffffff" filter="url(#cardShadow)"/>
  <rect x="86" y="78" width="548" height="804" rx="32" fill="#ffffff" stroke="#edf0f2" stroke-width="2"/>
  <g filter="url(#logoShadow)">{$logoMarkup}</g>
  <rect x="206" y="{$accentLineY}" width="308" height="6" rx="3" fill="{$this->escape($primaryColor)}" opacity="0.85"/>
  <text x="360" y="{$titleY}" text-anchor="middle" direction="rtl" unicode-bidi="plaintext" font-family="Tahoma, Arial, sans-serif" font-size="34" font-weight="700" fill="#111827">{$this->escape($this->limit($title, 34))}</text>
  <text x="360" y="{$subtitleY}" text-anchor="middle" direction="rtl" unicode-bidi="plaintext" font-family="Tahoma, Arial, sans-serif" font-size="20" fill="#4b5563">{$this->escape($subtitle)}</text>
  <text x="360" y="{$subtitleEnY}" text-anchor="middle" font-family="Arial, sans-serif" font-size="17" fill="#6b7280">{$this->escape($subtitleEn)}</text>
  <rect x="134" y="300" width="452" height="452" rx="30" fill="#ffffff" stroke="#e5e7eb" stroke-width="2"/>
  <svg x="164" y="330" width="392" height="392" viewBox="0 0 392 392">{$qrSvg}</svg>
  <text x="360" y="810" text-anchor="middle" direction="ltr" unicode-bidi="plaintext" font-family="Arial, sans-serif" font-size="18" fill="#374151">{$this->escape($displayUrl)}</text>
  <text x="360" y="852" text-anchor="middle" font-family="Arial, sans-serif" font-size="15" fill="#9ca3af">Powered by Linky</text>
</svg>
SVG;
    }

    private function buildQrSvg(string $publicUrl): string
    {
        $result = (new Builder(
            writer: new SvgWriter(),
            data: $publicUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 360,
            margin: 16,
            foregroundColor: new Color(17, 24, 39),
            backgroundColor: new Color(255, 255, 255),
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        $svg = $result->getString();

        if (preg_match('/<svg\b[^>]*>(.*)<\/svg>/is', $svg, $matches)) {
            return $matches[1];
        }

        return $svg;
    }

    private function logoDataUri(Page $page): ?string
    {
        if (! $page->logo) {
            return null;
        }

        $path = storage_path('app/public/' . ltrim($page->logo, '/'));

        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        if (! str_starts_with($mime, 'image/')) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function validHexColor(?string $color): ?string
    {
        $color = trim((string) $color);

        if (! preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color)) {
            return null;
        }

        if (strlen($color) === 4) {
            $color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
        }

        return strtolower($color);
    }

    private function shortUrl(string $url): string
    {
        return Str::limit(preg_replace('#^https?://#', '', $url) ?: $url, 52, '...');
    }

    private function limit(string $value, int $limit): string
    {
        return Str::limit($value, $limit, '...');
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
