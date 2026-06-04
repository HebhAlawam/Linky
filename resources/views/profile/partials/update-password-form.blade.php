<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title mb-1">تحديث كلمة المرور</h3>
            <p class="text-secondary mb-0">استخدم كلمة مرور قوية للحفاظ على أمان حسابك.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="update_password_current_password">كلمة المرور الحالية</label>
                <input id="update_password_current_password" name="current_password" type="password" class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif" autocomplete="current-password">
                @foreach ($errors->updatePassword->get('current_password') as $message)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @endforeach
            </div>

            <div class="mb-3">
                <label class="form-label" for="update_password_password">كلمة المرور الجديدة</label>
                <input id="update_password_password" name="password" type="password" class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif" autocomplete="new-password">
                @foreach ($errors->updatePassword->get('password') as $message)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @endforeach
            </div>

            <div class="mb-3">
                <label class="form-label" for="update_password_password_confirmation">تأكيد كلمة المرور</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif" autocomplete="new-password">
                @foreach ($errors->updatePassword->get('password_confirmation') as $message)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @endforeach
            </div>
        </div>

        <div class="card-footer d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">تحديث كلمة المرور</button>

            @if (session('status') === 'password-updated')
                <span class="text-success">تم الحفظ.</span>
            @endif
        </div>
    </form>
</div>
