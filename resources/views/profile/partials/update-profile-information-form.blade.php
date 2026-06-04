<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title mb-1">معلومات الحساب</h3>
            <p class="text-secondary mb-0">حدّث اسمك وبريدك الإلكتروني.</p>
        </div>
    </div>

    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="card-body">
            <div class="mb-3">
                <label class="form-label required" for="name">الاسم</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required autofocus autocomplete="name">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label required" for="email">البريد الإلكتروني</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required autocomplete="username" dir="ltr">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="alert alert-warning mt-3 mb-0" role="alert">
                        <div>بريدك الإلكتروني غير مؤكد.</div>
                        <button form="send-verification" class="btn btn-link p-0 mt-1" type="submit">
                            إعادة إرسال رسالة التحقق
                        </button>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-3 mb-0" role="alert">
                            تم إرسال رابط تحقق جديد إلى بريدك الإلكتروني.
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="card-footer d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">حفظ البيانات</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success">تم الحفظ.</span>
            @endif
        </div>
    </form>
</div>
