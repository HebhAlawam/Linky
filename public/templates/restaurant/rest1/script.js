'use strict';

const app = window.APP || { lang: 'ar', data: { page: {}, categories: [], links: [] } };
const data = app.data || { page: {}, categories: [], links: [] };

let lang = localStorage.getItem('user_lang') || app.lang || 'ar';
let activeCategory = 'All';
let currentSlide = 0;
let slideTimer = null;

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

  configureOrderLink(item, title);

  document.getElementById('dishOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function configureOrderLink(item, dishTitle) {
  const orderLink = document.getElementById('dishOrderLink');
  if (!orderLink) return;

  const whatsappLink = (data.links || []).find(link => link.type === 'whatsapp');
  const phoneLink = (data.links || []).find(link => link.type === 'phone');
  const message = lang === 'ar'
    ? `مرحبًا، أريد طلب: ${dishTitle}`
    : `Hello, I would like to order: ${dishTitle}`;

  orderLink.style.display = '';
  orderLink.removeAttribute('aria-disabled');
  orderLink.removeAttribute('target');
  orderLink.removeAttribute('rel');
  orderLink.onclick = () => {
    trackItemOrderClick(item?.id);
  };

  if (whatsappLink?.url) {
    const whatsappUrl = buildWhatsAppUrl(whatsappLink.url, message);
    if (whatsappUrl !== '#') {
      orderLink.href = whatsappUrl;
      orderLink.target = '_blank';
      orderLink.rel = 'noopener noreferrer';
      return;
    }
  }

  if (phoneLink?.url) {
    orderLink.href = phoneLink.url;
    return;
  }

  orderLink.href = '#';
  orderLink.style.display = 'none';
  orderLink.setAttribute('aria-disabled', 'true');
  orderLink.onclick = null;
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
  document.body.style.overflow = '';
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
