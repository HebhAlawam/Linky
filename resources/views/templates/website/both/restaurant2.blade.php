<!DOCTYPE html>
<html lang="ar" dir="rtl" id="mainHtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $titlePayload = $pageData['page']['title'] ?? [];
        $pageTitle = is_array($titlePayload)
            ? ($titlePayload['ar'] ?? $titlePayload['en'] ?? collect($titlePayload)->filter()->first() ?? 'Linky')
            : ($titlePayload ?: 'Linky');

        $primaryColor = $pageData['page']['settings']['primary_color'] ?? '#ea580c';
        $primaryColor = is_string($primaryColor) && preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $primaryColor)
            ? $primaryColor
            : '#ea580c';

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
    <meta property="og:title" content="{{ $publicMeta['title'] }}">
    <meta property="og:description" content="{{ $publicMeta['description'] }}">
    <meta property="og:image" content="{{ $publicMeta['image'] }}">
    <meta property="og:url" content="{{ $publicMeta['url'] }}">
    <meta property="og:type" content="{{ $publicMeta['type'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $publicMeta['title'] }}">
    <meta name="twitter:description" content="{{ $publicMeta['description'] }}">
    <meta name="twitter:image" content="{{ $publicMeta['image'] }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('templates/restaurant/rest2/style.css') }}?v={{ filemtime(public_path('templates/restaurant/rest2/style.css')) }}">
    <style>
        :root {
            --primary-rgb: {{ implode(' ', $rgb) }};
            --primary: rgb(var(--primary-rgb));
            --primary-hover: {{ $darken($rgb, 0.10) }};
            --primary-dark: {{ $darken($rgb, 0.25) }};
        }
        .shadow-main {
            --tw-shadow-color: rgb(var(--primary-rgb) / 0.3);
            --tw-shadow: var(--tw-shadow-colored);
        }
    </style>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        restaurant: {
                            DEFAULT: 'rgb(var(--primary-rgb) / <alpha-value>)',
                            hover: 'var(--primary-hover)',
                            dark: 'var(--primary-dark)',
                        },
                    },
                    borderRadius: { custom: '1.5rem' }
                }
            }
        };

        window.APP = {
            lang: 'ar',
            data: @json($pageData)
        };
    </script>
