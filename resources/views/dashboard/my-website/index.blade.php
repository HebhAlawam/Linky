@extends('dashboard.layouts.main')

@section('title', 'موقعي')
@section('navbar-title', 'موقعي')
@section('page-title', 'موقعي')
@section('page-subtitle', $isEdit ? 'عدّل بيانات موقعك الأساسية' : 'أنشئ موقعك الأول لبدء إضافة التصنيفات والعناصر والروابط')

@section('content')
@include('partials.alerts')

@php
    $title = $page?->translationPayload('title') ?? [];
    $slogan = $page?->translationPayload('slogan') ?? [];
    $action = $isEdit ? route('dashboard.my-website.update') : route('dashboard.my-website.store');
    $isSuspended = $isSuspended ?? false;
    $publicUrl = $isEdit ? url('/' . $page->slug) : null;
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="card">
    @csrf
    @if ($isEdit)
        @method('PATCH')
    @endif

    <div class="card-body">
        @if ($isEdit)
            <div class="card bg-primary-lt border-primary-lt mb-4">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="min-w-0">
                        <div class="text-secondary mb-1">رابط الموقع العام</div>
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="fw-bold text-truncate d-inline-block" style="max-width: 280px;" title="{{ $publicUrl }}" dir="ltr">
                            /{{ $page->slug }}
                        </a>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-external-link ms-1"></i>
                            معاينة الموقع
                        </a>
                        <a href="{{ route('dashboard.qr.download') }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-qrcode ms-1"></i>
                            تحميل QR
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-primary js-share-public-link" data-url="{{ $publicUrl }}" data-title="{{ $page->title_text }}">
                            <i class="ti ti-share ms-1"></i>
                            مشاركة الرابط
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if ($isSuspended)
            <div class="alert alert-warning" role="alert">
                موقعك موقوف مؤقتًا من الإدارة، يرجى التواصل مع الدعم.
            </div>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label required" for="title_ar">اسم الموقع بالعربي</label>
                <input type="text" id="title_ar" name="title_ar" value="{{ old('title_ar', $title['ar'] ?? '') }}" class="form-control @error('title_ar') is-invalid @enderror" required>
                @error('title_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="title_en">اسم الموقع بالإنكليزي</label>
                <input type="text" id="title_en" name="title_en" value="{{ old('title_en', $title['en'] ?? '') }}" class="form-control @error('title_en') is-invalid @enderror" dir="ltr">
                @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="slogan_ar">السلوغن بالعربي</label>
                <textarea id="slogan_ar" name="slogan_ar" rows="3" class="form-control @error('slogan_ar') is-invalid @enderror">{{ old('slogan_ar', $slogan['ar'] ?? '') }}</textarea>
                <div class="form-hint">يظهر السلوغن تحت اسم الموقع في القوالب.</div>
                @error('slogan_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="slogan_en">السلوغن بالإنكليزي</label>
                <textarea id="slogan_en" name="slogan_en" rows="3" class="form-control @error('slogan_en') is-invalid @enderror" dir="ltr">{{ old('slogan_en', $slogan['en'] ?? '') }}</textarea>
                <div class="form-hint">يظهر عند تبديل الموقع إلى الإنجليزية.</div>
                @error('slogan_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label required" for="slug">رابط الموقع</label>
                <div class="input-group" dir="ltr">
                    <span class="input-group-text">{{ url('/') }}/</span>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $page->slug ?? '') }}" class="form-control @error('slug') is-invalid @enderror" dir="ltr">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-hint">مثال: demo-restaurant</div>
            </div>

            <div class="col-md-4">
                <label class="form-label required" for="status">حالة النشر</label>
                @if ($isSuspended)
                    <select id="status" class="form-select" disabled>
                        <option selected>موقوف</option>
                    </select>
                    <div class="form-hint">لا يمكن تغيير حالة الموقع الموقوف من لوحة المستخدم.</div>
                @else
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $page->status ?? 'published') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="logo">الشعار</label>
                <input type="file" id="logo" name="logo" accept="image/*" class="form-control @error('logo') is-invalid @enderror">
                <div class="form-hint">الحد الأقصى 2MB. الصيغ المدعومة: JPG, PNG, WEBP, SVG. تُحسّن الصور النقطية تلقائيًا، ويدعم SVG كما هو. المقاس المفضل: 600×600 بكسل.</div>
                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if ($isEdit)
                <div class="col-md-6">
                    <div class="form-label">الشعار الحالي</div>
                    <img src="{{ $page->logo_url }}" alt="{{ $page->title_text }}" class="rounded " style="width:72px;height:72px;object-fit:contain">
                </div>
            @endif
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('dashboard') }}" class="btn btn-link">إلغاء</a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy ms-2"></i>
            {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الموقع' }}
        </button>
    </div>
</form>
@endsection

@push('scripts')
@if ($isEdit)
<script>
document.querySelectorAll('.js-share-public-link').forEach((button) => {
    button.addEventListener('click', async () => {
        const url = button.dataset.url;
        const title = button.dataset.title || 'Linky';

        if (navigator.share) {
            try {
                await navigator.share({ title, url });
                return;
            } catch (error) {
                if (error.name === 'AbortError') return;
            }
        }

        await copyPublicLink(url);
        const originalHtml = button.innerHTML;
        button.textContent = 'تم نسخ رابط الموقع.';

        setTimeout(() => {
            button.innerHTML = originalHtml;
        }, 1800);
    });
});

async function copyPublicLink(url) {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(url);
            return;
        } catch (error) {
            // Fall back to the temporary textarea method below.
        }
    }

    const input = document.createElement('textarea');
    input.value = url;
    input.setAttribute('readonly', 'readonly');
    input.style.position = 'fixed';
    input.style.opacity = '0';
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    input.remove();
}
</script>
@endif
@endpush
