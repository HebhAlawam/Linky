<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>{{ $pageData['page']['title']['ar'] ?? $pageData['page']['title']['en'] ?? 'Linky' }}</title>
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
    <link rel="icon" href="{{ $pageLogoFavicon }}">
  @else
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  @endif
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
  <link rel="stylesheet" href="{{ asset('templates/restaurant/rest1/style.css') }}" />
  @php
    $settings = $pageData['page']['settings'] ?? [];
    $primarySetting = is_string($settings['primary_color'] ?? null) ? trim($settings['primary_color']) : '';

    if (preg_match('/^#[0-9A-Fa-f]{3}$/', $primarySetting)) {
        $primary = '#' . $primarySetting[1] . $primarySetting[1] . $primarySetting[2] . $primarySetting[2] . $primarySetting[3] . $primarySetting[3];
    } elseif (preg_match('/^#[0-9A-Fa-f]{6}$/', $primarySetting)) {
        $primary = $primarySetting;
    } else {
        $primary = '#c94c4c';
    }

    $primary = strtolower($primary);
    $hexToRgb = fn ($hex) => [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    $darken = fn ($rgb, $amount) => array_map(fn ($value) => max(0, (int) round($value * (1 - $amount))), $rgb);
    $rgbString = fn ($rgb) => implode(', ', $rgb);

    $primaryRgb = $hexToRgb($primary);
    $primaryHoverRgb = $darken($primaryRgb, 0.10);
    $primaryDarkRgb = $darken($primaryRgb, 0.25);
  @endphp
  <style>
    :root {
      --primary-rgb: {{ $rgbString($primaryRgb) }};
      --primary: rgb(var(--primary-rgb));
      --primary-hover: rgb({{ $rgbString($primaryHoverRgb) }});
      --primary-dark: rgb({{ $rgbString($primaryDarkRgb) }});
    }
  </style>
</head>
<body>

  <nav class="navbar" id="navbar">
    <div class="nav-container">
      <button type="button" class="nav-brand" onclick="scrollToSection('home')" id="navBrand" aria-label="Home"></button>

      <div class="nav-menu" id="navMenu">
        <button onclick="scrollToSection('home')" data-i18n="nav.home">الرئيسية</button>
        <button onclick="scrollToSection('menu')" data-i18n="nav.menu">القائمة</button>
        <button onclick="scrollToSection('hours')" data-i18n="nav.hours">مواعيد العمل</button>
        <button onclick="scrollToSection('contact')" data-i18n="nav.contact">تواصل معنا</button>
      </div>

      <div class="nav-actions">
        <button class="btn-order" onclick="scrollToSection('menu')" data-i18n="nav.order">اطلب الآن</button>
        <button class="cart-trigger" id="cartTrigger" type="button" hidden><i class="ti ti-shopping-cart" aria-hidden="true"></i><span class="cart-count" id="cartCount" hidden></span></button>
        <button class="btn-share" type="button" onclick="shareSite()" aria-label="Share site"><i class="ti ti-share-2" aria-hidden="true"></i></button>
        <button class="btn-lang" id="langToggle" onclick="toggleLang()" data-i18n="nav.langToggle">English</button>
      </div>

      <div class="nav-mobile-right">
        <button class="cart-trigger" id="cartTriggerMobile" type="button" hidden><i class="ti ti-shopping-cart" aria-hidden="true"></i><span class="cart-count" id="cartCountMobile" hidden></span></button>
        <button class="btn-share" type="button" onclick="shareSite()" aria-label="Share site"><i class="ti ti-share-2" aria-hidden="true"></i></button>
        <button class="btn-lang" id="langToggleMobile" onclick="toggleLang()" data-i18n="nav.langToggle">English</button>
        <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()" aria-label="Menu"><span></span><span></span><span></span></button>
      </div>
    </div>

    <div class="mobile-menu" id="mobileMenu">
      <button onclick="scrollToSection('home')" data-i18n="nav.home">الرئيسية</button>
      <button onclick="scrollToSection('menu')" data-i18n="nav.menu">القائمة</button>
      <button onclick="scrollToSection('hours')" data-i18n="nav.hours">مواعيد العمل</button>
      <button onclick="scrollToSection('contact')" data-i18n="nav.contact">تواصل معنا</button>
      <button class="btn-order" onclick="scrollToSection('menu')" data-i18n="nav.order">اطلب الآن</button>
    </div>
  </nav>
  <div class="share-toast" id="shareToast" role="status" aria-live="polite"></div>

  <section id="home" class="hero">
    <div class="hero-slides" id="heroSlides"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1 class="hero-title" id="heroTitle"></h1>
      <p class="hero-subtitle" id="heroSubtitle"></p>
      <div class="hero-buttons">
        <button class="btn-primary" onclick="scrollToSection('menu')" data-i18n="hero.viewMenu">عرض القائمة</button>
        <button class="btn-outline" onclick="scrollToSection('contact')" data-i18n="hero.contactUs">تواصل معنا</button>
      </div>
    </div>
    <div class="hero-dots" id="heroDots"></div>
  </section>

  <div class="dish-overlay" id="dishOverlay">
    <div class="dish-overlay-inner">
      <button class="dish-close" id="dishClose" onclick="closeDish()">&#x2715;</button>
      <div class="dish-hero-img"><img id="dishImg" src="{{ asset('images/defaults/item.svg') }}" alt="" /><span class="dish-badge" id="dishBadge"></span></div>
      <div class="dish-body">
        <div class="dish-info"><h2 id="dishName"></h2><div class="dish-divider"></div><p id="dishDesc"></p></div>
        <div class="dish-order-panel">
          <div class="dish-price-block"><span class="dish-price-label" data-i18n="dishDetail.priceLabel">السعر</span><span class="dish-price" id="dishPrice"></span></div>
          <div class="cart-quantity" id="itemQuantityControl">
            <button type="button" id="itemQtyMinus" aria-label="تقليل الكمية">−</button>
            <span id="itemQuantity">1</span>
            <button type="button" id="itemQtyPlus" aria-label="زيادة الكمية">+</button>
          </div>
          <button class="btn-primary dish-order-btn" id="itemAddToCart" type="button">أضف إلى السلة</button>
          <div class="cart-add-feedback" id="itemCartFeedback" role="status" aria-live="polite" hidden>
            <span id="itemCartFeedbackText"></span>
            <button type="button" id="itemViewCart"></button>
          </div>
          <p class="item-modal-unavailable" id="itemCartUnavailable" hidden></p>
          <button class="dish-back-link" onclick="closeDish()" data-i18n="dishDetail.backToMenu">العودة للقائمة</button>
        </div>
      </div>
    </div>
  </div>

  <section id="menu" class="section menu-section">
    <div class="container"><div class="section-header reveal"><h2 class="section-title" data-i18n="menu.title">القائمة</h2><div class="section-divider"></div><div class="filter-buttons" id="filterButtons"></div></div><div class="menu-grid" id="menuGrid"></div></div>
  </section>

  <section id="hours" class="section hours-section">
    <div class="hours-glow"></div>
    <div class="container">
      <div class="hours-inner reveal">
        <div class="hours-left"><h2 class="section-title" data-i18n="hours.title">مواعيد العمل</h2><div class="section-divider left"></div><p class="hours-sub" data-i18n="hours.subtitle">يسعدنا استقبالكم لتجربة مميزة.</p></div>
        <div class="hours-card">
          <div class="hours-card-header"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><h3 data-i18n="hours.serviceTimes">أوقات الخدمة</h3></div>
          <ul class="hours-list">
            <li><span data-i18n="hours.monThu">الإثنين - الخميس</span><span class="hours-time" data-i18n="hours.monThuTime">9ص - 11م</span></li>
            <li><span data-i18n="hours.friSat">الجمعة - السبت</span><span class="hours-time" data-i18n="hours.friSatTime">9ص - 1ص</span></li>
            <li><span data-i18n="hours.sunday">الأحد</span><span class="hours-time closed" data-i18n="hours.closed">مغلق</span></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section id="contact" class="section contact-section">
    <div class="container">
      <div class="section-header reveal"><h2 class="section-title" data-i18n="contact.title">الحجوزات والاستفسارات</h2><div class="section-divider"></div><p class="section-sub" data-i18n="contact.subtitle">نرحب بتواصلكم للحجز أو الاستفسار.</p></div>
      <div class="contact-grid reveal">
        <div class="contact-card"><div class="contact-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg></div><h3 data-i18n="contact.locationTitle">الموقع</h3><p id="contactAddress"></p></div>
        <div class="contact-card"><div class="contact-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg></div><h3 data-i18n="contact.contactTitle">تواصل</h3><div class="contact-links" id="contactLinks"></div></div>
        <div class="contact-card"><div class="contact-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/></svg></div><h3 data-i18n="contact.followTitle">تابعنا</h3><p data-i18n="contact.followSubtitle">تابع أحدث أخبارنا وعروضنا.</p><div class="social-icons" id="socialLinks"></div></div>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="footer-inner"><div class="footer-brand"><div class="footer-logo" id="footerMainLogo"></div><p id="footerSloganText"></p></div><div class="footer-copy">&copy; <span id="year"></span> <span id="footerBrandName"></span><span data-i18n="footer.copyright">جميع الحقوق محفوظة.</span></div></div>
    <button class="back-to-top" id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top">
        <i class="ti ti-chevron-up"></i>
    </button>
  </footer>
  <div class="linky-signature"><span id="linkyCreditText"></span> <a href="{{ url('/') }}" target="_blank" rel="noopener">Linky</a></div>

  <div class="cart-drawer" id="cartDrawer" aria-hidden="true" hidden>
    <div class="cart-overlay" id="cartOverlay"></div>
    <aside class="cart-panel" id="cartPanel" role="dialog" aria-modal="true" aria-labelledby="cartTitle">
      <div class="cart-header">
        <h2 id="cartTitle"></h2>
        <button type="button" class="cart-close" id="cartClose" aria-label="إغلاق">×</button>
      </div>
      <div class="cart-body">
        <p class="cart-empty" id="cartEmpty"></p>
        <div class="cart-items" id="cartItems"></div>
        <button type="button" class="cart-clear" id="cartClear"></button>
        <label class="cart-note-label" for="cartNote" id="cartNoteLabel"></label>
        <textarea id="cartNote" class="cart-note" rows="3" maxlength="1000"></textarea>
      </div>
      <div class="cart-footer">
        <div class="cart-summary" id="cartSummary"></div>
        <button type="button" class="btn-primary cart-submit" id="cartSubmit"></button>
      </div>
    </aside>
  </div>

  <script>
    window.APP = { lang: 'ar', data: @json($pageData) };
    const T = {
      en: {
        nav: { home: 'Home', menu: 'Menu', hours: 'Hours', contact: 'Contact', order: 'Order', langToggle: 'العربية' },
        hero: { viewMenu: 'View Menu', contactUs: 'Contact Us' },
        menu: { title: 'Menu', order: 'View details', all: 'All' },
        hours: { title: 'Opening Hours', subtitle: 'Join us for an unforgettable dining experience.', serviceTimes: 'Service Times', monThu: 'Mon-Thu', friSat: 'Fri-Sat', sunday: 'Sunday', closed: 'Closed', monThuTime: '9AM - 11PM', friSatTime: '9AM - 1AM' },
        contact: { title: 'Reservations & Inquiries', subtitle: 'We invite you to reach out for bookings or special requests.', locationTitle: 'Location', contactTitle: 'Contact', followTitle: 'Follow Us', followSubtitle: 'Follow our latest news and offers.', address: 'Address will be added soon.' },
        footer: { copyright: 'All rights reserved.' },
        dishDetail: { backToMenu: 'Back to Menu', orderNow: 'Order Now', priceLabel: 'Price' }
      },
      ar: {
        nav: { home: 'الرئيسية', menu: 'القائمة', hours: 'مواعيد العمل', contact: 'تواصل معنا', order: 'اطلب الآن', langToggle: 'English' },
        hero: { viewMenu: 'عرض القائمة', contactUs: 'تواصل معنا' },
        menu: { title: 'القائمة', order: 'عرض التفاصيل', all: 'الكل' },
        hours: { title: 'مواعيد العمل', subtitle: 'يسعدنا استقبالكم لتجربة مميزة.', serviceTimes: 'أوقات الخدمة', monThu: 'الإثنين - الخميس', friSat: 'الجمعة - السبت', sunday: 'الأحد', closed: 'مغلق', monThuTime: '9ص - 11م', friSatTime: '9ص - 1ص' },
        contact: { title: 'الحجوزات والاستفسارات', subtitle: 'نرحب بتواصلكم للحجز أو الاستفسار.', locationTitle: 'الموقع', contactTitle: 'تواصل', followTitle: 'تابعنا', followSubtitle: 'تابع أحدث أخبارنا وعروضنا.', address: 'سيتم إضافة العنوان قريباً.' },
        footer: { copyright: 'جميع الحقوق محفوظة.' },
        dishDetail: { backToMenu: 'العودة للقائمة', orderNow: 'اطلب الآن', priceLabel: 'السعر' }
      }
    };
  </script>
  <script src="{{ asset('templates/restaurant/rest1/script.js') }}"></script>
</body>
</html>
