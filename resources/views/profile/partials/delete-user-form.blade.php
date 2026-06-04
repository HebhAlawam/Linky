<div class="card border-danger">
    <div class="card-header">
        <div>
            <h3 class="card-title mb-1 text-danger">حذف الحساب</h3>
            <p class="text-secondary mb-0">عند حذف الحساب سيتم حذف بياناته نهائياً. استخدم هذا الخيار بحذر.</p>
        </div>
    </div>

    <div class="card-body">
        <p class="text-secondary mb-0">
            قبل حذف حسابك، تأكد من حفظ أي بيانات تريد الاحتفاظ بها. لا يمكن التراجع عن هذا الإجراء.
        </p>
    </div>

    <div class="card-footer">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
            حذف الحساب
        </button>
    </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title">تأكيد حذف الحساب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger" role="alert">
                        هل أنت متأكد أنك تريد حذف حسابك؟ سيتم حذف جميع البيانات المرتبطة به نهائياً.
                    </div>

                    <div class="mb-3">
                        <label class="form-label required" for="delete_account_password">كلمة المرور</label>
                        <input id="delete_account_password" name="password" type="password" class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif" autocomplete="current-password">
                        @foreach ($errors->userDeletion->get('password') as $message)
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">حذف الحساب</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('deleteAccountModal');
                if (modal && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modal).show();
                }
            });
        </script>
    @endpush
@endif
