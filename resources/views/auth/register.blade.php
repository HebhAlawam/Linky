<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إنشاء حساب - Linky</title>
    <style>
        :root { --primary:#0f766e; --primary-dark:#115e59; --ink:#10202a; --muted:#667985; --line:#e5edf0; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; font-family:Tahoma, Arial, sans-serif; color:var(--ink); background:linear-gradient(135deg,#f6fbfb,#eef7f6); display:grid; place-items:center; padding:24px; }
        a { color:var(--primary); text-decoration:none; font-weight:700; }
        .auth-shell { width:min(980px,100%); display:grid; grid-template-columns:1fr 1.05fr; background:#fff; border:1px solid var(--line); border-radius:28px; overflow:hidden; box-shadow:0 26px 80px rgba(16,32,42,.12); }
        .panel { background:linear-gradient(145deg,#10202a,#123f43); color:#fff; padding:42px; display:flex; flex-direction:column; justify-content:space-between; min-height:660px; }
        .brand { display:inline-flex; align-items:center; gap:10px; font-weight:800; font-size:24px; color:#fff; }
        .brand-mark { width:40px; height:40px; border-radius:13px; display:grid; place-items:center; background:var(--primary); color:#fff; }
        .panel h1 { margin:70px 0 12px; font-size:34px; line-height:1.35; }
        .panel p { color:rgba(255,255,255,.72); margin:0; }
        .form-side { padding:44px; }
        .top-link { display:inline-flex; margin-bottom:28px; color:var(--muted); font-size:14px; }
        h2 { margin:0 0 8px; font-size:30px; }
        .subtitle { margin:0 0 24px; color:var(--muted); }
        .field { margin-bottom:16px; }
        label { display:block; margin-bottom:8px; font-weight:700; }
        input { width:100%; min-height:48px; border:1px solid var(--line); border-radius:14px; padding:0 14px; font:inherit; }
        input[type="email"], input[type="password"] { direction:ltr; text-align:left; }
        input:focus { outline:2px solid rgba(15,118,110,.18); border-color:var(--primary); }
        .btn { width:100%; min-height:50px; border:0; border-radius:14px; background:var(--primary); color:#fff; font:inherit; font-weight:800; cursor:pointer; margin-top:8px; }
        .btn:hover { background:var(--primary-dark); }
        .error { color:#b42318; font-size:13px; margin-top:6px; }
        .switch { margin-top:22px; color:var(--muted); text-align:center; }
        @media (max-width: 820px) { .auth-shell { grid-template-columns:1fr; } .panel { min-height:auto; padding:28px; } .panel h1 { margin:30px 0 10px; } .form-side { padding:30px 22px; } }
    </style>
</head>
<body>
    <main class="auth-shell">
        <aside class="panel">
            <a class="brand" href="{{ url('/') }}"><span class="brand-mark">L</span><span>Linky</span></a>
            <div>
                <h1>ابدأ ببناء موقع مطعمك اليوم</h1>
                <p>أنشئ الموقع، أضف المنتجات، اختر القالب، وشارك QR مع عملائك.</p>
            </div>
        </aside>

        <section class="form-side">
            <a class="top-link" href="{{ url('/') }}">العودة للرئيسية</a>
            <h2>إنشاء حساب</h2>
            <p class="subtitle">أنشئ حسابك وابدأ ببناء موقعك</p>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="field">
                    <label for="name">الاسم</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="email">البريد الإلكتروني</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="password">كلمة المرور</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">تأكيد كلمة المرور</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                    @error('password_confirmation') <div class="error">{{ $message }}</div> @enderror
                </div>
                <button class="btn" type="submit">إنشاء الحساب</button>
            </form>
            <p class="switch">لديك حساب بالفعل؟ <a href="{{ route('login') }}">تسجيل الدخول</a></p>
        </section>
    </main>
</body>
</html>
