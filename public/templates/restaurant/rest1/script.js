'use strict';

const app = window.APP || { lang: 'ar', data: { page: {}, categories: [], links: [] } };
const data = app.data || { page: {}, categories: [], links: [] };

let lang = localStorage.getItem('user_lang') || app.lang || 'ar';
let activeCategory = 'All';
let currentSlide = 0;
let slideTimer = null;
let activeDishItem = null;
let itemQuantity = 1;

const FALLBACKS = {
  logo: '/images/defaults/logo.png',
  slogan: {
    ar: 'موقعك الاحترافي خلال دقائق',
    en: 'Your professional mini website in minutes'
  },
  itemImage: '/images/defaults/item.png'
};

function getTranslated(obj, field, fallback = '') {
  const value = obj?.[field];

  if (value && typeof value === 'object') {
    const current = value[lang];
    if (typeof current === 'string' && current.trim() !== '') return current;

    const alternate = value[lang === 'ar' ? 'en' : 'ar'];
    if (typeof alternate === 'string' && alternate.trim() !== '') return alternate;

    const first = Object.values(value).find(item => typeof item === 'string' && item.trim() !== '');
    if (first) return first;
  }

  if (typeof value === 'string' && value.trim() !== '') return value;

  return fallback;
}

function getPrice(value, withFallback = false) {
  const details = priceDetails(value);
  const price = details.discount || details.base;

  if (price || !withFallback) return price;

  return lang === 'ar' ? 'السعر عند الطلب' : 'Price on request';
}

function priceDetails(value, locale = lang) {
  if (typeof value === 'string') {
    return { base: value.trim(), discount: '' };
  }

  if (!value || typeof value !== 'object') {
    return { base: '', discount: '' };
  }

  const selectedLocale = [locale, locale === 'ar' ? 'en' : 'ar']
    .find(candidate => (typeof value[candidate] === 'string' || typeof value[candidate] === 'number')
      && String(value[candidate]).trim() !== '');
  const base = selectedLocale ? String(value[selectedLocale]).trim() : '';
  const rawDiscount = selectedLocale && value.discount_price && typeof value.discount_price === 'object'
    ? value.discount_price[selectedLocale]
    : '';
  const discount = typeof rawDiscount === 'string' || typeof rawDiscount === 'number'
    ? String(rawDiscount).trim()
    : '';
  const hasValidDiscount = base !== '' && discount !== '';

  return { base, discount: hasValidDiscount ? discount : '' };
}

function priceMarkup(value, withFallback = true) {
  const details = priceDetails(value);

  if (details.discount) {
    return `<span class="price-original">${escapeHtml(details.base)}</span><span class="price-discount">${escapeHtml(details.discount)}</span>`;
  }

  const price = details.base || (withFallback ? getPrice(value, true) : '');
  return price ? `<span class="price-current">${escapeHtml(price)}</span>` : '';
}

function pageTitle() {
  return getTranslated(data.page, 'title', 'Linky');
}

function pageSlogan() {
  return getTranslated(data.page, 'slogan', FALLBACKS.slogan[lang]);
}

function t(keyPath) {
  return keyPath.split('.').reduce((obj, key) => obj?.[key], T?.[lang]) || '';
}

function allItems() {
  return (data.categories || []).flatMap(category =>
    (category.items || []).map(item => ({ ...item, category_id: category.id }))
  );
}

function sortItemsGlobally(items) {
  return [...items].sort((a, b) => {
    const featured = featuredRank(b) - featuredRank(a);
    if (featured !== 0) return featured;

    const order = Number(a.display_order || 0) - Number(b.display_order || 0);
    if (order !== 0) return order;

    return Number(a.id || 0) - Number(b.id || 0);
  });
}

function featuredRank(item) {
  return item?.is_featured === true || item?.is_featured === 1 || item?.is_featured === '1' ? 1 : 0;
}

document.addEventListener('DOMContentLoaded', () => {
  syncDirection();
  buildHeroSlides();
  buildHeroDots();
  startSlider();
  applyTranslations();
  renderFilters();
  renderMenu();
  renderLinks();
  initNavbarScroll();
  initScrollReveal();
  initDishOverlayClose();
  initCart();
});

function syncDirection() {
  const html = document.documentElement;
  html.setAttribute('lang', lang);
  html.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');
  document.body.classList.toggle('lang-ar', lang === 'ar');
}

function toggleLang() {
  lang = lang === 'en' ? 'ar' : 'en';
  localStorage.setItem('user_lang', lang);
  syncDirection();
  applyTranslations();
  renderFilters();
  renderMenu();
  renderLinks();
  updateHeroText();
  renderCart();
  if (activeDishItem) renderItemCartActions();
}

