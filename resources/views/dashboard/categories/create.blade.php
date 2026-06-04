@extends('dashboard.layouts.main')

@section('title', 'إضافة تصنيف')
@section('navbar-title', 'إضافة تصنيف')
@section('page-title', 'إضافة تصنيف')
@section('page-subtitle', 'أضف قسماً جديداً لتنظيم العناصر')

@section('page-actions')
<a href="{{ route('dashboard.categories.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-right ms-2"></i>
    رجوع
</a>
@endsection

@section('content')
@include('partials.alerts')

<form method="POST" action="{{ route('dashboard.categories.store') }}" enctype="multipart/form-data" class="card">
    @csrf

    <div class="card-body">
        @include('dashboard.categories._form')
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('dashboard.categories.index') }}" class="btn btn-link">إلغاء</a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy ms-2"></i>
            حفظ التصنيف
        </button>
    </div>
</form>
@endsection
