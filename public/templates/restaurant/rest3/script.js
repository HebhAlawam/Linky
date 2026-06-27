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
      viewMap: 'الموقع على الخريطة',
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
      viewMap: 'View on map',
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

  const contactTypes = ['phone', 'whatsapp', 'email', 'website', 'custom'];
  let lang = localStorage.getItem('linky_rest3_lang') || app.lang || 'ar';
  let theme = localStorage.getItem('linky_rest3_theme') || 'light';
  let currentSlide = 0;
  let activeCategory = 'all';
  let sliderTimer = null;
  let itemQuantity = 1;

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
    const details = localizedPriceDetails(value);
    const price = details.discount || details.base;

    if (price || !withFallback) return price;

    return T[lang].priceOnRequest;
  }

  function localizedPriceDetails(value, locale = lang) {
    if (typeof value === 'string') {
      return { base: clean(value), discount: '' };
    }

    if (!value || typeof value !== 'object') {
      return { base: '', discount: '' };
    }

    const selectedLocale = [locale, locale === 'ar' ? 'en' : 'ar']
      .find(candidate => clean(value[candidate]));
    const base = selectedLocale ? clean(value[selectedLocale]) : '';
    const discount = selectedLocale && value.discount_price && typeof value.discount_price === 'object'
      ? clean(value.discount_price[selectedLocale])
      : '';
    const hasValidDiscount = base !== '' && discount !== '';

    return { base, discount: hasValidDiscount ? discount : '' };
  }

  function priceMarkup(value, withFallback = true) {
    const details = localizedPriceDetails(value);

    if (details.discount) {
      return `<span class="price-original">${escapeHtml(`${T[lang].price}: ${details.base}`)}</span><span class="price-discount">${escapeHtml(`${T[lang].price}: ${details.discount}`)}</span>`;
    }

    if (details.base) {
      return `<span class="price-current">${escapeHtml(`${T[lang].price}: ${details.base}`)}</span>`;
    }

    const fallback = withFallback ? localizedPrice(value, true) : '';
    return fallback ? `<span class="price-current">${escapeHtml(fallback)}</span>` : '';
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
    const copied = document.execCommand('copy');
    input.remove();
    if (!copied) throw new Error('Clipboard copy failed');
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
      const priceText = priceMarkup(item.price, true);
      return `
        <article class="product-card" data-product-card="${escapeAttr(item.id)}">
          ${hasRealImage
            ? `<img class="product-image" src="${escapeAttr(image)}" alt="${escapeAttr(title)}" onerror="this.onerror=null;this.hidden=true;this.nextElementSibling.hidden=false;" />
               <div class="product-image product-image-fallback" hidden><span aria-hidden="true"></span></div>`
            : `<div class="product-image product-image-fallback"><span aria-hidden="true"></span></div>`}
          <div class="product-content">
            <h3>${escapeHtml(title)}</h3>
            <p>${escapeHtml(desc)}</p>
            <div class="product-bottom">
              <div class="product-meta">
                <strong class="price">${priceText}</strong>
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
    const image = clean(item?.image_url);
    return image && !isDefaultItemImage(image) ? image : '';
  }

  function renderItemModal() {
    if (!activeModalItem || !itemModal) return;

    const title = getTranslated(activeModalItem, 'title');
    const description = getTranslated(activeModalItem, 'description') || getTranslated(activeModalItem, 'short_description');
    const category = getTranslated(activeModalItem.category, 'name');

    const image = $('#itemModalImage');
    const imageFallback = $('#itemModalImageFallback');
    if (image && imageFallback) {
      const imageUrl = itemImage(activeModalItem);
      image.alt = title;
      image.hidden = !imageUrl;
      imageFallback.hidden = Boolean(imageUrl);
      image.onerror = () => {
        image.onerror = null;
        image.hidden = true;
        image.removeAttribute('src');
        imageFallback.hidden = false;
      };
      if (imageUrl) {
        image.src = imageUrl;
      } else {
        image.removeAttribute('src');
      }
    }

    setText('#itemModalCategory', category);
    setText('#itemModalTitle', title);
    setText('#itemModalDescription', description);
    const modalPrice = $('#itemModalPrice');
    if (modalPrice) modalPrice.innerHTML = priceMarkup(activeModalItem.price, true);
    renderItemCartActions();
  }

  function openItemModal(id) {
    const item = itemById(id);
    if (!item || !itemModal) return;
    activeModalItem = item;
    const existingCartItem = cart.items.find(entry => String(entry.id) === String(item.id));
    itemQuantity = existingCartItem ? existingCartItem.quantity : 1;
    if ($('#itemCartFeedback')) $('#itemCartFeedback').hidden = true;
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
    if (!$('#cartDrawer')?.classList.contains('open')) document.body.style.overflow = '';
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
    const map = mapLink();

    if (address || map) rows.push(contactRow(fallbackIconHtml('location'), T[lang].address, address, map));
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

  function mapLink() {
    return links.find((link) => link.type === 'map' && publicLinkUrl(link));
  }

  function contactRow(iconHtml, label, value, link = null) {
    const content = link
      ? `${value ? `<span class="contact-value-line">${escapeHtml(value)}</span>` : ''}<a href="${escapeAttr(publicLinkUrl(link))}" target="_blank" rel="noopener noreferrer" data-track-link="${escapeAttr(link.id)}" class="map-action">${linkIconHtml(link)}<span>${escapeHtml(T[lang].viewMap)}</span></a>`
      : escapeHtml(value);

    return `
      <div class="contact-row">
        <span class="contact-icon" aria-hidden="true">${iconHtml}</span>
        <dt>${escapeHtml(label)}</dt>
        <dd>${content}</dd>
      </div>`;
  }

  function contactLinkLabel(link) {
    if (['phone', 'whatsapp'].includes(link.type)) return linkTitle(link);
    return T[lang].linkFallback[link.type] || T[lang].linkFallback.custom;
  }

  function contactLinkText(link) {
    if (link.type === 'phone') return displayPhone(link) || contactLinkLabel(link);
    if (link.type === 'whatsapp') return displayPhone(link) || contactLinkLabel(link);
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
    const raw = clean(link?.url);
    if (link?.type === 'whatsapp') {
      try {
        const url = new URL(raw, window.location.origin);
        const pathPhone = url.hostname.includes('wa.me')
          ? url.pathname.replace(/^\/+/, '').split('/')[0]
          : url.searchParams.get('phone');
        const phone = normalizePhone(pathPhone || raw);
        return phone || raw;
      } catch (error) {
        const phone = normalizePhone(raw);
        return phone || raw;
      }
    }

    return raw.replace(/^tel:/i, '');
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

  const CART_TEXT = {
    ar: {
      title: 'سلة التسوق', empty: 'سلتك فارغة', clear: 'إفراغ السلة', notes: 'ملاحظات على الطلب',
      add: 'أضف إلى السلة', update: 'تحديث السلة', added: 'تمت الإضافة إلى السلة', updated: 'تم تحديث السلة', view: 'عرض السلة', remove: 'إزالة',
      total: 'المجموع', submit: 'إرسال الطلب عبر واتساب', copy: 'نسخ تفاصيل الطلب',
      copyHelp: 'انسخ الطلب وتواصل مع المطعم عبر وسيلة الاتصال المناسبة.', copied: 'تم نسخ تفاصيل الطلب',
      copyFailed: 'تعذر نسخ الطلب، يرجى المحاولة مرة أخرى.', unavailable: 'لا توجد وسيلة طلب عبر واتساب متاحة حاليًا.', close: 'إغلاق',
      confirmTotal: 'يتم تأكيد المجموع النهائي عبر واتساب.', decrease: 'تقليل الكمية', increase: 'زيادة الكمية', cart: 'فتح سلة التسوق'
    },
    en: {
      title: 'Shopping Cart', empty: 'Your cart is empty', clear: 'Clear cart', notes: 'Order notes',
      add: 'Add to cart', update: 'Update cart', added: 'Added to cart', updated: 'Cart updated', view: 'View cart', remove: 'Remove',
      total: 'Total', submit: 'Send order via WhatsApp', copy: 'Copy order details',
      copyHelp: 'Copy the order and contact the restaurant using the appropriate contact method.', copied: 'Order details copied',
      copyFailed: 'Unable to copy the order. Please try again.', unavailable: 'WhatsApp ordering is not available right now.', close: 'Close',
      confirmTotal: 'The final total will be confirmed via WhatsApp.', decrease: 'Decrease quantity', increase: 'Increase quantity', cart: 'Open shopping cart'
    }
  };

  let cart = { items: [], note: '' };
  let cartOpener = null;
  const cartStorageKey = `linky_cart_v1_${clean(page.id || page.slug || window.location.pathname).replace(/[^a-zA-Z0-9_-]/g, '_')}`;

  function cartWhatsappLink() {
    return links.find(link => link.type === 'whatsapp' && link.is_primary === true && publicLinkUrl(link));
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

  function effectivePrice(item) {
    const details = localizedPriceDetails(item?.price, lang);
    return details.discount || details.base || '';
  }

  function parseCartPrice(text) {
    const normalized = clean(text).replace(/[٠-٩۰-۹]/g, digit => {
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
      numeric = numeric.replaceAll(decimal === ',' ? '.' : ',', '').replace(decimal, '.');
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
    return { amount, label, normalizedLabel: label.toLowerCase().replace(/[\s.,،٬٫\-_/\\()[\]{}]+/gu, '') };
  }

  function formattedAmount(amount, label) {
    const number = new Intl.NumberFormat(lang === 'ar' ? 'ar' : 'en', { maximumFractionDigits: 2 }).format(amount);
    return label ? `${number} ${label}` : number;
  }

  function ensureCartCopyHelp() {
    const submit = $('#cartSubmit');
    if (!submit) return null;
    let helper = $('#cartCopyHelp');
    if (!helper) {
      helper = document.createElement('p');
      helper.id = 'cartCopyHelp';
      helper.className = 'cart-copy-help';
      helper.setAttribute('role', 'status');
      helper.setAttribute('aria-live', 'polite');
      submit.insertAdjacentElement('beforebegin', helper);
    }
    return helper;
  }

  function cartRows() {
    return cart.items.map(entry => {
      const item = itemById(entry.id);
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
    return { amount: rows.reduce((sum, row) => sum + row.subtotal, 0), label: rows[0].parsed.label };
  }

  function renderItemCartActions() {
    const control = $('#itemQuantityControl');
    const add = $('#itemAddToCart');
    const unavailable = $('#itemCartUnavailable');
    if (control) control.hidden = false;
    if (add) {
      add.hidden = false;
      const exists = activeModalItem && cart.items.some(entry => String(entry.id) === String(activeModalItem.id));
      add.textContent = exists ? CART_TEXT[lang].update : CART_TEXT[lang].add;
    }
    if (unavailable) {
      unavailable.hidden = true;
      unavailable.textContent = CART_TEXT[lang].unavailable;
    }
    setText('#itemQuantity', itemQuantity);
    $('#itemQtyMinus')?.setAttribute('aria-label', CART_TEXT[lang].decrease);
    $('#itemQtyPlus')?.setAttribute('aria-label', CART_TEXT[lang].increase);
  }

  function renderCart() {
    cleanCart();
    const whatsapp = cartWhatsappLink();
    const trigger = $('#cartTrigger');
    if (trigger) {
      trigger.hidden = false;
      trigger.setAttribute('aria-label', CART_TEXT[lang].cart);
    }
    const count = cart.items.reduce((sum, entry) => sum + entry.quantity, 0);
    const badge = $('#cartCount');
    if (badge) {
      badge.textContent = count;
      badge.hidden = count === 0;
    }
    setText('#cartTitle', CART_TEXT[lang].title);
    setText('#cartEmpty', CART_TEXT[lang].empty);
    setText('#cartClear', CART_TEXT[lang].clear);
    setText('#cartNoteLabel', CART_TEXT[lang].notes);
    setText('#cartSubmit', whatsapp ? CART_TEXT[lang].submit : CART_TEXT[lang].copy);
    const copyHelp = ensureCartCopyHelp();
    if (copyHelp) {
      copyHelp.textContent = whatsapp ? '' : CART_TEXT[lang].copyHelp;
      copyHelp.hidden = Boolean(whatsapp) || cart.items.length === 0;
    }
    $('#cartClose')?.setAttribute('aria-label', CART_TEXT[lang].close);
    const note = $('#cartNote');
    if (note && note.value !== cart.note) note.value = cart.note;
    const rows = cartRows();
    if ($('#cartEmpty')) $('#cartEmpty').hidden = rows.length > 0;
    if ($('#cartClear')) $('#cartClear').hidden = rows.length === 0;
    if ($('#cartItems')) $('#cartItems').innerHTML = rows.map(row => {
      const title = getTranslated(row.item, 'title');
      const image = itemImage(row.item) || '/images/defaults/item.svg';
      const subtotal = row.subtotal === null ? '' : `<small>${escapeHtml(formattedAmount(row.subtotal, row.parsed.label))}</small>`;
      return `<article class="cart-row" data-cart-id="${escapeAttr(row.entry.id)}"><img src="${escapeAttr(image)}" alt="" onerror="this.src='/images/defaults/item.svg'"><div class="cart-row-main"><strong>${escapeHtml(title)}</strong><span>${escapeHtml(row.price || localizedPrice(row.item.price, true))}</span>${subtotal}<div class="cart-row-actions"><div class="cart-quantity"><button type="button" data-cart-minus aria-label="${escapeAttr(CART_TEXT[lang].decrease)}">−</button><span>${row.entry.quantity}</span><button type="button" data-cart-plus aria-label="${escapeAttr(CART_TEXT[lang].increase)}">+</button></div><button type="button" class="cart-remove" data-cart-remove>${escapeHtml(CART_TEXT[lang].remove)}</button></div></div></article>`;
    }).join('');
    const total = cartTotal(rows);
    if ($('#cartSummary')) $('#cartSummary').textContent = !rows.length
      ? ''
      : total
        ? `${CART_TEXT[lang].total}: ${formattedAmount(total.amount, total.label)}`
        : CART_TEXT[lang].confirmTotal;
    if ($('#cartSubmit')) $('#cartSubmit').disabled = rows.length === 0;
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
    if (!activeModalItem) return;
    const id = String(activeModalItem.id);
    const existing = cart.items.find(entry => String(entry.id) === id);
    const isUpdate = Boolean(existing);
    if (existing) existing.quantity = Math.min(99, Math.max(1, itemQuantity));
    else cart.items.push({ id, quantity: itemQuantity });
    safeSaveCart();
    renderCart();
    renderItemCartActions();
    setText('#itemCartFeedbackText', isUpdate ? CART_TEXT[lang].updated : CART_TEXT[lang].added);
    setText('#itemViewCart', CART_TEXT[lang].view);
    if ($('#itemCartFeedback')) $('#itemCartFeedback').hidden = false;
  }

  function openCart(event) {
    cartOpener = event?.currentTarget?.id === 'itemViewCart'
      ? $('#cartTrigger')
      : event?.currentTarget || document.activeElement;
    const drawer = $('#cartDrawer');
    if (!drawer) return;
    drawer.hidden = false;
    drawer.setAttribute('aria-hidden', 'false');
    requestAnimationFrame(() => drawer.classList.add('open'));
    document.body.style.overflow = 'hidden';
    renderCart();
    $('#cartClose')?.focus();
  }

  function closeCart() {
    const drawer = $('#cartDrawer');
    if (!drawer) return;
    drawer.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
    setTimeout(() => { if (!drawer.classList.contains('open')) drawer.hidden = true; }, 200);
    if (itemModal?.hidden !== false) document.body.style.overflow = '';
    cartOpener?.focus?.();
  }

  async function copyCartOrder() {
    const helper = ensureCartCopyHelp();
    try {
      await copyToClipboard(cartMessage());
      if (helper) {
        helper.hidden = false;
        helper.textContent = CART_TEXT[lang].copied;
      }
    } catch (error) {
      if (helper) {
        helper.hidden = false;
        helper.textContent = CART_TEXT[lang].copyFailed;
      }
    }
  }

  function cartMessage() {
    const rows = cartRows();
    const total = cartTotal(rows);
    const lines = rows.map((row, index) => {
      const linePrice = row.subtotal === null
        ? row.price || localizedPrice(row.item.price, true)
        : formattedAmount(row.subtotal, row.parsed.label);
      return `${index + 1}. ${getTranslated(row.item, 'title')} × ${row.entry.quantity} — ${linePrice}`;
    });
    const note = cart.note.trim();
    if (lang === 'ar') return [`مرحبًا، أود طلب المنتجات التالية من ${pageTitle()}:`, '', ...lines, ...(note ? ['', 'ملاحظات:', note] : []), '', total ? `المجموع: ${formattedAmount(total.amount, total.label)}` : CART_TEXT.ar.confirmTotal, '', 'يرجى تأكيد الطلب والسعر النهائي، شكرًا.'].join('\n');
    return [`Hello, I would like to order the following items from ${pageTitle()}:`, '', ...lines, ...(note ? ['', 'Notes:', note] : []), '', total ? `Total: ${formattedAmount(total.amount, total.label)}` : CART_TEXT.en.confirmTotal, '', 'Please confirm the order and final total. Thank you.'].join('\n');
  }

  function initCart() {
    cart = safeLoadCart();
    cleanCart();
    renderCart();
    $('#cartTrigger')?.addEventListener('click', openCart);
    $('#cartClose')?.addEventListener('click', closeCart);
    $('#cartOverlay')?.addEventListener('click', closeCart);
    $('#itemQtyMinus')?.addEventListener('click', () => { itemQuantity = Math.max(1, itemQuantity - 1); renderItemCartActions(); });
    $('#itemQtyPlus')?.addEventListener('click', () => { itemQuantity = Math.min(99, itemQuantity + 1); renderItemCartActions(); });
    $('#itemAddToCart')?.addEventListener('click', addActiveItemToCart);
    $('#itemViewCart')?.addEventListener('click', event => { closeItemModal(); openCart(event); });
    $('#cartClear')?.addEventListener('click', () => { cart = { items: [], note: '' }; safeSaveCart(); renderCart(); });
    $('#cartNote')?.addEventListener('input', event => { cart.note = event.target.value.slice(0, 1000); safeSaveCart(); });
    $('#cartItems')?.addEventListener('click', event => {
      const row = event.target.closest('[data-cart-id]');
      if (!row) return;
      if (event.target.closest('[data-cart-minus]')) changeCartQuantity(row.dataset.cartId, -1);
      if (event.target.closest('[data-cart-plus]')) changeCartQuantity(row.dataset.cartId, 1);
      if (event.target.closest('[data-cart-remove]')) { cart.items = cart.items.filter(item => String(item.id) !== row.dataset.cartId); if (!cart.items.length) cart.note = ''; safeSaveCart(); renderCart(); }
    });
    $('#cartSubmit')?.addEventListener('click', async () => {
      const whatsapp = cartWhatsappLink();
      if (!cart.items.length) return;
      if (whatsapp) {
        cart.items.forEach(entry => trackItemOrderClick(entry.id));
        window.open(buildWhatsAppUrl(publicLinkUrl(whatsapp), cartMessage()), '_blank', 'noopener');
        return;
      }
      await copyCartOrder();
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && $('#cartDrawer')?.classList.contains('open')) closeCart();
    });
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
    setText('#linkyCreditText', lang === 'ar' ? 'طُوّر بواسطة' : 'powered by');
    document.querySelector('.linky-signature a')?.replaceChildren(document.createTextNode(lang === 'ar' ? 'لينكـي' : 'Linky'));

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
    renderCart();
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
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && itemModal && !itemModal.hidden) closeItemModal();
  });

  applyTheme(theme);
  initCart();
  renderAll();
})();
