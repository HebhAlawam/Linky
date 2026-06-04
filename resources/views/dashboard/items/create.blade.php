@extends('dashboard.layouts.main')

@section('title', 'إضافة عنصر')
@section('navbar-title', 'إضافة عنصر')
@section('page-title', 'إضافة عنصر')
@section('page-subtitle', 'أضف طبقاً / منتجاً / خدمة جديدة')

@section('page-actions')
<a href="{{ route('dashboard.items.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-right ms-2"></i>
    رجوع
</a>
@endsection

@section('content')
@include('partials.alerts')

<form method="POST" action="{{ route('dashboard.items.store') }}" enctype="multipart/form-data" class="card">
    @csrf

    <div class="card-body">
        @include('dashboard.items._form')
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('dashboard.items.index') }}" class="btn btn-link">إلغاء</a>
        <button type="submit" class="btn btn-primary" @disabled($categories->isEmpty())>
            <i class="ti ti-device-floppy ms-2"></i>
            حفظ العنصر
        </button>
    </div>
</form>
@endsection