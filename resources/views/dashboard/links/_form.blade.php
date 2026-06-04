@php
    $isEdit = isset($link);
    $disabledTypes = $disabledTypes ?? [];
    $selectedTypeValue = old('type', $isEdit ? $selectedType : request('type', 'custom'));
    $titlePayload = $isEdit ? $link->title_payload : ['ar' => null, 'en' => null];
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

<h3 class="card-title mb-3">معلومات الرابط</h3>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label required" for="type">النوع</label>
        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
            @foreach ($types as $value => $label)
                @php
                    $isDisabled = in_array($value, $disabledTypes, true) && $selectedTypeValue !== $value;
                @endphp
                <option value="{{ $value }}" @selected($selectedTypeValue === $value) @disabled($isDisabled)>
                    {{ $label }}{{ $isDisabled ? ' - مضاف مسبقًا' : '' }}
                </option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label required" for="title_ar">العنوان بالعربي</label>
        <input type="text" id="title_ar" name="title_ar" value="{{ old('title_ar', $titlePayload['ar'] ?? ($isEdit ? $link->title_text : '')) }}" class="form-control @error('title_ar') is-invalid @enderror" required>
        @error('title_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="title_en">العنوان بالإنكليزي</label>
        <input type="text" id="title_en" name="title_en" value="{{ old('title_en', $titlePayload['en'] ?? '') }}" class="form-control @error('title_en') is-invalid @enderror">
        @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="display_order">الترتيب</label>
        <input type="number" id="display_order" name="display_order" value="{{ old('display_order', $isEdit ? $link->display_order : '') }}" min="0" class="form-control @error('display_order') is-invalid @enderror" placeholder="تلقائي">
        @error('display_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <label class="form-label required" for="url">الرابط / الرقم</label>
        <input type="text" id="url" name="url" value="{{ old('url', $isEdit ? $link->url : '') }}" class="form-control @error('url') is-invalid @enderror" required dir="ltr" placeholder="مثال: 9639xxxxxxx أو instagram.com/linky">
        <div class="form-hint">سيتم تنسيق واتساب، الهاتف، والبريد الإلكتروني تلقائياً عند الحفظ.</div>
        @error('url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