function applyTranslations() {
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const val = t(el.getAttribute('data-i18n'));
    if (val) el.textContent = val;
  });

  const brand = pageTitle();
  const slogan = pageSlogan();

  renderBrand(brand);
  setText('footerMainLogo', brand);
  setText('footerSloganText', slogan);
  setText('footerBrandName', `${brand} `);
  setText('linkyCreditText', lang === 'ar' ? 'طُوّر بواسطة' : 'powered by');
  document.querySelector('.linky-signature a')?.replaceChildren(document.createTextNode(lang === 'ar' ? 'لينكـي' : 'Linky'));
  setText('contactAddress', currentAddress());
  applyHoursSettings();
  setText('year', new Date().getFullYear());

  document.title = brand || 'Linky';
}


function applyHoursSettings() {
  const hoursSection = document.getElementById('hours');
  const hoursList = document.querySelector('.hours-list');
  const hoursButtons = document.querySelectorAll('[data-i18n="nav.hours"]');
  const customHours = currentHours();
  const hasHours = customHours !== '';

  if (hoursSection) hoursSection.hidden = !hasHours;
  hoursButtons.forEach(button => { button.hidden = !hasHours; });

  if (!hoursList || !hasHours) return;

  hoursList.innerHTML = `<li><span>${escapeHtml(customHours).replace(/\n/g, '<br>')}</span></li>`;
}

function currentHours() {
  const hours = data.page?.settings?.hours;
  const value = hours && typeof hours === 'object' ? hours[lang] : hours;

  return typeof value === 'string' ? value.trim() : '';
}

function renderBrand(brand) {
  const el = document.getElementById('navBrand');
  if (!el) return;

  const logoUrl = usableLogoUrl();
  el.innerHTML = '';
  el.setAttribute('aria-label', brand || 'Home');

  if (!logoUrl) {
    el.textContent = brand || 'Linky';
    el.classList.remove('has-logo');
    return;
  }

  const img = document.createElement('img');
  img.src = logoUrl;
  img.alt = brand || 'Logo';
  img.loading = 'eager';
  img.decoding = 'async';
  img.addEventListener('error', () => {
    el.innerHTML = '';
    el.textContent = brand || 'Linky';
    el.classList.remove('has-logo');
  }, { once: true });

  el.classList.add('has-logo');
  el.appendChild(img);
}

function usableLogoUrl() {
  const url = String(data.page?.logo_url || '').trim();
  if (!url || url === '#') return '';
  if (url.includes('/images/defaults/logo.png')) return '';

  return url;
}

function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value || '';
}

function renderFilters() {
  const container = document.getElementById('filterButtons');
  if (!container) return;

  let html = `<button class="filter-btn ${activeCategory === 'All' ? 'active' : ''}" onclick="setCategory('All')">${t('menu.all')}</button>`;

  html += (data.categories || []).map(category => {
    const name = getTranslated(category, 'name');
    return `<button class="filter-btn ${category.id == activeCategory ? 'active' : ''}" onclick="setCategory('${category.id}')">${escapeHtml(name)}</button>`;
  }).join('');

  container.innerHTML = html;
}

function setCategory(id) {
  activeCategory = id;
  renderFilters();
  renderMenu();
}

function renderMenu() {
  const grid = document.getElementById('menuGrid');
  if (!grid) return;

  const items = activeCategory === 'All'
    ? sortItemsGlobally(allItems())
    : allItems().filter(item => item.category_id == activeCategory);

  grid.innerHTML = items.map((item, idx) => {
    const title = getTranslated(item, 'title');
    const desc = getTranslated(item, 'short_description', getTranslated(item, 'description'));
    const image = item.image_url || FALLBACKS.itemImage;
    const hasRealImage = image && !isDefaultItemImage(image);
    const price = priceMarkup(item.price, true);

    return `
      <div class="menu-card" onclick="openDish('${item.id}')" style="animation-delay:${idx * 0.05}s">
        <div class="menu-card-img">
          ${hasRealImage
            ? `<img src="${escapeAttr(image)}" alt="${escapeAttr(title)}" loading="lazy" />`
            : `<div class="template-placeholder-box item-placeholder"><span class="template-placeholder-icon item" aria-hidden="true"></span></div>`}
          <div class="menu-card-hover-label">${escapeHtml(t('menu.order'))}</div>
        </div>
        <div class="menu-card-body">
          <div class="menu-card-top">
            <h3>${escapeHtml(title)}</h3>
            <span class="menu-card-price">${price}</span>
          </div>
          <p>${escapeHtml(desc)}</p>
        </div>
      </div>`;
  }).join('');
}

function isDefaultItemImage(url) {
  return typeof url === 'string' && /\/images\/defaults\/item[^/]*\.(svg|png)$/i.test(url);
}

