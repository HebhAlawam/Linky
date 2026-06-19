@php
    $isEdit = isset($category);
    $nameAr = old('name_ar', $isEdit ? $category->getTranslation('name', 'ar', false) : '');
    $nameEn = old('name_en', $isEdit ? $category->getTranslation('name', 'en', false) : '');
    $descriptionAr = old('description_ar', $isEdit ? $category->getTranslation('description', 'ar', false) : '');
    $descriptionEn = old('description_en', $isEdit ? $category->getTranslation('description', 'en', false) : '');
@endphp

<h3 class="card-title mb-3">المعلومات الأساسية</h3>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label required" for="name_ar">الاسم بالعربي</label>
        <input type="text" id="name_ar" name="name_ar" value="{{ $nameAr }}" class="form-control @error('name_ar') is-invalid @enderror" required>
        @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="name_en">الاسم بالإنكليزي</label>
        <input type="text" id="name_en" name="name_en" value="{{ $nameEn }}" class="form-control @error('name_en') is-invalid @enderror" dir="ltr">
        @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<h3 class="card-title mb-3">الوصف</h3>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label" for="description_ar">الوصف بالعربي</label>
        <textarea id="description_ar" name="description_ar" rows="4" class="form-control @error('description_ar') is-invalid @enderror">{{ $descriptionAr }}</textarea>
        @error('description_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="description_en">الوصف بالإنكليزي</label>
        <textarea id="description_en" name="description_en" rows="4" class="form-control @error('description_en') is-invalid @enderror" dir="ltr">{{ $descriptionEn }}</textarea>
        @error('description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<h3 class="card-title mb-3">الصورة والظهور</h3>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="image">الصورة</label>
        <input type="file" id="image" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
        <div class="form-hint">الحد الأقصى 5MB. الصيغ المدعومة: JPG, PNG, WEBP. تُحسّن الصورة تلقائيًا للويب، والمقاس المفضل مربع 1:1.</div>
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror

        @if ($isEdit && $category->image)
            <div class="mt-3">
                <div class="form-label">الصورة الحالية</div>
                <img src="{{ $category->image_url }}" alt="{{ $category->name_text }}" class="rounded" style="width:72px;height:72px;object-fit:cover">
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <div class="form-label">إعدادات الظهور</div>
        <label class="form-check form-switch">
            <input type="hidden" name="is_visible" value="0">
            <input class="form-check-input" type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $isEdit ? $category->is_visible : true))>
            <span class="form-check-label">ظاهر في الصفحة العامة</span>
        </label>
    </div>
</div>
