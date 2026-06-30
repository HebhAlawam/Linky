@extends('dashboard.layouts.main')

@section('title', 'المظهر والقوالب')
@section('navbar-title', 'المظهر والقوالب')
@section('page-title', 'المظهر والقوالب')
@section('page-subtitle', 'خصص شكل موقعك وقوالب العرض.')

@push('styles')
<style>
    .template-card {
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .template-card:hover {
        transform: translateY(-2px);
    }

    .template-card.is-selected {
        border-color: var(--tblr-primary) !important;
        box-shadow: 0 0 0 3px rgba(var(--tblr-primary-rgb), .16);
    }

    .template-card .template-radio {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .template-selected-badge {
        display: none;
    }

    .template-card.is-selected .template-selected-badge {
        display: inline-flex;
    }

    .template-preview-frame {
        height: 260px;
        overflow: hidden;
        border-radius: var(--tblr-border-radius) var(--tblr-border-radius) 0 0;
        background: var(--tblr-secondary-bg);
    }

    .template-preview-img {
        display: block;
        width: 100%;
        height: auto;
        min-height: 100%;
        transition: transform 4s ease;
    }

    .template-card:hover .template-preview-img {
        transform: translateY(calc(-100% + 260px));
    }
</style>
@endpush

@section('content')
@include('partials.alerts')

@php
    $primaryColor = old('primary_color', $settings['primary_color'] ?? '#c94c4c');
    $address = is_array($settings['address'] ?? null) ? $settings['address'] : [];
    $hours = is_array($settings['hours'] ?? null) ? $settings['hours'] : [];
    $selectedTemplate = old('template', $selectedTemplate ?? 'restaurant1');
    $heroImages = collect($settings['hero_images'] ?? [])->map(function ($image) {
        if (is_array($image)) {
            return $image['url'] ?? null;
        }

        return is_string($image) ? $image : null;
    })->values();
@endphp

<form method="POST" action="{{ route('dashboard.appearance.update') }}" enctype="multipart/form-data" class="card">
    @csrf
    @method('PUT')

    <div class="card-body">
        <h3 class="card-title mb-1">اختيار القالب</h3>
        <p class="text-secondary mb-3">يمكنك تغيير شكل موقعك دون فقدان المحتوى.</p>
        <div class="row g-3 mb-4" id="templateCards">
            @foreach ($templates as $key => $template)
                @php
                    $preview = $template['preview'] ?? null;
                    $previewExists = $preview && file_exists(public_path($preview));
                    $isSelected = $selectedTemplate === $key;
                @endphp
                <div class="col-md-4">
                    <label class="card card-link h-100 template-card {{ $isSelected ? 'is-selected' : '' }}" for="template_{{ $key }}" data-template-card>
                        <input type="radio" id="template_{{ $key }}" name="template" value="{{ $key }}" class="template-radio" @checked($isSelected) required>
                        <div class="template-preview-frame">
                            @if ($previewExists)
                                <img src="{{ asset($preview) }}" alt="{{ $template['label'] }}" class="template-preview-img">
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 text-secondary">
                                    <i class="ti ti-layout-template fs-1"></i>
                                </div>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <h4 class="card-title mb-0">{{ $template['label'] }}</h4>
                                <span class="badge bg-primary-lt template-selected-badge">محدد</span>
                            </div>
                            <p class="text-secondary mb-0">{{ $template['description'] }}</p>
                        </div>
                    </label>
                </div>
            @endforeach
            @error('template')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <h3 class="card-title mb-3">الألوان</h3>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label required" for="primary_color">اللون الأساسي</label>
                <div class="input-group">
                    <input type="color" id="primary_color" name="primary_color" value="{{ $primaryColor }}" class="form-control form-control-color @error('primary_color') is-invalid @enderror" required>
                    <input type="text" value="{{ $primaryColor }}" class="form-control" readonly dir="ltr">
                    @error('primary_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-hint">يُستخدم هذا اللون للأزرار والعناصر البارزة في القوالب.</div>
            </div>
        </div>

        <h3 class="card-title mb-3">صور الواجهة الرئيسية</h3>
        <div class="row g-3 mb-4">
            @for ($i = 1; $i <= 3; $i++)
                @php $currentHero = $heroImages->get($i - 1); $field = 'hero_image_' . $i; @endphp
                <div class="col-md-4">
                    <label class="form-label" for="hero_image_{{ $i }}">صورة الواجهة {{ $i }}</label>
                    <input type="file" id="hero_image_{{ $i }}" name="hero_image_{{ $i }}" accept="image/*" class="form-control @error($field) is-invalid @enderror">
                    <div class="form-hint">الحد الأقصى 8MB. الصيغ المدعومة: JPG, PNG, WEBP. تُحسّن الصورة تلقائيًا للويب،
                        والمقاس المفضل 1920 × 850 بكسل .
                    </div>
                    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror

                    @if ($currentHero)
                        <div class="mt-3">
                            <img src="{{ $currentHero }}" alt="صورة الواجهة {{ $i }}" class="rounded" style="width:100%;height:120px;object-fit:cover">
                        </div>
                        <label class="form-check mt-2 cursor-pointer">
                            <input type="hidden" name="remove_hero_image_{{ $i }}" value="0">
                            <input type="checkbox" name="remove_hero_image_{{ $i }}" value="1" class="form-check-input">
                            <span class="form-check-label">إزالة الصورة الحالية</span>
                        </label>
                    @endif
                </div>
            @endfor
        </div>

        <h3 class="card-title mb-3">العنوان ومواعيد العمل</h3>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="address_ar">العنوان بالعربي</label>
                <textarea id="address_ar" name="address_ar" rows="3" class="form-control @error('address_ar') is-invalid @enderror">{{ old('address_ar', $address['ar'] ?? '') }}</textarea>
                @error('address_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="address_en">العنوان بالإنكليزي</label>
                <textarea id="address_en" name="address_en" rows="3" class="form-control @error('address_en') is-invalid @enderror" dir="ltr">{{ old('address_en', $address['en'] ?? '') }}</textarea>
                @error('address_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="hours_ar">مواعيد العمل بالعربي</label>
                <textarea id="hours_ar" name="hours_ar" rows="4" class="form-control @error('hours_ar') is-invalid @enderror">{{ old('hours_ar', $hours['ar'] ?? '') }}</textarea>
                @error('hours_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="hours_en">مواعيد العمل بالإنكليزي</label>
                <textarea id="hours_en" name="hours_en" rows="4" class="form-control @error('hours_en') is-invalid @enderror" dir="ltr">{{ old('hours_en', $hours['en'] ?? '') }}</textarea>
                @error('hours_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('dashboard') }}" class="btn btn-link">إلغاء</a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy ms-2"></i>
            حفظ الإعدادات
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[name="template"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('[data-template-card]').forEach((card) => {
                const input = card.querySelector('[name="template"]');
                card.classList.toggle('is-selected', Boolean(input && input.checked));
            });
        });
    });
</script>
@endpush
