@extends('dashboard.layouts.main')

@section('title', 'إعدادات الحساب')
@section('navbar-title', 'إعدادات الحساب')
@section('page-title', 'إعدادات الحساب')
@section('page-subtitle', 'إدارة بيانات حسابك وكلمة المرور')

@section('content')
<div class="mx-auto" style="max-width: 880px;">
    <div class="mb-4">
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="mb-4">
        @include('profile.partials.update-password-form')
    </div>

    <div>
        @include('profile.partials.delete-user-form')
    </div>
</div>
@endsection