function renderLinks() {
  const contactLinks = document.getElementById('contactLinks');
  const socialLinks = document.getElementById('socialLinks');
  const links = validLinks();
  const contactTypes = ['phone', 'whatsapp', 'email', 'map', 'website', 'custom'];
  const contactItems = links.filter(link => contactTypes.includes(link.type));
  const socialItems = links.filter(link => link.type === 'social');

  if (contactLinks) {
    contactLinks.innerHTML = contactItems
      .map(link => {
        const icon = iconHtml(link);
        const title = escapeHtml(linkTitle(link));
        return `<a href="${escapeAttr(link.url)}" target="_blank" rel="noopener" onclick="trackLinkClick('${escapeAttr(link.id)}')">${icon}<span>${title}</span></a>`;
      })
      .join('');
  }

  if (socialLinks) {
    socialLinks.innerHTML = socialItems
      .map(link => `<a href="${escapeAttr(link.url)}" target="_blank" rel="noopener" aria-label="${escapeAttr(linkTitle(link))}" onclick="trackLinkClick('${escapeAttr(link.id)}')">${iconHtml(link, 'ti ti-share')}</a>`)
      .join('');
  }

  updateContactSection(contactItems, socialItems);
}

function validLinks() {
  return (data.links || []).filter(link => String(link?.url || '').trim() !== '');
}

function linkTitle(link) {
  return getTranslated(link, 'title', fallbackLinkTitle(link?.type));
}

function fallbackLinkTitle(type) {
  const labels = {
    whatsapp: { ar: 'واتساب', en: 'WhatsApp' },
    phone: { ar: 'اتصال', en: 'Call' },
    email: { ar: 'بريد إلكتروني', en: 'Email' },
    map: { ar: 'الموقع', en: 'Location' },
    website: { ar: 'الموقع الإلكتروني', en: 'Website' },
    custom: { ar: 'رابط', en: 'Link' },
    social: { ar: 'تابعنا', en: 'Follow' }
  };

  return labels[type]?.[lang] || labels.custom[lang];
}

function currentAddress() {
  const address = data.page?.settings?.address;
  const value = address && typeof address === 'object' ? address[lang] : address;

  return typeof value === 'string' ? value.trim() : '';
}

function setCardVisible(childId, visible) {
  const child = document.getElementById(childId);
  const card = child?.closest('.contact-card');
  if (card) card.hidden = !visible;
}

function updateContactSection(contactItems, socialItems) {
  const address = currentAddress();
  const hasAddress = address !== '';
  const hasContactLinks = contactItems.length > 0;
  const hasSocialLinks = socialItems.length > 0;
  const contactLinks = document.getElementById('contactLinks');

  setText('contactAddress', address);
  setCardVisible('contactAddress', hasAddress);
  setCardVisible('contactLinks', true);
  setCardVisible('socialLinks', hasSocialLinks);

  if (contactLinks && !hasContactLinks) {
    contactLinks.innerHTML = `<p class="contact-fallback">${escapeHtml(contactFallbackMessage())}</p>`;
  }

  updateContactGridCount();

  const section = document.getElementById('contact');
  if (section) section.hidden = false;
}


function updateContactGridCount() {
  const grid = document.querySelector('.contact-grid');
  if (!grid) return;

  const visibleCount = Array.from(grid.querySelectorAll('.contact-card'))
    .filter(card => !card.hidden)
    .length;
  const count = Math.max(1, Math.min(3, visibleCount));

  grid.classList.remove('cards-count-1', 'cards-count-2', 'cards-count-3');
  grid.classList.add(`cards-count-${count}`);
}

function contactFallbackMessage() {
  return lang === 'ar'
    ? 'سيتم إضافة وسائل التواصل قريبًا.'
    : 'Contact details will be added soon.';
}

function iconHtml(link, fallback = null) {
  const iconClass = sanitizeIconClass(link?.icon) || fallback || fallbackIconClass(link?.type);
  return `<i class="${escapeAttr(iconClass)}" aria-hidden="true"></i>`;
}

function sanitizeIconClass(value) {
  const iconClass = String(value || '').trim().replace(/\s+/g, ' ');
  if (!iconClass || !/^[A-Za-z0-9_\-\s]+$/.test(iconClass)) return '';

  return iconClass;
}

function fallbackIconClass(type) {
  const icons = {
    whatsapp: 'ti ti-brand-whatsapp',
    phone: 'ti ti-phone',
    email: 'ti ti-mail',
    map: 'ti ti-map-pin',
    website: 'ti ti-world',
    social: 'ti ti-share',
    custom: 'ti ti-link'
  };

  return icons[type] || icons.custom;
}