</head>
<body class="bg-gray-50 dark:bg-[#0d0d0d] text-gray-900 dark:text-gray-100 min-h-screen">

    <nav class="sticky top-0 z-50 bg-white/90 dark:bg-black/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
            <button type="button" onclick="scrollToTop()" class="flex items-center gap-3 min-w-0 text-start">
                <img id="navLogo" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="" class="h-10 w-auto max-w-[96px] sm:max-w-[120px] object-contain hidden max-sm:hidden">
                <span id="navTitle" class="text-xl font-black text-restaurant truncate"></span>
            </button>
            <div class="flex gap-3">
                <button type="button" onclick="toggleLanguage()" id="langToggle" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-800 font-bold text-sm hover:bg-restaurant hover:text-white transition-colors">English</button>
                <button type="button" onclick="toggleTheme()" id="themeToggle" class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:scale-110 transition-transform" aria-label="Toggle theme">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">

        <div id="heroCard" class="rounded-[2.5rem] overflow-hidden mb-12 relative min-h-[340px] sm:min-h-[390px] shadow-2xl hero-fallback">
            <img id="heroImage" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="" class="absolute inset-0 w-full h-full object-cover hidden">
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/10 flex flex-col justify-end p-7 sm:p-10 lg:p-12 text-white">
                <div class="max-w-2xl">
                    <h1 id="heroTitle" class="text-4xl sm:text-5xl lg:text-6xl font-black mb-4 leading-tight drop-shadow-2xl"></h1>
                    <p id="heroSlogan" class="text-base sm:text-lg opacity-95 leading-8 drop-shadow-lg"></p>
                    <button type="button" onclick="scrollToMenu()" id="heroCta" class="mt-7 inline-flex items-center justify-center gap-2 rounded-2xl bg-restaurant px-6 py-3 font-bold text-white shadow-lg shadow-main transition hover:-translate-y-0.5 hover:bg-restaurant-hover">
                        <span id="heroCtaText"></span>
                        <i class="fa-solid fa-arrow-down"></i>
                    </button>
                </div>
            </div>
        </div>

        <section id="menu" class="mb-10 text-center">
            <h2 id="menuTitle" class="text-3xl font-black text-gray-900 dark:text-white"></h2>
            <p id="menuSubtitle" class="mt-3 text-gray-500 dark:text-gray-400"></p>
        </section>

        <div id="categoryTabs" class="flex gap-4 mb-12 overflow-x-auto pb-4 no-scrollbar text-center"></div>

        <div id="menuGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-20"></div>

        <section id="contact" class="py-12 border-t border-gray-100 dark:border-gray-800">
            <div class="text-center">
                <h2 id="contactTitle" class="text-3xl font-black"></h2>
                <p id="contactSubtitle" class="mt-3 mb-10 text-gray-500 dark:text-gray-400"></p>

                <div id="contactInfoCard" class="mx-auto max-w-[720px] rounded-[1.5rem] bg-white dark:bg-[#1a1a1a] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden text-start">
                    <div id="contactLinksRow" class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="contact-info-line">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-restaurant/10 text-restaurant">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div class="contact-info-content">
                                <h3 id="linksTitle" class="mb-5 font-black"></h3>
                                <div id="contactLinks" class="contact-links-pills mt-2 flex flex-wrap gap-2"></div>
                                <p id="contactLinksEmpty" class="hidden mt-2 text-sm text-gray-500 dark:text-gray-400"></p>
                            </div>
                        </div>
                    </div>

                    <div id="addressRow" class="hidden p-4 sm:p-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="contact-info-line">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-restaurant/10 text-restaurant">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div class="contact-info-content">
                                <h3 id="addressTitle" class="mb-1 font-black"></h3>
                                <p id="addressText" class="text-sm leading-7 text-gray-500 dark:text-gray-400 whitespace-pre-line"></p>
                            </div>
                        </div>
                    </div>

                    <div id="hoursRow" class="hidden p-4 sm:p-5">
                        <div class="contact-info-line">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-restaurant/10 text-restaurant">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div class="contact-info-content">
                                <h3 id="hoursTitle" class="mb-1 font-black"></h3>
                                <p id="hoursText" class="text-sm leading-7 text-gray-500 dark:text-gray-400 whitespace-pre-line"></p>
                            </div>
                        </div>
                    </div>

                    <p id="contactAllEmpty" class="hidden p-6 text-center text-sm text-gray-500 dark:text-gray-400"></p>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-gray-100 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-800">
        <div class="max-w-5xl mx-auto px-6 py-12 text-center">
            <div id="footerLogo" class="mb-6"></div>
            <p id="footerSlogan" class="text-gray-500 dark:text-gray-400 mb-8"></p>

            <ul id="footerSocialLinks" class="flex justify-center gap-8 mb-8"></ul>

            <p class="text-xs text-gray-400">© <span id="year"></span> <span id="footerName"></span> <span id="rightsText">جميع الحقوق محفوظة.</span></p>
        </div>
    </footer>

    <div id="itemModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4 opacity-0 pointer-events-none transition-opacity duration-300" aria-hidden="true">
        <div id="itemModalBackdrop" class="absolute inset-0"></div>
        <div id="itemModalPanel" class="relative w-full max-w-2xl max-h-[92vh] overflow-y-auto rounded-[2rem] bg-white dark:bg-[#1a1a1a] shadow-2xl scale-95 transition-transform duration-300">
            <button type="button" onclick="closeItemModal()" class="absolute top-4 end-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-gray-900 shadow-lg hover:bg-restaurant hover:text-white transition-colors dark:bg-gray-900 dark:text-white" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img id="modalItemImg" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==" alt="" class="w-full h-64 sm:h-80 object-cover">
            <div class="p-6 sm:p-8 text-start">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <h3 id="modalItemName" class="text-2xl sm:text-3xl font-black"></h3>
                    <span id="modalItemPrice" class="text-restaurant font-black text-xl whitespace-nowrap"></span>
                </div>
                <p id="modalItemDesc" class="text-gray-500 dark:text-gray-400 whitespace-pre-line mb-6"></p>
                <a id="modalOrderLink" href="#" target="_blank" rel="noopener" class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-restaurant text-white font-bold hover:bg-restaurant-hover transition-all">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span id="modalOrderText">اطلب عبر واتساب</span>
                </a>
            </div>
        </div>
    </div>

    <script src="{{ asset('templates/restaurant/rest2/script.js') }}?v={{ filemtime(public_path('templates/restaurant/rest2/script.js')) }}"></script>
</body>
</html>
