(() => {
  const app = window.APP || { lang: 'ar', data: {} };
  const data = app.data || {};
  const page = data.page || {};
  const settings = page.settings || {};
  const categories = Array.isArray(data.categories) ? data.categories : [];
  const links = Array.isArray(data.links) ? data.links : [];

  const T = {
    ar: {
      categories: 'التصنيفات',
      products: 'المنتجات',
      contactInfo: ' التواصل',
      all: 'الكل',
      viewDetails: 'عرض التفاصيل',
      orderNow: 'اطلب الآن',
      price: 'السعر',
      priceOnRequest: 'السعر عند الطلب',
      orderUnavailable: 'لا توجد وسيلة طلب متاحة حاليًا.',
      callUs: 'اتصل بنا',
      address: 'الموقع',
      hours: 'مواعيد العمل',
      contact: 'التواصل',
      noCategories: 'لا توجد تصنيفات بعد.',
      noProducts: 'لا توجد منتجات بعد.',
      noContact: 'سيتم إضافة معلومات التواصل قريبًا.',
      rights: 'جميع الحقوق محفوظة',
      fallbackTitle: 'Linky',
      whatsappMessage: 'مرحبًا، أريد طلب: ',
      linkFallback: {
        whatsapp: 'واتساب', phone: 'اتصال', email: 'بريد إلكتروني', map: 'الموقع',
        website: 'الموقع الإلكتروني', custom: 'رابط', social: 'تابعنا'
      }
    },
    en: {
      categories: 'Categories',
      products: 'Products',
      contactInfo: 'Contact',
      all: 'All',
      viewDetails: 'View Details',
      orderNow: 'Order Now',
      price: 'Price',
      priceOnRequest: 'Price on request',
      orderUnavailable: 'Ordering is not available right now.',
      callUs: 'Call Us',
      address: 'Address',
      hours: 'Working Hours',
      contact: 'Contact',
      noCategories: 'No categories yet.',
      noProducts: 'No products yet.',
      noContact: 'Contact information will be added soon.',
      rights: 'All rights reserved',
      fallbackTitle: 'Linky',
      whatsappMessage: 'Hello, I would like to order: ',
      linkFallback: {
        whatsapp: 'WhatsApp', phone: 'Call', email: 'Email', map: 'Location',
        website: 'Website', custom: 'Link', social: 'Follow'
      }
    }
  };

  const contactTypes = ['phone', 'whatsapp', 'email', 'map', 'website', 'custom'];
  let lang = localStorage.getItem('linky_rest3_lang') || app.lang || 'ar';
  let theme = localStorage.getItem('linky_rest3_theme') || 'light';
  let currentSlide = 0;
  let activeCategory = 'all';
  let sliderTimer = null;

  const $ = (selector) => document.querySelector(selector);
  const clean = (value) => String(value || '').trim();

  const hero = $('#hero');
  const heroTrack = $('#heroTrack');
  const heroDots = $('#heroDots');
  const heroNext = $('#heroNext');
  const heroPrev = $('#heroPrev');
  const categoryGrid = $('#categoryGrid');
  const productGrid = $('#productGrid');
  const contactList = $('#contactList');
  const socialLinksContainer = $('#socialLinks');
  const shareButton = $('#shareButton');
  const themeToggle = $('#themeToggle');
  const langToggle = $('#langToggle');
  const headerCallButton = $('#headerCallButton');
  const itemModal = $('#itemModal');
  let activeModalItem = null;

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>'"]/g, (char) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[char]));
  }

  function escapeAttr(value) {
    return escapeHtml(value).replace(/`/g, '&#096;');
  }

  function getTranslated(source, field, fallback = '') {
    const value = field ? source?.[field] : source;
    if (value == null) return fallback;
    if (typeof value === 'string') return clean(value) || fallback;
    if (typeof value === 'object') {
      return clean(value[lang]) || clean(value.ar) || clean(value.en) || clean(Object.values(value).find(Boolean)) || fallback;
    }
    return fallback;
  }

  function localizedPrice(value, withFallback = false) {
    let price = '';

    if (value && typeof value === 'object') {
      price = clean(value[lang]) || clean(value[lang === 'ar' ? 'en' : 'ar']) || clean(Object.values(value).find(Boolean));
    } else if (typeof value === 'string') {
      price = clean(value);
    }

    if (price || !withFallback) return price;

    return T[lang].priceOnRequest;
  }

  function translationValue(source, field, locale) {
    const value = source?.[field];
    if (typeof value === 'object' && value !== null) {
      return clean(value[locale]) || clean(value.ar) || clean(value.en) || clean(Object.values(value).find(Boolean));
    }
    return clean(value);
  }

  function pageTitle() {
    return getTranslated(page, 'title', T[lang].fallbackTitle);
  }

  function pageSlogan() {
    return getTranslated(page, 'slogan', '');
  }

  function validHeroImages() {
    const images = Array.isArray(settings.hero_images) ? settings.hero_images : [];
    return images.map((image) => {
      if (typeof image === 'string') return image;
      if (image && typeof image === 'object') return image.url || image.image_url || image.src || '';
      return '';
    }).map(clean).filter(Boolean).slice(0, 3);
  }

  function allItems() {
    return categories.flatMap((category) => (category.items || []).map((item) => ({ ...item, category })));
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

  function setText(selector, value) {
    const element = $(selector);
    if (element) element.textContent = value || '';
  }

  function syncLanguage() {
    const html = document.getElementById('mainHtml') || document.documentElement;
    html.lang = lang;
    html.dir = lang === 'ar' ? 'rtl' : 'ltr';
    if (langToggle) {
      langToggle.textContent = lang === 'ar' ? 'EN' : 'AR';
      langToggle.setAttribute('aria-label', lang === 'ar' ? 'Switch to English' : 'التبديل إلى العربية');
    }
    if (shareButton) {
      shareButton.setAttribute('aria-label', lang === 'ar' ? 'مشاركة الموقع' : 'Share site');
    }

    document.querySelectorAll('[data-i18n]').forEach((element) => {
      const key = element.getAttribute('data-i18n');
      element.textContent = T[lang][key] || element.textContent;
    });

    syncSliderDirection();
  }

  function applyTheme(nextTheme = theme) {
    theme = nextTheme === 'dark' ? 'dark' : 'light';
    document.documentElement.dataset.theme = theme;
    localStorage.setItem('linky_rest3_theme', theme);
    if (themeToggle) {
      themeToggle.textContent = theme === 'dark' ? '☀' : '◐';
      themeToggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    }
  }

  function renderBrand() {
    const title = pageTitle();
    const slogan = pageSlogan();
    const logo = clean(page.logo_url);
    const initial = title.charAt(0).toUpperCase() || 'L';

    setText('#brandTitle', title);
    setText('#brandSlogan', slogan);
    setText('#footerTitle', title);
    setText('#footerSlogan', slogan);

    [['#brandLogo', '#brandMark'], ['#footerLogo', '#footerBrandMark']].forEach(([logoSelector, markSelector]) => {
      const image = $(logoSelector);
      const mark = $(markSelector);
      if (!image || !mark) return;

      mark.textContent = initial;

      if (logo) {
        image.onerror = () => {
          image.hidden = true;
          image.removeAttribute('src');
          mark.hidden = false;
        };
        image.src = logo;
        image.alt = title;
        image.hidden = false;
        mark.hidden = true;
      } else {
        image.hidden = true;
        image.removeAttribute('src');
        image.alt = '';
        mark.hidden = false;
      }
    });

    document.title = title;
  }

  let shareToastTimer = null;

  function showShareToast() {
    const toast = $('#shareToast');
    if (!toast) return;
    toast.textContent = lang === 'ar' ? 'تم نسخ رابط الموقع' : 'Site link copied';
    toast.classList.add('is-visible');
    clearTimeout(shareToastTimer);
    shareToastTimer = setTimeout(() => toast.classList.remove('is-visible'), 2200);
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

  function renderHero() {
    if (!heroTrack || !heroDots || !hero) return;
    const images = validHeroImages();
    currentSlide = Math.min(currentSlide, Math.max(images.length - 1, 0));
    hero.classList.toggle('hero-fallback', images.length === 0);

    if (!images.length) {
      heroTrack.innerHTML = `<article class="hero-slide hero-slide-content">${heroContent()}</article>`;
      heroDots.innerHTML = '';
      toggleSliderControls(false);
      return;
    }

    heroTrack.innerHTML = images.map((image) => `
      <article class="hero-slide">
        <img class="hero-banner" src="${escapeAttr(image)}" alt="${escapeAttr(pageTitle())}" />
        ${heroContent()}
      </article>
    `).join('');

    heroDots.innerHTML = images.length > 1
      ? images.map((_, index) => `<button type="button" aria-label="${escapeAttr(T[lang].products)} ${index + 1}" data-slide="${index}"></button>`).join('')
      : '';

    toggleSliderControls(images.length > 1);
    updateHero();
    startSlider();
  }

  function heroContent() {
    return `
      <div class="hero-content">
        <h1>${escapeHtml(pageTitle())}</h1>
        <p>${escapeHtml(pageSlogan())}</p>
      </div>`;
  }

  function toggleSliderControls(show) {
    [heroNext, heroPrev, heroDots].forEach((element) => {
      if (element) element.hidden = !show;
    });
  }

  function syncSliderDirection() {
    if (heroNext) {
      heroNext.textContent = '›';
      heroNext.setAttribute('aria-label', lang === 'ar' ? 'التالي' : 'Next');
    }
    if (heroPrev) {
      heroPrev.textContent = '‹';
      heroPrev.setAttribute('aria-label', lang === 'ar' ? 'السابق' : 'Previous');
    }
  }

  function updateHero() {
    if (!heroTrack || !heroDots) return;
    heroTrack.style.transform = `translateX(-${currentSlide * 100}%)`;
    heroDots.querySelectorAll('button').forEach((dot, index) => {
      dot.classList.toggle('is-active', index === currentSlide);
    });
  }

  function moveSlide(direction) {
    const count = validHeroImages().length;
    if (count <= 1) return;
    currentSlide = (currentSlide + direction + count) % count;
    updateHero();
  }

  function startSlider() {
    clearInterval(sliderTimer);
    if (validHeroImages().length > 1) {
      sliderTimer = setInterval(() => moveSlide(1), 6000);
    }
  }

  function renderCategories() {
    if (!categoryGrid) return;
    if (!categories.length) {
      categoryGrid.innerHTML = `<p class="empty-state">${escapeHtml(T[lang].noCategories)}</p>`;
      activeCategory = 'all';
      return;
    }

    const items = [{ id: 'all', slug: 'all', name: { ar: T.ar.all, en: T.en.all }, icon: '☰' }, ...categories];
    categoryGrid.innerHTML = items.map((category) => {
      const key = category.slug || String(category.id);
      const selected = key === activeCategory;
      const image = categoryImageUrl(category);
      const hasRealImage = image && !isDefaultCategoryImage(image);
      return `
        <button class="category-card ${selected ? 'is-active' : ''}" type="button" data-category="${escapeAttr(key)}">
          <span class="category-icon ${!hasRealImage && key !== 'all' ? 'is-placeholder' : ''}" aria-hidden="true">
            ${hasRealImage
              ? `<img src="${escapeAttr(image)}" alt="" onerror="this.remove(); this.parentElement.classList.remove('has-image'); this.parentElement.innerHTML='${escapeAttr(categoryFallbackIcon(category))}';" />`
              : key !== 'all'
                ? `<span class="template-placeholder-icon category" aria-hidden="true"></span>`
                : escapeHtml(categoryFallbackIcon(category))}
          </span>
          <span>${escapeHtml(getTranslated(category, 'name'))}</span>
        </button>`;
    }).join('');

    categoryGrid.querySelectorAll('.category-icon img').forEach((image) => {
      image.parentElement?.classList.add('has-image');
    });
  }

  function renderProducts() {
    if (!productGrid) return;
    let items = allItems();
    if (activeCategory !== 'all') {
      items = items.filter((item) => (item.category?.slug || String(item.category?.id)) === activeCategory);
    } else {
      items = sortItemsGlobally(items);
    }

    if (!items.length) {
      productGrid.innerHTML = `<p class="empty-state">${escapeHtml(T[lang].noProducts)}</p>`;
      return;
    }

    productGrid.innerHTML = items.map((item) => {
      const title = getTranslated(item, 'title');
      const desc = getTranslated(item, 'short_description') || getTranslated(item, 'description');
      const image = clean(item.image_url);
      const hasRealImage = image && !isDefaultItemImage(image);
      const price = localizedPrice(item.price);
      const priceText = price ? `${T[lang].price}: ${price}` : T[lang].priceOnRequest;
      return `
        <article class="product-card" data-product-card="${escapeAttr(item.id)}">
          ${hasRealImage
            ? `<img class="product-image" src="${escapeAttr(image)}" alt="${escapeAttr(title)}" onerror="this.onerror=null;this.src='/images/defaults/item.png';" />`
            : `<div class="product-image product-image-fallback"><span>☕</span></div>`}
          <div class="product-content">
            <h3>${escapeHtml(title)}</h3>
            <p>${escapeHtml(desc)}</p>
            <div class="product-bottom">
              <div class="product-meta">
                <strong class="price">${escapeHtml(priceText)}</strong>
              </div>
              <button class="add-button" type="button" data-view-item="${escapeAttr(item.id)}">${escapeHtml(T[lang].viewDetails)}</button>
            </div>
          </div>
        </article>`;
    }).join('');
  }

  function itemById(id) {
    return allItems().find((item) => String(item.id) === String(id)) || null;
  }

  function itemImage(item) {
    return clean(item?.image_url) || '/images/defaults/item.png';
  }

  function renderItemModal() {
    if (!activeModalItem || !itemModal) return;

    const title = getTranslated(activeModalItem, 'title');
    const description = getTranslated(activeModalItem, 'description') || getTranslated(activeModalItem, 'short_description');
    const category = getTranslated(activeModalItem.category, 'name');
    const price = localizedPrice(activeModalItem.price);
    const target = orderTarget(title, price);

    const image = $('#itemModalImage');
    if (image) {
      image.src = itemImage(activeModalItem);
      image.alt = title;
      image.onerror = () => {
        image.onerror = null;
        image.src = '/images/defaults/item.png';
      };
    }

    setText('#itemModalCategory', category);
    setText('#itemModalTitle', title);
    setText('#itemModalDescription', description);
    setText('#itemModalPrice', price ? `${T[lang].price}: ${price}` : T[lang].priceOnRequest);
    setText('#itemModalOrder', T[lang].orderNow);
    setText('#itemModalUnavailable', T[lang].orderUnavailable);

    const orderButton = $('#itemModalOrder');
    const unavailable = $('#itemModalUnavailable');
    if (orderButton) {
      orderButton.hidden = !target;
      orderButton.disabled = !target;
      orderButton.dataset.target = target || '';
      orderButton.dataset.itemId = activeModalItem.id || '';
    }
    if (unavailable) unavailable.hidden = Boolean(target);
  }

  function openItemModal(id) {
    const item = itemById(id);
    if (!item || !itemModal) return;
    activeModalItem = item;
    renderItemModal();
    itemModal.hidden = false;
    itemModal.setAttribute('aria-hidden', 'false');
    requestAnimationFrame(() => itemModal.classList.add('is-open'));
    document.body.style.overflow = 'hidden';
    $('#itemModalClose')?.focus();
  }

  function closeItemModal() {
    if (!itemModal) return;
    if (document.activeElement && itemModal.contains(document.activeElement)) {
      document.activeElement.blur();
    }
    itemModal.classList.remove('is-open');
    itemModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    setTimeout(() => {
      if (!itemModal.classList.contains('is-open')) {
        itemModal.hidden = true;
      }
    }, 180);
  }

  function categoryImageUrl(category) {
    if (!category || String(category.id) === 'all' || category.slug === 'all') return '';
    return clean(category.image_url) || normalizeAssetPath(category.image);
  }

  function isDefaultItemImage(url) {
    return typeof url === 'string' && /\/images\/defaults\/item[^/]*\.(svg|png)$/i.test(url);
  }

  function isDefaultCategoryImage(url) {
    return typeof url === 'string' && /\/images\/defaults\/category[^/]*\.(svg|png)$/i.test(url);
  }

  function normalizeAssetPath(value) {
    const raw = clean(value);
    if (!raw) return '';
    if (/^(https?:|\/|data:image\/)/i.test(raw)) return raw;
    return `/${raw.replace(/^\/+/, '')}`;
  }

  function categoryFallbackIcon(category) {
    return clean(category?.icon) || '◆';
  }

  function renderContact() {
    if (!contactList) return;
    const rows = [];
    const contactLinks = links.filter((link) => contactTypes.includes(link.type) && publicLinkUrl(link));
    const socialLinks = links.filter((link) => link.type === 'social' && publicLinkUrl(link));
    const address = getTranslated(settings.address, null, '');
    const hours = getTranslated(settings.hours, null, '');

    if (address) rows.push(contactRow(fallbackIconHtml('location'), T[lang].address, address));
    if (hours) rows.push(contactRow(fallbackIconHtml('clock'), T[lang].hours, hours));
    contactLinks.forEach((link) => {
      rows.push(contactLinkRow(link));
    });

    contactList.innerHTML = rows.length ? rows.join('') : `<p class="empty-state">${escapeHtml(T[lang].noContact)}</p>`;

    if (socialLinksContainer) {
      socialLinksContainer.innerHTML = socialLinks.map((link) => `
        <a href="${escapeAttr(publicLinkUrl(link))}" target="_blank" rel="noreferrer" data-track-link="${escapeAttr(link.id)}" aria-label="${escapeAttr(linkTitle(link))}" title="${escapeAttr(linkTitle(link))}">
          <span class="social-icon" aria-hidden="true">${linkIconHtml(link, socialIconName(link))}</span>
        </a>`).join('');
      socialLinksContainer.hidden = socialLinks.length === 0;
    }

    document.querySelectorAll('[data-track-link]').forEach((element) => {
      element.onclick = () => trackLinkClick(element.getAttribute('data-track-link'));
    });
  }

  function contactLinkRow(link) {
    const href = publicLinkUrl(link);
    const label = contactLinkLabel(link);
    const text = contactLinkText(link);

    return `
      <div class="contact-row">
        <span class="contact-icon" aria-hidden="true">${linkIconHtml(link)}</span>
        <dt>${escapeHtml(label)}</dt>
        <dd><a href="${escapeAttr(href)}" target="_blank" rel="noreferrer" data-track-link="${escapeAttr(link.id)}">${escapeHtml(text)}</a></dd>
      </div>`;
  }

  function contactRow(iconHtml, label, value, link = null) {
    const content = link
      ? `<a href="${escapeAttr(publicLinkUrl(link))}" target="_blank" rel="noreferrer" data-track-link="${escapeAttr(link.id)}">${escapeHtml(label)}</a>${value ? `<small>${escapeHtml(value)}</small>` : ''}`
      : escapeHtml(value);

    return `
      <div class="contact-row">
        <span class="contact-icon" aria-hidden="true">${iconHtml}</span>
        <dt>${escapeHtml(label)}</dt>
        <dd>${content}</dd>
      </div>`;
  }

  function contactLinkLabel(link) {
    if (link.type === 'phone') return lang === 'ar' ? 'الهاتف' : 'Phone';
    return T[lang].linkFallback[link.type] || T[lang].linkFallback.custom;
  }

  function contactLinkText(link) {
    if (link.type === 'phone') return displayPhone(link) || contactLinkLabel(link);
    if (link.type === 'whatsapp') return linkTitle(link) || T[lang].linkFallback.whatsapp;
    return linkTitle(link);
  }

  function linkTitle(link) {
    return getTranslated(link, 'title', T[lang].linkFallback[link.type] || T[lang].linkFallback.custom);
  }

  function socialIconName(link) {
    const haystack = `${linkTitle(link)} ${link.url || ''} ${link.icon || ''}`.toLowerCase();
    if (haystack.includes('instagram')) return 'instagram';
    if (haystack.includes('facebook') || haystack.includes('fb.com')) return 'facebook';
    if (haystack.includes('whatsapp') || haystack.includes('wa.me')) return 'whatsapp';
    if (haystack.includes('telegram') || haystack.includes('t.me')) return 'telegram';
    if (haystack.includes('tiktok')) return 'music';
    if (haystack.includes('youtube') || haystack.includes('youtu.be')) return 'play';
    if (haystack.includes('twitter') || haystack.includes('x.com')) return 'x';
    return 'link';
  }

  function linkIcon(type) {
    return {
      whatsapp: 'whatsapp', phone: 'phone', email: 'mail', map: 'location', website: 'world', custom: 'link', social: 'link',
    }[type] || 'link';
  }

  function linkIconHtml(link, fallback = null) {
    const iconClass = sanitizeIconClass(link?.icon || '');
    if (iconClass) return `<i class="${escapeAttr(iconClass)}"></i>`;
    return fallbackIconHtml(fallback || linkIcon(link?.type));
  }

  function fallbackIconHtml(name) {
    return svgIcon(name);
  }

  function sanitizeIconClass(value) {
    return clean(value).replace(/[^a-zA-Z0-9_\-\s]/g, '');
  }

  function svgIcon(name) {
    const icons = {
      phone: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.4 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>',
      location: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>',
      clock: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
      mail: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
      world: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>',
      whatsapp: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.5 19.2 3 21l1.2-4.7A9 9 0 1 1 7.5 19.2z"/><path d="M9.2 8.8c.2-.5.4-.5.7-.5h.5c.2 0 .4 0 .6.4l.8 1.9c.1.2.1.4 0 .6l-.5.7c-.1.2-.2.3 0 .6.4.7 1 1.4 1.7 1.9.8.5 1 .5 1.2.3l.8-1c.2-.2.4-.2.6-.1l1.9.9c.3.1.5.3.5.5 0 .6-.3 1.5-.8 1.8-.5.4-1.8.7-4.1-.4-3.4-1.5-5.6-4.9-5.8-5.2-.1-.2-1.3-1.7-1.3-3.2 0-1.6.8-2.3 1.1-2.6z"/></svg>',
      instagram: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8"/></svg>',
      facebook: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8h3V4h-3c-3 0-5 2-5 5v3H6v4h3v6h4v-6h3l1-4h-4V9c0-.6.4-1 1-1z"/></svg>',
      telegram: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 4 3 11l7 2.5M21 4l-4 17-7-7.5M21 4 10 13.5"/></svg>',
      play: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12s0-3-.4-4.4c-.2-.8-.8-1.4-1.6-1.6C18.6 5.6 12 5.6 12 5.6s-6.6 0-8 .4c-.8.2-1.4.8-1.6 1.6C2 9 2 12 2 12s0 3 .4 4.4c.2.8.8 1.4 1.6 1.6 1.4.4 8 .4 8 .4s6.6 0 8-.4c.8-.2 1.4-.8 1.6-1.6.4-1.4.4-4.4.4-4.4z"/><path d="m10 9 5 3-5 3z"/></svg>',
      x: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 4 16 16M20 4 4 20"/></svg>',
      music: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3v10.2a4.2 4.2 0 1 1-3.6-4.1"/><path d="M14 3c.5 3 2.2 4.7 5 5"/></svg>',
      share: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg>',
      link: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.1 0l2-2a5 5 0 0 0-7.1-7.1l-1.2 1.2"/><path d="M14 11a5 5 0 0 0-7.1 0l-2 2a5 5 0 0 0 7.1 7.1l1.2-1.2"/></svg>'
    };
    return icons[name] || icons.link;
  }

  function orderTarget(title, price) {
    const whatsapp = links.find((link) => link.type === 'whatsapp' && publicLinkUrl(link));
    const phone = links.find((link) => link.type === 'phone' && publicLinkUrl(link));
    const message = `${T[lang].whatsappMessage}${title}${price ? ` - ${price}` : ''}`;

    if (whatsapp) return buildWhatsAppUrl(publicLinkUrl(whatsapp), message);
    if (phone) return publicLinkUrl(phone);
    return '';
  }

  function publicLinkUrl(link) {
    const raw = clean(link?.url);
    if (!raw || isDashboardUrl(raw)) return '';

    if (link.type === 'whatsapp') return normalizeWhatsAppLink(raw);
    if (link.type === 'phone') return raw.startsWith('tel:') ? raw : `tel:${normalizePhone(raw)}`;
    if (link.type === 'email') return raw.startsWith('mailto:') ? raw : `mailto:${raw}`;
    if (/^(https?:|mailto:|tel:|sms:)/i.test(raw)) return raw;
    return `https://${raw.replace(/^\/+/, '')}`;
  }

  function isDashboardUrl(value) {
    const raw = clean(value);
    if (/^\/dashboard(\/|$)/i.test(raw)) return true;
    try {
      const url = new URL(raw, window.location.origin);
      return /^\/dashboard(\/|$)/i.test(url.pathname);
    } catch (error) {
      return false;
    }
  }

  function normalizeWhatsAppLink(value) {
    const raw = clean(value);
    if (/wa\.me|api\.whatsapp\.com/i.test(raw)) return raw;
    const phone = normalizePhone(raw);
    return phone ? `https://wa.me/${phone}` : raw;
  }

  function displayPhone(link) {
    return clean(link?.url).replace(/^tel:/i, '');
  }

  function buildWhatsAppUrl(value, message) {
    const raw = clean(value);
    const encoded = encodeURIComponent(message);
    if (/wa\.me|api\.whatsapp\.com/i.test(raw)) {
      try {
        const url = new URL(raw);
        url.searchParams.set('text', message);
        return url.toString();
      } catch (error) {
        return `${raw}${raw.includes('?') ? '&' : '?'}text=${encoded}`;
      }
    }
    const phone = normalizePhone(raw);
    return phone ? `https://wa.me/${phone}?text=${encoded}` : '';
  }

  function normalizePhone(value) {
    return clean(value).replace(/^tel:/i, '').replace(/[^0-9+]/g, '').replace(/^\+/, '');
  }

  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  function trackPublicClick(url) {
    const token = csrfToken();
    if (navigator.sendBeacon) {
      const formData = new FormData();
      if (token) formData.append('_token', token);
      navigator.sendBeacon(url, formData);
      return;
    }

    fetch(url, {
      method: 'POST',
      headers: token ? { 'X-CSRF-TOKEN': token } : {},
      keepalive: true
    }).catch(() => {});
  }

  function trackLinkClick(id) {
    if (id) trackPublicClick(`/track/link/${id}`);
  }

  function trackItemOrderClick(id) {
    if (id) trackPublicClick(`/track/item-order/${id}`);
  }

  function renderAll() {
    syncLanguage();
    renderBrand();
    renderHero();
    renderCategories();
    renderProducts();
    renderContact();
    setText('#year', new Date().getFullYear());
    setText('#rightsText', T[lang].rights);
    setText('#linkyCreditText', lang === 'ar' ? 'تم إنشاء هذا الموقع بواسطة' : 'Created with');

    const phone = links.find((link) => ['phone', 'whatsapp'].includes(link.type) && publicLinkUrl(link));
    if (headerCallButton) {
      headerCallButton.textContent = T[lang].callUs;
      headerCallButton.href = phone ? publicLinkUrl(phone) : '#contact';
      if (phone?.id) {
        headerCallButton.dataset.trackLink = phone.id;
        headerCallButton.onclick = () => trackLinkClick(phone.id);
      } else {
        delete headerCallButton.dataset.trackLink;
        headerCallButton.onclick = null;
      }
    }
  }

  window.addEventListener('scroll', () => {
    document.querySelector('.site-header')?.classList.toggle('is-scrolled', window.scrollY > 8);
  });

  langToggle?.addEventListener('click', () => {
    lang = lang === 'ar' ? 'en' : 'ar';
    localStorage.setItem('linky_rest3_lang', lang);
    renderAll();
    if (activeModalItem && itemModal && !itemModal.hidden) renderItemModal();
  });

  themeToggle?.addEventListener('click', () => {
    applyTheme(theme === 'dark' ? 'light' : 'dark');
  });

  shareButton?.addEventListener('click', shareSite);

  heroNext?.addEventListener('click', () => moveSlide(1));
  heroPrev?.addEventListener('click', () => moveSlide(-1));

  heroDots?.addEventListener('click', (event) => {
    const dot = event.target.closest('[data-slide]');
    if (!dot) return;
    currentSlide = Number(dot.dataset.slide);
    updateHero();
  });

  categoryGrid?.addEventListener('click', (event) => {
    const card = event.target.closest('[data-category]');
    if (!card) return;
    activeCategory = card.dataset.category || 'all';
    renderCategories();
    renderProducts();
  });

  productGrid?.addEventListener('click', (event) => {
    const targetElement = event.target.closest('[data-view-item], [data-product-card]');
    if (!targetElement) return;
    const id = targetElement.dataset.viewItem || targetElement.dataset.productCard;
    openItemModal(id);
  });

  $('#itemModalClose')?.addEventListener('click', closeItemModal);
  $('#itemModalBackdrop')?.addEventListener('click', closeItemModal);
  $('#itemModalOrder')?.addEventListener('click', (event) => {
    const button = event.currentTarget;
    const target = button.dataset.target;
    if (!target || !button.dataset.itemId) return;
    trackItemOrderClick(button.dataset.itemId);
    window.open(target, '_blank', 'noopener');
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && itemModal && !itemModal.hidden) closeItemModal();
  });

  applyTheme(theme);
  renderAll();
})();
