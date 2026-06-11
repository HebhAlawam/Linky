<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>لينكـي | منيو QR وموقع مصغّر للمطاعم</title>
    <meta name="description" content="أنشئ منيو QR وموقعًا مصغّرًا لمطعمك خلال دقائق مع قوالب جاهزة ولوحة تحكم عربية.">
    <link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #115e59;
            --ink: #10202a;
            --muted: #61717c;
            --line: #e6edf0;
            --soft: #f4f8f8;
            --white: #fff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Tahoma, Arial, sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #f7fbfb 0%, #fff 42%, #f7fbfb 100%);
            line-height: 1.8;
        }

        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; display: block; }

        .container {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 0;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 24px;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            overflow: hidden;
            background: transparent;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .nav-links,
        .hero-actions,
        .cta-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-links {
            color: var(--muted);
            font-size: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 20px;
            border-radius: 12px;
            border: 1px solid var(--line);
            font-weight: 700;
            transition: background .2s ease, color .2s ease, border-color .2s ease, transform .2s ease;
            white-space: nowrap;
        }

        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .btn-outline { background: #fff; color: var(--primary); border-color: rgba(15, 118, 110, .28); }
        .btn-light { background: #fff; color: var(--ink); }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 440px);
            gap: 48px;
            align-items: center;
            padding: 56px 0 76px;
        }

        .eyebrow {
            display: inline-flex;
            padding: 5px 12px;
            border-radius: 999px;
            color: var(--primary);
            background: rgba(15, 118, 110, .08);
            font-weight: 700;
            font-size: 13px;
        }

        h1 {
            margin: 18px 0;
            font-size: clamp(34px, 5vw, 58px);
            line-height: 1.18;
            letter-spacing: 0;
        }

        .hero p {
            margin: 0;
            color: var(--muted);
            font-size: 18px;
            max-width: 680px;
        }

        .hero-actions { margin-top: 28px; }

        .hero-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 28px;
            padding: 18px;
            box-shadow: 0 24px 70px rgba(16, 32, 42, .12);
        }

        .hero-preview {
            min-height: 460px;
            border-radius: 22px;
            background:
                radial-gradient(circle at 24% 18%, rgba(15, 118, 110, .24), transparent 30%),
                linear-gradient(145deg, #10202a, #172f38);
            color: #fff;
            padding: 26px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .qr-box {
            width: 132px;
            height: 132px;
            border-radius: 22px;
            background: #fff;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 6px;
            padding: 16px;
            align-self: flex-start;
        }

        .qr-box span { background: #101820; border-radius: 3px; }
        .hero-preview strong { font-size: 30px; line-height: 1.35; }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 18px;
        }

        .stat {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 16px;
            padding: 12px;
        }

        .stat b {
            display: block;
            /* font-size: 22px; */
            line-height: 1.2;
        }

        .stat span { display: block; font-size: 13px; color: rgba(255,255,255,.82); }

        section { padding: 54px 0; }
        .section-head { text-align: center; margin-bottom: 28px; }
        .section-head h2 { margin: 0 0 8px; font-size: 32px; }
        .section-head p { margin: 0; color: var(--muted); }

        .features,
        .templates,
        .steps {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .feature,
        .step,
        .template-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 10px 30px rgba(16, 32, 42, .04);
        }

        .feature i {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            background: rgba(15, 118, 110, .1);
            color: var(--primary);
            font-style: normal;
            font-weight: 800;
        }

        .feature h3,
        .step h3,
        .template-card h3 {
            margin: 14px 0 6px;
            font-size: 19px;
        }

        .feature p,
        .step p,
        .template-card p {
            color: var(--muted);
            margin: 0;
            font-size: 14px;
        }

        .template-card {
            display: block;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .template-card:hover {
            transform: translateY(-3px);
            border-color: rgba(15, 118, 110, .32);
            box-shadow: 0 18px 45px rgba(16, 32, 42, .08);
        }

        .template-preview {
            height: 250px;
            border-radius: 16px;
            overflow: hidden;
            background: var(--soft);
            border: 1px solid var(--line);
            display: grid;
            place-items: center;
            color: var(--muted);
            font-weight: 700;
            text-align: center;
        }

        .template-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;
        }

        .steps { counter-reset: step; direction: rtl; }
        .step { position: relative; }
        .step::before {
            counter-increment: step;
            content: counter(step);
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: var(--primary);
            color: #fff;
            font-weight: 800;
        }

        .final-cta {
            background: var(--ink);
            color: #fff;
            border-radius: 28px;
            padding: 38px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .final-cta h2 { margin: 0 0 8px; }
        .final-cta p { margin: 0; color: rgba(255,255,255,.72); }
        .final-cta .btn-outline { color: #fff; background: transparent; border-color: rgba(255,255,255,.36); }
        .final-cta .btn-outline:hover { background: rgba(255,255,255,.08); }

        .footer {
            padding: 28px 0;
            color: var(--muted);
            border-top: 1px solid var(--line);
        }

        .footer .container {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        @media (max-width: 880px) {
            .hero,
            .features,
            .templates,
            .steps {
                grid-template-columns: 1fr;
            }

            .hero {
                padding-top: 28px;
                gap: 28px;
            }

            .hero-card { order: -1; }
            .hero-preview { min-height: 380px; }
            .final-cta { align-items: stretch; flex-direction: column; }
            .cta-actions .btn,
            .hero-actions .btn { flex: 1 1 160px; }
            .nav-links { gap: 8px; }
        }

        @media (max-width: 520px) {
            .container { width: min(100% - 24px, 1120px); }
            .nav { align-items: flex-start; }
            .nav-links { justify-content: flex-end; }
            .btn { min-height: 42px; padding: 0 14px; }
            .hero-preview { min-height: 340px; padding: 20px; }
            .qr-box { width: 112px; height: 112px; }
            .stats { grid-template-columns: 1fr; }
            .final-cta { padding: 28px 20px; }
            .footer .container { justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>
    <header class="container nav">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand-mark">
                <img src="{{ asset('images/logo/linky-logo.png') }}" alt="Linky">
            </span>
            <span>لينكـي</span>
        </a>
        <nav class="nav-links" aria-label="روابط رئيسية">
            @auth
                <a class="btn btn-light" href="{{ route('dashboard') }}">لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}">تسجيل الدخول</a>
                <a class="btn btn-primary" href="{{ route('register') }}">ابدأ الآن</a>
            @endauth
        </nav>
    </header>

    <main>
        <section class="container hero">
            <div>
                <span class="eyebrow">منيو QR ومواقع مصغّرة</span>
                <h1>موقع ومنيو QR جاهز لمطعمك خلال دقائق</h1>
                <p>اعرض قائمتك، منتجاتك، روابط التواصل، وشارك QR مع زبائنك بسهولة.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="{{ route('register') }}">ابدأ الآن</a>
                    <a class="btn btn-outline" href="{{ url('/demo-restaurant') }}" target="_blank" rel="noopener">مشاهدة الديمو</a>
                </div>
            </div>

            <div class="hero-card" aria-hidden="true">
                <div class="hero-preview">
                    <div class="qr-box">
                        @for ($i = 0; $i < 25; $i++)
                            <span style="opacity: {{ in_array($i, [1, 3, 6, 7, 10, 12, 13, 17, 18, 20, 22, 24]) ? '.25' : '1' }}"></span>
                        @endfor
                    </div>
<div>
    <strong>صفحة مطعم جاهزة للمشاركة</strong>

    <div class="stats">
        <div class="stat">
            <b>3</b>
            <span>قوالب</span>
        </div>

        <div class="stat">
            <b>QR</b>
            <span>جاهز</span>
        </div>

        <div class="stat">
            <b>إحصائيات</b>
            <span>مباشرة</span>
        </div>
    </div>
</div>
                </div>
            </div>
        </section>

        <section class="container">
            <div class="section-head">
                <h2>المميزات</h2>
                <p>كل ما تحتاجه لإطلاق صفحة مطعم قابلة للمشاركة بسرعة.</p>
            </div>
            <div class="features">
                @foreach ([
                    ['قوالب جاهزة', 'اختر قالبًا مناسبًا وغيّره بدون فقدان المحتوى.'],
                    ['منيو وتصنيفات', 'نظّم المنتجات والأطباق ضمن تصنيفات واضحة.'],
                    ['QR قابل للتحميل', 'حمّل بطاقة QR احترافية لموقعك العام.'],
                    ['روابط تواصل وسوشال', 'واتساب، هاتف، خرائط، وحسابات التواصل الإجتماعي.'],
                    ['إحصائيات الزيارات والنقرات', 'تابع زيارات الصفحة ونقرات الطلب والروابط.'],
                    ['لوحة تحكم سهلة', ' إدارة بسيطة تناسب أصحاب الأعمال الصغيرة.'],
                ] as $index => [$title, $text])
                    <article class="feature">
                        <i>{{ $index + 1 }}</i>
                        <h3>{{ $title }}</h3>
                        <p>{{ $text }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="container">
            <div class="section-head">
                <h2>القوالب</h2>
                <p>ثلاثة قوالب جاهزة يمكنك التبديل بينها بسهولة دون فقدان بياناتك.</p>
            </div>
            <div class="templates">
                @foreach ([
                    ['منيو QR حديث', 'restaurant3-long.png'],
                    ['مطعم داكن فاخر', 'restaurant1-long.png'],
                    ['مطعم بسيط فاتح', 'restaurant2-long.png'],
                ] as [$title, $image])
                    <a class="template-card" href="{{ url('/demo-restaurant') }}" target="_blank" rel="noopener">
                        <div class="template-preview">
                            <img src="{{ asset('templates/previews/' . $image) }}" alt="{{ $title }}" onerror="this.style.display='none'; this.parentElement.textContent='{{ $title }}';">
                        </div>
                        <h3>{{ $title }}</h3>
                        <p>قالب جاهز للمعاينة والتخصيص من لوحة التحكم.</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="container">
            <div class="section-head">
                <h2>كيف يعمل؟</h2>
            </div>
            <div class="steps">
                <article class="step">
                    <h3>سجّل حسابك</h3>
                    <p>ابدأ بحساب بسيط للوصول إلى لوحة التحكم.</p>
                </article>
                <article class="step">
                    <h3>أضف بيانات مطعمك ومنتجاتك</h3>
                    <p>ارفع الشعار، الصور، التصنيفات، المنتجات وروابط التواصل.</p>
                </article>
                <article class="step">
                    <h3>اختر القالب وشارك QR</h3>
                    <p>حمّل QR أو شارك الرابط مباشرة مع عملائك.</p>
                </article>
            </div>
        </section>

        <section class="container">
            <div class="final-cta">
                <div>
                    <h2>جاهز لتجربة لينكـي؟</h2>
                    <p> ابدأ ببناء موقعك وشارك الـ  QR مع عملائك خلال دقائق.</p>
                </div>
                <div class="cta-actions">
                    <a class="btn btn-primary" href="{{ route('register') }}">إنشاء حساب مجاني</a>
                    <a class="btn btn-outline" href="{{ url('/demo-restaurant') }}" target="_blank" rel="noopener">مشاهدة الديمو</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <span>© 2026 Linky</span>
            <span>مواقع مصغّرة ومنيو QR للأعمال المحلية.</span>
        </div>
    </footer>
</body>
</html>
