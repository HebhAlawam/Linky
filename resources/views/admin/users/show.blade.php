@extends('dashboard.layouts.main')

@section('title', 'تفاصيل المستخدم')
@section('navbar-title', 'إدارة المنصة')
@section('page-title', 'تفاصيل المستخدم')
@section('page-subtitle', $managedUser->name)

@section('page-actions')
<a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-right ms-1"></i>
    العودة للمستخدمين
</a>
@endsection

@section('content')
@include('partials.alerts')

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">بيانات الحساب</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">الاسم</dt>
                    <dd class="col-sm-8">{{ $managedUser->name }}</dd>

                    <dt class="col-sm-4">البريد الإلكتروني</dt>
                    <dd class="col-sm-8" dir="ltr">{{ $managedUser->email }}</dd>

                    <dt class="col-sm-4">اسم النشاط</dt>
                    <dd class="col-sm-8">{{ $managedUser->business_name ?: '—' }}</dd>

                    <dt class="col-sm-4">الدور</dt>
                    <dd class="col-sm-8">{{ $managedUser->isAdmin() ? 'مدير' : 'مستخدم' }}</dd>

                    <dt class="col-sm-4">تاريخ التسجيل</dt>
                    <dd class="col-sm-8">{{ $managedUser->created_at?->format('Y-m-d H:i') }}</dd>

                    <dt class="col-sm-4">حالة الحساب</dt>
                    <dd class="col-sm-8">
                        @if ($managedUser->isSuspended())
                            <span class="badge bg-red-lt">موقوف</span>
                        @else
                            <span class="badge bg-green-lt">نشط</span>
                        @endif
                    </dd>

                    @if ($managedUser->isSuspended())
                        <dt class="col-sm-4">تاريخ الإيقاف</dt>
                        <dd class="col-sm-8">{{ $managedUser->suspended_at?->format('Y-m-d H:i') ?: '—' }}</dd>

                        <dt class="col-sm-4">سبب الإيقاف</dt>
                        <dd class="col-sm-8">{{ $managedUser->suspended_reason ?: 'لم يتم تحديد سبب.' }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">بيانات الموقع</h3>
            </div>
            <div class="card-body">
                @if ($page)
                    <dl class="row mb-4">
                        <dt class="col-sm-4">عنوان الموقع</dt>
                        <dd class="col-sm-8">{{ $page->title_text }}</dd>

                        <dt class="col-sm-4">الرابط</dt>
                        <dd class="col-sm-8">
                            <a href="{{ $page->url }}" target="_blank" rel="noopener" dir="ltr">/{{ $page->slug }}</a>
                        </dd>

                        <dt class="col-sm-4">حالة الموقع</dt>
                        <dd class="col-sm-8">{{ $page->status }}</dd>
                    </dl>

                    <div class="row g-3">
                        <div class="col-sm-4">
                            <div class="card bg-primary-lt">
                                <div class="card-body text-center">
                                    <div class="h2 mb-1">{{ $page->categories_count }}</div>
                                    <div class="text-secondary">التصنيفات</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card bg-primary-lt">
                                <div class="card-body text-center">
                                    <div class="h2 mb-1">{{ $page->items_count }}</div>
                                    <div class="text-secondary">العناصر</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card bg-primary-lt">
                                <div class="card-body text-center">
                                    <div class="h2 mb-1">{{ $page->links_count }}</div>
                                    <div class="text-secondary">الروابط</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="mb-0 text-secondary">لم ينشئ هذا المستخدم موقعًا بعد.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">إدارة حالة الحساب</h3>
            </div>
            <div class="card-body">
                @if ($managedUser->isSuspended())
                    <p class="text-secondary">إعادة التفعيل تسمح للمستخدم بالدخول إلى لوحة التحكم وتعيد إظهار موقعه المنشور.</p>
                    <form method="POST" action="{{ route('admin.users.activate', $managedUser) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success w-100">إعادة تفعيل الحساب</button>
                    </form>
                @elseif (auth()->user()->is($managedUser))
                    <div class="alert alert-info mb-0">لا يمكنك إيقاف حسابك الإداري الحالي.</div>
                @else
                    <p class="text-secondary">سيُمنع المستخدم من لوحة التحكم، ولن يظهر موقعه للعامة أثناء الإيقاف.</p>
                    <form method="POST" action="{{ route('admin.users.suspend', $managedUser) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label" for="suspended_reason">سبب الإيقاف (اختياري)</label>
                            <textarea
                                id="suspended_reason"
                                name="suspended_reason"
                                rows="4"
                                maxlength="500"
                                class="form-control @error('suspended_reason') is-invalid @enderror"
                            >{{ old('suspended_reason') }}</textarea>
                            @error('suspended_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger w-100">إيقاف الحساب</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
