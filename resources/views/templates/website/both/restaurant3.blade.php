<!doctype html>
<html lang="ar" dir="rtl" id="mainHtml">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @php
        $titlePayload = $pageData['page']['title'] ?? [];
        $pageTitle = is_array($titlePayload)
            ? ($titlePayload['ar'] ?? $titlePayload['en'] ?? collect($titlePayload)->filter()->first() ?? 'Linky')
            : ($titlePayload ?: 'Linky');

        $primaryColor = $pageData['page']['settings']['primary_color'] ?? '#00aa9a';
        $primaryColor = is_string($primaryColor) && preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $primaryColor)
            ? $primaryColor
            : '#00aa9a';

        $hex = ltrim($primaryColor, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $rgb = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        $darken = static fn (array $color, float $amount): string => sprintf(
            'rgb(%d, %d, %d)',
            max(0, (int) round($color[0] * (1 - $amount))),
            max(0, (int) round($color[1] * (1 - $amount))),
            max(0, (int) round($color[2] * (1 - $amount)))
        );
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta property="og:title" content="{{ $publicMeta['title'] }}" />
    <meta property="og:description" content="{{ $publicMeta['description'] }}" />
    <meta property="og:image" content="{{ $publicMeta['image'] }}" />
    <meta property="og:url" content="{{ $publicMeta['url'] }}" />
    <meta property="og:type" content="{{ $publicMeta['type'] }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $publicMeta['title'] }}" />
    <meta name="twitter:description" content="{{ $publicMeta['description'] }}" />
    <meta name="twitter:image" content="{{ $publicMeta['image'] }}" />
    @php
        $pageLogoFavicon = $page->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($page->logo)
            ? $page->logo_url
            : null;
    @endphp
    @if ($pageLogoFavicon)
      <link rel="icon" href="{{ $pageLogoFavicon }}" />
    @else
      <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
    <link rel="stylesheet" href="{{ asset('templates/restaurant/rest3/style.css') }}?v={{ filemtime(public_path('templates/restaurant/rest3/style.css')) }}" />
    <style>
      :root {
        --primary-rgb: {{ implode(' ', $rgb) }};
        --primary-color: rgb(var(--primary-rgb));
        --primary: rgb(var(--primary-rgb));
        --primary-hover: {{ $darken($rgb, 0.10) }};
        --primary-dark: {{ $darken($rgb, 0.25) }};
        --primary-soft: rgb(var(--primary-rgb) / 0.14);
      }
    </style>
    <script>
      window.APP = {
        lang: 'ar',
        data: @json($pageData)
      };
    </script>
  </head>
  <body>
    <header class="site-header">
      <div class="header-shell">
        <a class="brand" href="#" id="brandLink" aria-label="Linky">
          <span class="brand-mark" id="brandMark">L</span>
          <img id="brandLogo" class="brand-logo" src="" alt="" hidden />
          <span>
            <strong id="brandTitle"></strong>
            <small id="brandSlogan"></small>
          </span>
        </a>

        <nav class="main-nav" id="mainNav" aria-label="القائمة الرئيسية">
          <a href="#categories" data-i18n="categories">التصنيفات</a>
          <a href="#products" data-i18n="products">المنتجات</a>
          <a href="#contact" data-i18n="contactInfo">معلومات التواصل</a>
        </nav>

        <div class="header-actions">
          <button class="icon-button" id="shareButton" type="button" aria-label="Share site"><i class="ti ti-share-2" aria-hidden="true"></i></button>
          <button class="icon-button" id="langToggle" type="button" aria-label="تغيير اللغة">EN</button>
          <button class="icon-button theme-toggle" id="themeToggle" type="button" aria-label="تبديل المظهر">◐</button>
          <a class="call-button" id="headerCallButton" href="#" target="_blank" rel="noopener" data-i18n="callUs">اتصل بنا</a>
        </div>
      </div>
    </header>

    <div class="share-toast" id="shareToast" role="status" aria-live="polite"></div>

    <main>
      <section class="hero hero-fallback" id="hero" aria-label="العروض الرئيسية">
        <button class="slider-button slider-next" id="heroNext" type="button" aria-label="الشريحة التالية">›</button>
        <div class="hero-track" id="heroTrack"></div>
        <button class="slider-button slider-prev" id="heroPrev" type="button" aria-label="الشريحة السابقة">‹</button>
        <div class="slider-dots" id="heroDots" aria-label="مؤشرات الشرائح"></div>
      </section>

      <section class="section" id="categories">
        <div class="section-heading">
          <h2 data-i18n="categories">التصنيفات</h2>
        </div>
        <div class="category-grid" id="categoryGrid"></div>
      </section>

      <section class="section" id="products">
        <div class="section-heading">
          <h2 data-i18n="products">المنتجات</h2>
        </div>
        <div class="product-grid" id="productGrid"></div>
      </section>

      <section class="section contact-section" id="contact">
        <div class="section-heading">
          <h2 data-i18n="contactInfo">معلومات التواصل</h2>
        </div>
        <div class="contact-card">
          <dl id="contactList"></dl>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <div class="footer-main">
        <a class="footer-brand" href="#" aria-label="Linky">
          <span class="brand-mark" id="footerBrandMark">L</span>
          <img id="footerLogo" class="brand-logo" src="" alt="" hidden />
          <span>
            <strong id="footerTitle"></strong>
            <small id="footerSlogan"></small>
          </span>
        </a>
        <div class="social-links" id="socialLinks" aria-label="روابط التواصل الاجتماعي"></div>
      </div>
      <small><span id="year"></span> © <span id="rightsText"></span></small>
    </footer>
    <div class="linky-signature"><span id="linkyCreditText"></span> <a href="{{ url('/') }}" target="_blank" rel="noopener">Linky</a></div>

    <div class="item-modal" id="itemModal" aria-hidden="true" hidden>
      <div class="item-modal-backdrop" id="itemModalBackdrop"></div>
      <div class="item-modal-panel" role="dialog" aria-modal="true" aria-labelledby="itemModalTitle">
        <button class="item-modal-close" id="itemModalClose" type="button" aria-label="إغلاق">×</button>
        <div class="item-modal-media">
          <img id="itemModalImage" src="" alt="" hidden />
          <div class="item-modal-image-fallback" id="itemModalImageFallback" hidden>
            <span class="template-placeholder-icon" aria-hidden="true"></span>
          </div>
        </div>
        <div class="item-modal-content">
          <span class="item-modal-category" id="itemModalCategory"></span>
          <h2 id="itemModalTitle"></h2>
          <p id="itemModalDescription"></p>
          <strong class="item-modal-price" id="itemModalPrice"></strong>
          <p class="item-modal-unavailable" id="itemModalUnavailable" hidden></p>
          <button class="add-button item-modal-order" id="itemModalOrder" type="button"></button>
        </div>
      </div>
    </div>

    <script src="{{ asset('templates/restaurant/rest3/script.js') }}?v={{ filemtime(public_path('templates/restaurant/rest3/script.js')) }}"></script>
  </body>
</html>
