@extends('dashboard.layouts.main')

@section('title', 'تعديل الرابط')
@section('navbar-title', 'تعديل الرابط')
@section('page-title', 'تعديل الرابط')
@section('page-subtitle', 'عدّل بيانات الرابط')

@section('page-actions')
<a href="{{ route('dashboard.links.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-right ms-2"></i>
    رجوع
</a>
@endsection

@section('content')
@include('partials.alerts')

<form method="POST" action="{{ route('dashboard.links.update', $link) }}" class="card">
    @csrf
    @method('PUT')

    <div class="card-body">
        @include('dashboard.links._form')
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('dashboard.links.index') }}" class="btn btn-link">إلغاء</a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy ms-2"></i>
            حفظ التعديلات
        </button>
    </div>
</form>
@endsection