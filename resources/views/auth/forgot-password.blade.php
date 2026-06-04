<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>استعادة كلمة المرور - Linky</title>
    <style>
        :root { --primary:#0f766e; --primary-dark:#115e59; --ink:#10202a; --muted:#667985; --line:#e5edf0; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; font-family:Tahoma, Arial, sans-serif; color:var(--ink); background:linear-gradient(135deg,#f6fbfb,#eef7f6); display:grid; place-items:center; padding:24px; }
        a { color:var(--primary); text-decoration:none; font-weight:700; }
        .card { width:min(520px,100%); background:#fff; border:1px solid var(--line); border-radius:26px; padding:34px; box-shadow:0 24px 70px rgba(16,32,42,.12); }
        .brand { display:inline-flex; align-items:center; gap:10px; font-weight:800; font-size:24px; margin-bottom:28px; }
        .brand-mark { width:40px; height:40px; border-radius:13px; display:grid; place-items:center; background:var(--primary); color:#fff; }
        h1 { margin:0 0 10px; font-size:28px; }
        p { margin:0 0 24px; color:var(--muted); }
        label { display:block; margin-bottom:8px; font-weight:700; }
        input { width:100%; min-height:48px; border:1px solid var(--line); border-radius:14px; padding:0 14px; font:inherit; direction:ltr; text-align:left; }
        input:focus { outline:2px solid rgba(15,118,110,.18); border-color:var(--primary); }
        .btn { width:100%; min-height:50px; border:0; border-radius:14px; background:var(--primary); color:#fff; font:inherit; font-weight:800; cursor:pointer; margin-top:18px; }
        .btn:hover { background:var(--primary-dark); }
        .error { color:#b42318; font-size:13px; margin-top:6px; }
        .status { background:#ecfdf3; color:#027a48; border:1px solid #abefc6; border-radius:14px; padding:12px 14px; margin-bottom:18px; }
        .links { display:flex; justify-content:space-between; gap:12px; margin-top:20px; flex-wrap:wrap; }
    </style>
</head>
<body>
    <main class="card">
        <a class="brand" href="{{ url('/') }}"><span class="brand-mark">L</span><span>Linky</span></a>
        <h1>استعادة كلمة المرور</h1>
        <p>أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.</p>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">البريد الإلكتروني</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email') <div class="error">{{ $message }}</div> @enderror
            <button class="btn" type="submit">إرسال رابط الاستعادة</button>
        </form>

        <div class="links">
            <a href="{{ route('login') }}">تسجيل الدخول</a>
            <a href="{{ url('/') }}">العودة للرئيسية</a>
        </div>
    </main>
</body>
</html>
