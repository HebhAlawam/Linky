@extends('dashboard.layouts.main')

@section('title', 'إضافة رابط')
@section('navbar-title', 'إضافة رابط')
@section('page-title', 'إضافة رابط')
@section('page-subtitle', 'أضف رابط تواصل أو حساب اجتماعي')

@section('page-actions')
<a href="{{ route('dashboard.links.index') }}" class="btn btn-outline-secondary">
    <i class="ti ti-arrow-right ms-2"></i>
    رجوع
</a>
@endsection

@section('content')
@include('partials.alerts')

<form method="POST" action="{{ route('dashboard.links.store') }}" class="card">
    @csrf

    <div class="card-body">
        @include('dashboard.links._form')
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('dashboard.links.index') }}" class="btn btn-link">إلغاء</a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy ms-2"></i>
            حفظ الرابط
        </button>
    </div>
</form>
@endsection