function buildHeroSlides() {
  const container = document.getElementById('heroSlides');
  const hero = document.getElementById('home');
  const dotsEl = document.getElementById('heroDots');
  if (!container) return;

  const images = heroImages().slice(0, 3);
  currentSlide = 0;

  if (!images.length) {
    container.innerHTML = '';
    container.hidden = true;
    if (dotsEl) {
      dotsEl.innerHTML = '';
      dotsEl.hidden = true;
    }
    hero?.classList.add('hero-no-image');
    return;
  }

  container.hidden = false;
  if (dotsEl) dotsEl.hidden = false;
  hero?.classList.remove('hero-no-image');

  container.innerHTML = images.map((src, i) =>
    `<div class="hero-slide${i === 0 ? ' active' : ''}" style="background-image:url('${escapeAttr(src)}')"></div>`
  ).join('');
}

function heroImages() {
  const rawImages = Array.isArray(data.page?.settings?.hero_images)
    ? data.page.settings.hero_images
    : [];

  const images = rawImages
    .map(image => {
      if (typeof image === 'string') return image;
      if (image && typeof image === 'object') return image.url || image.image_url || image.src || '';
      return '';
    })
    .map(image => String(image || '').trim())
    .filter(Boolean);

  return images;
}

function buildHeroDots() {
  const dotsEl = document.getElementById('heroDots');
  if (!dotsEl) return;

  const slideCount = document.querySelectorAll('.hero-slide').length;
  dotsEl.hidden = slideCount <= 1;
  dotsEl.innerHTML = Array.from({ length: slideCount }, (_, i) =>
    `<button class="hero-dot${i === 0 ? ' active' : ''}" onclick="goToSlide(${i})"></button>`
  ).join('');
}

function updateHeroText() {
  const titleEl = document.getElementById('heroTitle');
  const subEl = document.getElementById('heroSubtitle');
  if (!titleEl || !subEl) return;

  titleEl.style.opacity = '0';
  subEl.style.opacity = '0';

  setTimeout(() => {
    titleEl.textContent = pageTitle();
    subEl.textContent = pageSlogan();
    titleEl.style.opacity = '1';
    subEl.style.opacity = '1';
  }, 200);
}

function goToSlide(idx) {
  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.hero-dot');
  if (!slides.length) return;

  const nextSlide = ((idx % slides.length) + slides.length) % slides.length;
  slides[currentSlide]?.classList.remove('active');
  dots[currentSlide]?.classList.remove('active');
  currentSlide = nextSlide;
  slides[currentSlide]?.classList.add('active');
  dots[currentSlide]?.classList.add('active');
  updateHeroText();
}

function startSlider() {
  updateHeroText();
  if (slideTimer) clearInterval(slideTimer);

  const slideCount = document.querySelectorAll('.hero-slide').length;
  if (slideCount <= 1) return;

  slideTimer = setInterval(() => goToSlide(currentSlide + 1), 4500);
}

