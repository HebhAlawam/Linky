(() => {
  const app = window.APP || { lang: 'ar', data: {} };
  const data = app.data || {};
  const page = data.page || {};
  const settings = page.settings || {};
  const categories = Array.isArray(data.categories) ? data.categories : [];
  const links = Array.isArray(data.links) ? data.links : [];

  const T = {
    ar: {
      all: 'الكل',
      menuTitle: 'القائمة',
      menuSubtitle: 'اختر من أصنافنا المميزة.',
      heroCta: 'عرض القائمة',
      contact: 'تواصل معنا',
      contactSubtitle: 'نسعد بتواصلكم للطلب أو الاستفسار.',
      infoEmpty: 'سيتم إضافة معلومات الموقع قريبًا.',
      linksEmpty: 'سيتم إضافة وسائل التواصل قريبًا.',
      allContactEmpty: 'سيتم إضافة معلومات التواصل قريبًا.',
      openMap: 'فتح الخريطة',
      address: 'العنوان',
      hours: 'مواعيد العمل',
      links: 'تواصل',
      social: 'تابعنا',
      order: 'عرض التفاصيل',
      modalOrder: 'اطلب عبر واتساب',
      modalCall: 'اتصل للطلب',
      priceOnRequest: 'السعر عند الطلب',
      noItems: 'لا توجد عناصر بعد.',
      rights: 'جميع الحقوق محفوظة.',
      whatsappMessage: 'مرحبًا، أريد طلب: ',
      fallbackTitle: 'Linky',
      linkFallback: {
        whatsapp: 'واتساب', phone: 'اتصال', email: 'بريد إلكتروني', map: 'الموقع',
        website: 'الموقع الإلكتروني', custom: 'رابط', social: 'تابعنا'
      }
    },
    en: {
      all: 'All',
      menuTitle: 'Menu',
      menuSubtitle: 'Choose from our featured items.',
      heroCta: 'View Menu',
      contact: 'Contact Us',
      contactSubtitle: 'Reach us for orders or inquiries.',
      infoEmpty: 'Location details will be added soon.',
      linksEmpty: 'Contact details will be added soon.',
      allContactEmpty: 'Contact information will be added soon.',
      openMap: 'Open Map',
      address: 'Address',
      hours: 'Working Hours',
      links: 'Contact',
      social: 'Follow Us',
      order: 'View details',
      modalOrder: 'Order via WhatsApp',
      modalCall: 'Call to order',
      priceOnRequest: 'Price on request',
      noItems: 'No items yet.',
      rights: 'All Rights Reserved.',
      whatsappMessage: 'Hello, I would like to order: ',
      fallbackTitle: 'Linky',
      linkFallback: {
        whatsapp: 'WhatsApp', phone: 'Call', email: 'Email', map: 'Location',
        website: 'Website', custom: 'Link', social: 'Follow'
      }
    }
  };

  const contactTypes = ['phone', 'whatsapp', 'email', 'map', 'website', 'custom'];
  const EMPTY_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
  let lang = localStorage.getItem('linky_rest2_lang') || app.lang || 'ar';
  let activeCategory = 'all';
  let activeModalItemId = null;
  let itemQuantity = 1;

  const $ = (id) => document.getElementById(id);
  const clean = (value) => String(value || '').trim();

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
    if (typeof value === 'string') return value.trim() || fallback;
    if (typeof value === 'object') {
      return clean(value[lang]) || clean(value.ar) || clean(value.en) || clean(Object.values(value).find(Boolean)) || fallback;
    }
    return fallback;
  }

  function translationValue(source, field, locale) {
    const value = source?.[field];
    if (typeof value === 'object' && value !== null) {
      return clean(value[locale]) || clean(value.ar) || clean(value.en) || clean(Object.values(value).find(Boolean));
    }
    return clean(value);
  }

  function localizedPrice(value, withFallback = false, locale = lang) {
    const details = localizedPriceDetails(value, locale);
    const price = details.discount || details.base;

    if (price || !withFallback) return price;

    return locale === 'ar' ? T.ar.priceOnRequest : T.en.priceOnRequest;
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

  function priceMarkup(value, locale = lang, withFallback = true) {
    const details = localizedPriceDetails(value, locale);

    if (details.discount) {
      return `<span class="price-original">${escapeHtml(details.base)}</span><span class="price-discount">${escapeHtml(details.discount)}</span>`;
    }

    const price = details.base || (withFallback ? localizedPrice(value, true, locale) : '');
    return price ? `<span class="price-current">${escapeHtml(price)}</span>` : '';
  }

  function pageTitle() {
    return getTranslated(page, 'title', T[lang].fallbackTitle);
  }

  function pageSlogan() {
    return getTranslated(page, 'slogan', '');
  }

  function currentModalLang() {
    return (document.getElementById('mainHtml')?.dir || document.documentElement.dir) === 'rtl' ? 'ar' : 'en';
  }

  function syncDirection() {
    const html = document.getElementById('mainHtml') || document.documentElement;
    html.lang = lang;
    html.dir = lang === 'ar' ? 'rtl' : 'ltr';
    const toggle = $('langToggle');
    if (toggle) toggle.textContent = lang === 'ar' ? 'English' : 'العربية';
  }

  function applyTheme() {
    const html = document.getElementById('mainHtml') || document.documentElement;
    const theme = localStorage.getItem('linky_rest2_theme') || 'light';
    html.classList.toggle('dark', theme === 'dark');
    updateThemeIcon();
  }

  function updateThemeIcon() {
    const html = document.getElementById('mainHtml') || document.documentElement;
    const icon = $('themeToggle')?.querySelector('i');
    if (icon) icon.className = html.classList.contains('dark') ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
  }

  window.toggleTheme = function toggleTheme() {
    const html = document.getElementById('mainHtml') || document.documentElement;
    const isDark = html.classList.toggle('dark');
    localStorage.setItem('linky_rest2_theme', isDark ? 'dark' : 'light');
    updateThemeIcon();
  };

  window.toggleLanguage = function toggleLanguage() {
    lang = lang === 'ar' ? 'en' : 'ar';
    localStorage.setItem('linky_rest2_lang', lang);
    syncDirection();
    renderAll();
    renderCart();
    if (activeModalItemId) renderItemCartActions();
  };

  window.scrollToTop = function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  window.scrollToMenu = function scrollToMenu() {
    document.getElementById('menu')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  function heroImageUrl() {
    const images = Array.isArray(settings.hero_images) ? settings.hero_images : [];
    return images.map((image) => {
      if (typeof image === 'string') return image;
      if (image && typeof image === 'object') return image.url || image.image_url || image.src || '';
      return '';
    }).map(clean).find(Boolean) || '';
  }

  function renderBrand() {
    const title = pageTitle();
    const logo = usableLogoUrl();
    const navLogo = $('navLogo');
    const navTitle = $('navTitle');

    if (navTitle) {
      navTitle.textContent = title;
      navTitle.classList.remove('hidden');
    }

    if (navLogo) {
      if (logo) {
        navLogo.onerror = () => {
          navLogo.onerror = null;
          navLogo.removeAttribute('src');
          navLogo.alt = '';
          navLogo.classList.add('hidden');
        };
        navLogo.src = logo;
        navLogo.alt = title;
        navLogo.classList.remove('hidden');
      } else {
        navLogo.onerror = null;
        navLogo.removeAttribute('src');
        navLogo.alt = '';
        navLogo.classList.add('hidden');
      }
    }

    const footerLogo = $('footerLogo');
    if (footerLogo) {
      footerLogo.innerHTML = logo
        ? `<img src="${escapeAttr(logo)}" alt="${escapeAttr(title)}" class="h-12 mx-auto object-contain">`
        : `<span class="text-2xl font-black text-restaurant">${escapeHtml(title)}</span>`;
    }

    if ($('footerName')) $('footerName').textContent = title;
  }

  function usableLogoUrl() {
    const url = clean(page.logo_url);
    if (!url || url === '#') return '';
    if (/\/images\/defaults\/logo[^/]*\.(svg|png)$/i.test(url)) return '';

    return url;
  }

  function renderHero() {
    const heroCard = $('heroCard');
    const heroImage = $('heroImage');
    const imageUrl = heroImageUrl();

    if ($('heroTitle')) $('heroTitle').textContent = pageTitle();
    if ($('heroSlogan')) $('heroSlogan').textContent = pageSlogan();
    setText('heroCtaText', T[lang].heroCta);

    if (imageUrl && heroImage) {
      heroImage.src = imageUrl;
      heroImage.alt = pageTitle();
      heroImage.classList.remove('hidden');
      heroCard?.classList.remove('hero-fallback');
    } else {
      if (heroImage) {
        heroImage.src = EMPTY_IMAGE;
        heroImage.alt = '';
        heroImage.classList.add('hidden');
      }
      heroCard?.classList.add('hero-fallback');
    }
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

  function renderCategories() {
    const wrap = $('categoryTabs');
    if (!wrap) return;

    setText('menuTitle', T[lang].menuTitle);
    setText('menuSubtitle', T[lang].menuSubtitle);

    wrap.classList.toggle('hidden', categories.length === 0);
    if (!categories.length) {
      wrap.innerHTML = '';
      activeCategory = 'all';
      return;
    }

    const tab = (key, label, selected) => `
      <button type="button" data-category="${escapeAttr(key)}" class="${selected ? 'bg-restaurant text-white shadow-lg shadow-main' : 'bg-white text-gray-700 dark:bg-[#1a1a1a] dark:text-gray-200 shadow-sm border border-gray-100 dark:border-gray-800 hover:border-restaurant hover:text-restaurant'} px-8 py-3 rounded-2xl font-bold transition-all whitespace-nowrap">
        ${escapeHtml(label)}
      </button>`;

    wrap.innerHTML = tab('all', T[lang].all, activeCategory === 'all') + categories.map((category) => {
      const key = category.slug || String(category.id);
      return tab(key, getTranslated(category, 'name'), activeCategory === key);
    }).join('');

    wrap.querySelectorAll('[data-category]').forEach((button) => {
      button.addEventListener('click', () => {
        activeCategory = button.getAttribute('data-category') || 'all';
        renderCategories();
        renderMenu();
      });
    });
  }

  function renderMenu() {
    const grid = $('menuGrid');
    if (!grid) return;

    let items = allItems();
    if (activeCategory !== 'all') {
      items = items.filter((item) => (item.category?.slug || String(item.category?.id)) === activeCategory);
    } else {
      items = sortItemsGlobally(items);
    }

    if (!items.length) {
      grid.innerHTML = `<div class="md:col-span-2 lg:col-span-3 rounded-[2rem] bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-gray-800 p-10 text-center text-gray-500 dark:text-gray-400">${escapeHtml(T[lang].noItems)}</div>`;
      return;
    }

    grid.innerHTML = items.map((item) => {
      const titleAr = translationValue(item, 'title', 'ar');
      const titleEn = translationValue(item, 'title', 'en');
      const shortDescAr = translationValue(item, 'short_description', 'ar');
      const shortDescEn = translationValue(item, 'short_description', 'en');
      const fullDescAr = translationValue(item, 'description', 'ar') || shortDescAr;
      const fullDescEn = translationValue(item, 'description', 'en') || shortDescEn;
      const title = lang === 'ar' ? titleAr : titleEn;
      const cardDescription = lang === 'ar' ? shortDescAr : shortDescEn;
      const price = localizedPrice(item.price);
      const priceText = priceMarkup(item.price, lang, true);
      const image = clean(item.image_url);
      const hasRealImage = image && !isDefaultItemImage(image);
      return `
        <div onclick="openItemModal(this)" data-id="${escapeAttr(item.id)}" data-name="${escapeAttr(titleAr)}" data-name-en="${escapeAttr(titleEn)}" data-desc="${escapeAttr(fullDescAr)}" data-desc-en="${escapeAttr(fullDescEn)}" data-price="${escapeAttr(price)}" data-img="${escapeAttr(image)}" class="bg-white dark:bg-[#1a1a1a] p-4 rounded-[2rem] card-hover shadow-sm border border-gray-100 dark:border-gray-800 cursor-pointer overflow-hidden">
          ${hasRealImage
            ? `<img src="${escapeAttr(image)}" alt="${escapeAttr(title)}" class="w-full aspect-square object-cover rounded-[1.5rem] mb-5 pointer-events-none">`
            : `<div class="template-placeholder-box w-full aspect-square rounded-[1.5rem] mb-5 pointer-events-none"><span class="template-placeholder-icon item" aria-hidden="true"></span></div>`}
          <div class="px-2 pointer-events-none">
            <div class="flex items-start justify-between gap-4 mb-3">
              <h3 class="text-xl font-black leading-7 text-start">${escapeHtml(title)}</h3>
              <span class="item-price text-restaurant font-black text-lg">${priceText}</span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm leading-7 mb-6 min-h-[52px] text-start">${escapeHtml(cardDescription)}</p>
            <button type="button" class="w-full py-3 bg-restaurant text-white rounded-xl font-bold shadow-md shadow-main transition-all hover:bg-restaurant-hover">${escapeHtml(T[lang].order)}</button>
          </div>
        </div>`;
    }).join('');
  }

  function isDefaultItemImage(url) {
    return typeof url === 'string' && /\/images\/defaults\/item[^/]*\.(svg|png)$/i.test(url);
  }

  function renderContact() {
    const address = getTranslated(settings.address, null, '');
    const hours = getTranslated(settings.hours, null, '');
    const contactLinks = links.filter((link) => contactTypes.includes(link.type) && clean(link.url));
    const socialLinks = links.filter((link) => link.type === 'social' && clean(link.url));

    setText('contactTitle', T[lang].contact);
    setText('contactSubtitle', T[lang].contactSubtitle);
    setText('addressTitle', T[lang].address);
    setText('hoursTitle', T[lang].hours);
    setText('linksTitle', T[lang].links);
    setText('rightsText', T[lang].rights);

    const hasAnyContactData = contactLinks.length > 0 || Boolean(address) || Boolean(hours);

    toggleCard('contactLinksRow', hasAnyContactData || contactLinks.length === 0);
    const contactWrap = $('contactLinks');
    if (contactWrap) {
      contactWrap.innerHTML = contactLinks.map((link) => contactPill(link)).join('');
    }

    const linksEmpty = $('contactLinksEmpty');
    if (linksEmpty) {
      linksEmpty.textContent = T[lang].linksEmpty;
      linksEmpty.classList.toggle('hidden', contactLinks.length > 0 || !hasAnyContactData);
    }

    toggleCard('addressRow', Boolean(address));
    setText('addressText', address);

    toggleCard('hoursRow', Boolean(hours));
    setText('hoursText', hours);

    const allEmpty = $('contactAllEmpty');
    if (allEmpty) {
      allEmpty.textContent = T[lang].allContactEmpty;
      allEmpty.classList.toggle('hidden', hasAnyContactData);
    }

    const footerSocial = $('footerSocialLinks');
    if (footerSocial) {
      footerSocial.innerHTML = socialLinks.map((link) => `
        <li><a href="${escapeAttr(link.url)}" target="_blank" rel="noopener" data-track-link="${escapeAttr(link.id)}" aria-label="${escapeAttr(linkTitle(link))}" class="text-gray-400 hover:text-restaurant transition text-2xl"><i class="${escapeAttr(iconClass(link))}"></i></a></li>
      `).join('');
      footerSocial.classList.toggle('hidden', socialLinks.length === 0);
    }

    document.querySelectorAll('[data-track-link]').forEach((element) => {
      element.onclick = () => trackLinkClick(element.getAttribute('data-track-link'));
    });
  }

  function contactPill(link) {
    const classes = link.type === 'whatsapp'
      ? 'bg-restaurant text-white hover:bg-restaurant-hover shadow-sm shadow-main'
      : 'bg-gray-50 text-gray-700 hover:border-restaurant hover:text-restaurant border border-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700';

    return `
      <a href="${escapeAttr(link.url)}" target="_blank" rel="noopener" data-track-link="${escapeAttr(link.id)}" class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-sm font-bold transition whitespace-nowrap ${classes}">
        <i class="${escapeAttr(iconClass(link))}"></i>
        <span>${escapeHtml(linkTitle(link))}</span>
      </a>`;
  }
  function renderFooter() {
    setText('footerSlogan', pageSlogan());
    setText('year', new Date().getFullYear());
    setText('linkyCreditText', lang === 'ar' ? 'طُوّر بواسطة' : 'powered by');
    $('linkyCreditText')?.parentElement?.querySelector('a')?.replaceChildren(document.createTextNode(lang === 'ar' ? 'لينكـي' : 'Linky'));
  }

  function setText(id, value) {
    const element = $(id);
    if (element) element.textContent = value || '';
  }

  let shareToastTimer = null;

  function showShareToast() {
    const toast = $('shareToast');
    if (!toast) return;
    toast.textContent = lang === 'ar' ? 'تم نسخ رابط الموقع' : 'Site link copied';
    toast.classList.remove('hidden');
    clearTimeout(shareToastTimer);
    shareToastTimer = setTimeout(() => toast.classList.add('hidden'), 2200);
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

  window.shareSite = async function shareSite() {
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
  };

  function toggleCard(id, visible) {
    const element = $(id);
    if (element) element.classList.toggle('hidden', !visible);
  }

  function linkTitle(link) {
    return getTranslated(link, 'title', T[lang].linkFallback[link.type] || T[lang].linkFallback.custom);
  }

  function sanitizeIconClass(value) {
    return clean(value).replace(/[^a-zA-Z0-9_\-\s]/g, '');
  }

  function iconClass(link) {
    const sanitized = sanitizeIconClass(link.icon || '');
    if (sanitized.startsWith('ti ')) return faIconFromTabler(sanitized) || fallbackIconClass(link.type);
    return sanitized || fallbackIconClass(link.type);
  }

  function faIconFromTabler(icon) {
    const map = {
      'brand-whatsapp': 'fa-brands fa-whatsapp',
      'brand-instagram': 'fa-brands fa-instagram',
      'brand-facebook': 'fa-brands fa-facebook',
      'brand-tiktok': 'fa-brands fa-tiktok',
      'brand-x': 'fa-brands fa-x-twitter',
      'brand-twitter': 'fa-brands fa-x-twitter',
      'brand-youtube': 'fa-brands fa-youtube',
      phone: 'fa-solid fa-phone',
      mail: 'fa-solid fa-envelope',
      'map-pin': 'fa-solid fa-location-dot',
      world: 'fa-solid fa-globe',
      link: 'fa-solid fa-link',
      share: 'fa-solid fa-share-nodes'
    };
    const key = Object.keys(map).find((name) => icon.includes(name));
    return key ? map[key] : '';
  }

  function fallbackIconClass(type) {
    return {
      whatsapp: 'fa-brands fa-whatsapp',
      phone: 'fa-solid fa-phone',
      email: 'fa-solid fa-envelope',
      map: 'fa-solid fa-location-dot',
      website: 'fa-solid fa-globe',
      custom: 'fa-solid fa-link',
      social: 'fa-solid fa-share-nodes'
    }[type] || 'fa-solid fa-link';
  }

  window.openItemModal = function openItemModal(card) {
    if (!card) return;
    const modalLang = currentModalLang();
    const isArabic = modalLang === 'ar';
    const name = clean(card.dataset[isArabic ? 'name' : 'nameEn']) || clean(card.dataset.name);
    const desc = clean(card.dataset[isArabic ? 'desc' : 'descEn']) || clean(card.dataset.desc);
    const price = clean(card.dataset.price);
    const priceText = price || T[modalLang].priceOnRequest;
    const img = clean(card.dataset.img);
    activeModalItemId = card.dataset.id || null;
    const existingCartItem = cart.items.find(entry => String(entry.id) === String(activeModalItemId));
    itemQuantity = existingCartItem ? existingCartItem.quantity : 1;
    if ($('itemCartFeedback')) $('itemCartFeedback').hidden = true;

    setText('modalItemName', name);
    setText('modalItemDesc', desc);
    const modalPrice = $('modalItemPrice');
    if (modalPrice) {
      const item = allItems().find(menuItem => String(menuItem.id) === String(activeModalItemId));
      modalPrice.innerHTML = item ? priceMarkup(item.price, modalLang, true) : escapeHtml(priceText);
    }

    const image = $('modalItemImg');
    if (image) {
      image.src = img || EMPTY_IMAGE;
      image.alt = name;
      image.classList.toggle('hidden', !img);
    }

    renderItemCartActions();

    const modal = $('itemModal');
    const panel = $('itemModalPanel');
    if (!modal || !panel) return;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.classList.add('opacity-100');
    panel.classList.remove('scale-95');
    panel.classList.add('scale-100');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
  };

  window.closeItemModal = function closeItemModal() {
    const modal = $('itemModal');
    const panel = $('itemModalPanel');
    if (!modal || !panel) return;

    if (document.activeElement && modal.contains(document.activeElement)) {
      document.activeElement.blur();
    }

    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');
    panel.classList.add('scale-95');
    panel.classList.remove('scale-100');
    modal.setAttribute('aria-hidden', 'true');
    if (!$('cartDrawer')?.classList.contains('open')) document.body.classList.remove('overflow-hidden');
    activeModalItemId = null;
  };

  function modalOrderTarget(name, price, modalLang) {
    const whatsapp = links.find((link) => link.type === 'whatsapp' && clean(link.url));
    const phone = links.find((link) => link.type === 'phone' && clean(link.url));
    const messagePrefix = modalLang === 'ar' ? T.ar.whatsappMessage : T.en.whatsappMessage;
    const message = `${messagePrefix}${name}${price ? ` - ${price}` : ''}`;

    if (whatsapp) return { url: buildWhatsAppUrl(whatsapp.url, message), type: 'whatsapp' };
    if (phone) return { url: clean(phone.url).startsWith('tel:') ? phone.url : `tel:${normalizePhone(phone.url)}`, type: 'phone' };
    return { url: '', type: null };
  }

  function orderUrl(title) {
    const modalLang = currentModalLang();
    return modalOrderTarget(title, '', modalLang).url;
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
      total: 'المجموع', submit: 'إرسال الطلب عبر واتساب', unavailable: 'لا توجد وسيلة طلب عبر واتساب متاحة حاليًا.', close: 'إغلاق',
      confirmTotal: 'يتم تأكيد المجموع النهائي عبر واتساب.', decrease: 'تقليل الكمية', increase: 'زيادة الكمية', cart: 'فتح سلة التسوق'
    },
    en: {
      title: 'Shopping Cart', empty: 'Your cart is empty', clear: 'Clear cart', notes: 'Order notes',
      add: 'Add to cart', update: 'Update cart', added: 'Added to cart', updated: 'Cart updated', view: 'View cart', remove: 'Remove',
      total: 'Total', submit: 'Send order via WhatsApp', unavailable: 'WhatsApp ordering is not available right now.', close: 'Close',
      confirmTotal: 'The final total will be confirmed via WhatsApp.', decrease: 'Decrease quantity', increase: 'Increase quantity', cart: 'Open shopping cart'
    }
  };

  let cart = { items: [], note: '' };
  let cartOpener = null;
  const cartStorageKey = `linky_cart_v1_${clean(page.id || page.slug || window.location.pathname).replace(/[^a-zA-Z0-9_-]/g, '_')}`;

  function cartWhatsappLink() {
    return links.find(link => link.type === 'whatsapp' && buildWhatsAppUrl(link.url, ''));
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
    return { amount: rows.reduce((sum, row) => sum + row.subtotal, 0), label: rows[0].parsed.label };
  }

  function renderItemCartActions() {
    const enabled = Boolean(cartWhatsappLink());
    const control = $('itemQuantityControl');
    const add = $('itemAddToCart');
    const unavailable = $('itemCartUnavailable');
    if (control) control.hidden = !enabled;
    if (add) {
      add.hidden = !enabled;
      const exists = activeModalItemId && cart.items.some(entry => String(entry.id) === String(activeModalItemId));
      add.textContent = exists ? CART_TEXT[lang].update : CART_TEXT[lang].add;
    }
    if (unavailable) {
      unavailable.hidden = enabled;
      unavailable.textContent = CART_TEXT[lang].unavailable;
    }
    setText('itemQuantity', itemQuantity);
    $('itemQtyMinus')?.setAttribute('aria-label', CART_TEXT[lang].decrease);
    $('itemQtyPlus')?.setAttribute('aria-label', CART_TEXT[lang].increase);
  }

  function renderCart() {
    cleanCart();
    const enabled = Boolean(cartWhatsappLink());
    const trigger = $('cartTrigger');
    if (trigger) {
      trigger.hidden = !enabled;
      trigger.setAttribute('aria-label', CART_TEXT[lang].cart);
    }
    const count = cart.items.reduce((sum, entry) => sum + entry.quantity, 0);
    const badge = $('cartCount');
    if (badge) {
      badge.textContent = count;
      badge.hidden = count === 0;
    }
    setText('cartTitle', CART_TEXT[lang].title);
    setText('cartEmpty', CART_TEXT[lang].empty);
    setText('cartClear', CART_TEXT[lang].clear);
    setText('cartNoteLabel', CART_TEXT[lang].notes);
    setText('cartSubmit', CART_TEXT[lang].submit);
    $('cartClose')?.setAttribute('aria-label', CART_TEXT[lang].close);
    const note = $('cartNote');
    if (note && note.value !== cart.note) note.value = cart.note;
    const rows = cartRows();
    if ($('cartEmpty')) $('cartEmpty').hidden = rows.length > 0;
    if ($('cartClear')) $('cartClear').hidden = rows.length === 0;
    if ($('cartItems')) $('cartItems').innerHTML = rows.map(row => {
      const title = getTranslated(row.item, 'title');
      const image = clean(row.item.image_url) || '/images/defaults/item.svg';
      const subtotal = row.subtotal === null ? '' : `<small>${escapeHtml(formattedAmount(row.subtotal, row.parsed.label))}</small>`;
      return `<article class="cart-row" data-cart-id="${escapeAttr(row.entry.id)}"><img src="${escapeAttr(image)}" alt="" onerror="this.src='/images/defaults/item.svg'"><div class="cart-row-main"><strong>${escapeHtml(title)}</strong><span>${escapeHtml(row.price || localizedPrice(row.item.price, true))}</span>${subtotal}<div class="cart-row-actions"><div class="cart-quantity"><button type="button" data-cart-minus aria-label="${escapeAttr(CART_TEXT[lang].decrease)}">−</button><span>${row.entry.quantity}</span><button type="button" data-cart-plus aria-label="${escapeAttr(CART_TEXT[lang].increase)}">+</button></div><button type="button" class="cart-remove" data-cart-remove>${escapeHtml(CART_TEXT[lang].remove)}</button></div></div></article>`;
    }).join('');
    const total = cartTotal(rows);
    if ($('cartSummary')) $('cartSummary').textContent = !rows.length
      ? ''
      : total
        ? `${CART_TEXT[lang].total}: ${formattedAmount(total.amount, total.label)}`
        : CART_TEXT[lang].confirmTotal;
    if ($('cartSubmit')) $('cartSubmit').disabled = !enabled || rows.length === 0;
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
    const item = cartItem(activeModalItemId);
    if (!item || !cartWhatsappLink()) return;
    const id = String(item.id);
    const existing = cart.items.find(entry => String(entry.id) === id);
    const isUpdate = Boolean(existing);
    if (existing) existing.quantity = Math.min(99, Math.max(1, itemQuantity));
    else cart.items.push({ id, quantity: itemQuantity });
    safeSaveCart();
    renderCart();
    renderItemCartActions();
    setText('itemCartFeedbackText', isUpdate ? CART_TEXT[lang].updated : CART_TEXT[lang].added);
    setText('itemViewCart', CART_TEXT[lang].view);
    if ($('itemCartFeedback')) $('itemCartFeedback').hidden = false;
  }

  function openCart(event) {
    if (!cartWhatsappLink()) return;
    cartOpener = event?.currentTarget?.id === 'itemViewCart'
      ? $('cartTrigger')
      : event?.currentTarget || document.activeElement;
    const drawer = $('cartDrawer');
    if (!drawer) return;
    drawer.hidden = false;
    drawer.setAttribute('aria-hidden', 'false');
    requestAnimationFrame(() => drawer.classList.add('open'));
    document.body.classList.add('overflow-hidden');
    renderCart();
    $('cartClose')?.focus();
  }

  function closeCart() {
    const drawer = $('cartDrawer');
    if (!drawer) return;
    drawer.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
    setTimeout(() => { if (!drawer.classList.contains('open')) drawer.hidden = true; }, 200);
    if ($('itemModal')?.getAttribute('aria-hidden') !== 'false') document.body.classList.remove('overflow-hidden');
    cartOpener?.focus?.();
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
    $('cartTrigger')?.addEventListener('click', openCart);
    $('cartClose')?.addEventListener('click', closeCart);
    $('cartOverlay')?.addEventListener('click', closeCart);
    $('itemQtyMinus')?.addEventListener('click', () => { itemQuantity = Math.max(1, itemQuantity - 1); renderItemCartActions(); });
    $('itemQtyPlus')?.addEventListener('click', () => { itemQuantity = Math.min(99, itemQuantity + 1); renderItemCartActions(); });
    $('itemAddToCart')?.addEventListener('click', addActiveItemToCart);
    $('itemViewCart')?.addEventListener('click', event => { closeItemModal(); openCart(event); });
    $('cartClear')?.addEventListener('click', () => { cart = { items: [], note: '' }; safeSaveCart(); renderCart(); });
    $('cartNote')?.addEventListener('input', event => { cart.note = event.target.value.slice(0, 1000); safeSaveCart(); });
    $('cartItems')?.addEventListener('click', event => {
      const row = event.target.closest('[data-cart-id]');
      if (!row) return;
      if (event.target.closest('[data-cart-minus]')) changeCartQuantity(row.dataset.cartId, -1);
      if (event.target.closest('[data-cart-plus]')) changeCartQuantity(row.dataset.cartId, 1);
      if (event.target.closest('[data-cart-remove]')) { cart.items = cart.items.filter(item => String(item.id) !== row.dataset.cartId); if (!cart.items.length) cart.note = ''; safeSaveCart(); renderCart(); }
    });
    $('cartSubmit')?.addEventListener('click', () => {
      const whatsapp = cartWhatsappLink();
      if (!whatsapp || !cart.items.length) return;
      cart.items.forEach(entry => trackItemOrderClick(entry.id));
      window.open(buildWhatsAppUrl(whatsapp.url, cartMessage()), '_blank', 'noopener');
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && $('cartDrawer')?.classList.contains('open')) closeCart();
    });
  }

  function bindModalEvents() {
    $('itemModalBackdrop')?.addEventListener('click', closeItemModal);
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') closeItemModal();
    });
  }

  function renderAll() {
    renderBrand();
    renderHero();
    renderCategories();
    renderMenu();
    renderContact();
    renderFooter();
  }

  applyTheme();
  syncDirection();
  bindModalEvents();
  initCart();
  renderAll();
})();
