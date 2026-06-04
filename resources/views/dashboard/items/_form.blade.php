@php
    $isEdit = isset($item);
    $titleAr = old('title_ar', $isEdit ? $item->getTranslation('title', 'ar', false) : '');
    $titleEn = old('title_en', $isEdit ? $item->getTranslation('title', 'en', false) : '');
    $shortDescriptionAr = old('short_description_ar', $isEdit ? $item->getTranslation('short_description', 'ar', false) : '');
    $shortDescriptionEn = old('short_description_en', $isEdit ? $item->getTranslation('short_description', 'en', false) : '');
    $descriptionAr = old('description_ar', $isEdit ? $item->getTranslation('description', 'ar', false) : '');
    $descriptionEn = old('description_en', $isEdit ? $item->getTranslation('description', 'en', false) : '');
    $selectedCategoryId = old('category_id', $isEdit ? $item->category_id : '');
    $priceValue = $isEdit ? $item->price : null;
    $defaultPriceAr = is_array($priceValue) ? ($priceValue['ar'] ?? $priceValue['en'] ?? '') : (is_string($priceValue) ? $priceValue : '');
    $defaultPriceEn = is_array($priceValue) ? ($priceValue['en'] ?? '') : '';
    $priceAr = old('price_ar', $defaultPriceAr);
    $priceEn = old('price_en', $defaultPriceEn);
@endphp

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-bold mb-1">يرجى مراجعة الحقول التالية:</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($categories->isEmpty())
    <div class="alert alert-warning d-flex align-items-center justify-content-between gap-3" role="alert">
        <div>أضف تصنيفاً أولاً قبل إنشاء العناصر.</div>
        <a href="{{ route('dashboard.categories.create') }}" class="btn btn-warning">
            <i class="ti ti-plus ms-2"></i>
            إضافة تصنيف
        </a>
    </div>
@endif

<h3 class="card-title mb-3">المعلومات الأساسية</h3>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label required" for="title_ar">العنوان بالعربي</label>
        <input type="text" id="title_ar" name="title_ar" value="{{ $titleAr }}" class="form-control @error('title_ar') is-invalid @enderror" required @disabled($categories->isEmpty())>
        @error('title_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="title_en">العنوان بالإنكليزي</label>
        <input type="text" id="title_en" name="title_en" value="{{ $titleEn }}" class="form-control @error('title_en') is-invalid @enderror" dir="ltr" @disabled($categories->isEmpty())>
        @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<h3 class="card-title mb-3">الوصف</h3>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label" for="short_description_ar">الوصف المختصر بالعربي</label>
        <textarea id="short_description_ar" name="short_description_ar" rows="3" class="form-control @error('short_description_ar') is-invalid @enderror" @disabled($categories->isEmpty())>{{ $shortDescriptionAr }}</textarea>
        @error('short_description_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="short_description_en">الوصف المختصر بالإنكليزي</label>
        <textarea id="short_description_en" name="short_description_en" rows="3" class="form-control @error('short_description_en') is-invalid @enderror" dir="ltr" @disabled($categories->isEmpty())>{{ $shortDescriptionEn }}</textarea>
        @error('short_description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="description_ar">الوصف الكامل بالعربي</label>
        <textarea id="description_ar" name="description_ar" rows="5" class="form-control @error('description_ar') is-invalid @enderror" @disabled($categories->isEmpty())>{{ $descriptionAr }}</textarea>
        @error('description_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="description_en">الوصف الكامل بالإنكليزي</label>
        <textarea id="description_en" name="description_en" rows="5" class="form-control @error('description_en') is-invalid @enderror" dir="ltr" @disabled($categories->isEmpty())>{{ $descriptionEn }}</textarea>
        @error('description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<h3 class="card-title mb-3">التصنيف والسعر</h3>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label required" for="category_id">التصنيف</label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required @disabled($categories->isEmpty())>
            <option value="">اختر التصنيف</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>
                    {{ $category->name_text }}
                </option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="price_ar">السعر بالعربي</label>
        <input type="text" id="price_ar" name="price_ar" value="{{ $priceAr }}" class="form-control @error('price_ar') is-invalid @enderror" placeholder="25000 ل.س" @disabled($categories->isEmpty())>
        @error('price_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-3">
        <label class="form-label" for="price_en">السعر بالإنكليزي</label>
        <input type="text" id="price_en" name="price_en" value="{{ $priceEn }}" class="form-control @error('price_en') is-invalid @enderror" placeholder="SYP 25,000" dir="ltr" @disabled($categories->isEmpty())>
        @error('price_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-12">
        <label class="form-label" for="display_order">الترتيب</label>
        <input type="number" id="display_order" name="display_order" value="{{ old('display_order', $isEdit ? $item->display_order : '') }}" min="0" class="form-control @error('display_order') is-invalid @enderror" placeholder="تلقائي" @disabled($categories->isEmpty())>
        @error('display_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<h3 class="card-title mb-3">الصورة والظهور</h3>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="image">الصورة</label>
        <input type="file" id="image" name="image" accept="image/*" class="form-control @error('image') is-invalid @enderror" @disabled($categories->isEmpty())>
        <div class="form-hint">الحد الأقصى 2MB. المقاس المفضل: 1000×1000 بكسل بنسبة مربعة 1:1.</div>
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror

        @if ($isEdit && $item->image)
            <div class="mt-3">
                <div class="form-label">الصورة الحالية</div>
                <img src="{{ $item->image_url }}" alt="{{ $item->title_text }}" class="rounded" style="width:72px;height:72px;object-fit:cover">
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <div class="form-label">إعدادات الظهور</div>
        <label class="form-check form-switch mb-2">
            <input type="hidden" name="is_visible" value="0" @disabled($categories->isEmpty())>
            <input class="form-check-input" type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $isEdit ? $item->is_visible : true)) @disabled($categories->isEmpty())>
            <span class="form-check-label">ظاهر في الصفحة العامة</span>
        </label>

        <label class="form-check form-switch">
            <input type="hidden" name="is_featured" value="0" @disabled($categories->isEmpty())>
            <input class="form-check-input" type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $isEdit ? $item->is_featured : false)) @disabled($categories->isEmpty())>
            <span class="form-check-label">مميز</span>
        </label>
    </div>
</div>
