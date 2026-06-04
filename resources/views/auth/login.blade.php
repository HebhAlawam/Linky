<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول - Linky</title>
    <style>
        :root { --primary:#0f766e; --primary-dark:#115e59; --ink:#10202a; --muted:#667985; --line:#e5edf0; --soft:#f5f9f9; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; font-family:Tahoma, Arial, sans-serif; color:var(--ink); background:linear-gradient(135deg,#f6fbfb,#eef7f6); display:grid; place-items:center; padding:24px; }
        a { color:var(--primary); text-decoration:none; font-weight:700; }
        .auth-shell { width:min(980px,100%); display:grid; grid-template-columns:1fr 1.05fr; background:#fff; border:1px solid var(--line); border-radius:28px; overflow:hidden; box-shadow:0 26px 80px rgba(16,32,42,.12); }
        .panel { background:linear-gradient(145deg,#10202a,#123f43); color:#fff; padding:42px; display:flex; flex-direction:column; justify-content:space-between; min-height:620px; }
        .brand { display:inline-flex; align-items:center; gap:10px; font-weight:800; font-size:24px; color:#fff; }
        .brand-mark { width:40px; height:40px; border-radius:13px; display:grid; place-items:center; background:var(--primary); color:#fff; }
        .panel h1 { margin:70px 0 12px; font-size:34px; line-height:1.35; }
        .panel p { color:rgba(255,255,255,.72); margin:0; }
        .form-side { padding:44px; }
        .top-link { display:inline-flex; margin-bottom:34px; color:var(--muted); font-size:14px; }
        h2 { margin:0 0 8px; font-size:30px; }
        .subtitle { margin:0 0 28px; color:var(--muted); }
        .field { margin-bottom:18px; }
        label { display:block; margin-bottom:8px; font-weight:700; }
        input[type="email"], input[type="password"] { width:100%; min-height:48px; border:1px solid var(--line); border-radius:14px; padding:0 14px; font:inherit; direction:ltr; text-align:left; }
        input:focus { outline:2px solid rgba(15,118,110,.18); border-color:var(--primary); }
        .row { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:10px 0 22px; flex-wrap:wrap; }
        .remember { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:14px; }
        .btn { width:100%; min-height:50px; border:0; border-radius:14px; background:var(--primary); color:#fff; font:inherit; font-weight:800; cursor:pointer; }
        .btn:hover { background:var(--primary-dark); }
        .error { color:#b42318; font-size:13px; margin-top:6px; }
        .status { background:#ecfdf3; color:#027a48; border:1px solid #abefc6; border-radius:14px; padding:12px 14px; margin-bottom:18px; }
        .switch { margin-top:22px; color:var(--muted); text-align:center; }
        @media (max-width: 820px) { .auth-shell { grid-template-columns:1fr; } .panel { min-height:auto; padding:28px; } .panel h1 { margin:30px 0 10px; } .form-side { padding:30px 22px; } }
    </style>
</head>
<body>
    <main class="auth-shell">
        <aside class="panel">
            <a class="brand" href="{{ url('/') }}"><span class="brand-mark">L</span><span>Linky</span></a>
            <div>
                <h1>منيو QR وموقع مصغّر خلال دقائق</h1>
                <p>أدر موقع مطعمك، روابط التواصل، والقوالب من لوحة عربية بسيطة.</p>
            </div>
        </aside>

        <section class="form-side">
            <a class="top-link" href="{{ url('/') }}">العودة للرئيسية</a>
            <h2>تسجيل الدخول</h2>
            <p class="subtitle">ادخل إلى لوحة تحكم Linky</p>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field">
                    <label for="email">البريد الإلكتروني</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="password">كلمة المرور</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="row">
                    <label class="remember" for="remember_me">
                        <input id="remember_me" type="checkbox" name="remember">
                        <span>تذكرني</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">نسيت كلمة المرور؟</a>
                    @endif
                </div>
                <button class="btn" type="submit">تسجيل الدخول</button>
            </form>
            <p class="switch">ليس لديك حساب؟ <a href="{{ route('register') }}">إنشاء حساب</a></p>
        </section>
    </main>
</body>
</html>
