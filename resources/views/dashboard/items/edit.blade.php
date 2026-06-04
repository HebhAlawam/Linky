@extends('dashboard.layouts.main')

@section('title', 'تعديل العنصر')
@section('navbar-title', 'تعديل العنصر')
@section('page-title', 'تعديل العنصر')
@section('page-subtitle', 'عدّل بيانات العنصر')

@section('page-actions')
<a href="{{ route('dashboard.items.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-right ms-2"></i>
    رجوع
</a>
@endsection

@section('content')
@include('partials.alerts')

<form method="POST" action="{{ route('dashboard.items.update', $item) }}" enctype="multipart/form-data" class="card">
    @csrf
    @method('PUT')

    <div class="card-body">
        @include('dashboard.items._form')
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('dashboard.items.index') }}" class="btn btn-link">إلغاء</a>
        <button type="submit" class="btn btn-primary" @disabled($categories->isEmpty())>
            <i class="ti ti-device-floppy ms-2"></i>
            حفظ التعديلات
        </button>
    </div>
</form>
@endsection