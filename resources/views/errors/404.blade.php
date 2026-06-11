<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الصفحة غير موجودة - Linky</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #115e59;
            --dark: #10202A;
            --muted: #667985;
            --background: #F7FBFB;
            --line: #e5edf0;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: Tahoma, Arial, sans-serif;
            color: var(--dark);
            background:
                radial-gradient(circle at 20% 15%, rgba(15, 118, 110, 0.10), transparent 30%),
                linear-gradient(180deg, var(--background), #ffffff);
        }

        .card {
            width: min(520px, 100%);
            padding: 42px 30px;
            text-align: center;
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(16, 32, 42, 0.10);
        }

        .logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            display: block;
            margin: 0 auto 22px;
        }

        .code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 64px;
            height: 34px;
            margin-bottom: 18px;
            padding: 0 16px;
            border-radius: 999px;
            color: var(--primary);
            background: rgba(15, 118, 110, 0.10);
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(28px, 5vw, 38px);
            line-height: 1.35;
        }

        p {
            margin: 0 auto 28px;
            max-width: 380px;
            color: var(--muted);
            line-height: 1.8;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 24px;
            border-radius: 14px;
            color: #ffffff;
            background: var(--primary);
            font-weight: 800;
            text-decoration: none;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        @media (max-width: 520px) {
            .card {
                padding: 34px 22px;
                border-radius: 22px;
            }
        }
    </style>
</head>
<body>
    <main class="card" role="main">
        <img class="logo" src="{{ asset('images/logo/linky-logo.png') }}" alt="Linky">
        <div class="code">404</div>
        <h1>الصفحة غير موجودة</h1>
        <p>الرابط الذي تحاول فتحه غير صحيح أو لم يعد متاحًا.</p>
        <a class="button" href="{{ url('/') }}">العودة للرئيسية</a>
    </main>
</body>
</html>
