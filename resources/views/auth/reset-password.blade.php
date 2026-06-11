<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تعيين كلمة المرور - Linky</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <style>
        :root { --primary:#0f766e; --primary-dark:#115e59; --ink:#10202a; --muted:#667985; --line:#e5edf0; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; font-family:Tahoma, Arial, sans-serif; color:var(--ink); background:linear-gradient(135deg,#f6fbfb,#eef7f6); display:grid; place-items:center; padding:24px; }
        a { color:var(--primary); text-decoration:none; font-weight:700; }
        .card { width:min(560px,100%); background:#fff; border:1px solid var(--line); border-radius:26px; padding:34px; box-shadow:0 24px 70px rgba(16,32,42,.12); }
        .brand { display:inline-flex; align-items:center; gap:10px; font-weight:800; font-size:24px; margin-bottom:28px; }
        .brand-mark { width:64px; height:64px; display:grid; place-items:center; overflow:hidden; }
        .brand-mark img { width:100%; height:100%; object-fit:contain; }
        h1 { margin:0 0 10px; font-size:28px; }
        .subtitle { margin:0 0 24px; color:var(--muted); }
        .field { margin-bottom:16px; }
        label { display:block; margin-bottom:8px; font-weight:700; }
        input { width:100%; min-height:48px; border:1px solid var(--line); border-radius:14px; padding:0 14px; font:inherit; direction:ltr; text-align:left; }
        input:focus { outline:2px solid rgba(15,118,110,.18); border-color:var(--primary); }
        .btn { width:100%; min-height:50px; border:0; border-radius:14px; background:var(--primary); color:#fff; font:inherit; font-weight:800; cursor:pointer; margin-top:8px; }
        .btn:hover { background:var(--primary-dark); }
        .error { color:#b42318; font-size:13px; margin-top:6px; }
    </style>
</head>
<body>
    <main class="card">
        <a class="brand" href="{{ url('/') }}"><span class="brand-mark"><img src="{{ asset('images/logo/linky-logo.png') }}" alt="Linky"></span><span>Linky</span></a>
        <h1>تعيين كلمة المرور</h1>
        <p class="subtitle">أدخل كلمة مرور جديدة لحسابك.</p>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="field">
                <label for="email">البريد الإلكتروني</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label for="password">كلمة المرور الجديدة</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label for="password_confirmation">تأكيد كلمة المرور</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                @error('password_confirmation') <div class="error">{{ $message }}</div> @enderror
            </div>
            <button class="btn" type="submit">حفظ كلمة المرور الجديدة</button>
        </form>
    </main>
</body>
</html>
