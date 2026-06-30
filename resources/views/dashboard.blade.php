@extends('dashboard.layouts.main')

@section('title', 'لوحة التحكم')
@section('navbar-title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')
@section('page-subtitle', 'نظرة عامة على موقعك')

@section('content')
@include('partials.alerts')

@if (! $page)
    <div class="card">
        <div class="card-body text-center py-5">
            <div class="mb-3">
                <span class="avatar avatar-xl bg-primary-lt text-primary">
                    <i class="ti ti-world-plus fs-1"></i>
                </span>
            </div>
            <h3 class="card-title mb-2">أنشئ موقعك الأول للبدء بإدارة المحتوى.</h3>
            <p class="text-secondary mb-4">بعد إنشاء الموقع ستتمكن من إضافة التصنيفات والعناصر وروابط التواصل.</p>
            <a href="{{ route('dashboard.my-website.index') }}" class="btn btn-primary">
                <i class="ti ti-plus ms-2"></i>
                إنشاء موقعي
            </a>
        </div>
    </div>
@else
    @php($publicUrl = url('/' . $page->slug))

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="min-w-0">
                        <div class="text-secondary mb-1">موقعك الحالي</div>
                        <h3 class="card-title mb-2">{{ $page->title_text }}</h3>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-{{ $page->status === 'published' ? 'green' : 'secondary' }}-lt">
                                {{ $page->status === 'published' ? 'منشور' : 'مسودة' }}
                            </span>
                            <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="text-secondary text-truncate d-inline-block" style="max-width: 260px;" title="{{ $publicUrl }}" dir="ltr">
                                /{{ $page->slug }}
                            </a>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                            <i class="ti ti-external-link ms-2"></i>
                            معاينة الموقع
                        </a>
                        <a href="{{ route('dashboard.qr.download') }}" class="btn btn-outline-primary">
                            <i class="ti ti-qrcode ms-2"></i>
                            تحميل QR
                        </a>
                        <button type="button" class="btn btn-outline-primary js-share-public-link" data-url="{{ $publicUrl }}" data-title="{{ $page->title_text }}">
                            <i class="ti ti-share ms-2"></i>
                            مشاركة الرابط
                        </button>
                        <a href="{{ route('dashboard.my-website.index') }}" class="btn btn-primary">
                            <i class="ti ti-edit ms-2"></i>
                            تعديل موقعي
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('dashboard.categories.index') }}" class="card card-link h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary">التصنيفات</div>
                            <div class="h1 mb-0">{{ $stats['categories'] }}</div>
                        </div>
                        <span class="avatar bg-primary-lt text-primary"><i class="ti ti-category"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('dashboard.items.index') }}" class="card card-link h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary">العناصر</div>
                            <div class="h1 mb-0">{{ $stats['items'] }}</div>
                        </div>
                        <span class="avatar bg-primary-lt text-primary"><i class="ti ti-tools-kitchen-2"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('dashboard.links.index') }}" class="card card-link h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary">روابط التواصل</div>
                            <div class="h1 mb-0">{{ $stats['links'] }}</div>
                        </div>
                        <span class="avatar bg-primary-lt text-primary"><i class="ti ti-link"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary">زيارات اليوم</div>
                            <div class="h1 mb-0">{{ $stats['today_visits'] }}</div>
                        </div>
                        <span class="avatar bg-primary-lt text-primary"><i class="ti ti-chart-bar"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">إجراءات سريعة</h3>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('dashboard.categories.create') }}" class="btn btn-outline-primary">
                    <i class="ti ti-plus ms-2"></i>
                    إضافة تصنيف
                </a>
                <a href="{{ route('dashboard.items.create') }}" class="btn btn-outline-primary">
                    <i class="ti ti-plus ms-2"></i>
                    إضافة منتج
                </a>
                <a href="{{ route('dashboard.links.create') }}" class="btn btn-outline-primary">
                    <i class="ti ti-plus ms-2"></i>
                    إضافة رابط
                </a>
                @if ($hasAppearanceRoute)
                    <a href="{{ route('dashboard.appearance.index') }}" class="btn btn-outline-primary">
                        <i class="ti ti-paint ms-2"></i>
                        تعديل المظهر
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
@if ($page)
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