function openDish(id) {
  const item = allItems().find(menuItem => menuItem.id == id);
  if (!item) return;
  activeDishItem = item;
  const existingCartItem = cart.items.find(entry => String(entry.id) === String(item.id));
  itemQuantity = existingCartItem ? existingCartItem.quantity : 1;
  const feedback = document.getElementById('itemCartFeedback');
  if (feedback) feedback.hidden = true;

  const category = (data.categories || []).find(cat => cat.id == item.category_id);
  const title = getTranslated(item, 'title');
  const desc = getTranslated(item, 'description', getTranslated(item, 'short_description'));
  const catName = getTranslated(category, 'name');
  const price = getPrice(item.price, true);

  document.getElementById('dishImg').src = item.image_url || FALLBACKS.itemImage;
  document.getElementById('dishName').textContent = title;
  document.getElementById('dishDesc').textContent = desc;
  document.getElementById('dishPrice').innerHTML = priceMarkup(item.price, true);
  document.getElementById('dishBadge').textContent = catName;

  renderItemCartActions();

  document.getElementById('dishOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function trackLinkClick(id) {
  if (!id) return;

  trackPublicClick(`/track/link/${encodeURIComponent(id)}`);
}

function trackItemOrderClick(id) {
  if (!id) return;

  trackPublicClick(`/track/item-order/${encodeURIComponent(id)}`);
}

function trackPublicClick(url) {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  if (navigator.sendBeacon) {
    const payload = new FormData();
    if (token) payload.append('_token', token);

    if (navigator.sendBeacon(url, payload)) {
      return;
    }
  }

  fetch(url, {
    method: 'POST',
    keepalive: true,
    headers: {
      'Accept': 'application/json',
      ...(token ? { 'X-CSRF-TOKEN': token } : {})
    }
  }).catch(() => {});
}

function buildWhatsAppUrl(rawUrl, message) {
  const value = String(rawUrl || '').trim();
  const encodedMessage = encodeURIComponent(message);

  if (value.includes('wa.me') || value.includes('api.whatsapp.com')) {
    try {
      const url = new URL(value);
      url.searchParams.set('text', message);
      return url.toString();
    } catch (error) {
      const separator = value.includes('?') ? '&' : '?';
      return `${value}${separator}text=${encodedMessage}`;
    }
  }

  const phone = normalizePhone(value);
  return phone ? `https://wa.me/${phone}?text=${encodedMessage}` : '#';
}

function normalizePhone(value) {
  const phone = String(value || '')
    .replace(/^tel:/i, '')
    .replace(/^whatsapp:/i, '')
    .replace(/[^\d+]/g, '')
    .replace(/^\+/, '');

  return phone;
}
function closeDish() {
  document.getElementById('dishOverlay').classList.remove('open');
  activeDishItem = null;
  if (!document.getElementById('cartDrawer')?.classList.contains('open')) document.body.style.overflow = '';
}

function initDishOverlayClose() {
  const overlay = document.getElementById('dishOverlay');
  if (!overlay) return;
  overlay.addEventListener('click', e => { if (e.target === overlay) closeDish(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDish(); });
}

function toggleMobileMenu() {
  document.getElementById('mobileMenu')?.classList.toggle('open');
  document.getElementById('hamburger')?.classList.toggle('open');
}

function scrollToSection(id) {
  const el = document.getElementById(id);
  if (!el) return;

  el.scrollIntoView({ behavior: 'smooth' });
  document.getElementById('mobileMenu')?.classList.remove('open');
  document.getElementById('hamburger')?.classList.remove('open');
}

function initNavbarScroll() {
  const nav = document.getElementById('navbar');
  if (!nav) return;
  window.addEventListener('scroll', () => nav.classList.toggle('scrolled', window.scrollY > 60));
}

function initScrollReveal() {
  const els = document.querySelectorAll('.reveal');
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  els.forEach(el => obs.observe(el));
}

const CART_TEXT = {
  ar: {
    title: 'سلة التسوق', empty: 'سلتك فارغة', clear: 'إفراغ السلة', notes: 'ملاحظات على الطلب',
    add: 'أضف إلى السلة', update: 'تحديث السلة', added: 'تمت الإضافة إلى السلة', updated: 'تم تحديث السلة', view: 'عرض السلة',
    remove: 'إزالة', total: 'المجموع', submit: 'إرسال الطلب عبر واتساب',
    unavailable: 'لا توجد وسيلة طلب عبر واتساب متاحة حاليًا.', close: 'إغلاق',
    confirmTotal: 'يتم تأكيد المجموع النهائي عبر واتساب.',
    decrease: 'تقليل الكمية', increase: 'زيادة الكمية', cart: 'فتح سلة التسوق'
  },
  en: {
    title: 'Shopping Cart', empty: 'Your cart is empty', clear: 'Clear cart', notes: 'Order notes',
    add: 'Add to cart', update: 'Update cart', added: 'Added to cart', updated: 'Cart updated', view: 'View cart',
    remove: 'Remove', total: 'Total', submit: 'Send order via WhatsApp',
    unavailable: 'WhatsApp ordering is not available right now.', close: 'Close',
    confirmTotal: 'The final total will be confirmed via WhatsApp.',
    decrease: 'Decrease quantity', increase: 'Increase quantity', cart: 'Open shopping cart'
  }
};

let cart = { items: [], note: '' };
let cartOpener = null;
const cartStorageKey = `linky_cart_v1_${String(data.page?.id || data.page?.slug || window.location.pathname).replace(/[^a-zA-Z0-9_-]/g, '_')}`;

function cartWhatsappLink() {
  return (data.links || []).find(link => link.type === 'whatsapp'
    && buildWhatsAppUrl(link.url, '') !== '#');
}

function safeLoadCart() {
  try {
    const parsed = JSON.parse(localStorage.getItem(cartStorageKey) || '{}');
    return { items: Array.isArray(parsed.items) ? parsed.items : [], note: typeof parsed.note === 'string' ? parsed.note : '' };
  } catch (error) {
    return { items: [], note: '' };
  }
}

function safeSaveCart() {
  try { localStorage.setItem(cartStorageKey, JSON.stringify(cart)); } catch (error) {}
}

function cleanCart() {
  const available = new Set(allItems().map(item => String(item.id)));
  const merged = new Map();
  cart.items.forEach(entry => {
    const id = String(entry?.id ?? '');
    const quantity = Number(entry?.quantity);
    if (!available.has(id) || !Number.isInteger(quantity) || quantity < 1) return;
    merged.set(id, Math.min(99, (merged.get(id) || 0) + quantity));
  });
  cart.items = [...merged].map(([id, quantity]) => ({ id, quantity }));
  cart.note = typeof cart.note === 'string' ? cart.note.slice(0, 1000) : '';
  if (!cart.items.length) cart.note = '';
  safeSaveCart();
}

function cartItem(id) {
  return allItems().find(item => String(item.id) === String(id));
}

function effectivePrice(item) {
  const details = priceDetails(item?.price);
  return details.discount || details.base || '';
}

function parseCartPrice(text) {
  const normalized = String(text || '').trim().replace(/[٠-٩۰-۹]/g, digit => {
    const arabic = '٠١٢٣٤٥٦٧٨٩'.indexOf(digit);
    return String(arabic >= 0 ? arabic : '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit));
  });
  const match = normalized.match(/\d+(?:[\s\u00a0,،٬.٫]\d+)*/u);
  if (!match) return null;
  let numeric = match[0].replace(/[\s\u00a0]/gu, '').replace(/[،٬]/gu, ',').replace(/٫/gu, '.');
  const comma = numeric.lastIndexOf(',');
  const dot = numeric.lastIndexOf('.');
  if (comma >= 0 && dot >= 0) {
    const decimal = comma > dot ? ',' : '.';
    const thousands = decimal === ',' ? '.' : ',';
    numeric = numeric.replaceAll(thousands, '').replace(decimal, '.');
  } else {
    const separator = comma >= 0 ? ',' : dot >= 0 ? '.' : '';
    if (separator) {
      const parts = numeric.split(separator);
      numeric = parts.length === 2 && parts[1].length <= 2 ? `${parts[0]}.${parts[1]}` : parts.join('');
    }
  }
  const amount = Number(numeric);
  if (!Number.isFinite(amount)) return null;
  const label = `${normalized.slice(0, match.index)}${normalized.slice((match.index || 0) + match[0].length)}`.trim();
  const normalizedLabel = label.toLowerCase().replace(/[\s.,،٬٫\-_/\\()[\]{}]+/gu, '');
  return { amount, label, normalizedLabel };
}

function formattedAmount(amount, label) {
  const number = new Intl.NumberFormat(lang === 'ar' ? 'ar' : 'en', { maximumFractionDigits: 2 }).format(amount);
  return label ? `${number} ${label}` : number;
}

function cartRows() {
  return cart.items.map(entry => {
    const item = cartItem(entry.id);
    if (!item) return null;
    const price = effectivePrice(item);
    const parsed = parseCartPrice(price);
    return { entry, item, price, parsed, subtotal: parsed ? parsed.amount * entry.quantity : null };
  }).filter(Boolean);
}

function cartTotal(rows) {
  if (!rows.length || rows.some(row => !row.parsed)) return null;
  const label = rows[0].parsed.normalizedLabel;
  if (rows.some(row => row.parsed.normalizedLabel !== label)) return null;
  return {
    amount: rows.reduce((sum, row) => sum + row.subtotal, 0),
    label: rows[0].parsed.label
  };
}

function renderItemCartActions() {
  const enabled = Boolean(cartWhatsappLink());
  const control = document.getElementById('itemQuantityControl');
  const add = document.getElementById('itemAddToCart');
  const unavailable = document.getElementById('itemCartUnavailable');
  if (control) control.hidden = !enabled;
  if (add) {
    add.hidden = !enabled;
    const exists = activeDishItem && cart.items.some(entry => String(entry.id) === String(activeDishItem.id));
    add.textContent = exists ? CART_TEXT[lang].update : CART_TEXT[lang].add;
  }
  if (unavailable) {
    unavailable.hidden = enabled;
    unavailable.textContent = CART_TEXT[lang].unavailable;
  }
  setText('itemQuantity', itemQuantity);
  document.getElementById('itemQtyMinus')?.setAttribute('aria-label', CART_TEXT[lang].decrease);
  document.getElementById('itemQtyPlus')?.setAttribute('aria-label', CART_TEXT[lang].increase);
}

function renderCart() {
  cleanCart();
  const enabled = Boolean(cartWhatsappLink());
  ['cartTrigger', 'cartTriggerMobile'].forEach(id => {
    const trigger = document.getElementById(id);
    if (!trigger) return;
    trigger.hidden = !enabled;
    trigger.setAttribute('aria-label', CART_TEXT[lang].cart);
  });
  const count = cart.items.reduce((sum, entry) => sum + entry.quantity, 0);
  ['cartCount', 'cartCountMobile'].forEach(id => {
    const badge = document.getElementById(id);
    if (!badge) return;
    badge.textContent = count;
    badge.hidden = count === 0;
  });
  setText('cartTitle', CART_TEXT[lang].title);
  setText('cartEmpty', CART_TEXT[lang].empty);
  setText('cartClear', CART_TEXT[lang].clear);
  setText('cartNoteLabel', CART_TEXT[lang].notes);
  setText('cartSubmit', CART_TEXT[lang].submit);
  document.getElementById('cartClose')?.setAttribute('aria-label', CART_TEXT[lang].close);
  const note = document.getElementById('cartNote');
  if (note && note.value !== cart.note) note.value = cart.note;
  const rows = cartRows();
  const items = document.getElementById('cartItems');
  const empty = document.getElementById('cartEmpty');
  const clear = document.getElementById('cartClear');
  if (empty) empty.hidden = rows.length > 0;
  if (clear) clear.hidden = rows.length === 0;
  if (items) items.innerHTML = rows.map(row => {
    const title = getTranslated(row.item, 'title');
    const image = row.item.image_url || FALLBACKS.itemImage;
    const subtotal = row.subtotal === null ? '' : `<small>${escapeHtml(formattedAmount(row.subtotal, row.parsed.label))}</small>`;
    return `<article class="cart-row" data-cart-id="${escapeAttr(row.entry.id)}">
      <img src="${escapeAttr(image)}" alt="" onerror="this.src='${escapeAttr(FALLBACKS.itemImage)}'">
      <div class="cart-row-main"><strong>${escapeHtml(title)}</strong><span>${escapeHtml(row.price || getPrice(row.item.price, true))}</span>${subtotal}
        <div class="cart-row-actions"><div class="cart-quantity"><button type="button" data-cart-minus aria-label="${escapeAttr(CART_TEXT[lang].decrease)}">−</button><span>${row.entry.quantity}</span><button type="button" data-cart-plus aria-label="${escapeAttr(CART_TEXT[lang].increase)}">+</button></div><button type="button" class="cart-remove" data-cart-remove>${escapeHtml(CART_TEXT[lang].remove)}</button></div>
      </div></article>`;
  }).join('');
  const total = cartTotal(rows);
  const summary = document.getElementById('cartSummary');
  if (summary) summary.textContent = !rows.length
    ? ''
    : total
      ? `${CART_TEXT[lang].total}: ${formattedAmount(total.amount, total.label)}`
      : CART_TEXT[lang].confirmTotal;
  const submit = document.getElementById('cartSubmit');
  if (submit) submit.disabled = !enabled || rows.length === 0;
}

function changeCartQuantity(id, delta) {
  const entry = cart.items.find(item => String(item.id) === String(id));
  if (!entry) return;
  const next = entry.quantity + delta;
  if (next < 1) cart.items = cart.items.filter(item => String(item.id) !== String(id));
  else entry.quantity = Math.min(99, next);
  if (!cart.items.length) cart.note = '';
  safeSaveCart();
  renderCart();
}

function addActiveItemToCart() {
  if (!activeDishItem || !cartWhatsappLink()) return;
  const id = String(activeDishItem.id);
  const existing = cart.items.find(entry => String(entry.id) === id);
  const isUpdate = Boolean(existing);
  if (existing) existing.quantity = Math.min(99, Math.max(1, itemQuantity));
  else cart.items.push({ id, quantity: itemQuantity });
  safeSaveCart();
  renderCart();
  renderItemCartActions();
  setText('itemCartFeedbackText', isUpdate ? CART_TEXT[lang].updated : CART_TEXT[lang].added);
  setText('itemViewCart', CART_TEXT[lang].view);
  const feedback = document.getElementById('itemCartFeedback');
  if (feedback) feedback.hidden = false;
}

function openCart(event) {
  if (!cartWhatsappLink()) return;
  cartOpener = event?.currentTarget?.id === 'itemViewCart'
    ? document.getElementById('cartTrigger') || document.getElementById('cartTriggerMobile')
    : event?.currentTarget || document.activeElement;
  const drawer = document.getElementById('cartDrawer');
  if (!drawer) return;
  drawer.hidden = false;
  drawer.setAttribute('aria-hidden', 'false');
  requestAnimationFrame(() => drawer.classList.add('open'));
  document.body.style.overflow = 'hidden';
  renderCart();
  document.getElementById('cartClose')?.focus();
}

function closeCart() {
  const drawer = document.getElementById('cartDrawer');
  if (!drawer) return;
  drawer.classList.remove('open');
  drawer.setAttribute('aria-hidden', 'true');
  setTimeout(() => { if (!drawer.classList.contains('open')) drawer.hidden = true; }, 200);
  if (!document.getElementById('dishOverlay')?.classList.contains('open')) document.body.style.overflow = '';
  cartOpener?.focus?.();
}

function cartMessage() {
  const rows = cartRows();
  const total = cartTotal(rows);
  const restaurant = pageTitle();
  const lines = rows.map((row, index) => {
    const linePrice = row.subtotal === null
      ? row.price || getPrice(row.item.price, true)
      : formattedAmount(row.subtotal, row.parsed.label);
    return `${index + 1}. ${getTranslated(row.item, 'title')} × ${row.entry.quantity} — ${linePrice}`;
  });
  const note = cart.note.trim();
  if (lang === 'ar') {
    return [`مرحبًا، أود طلب المنتجات التالية من ${restaurant}:`, '', ...lines,
      ...(note ? ['', 'ملاحظات:', note] : []), '',
      total ? `المجموع: ${formattedAmount(total.amount, total.label)}` : CART_TEXT.ar.confirmTotal,
      '', 'يرجى تأكيد الطلب والسعر النهائي، شكرًا.'].join('\n');
  }
  return [`Hello, I would like to order the following items from ${restaurant}:`, '', ...lines,
    ...(note ? ['', 'Notes:', note] : []), '',
    total ? `Total: ${formattedAmount(total.amount, total.label)}` : CART_TEXT.en.confirmTotal,
    '', 'Please confirm the order and final total. Thank you.'].join('\n');
}

function initCart() {
  cart = safeLoadCart();
  cleanCart();
  renderCart();
  ['cartTrigger', 'cartTriggerMobile'].forEach(id => document.getElementById(id)?.addEventListener('click', openCart));
  document.getElementById('cartClose')?.addEventListener('click', closeCart);
  document.getElementById('cartOverlay')?.addEventListener('click', closeCart);
  document.getElementById('itemQtyMinus')?.addEventListener('click', () => { itemQuantity = Math.max(1, itemQuantity - 1); renderItemCartActions(); });
  document.getElementById('itemQtyPlus')?.addEventListener('click', () => { itemQuantity = Math.min(99, itemQuantity + 1); renderItemCartActions(); });
  document.getElementById('itemAddToCart')?.addEventListener('click', addActiveItemToCart);
  document.getElementById('itemViewCart')?.addEventListener('click', event => { closeDish(); openCart(event); });
  document.getElementById('cartClear')?.addEventListener('click', () => { cart = { items: [], note: '' }; safeSaveCart(); renderCart(); });
  document.getElementById('cartNote')?.addEventListener('input', event => { cart.note = event.target.value.slice(0, 1000); safeSaveCart(); });
  document.getElementById('cartItems')?.addEventListener('click', event => {
    const row = event.target.closest('[data-cart-id]');
    if (!row) return;
    if (event.target.closest('[data-cart-minus]')) changeCartQuantity(row.dataset.cartId, -1);
    if (event.target.closest('[data-cart-plus]')) changeCartQuantity(row.dataset.cartId, 1);
    if (event.target.closest('[data-cart-remove]')) { cart.items = cart.items.filter(item => String(item.id) !== row.dataset.cartId); if (!cart.items.length) cart.note = ''; safeSaveCart(); renderCart(); }
  });
  document.getElementById('cartSubmit')?.addEventListener('click', () => {
    const whatsapp = cartWhatsappLink();
    if (!whatsapp || !cart.items.length) return;
    cart.items.forEach(entry => trackItemOrderClick(entry.id));
    window.open(buildWhatsAppUrl(whatsapp.url, cartMessage()), '_blank', 'noopener');
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && document.getElementById('cartDrawer')?.classList.contains('open')) closeCart();
  });
}

async function shareSite() {
  const shareData = {
    title: document.title || pageTitle() || 'Linky',
    url: window.location.href
  };

  try {
    if (navigator.share) {
      await navigator.share(shareData);
      showShareToast();
      return;
    }

    await copyToClipboard(shareData.url);
    showShareToast();
  } catch (error) {
    if (error?.name !== 'AbortError') {
      await copyToClipboard(shareData.url);
      showShareToast();
    }
  }
}

async function copyToClipboard(text) {
  if (navigator.clipboard?.writeText) {
    await navigator.clipboard.writeText(text);
    return;
  }

  const input = document.createElement('textarea');
  input.value = text;
  input.setAttribute('readonly', '');
  input.style.position = 'fixed';
  input.style.opacity = '0';
  document.body.appendChild(input);
  input.select();
  document.execCommand('copy');
  input.remove();
}

let shareToastTimer = null;
function showShareToast() {
  const toast = document.getElementById('shareToast');
  if (!toast) return;
  toast.textContent = lang === 'ar' ? 'تم نسخ رابط الموقع' : 'Site link copied';
  toast.classList.add('is-visible');
  clearTimeout(shareToastTimer);
  shareToastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2200);
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[char]));
}

function escapeAttr(value) {
  return escapeHtml(value).replace(/'/g, '&#039;');
}
