@extends('dashboard.layouts.main')

@section('title', 'إدارة المستخدمين')
@section('navbar-title', 'إدارة المنصة')
@section('page-title', 'إدارة المستخدمين')
@section('page-subtitle', 'عرض حسابات Linky ومواقعها وحالة كل حساب')

@section('content')
@include('partials.alerts')

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>اسم النشاط</th>
                    <th>الموقع</th>
                    <th>تاريخ التسجيل</th>
                    <th>الحالة</th>
                    <th class="w-1">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $managedUser)
                    @php($page = $managedUser->pages->first())
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $managedUser->name }}</div>
                            @if ($managedUser->isAdmin())
                                <span class="badge bg-purple-lt mt-1">مدير</span>
                            @endif
                        </td>
                        <td dir="ltr" class="text-start">{{ $managedUser->email }}</td>
                        <td>{{ $managedUser->business_name ?: '—' }}</td>
                        <td>
                            @if ($page)
                                <div class="fw-medium">{{ $page->title_text }}</div>
                                <a href="{{ $page->url }}" target="_blank" rel="noopener" dir="ltr">
                                    /{{ $page->slug }}
                                </a>
                            @else
                                <span class="text-secondary">لا يوجد موقع</span>
                            @endif
                        </td>
                        <td>{{ $managedUser->created_at?->format('Y-m-d') }}</td>
                        <td>
                            @if ($managedUser->isSuspended())
                                <span class="badge bg-red-lt">موقوف</span>
                            @else
                                <span class="badge bg-green-lt">نشط</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $managedUser) }}" class="btn btn-sm btn-outline-primary">
                                عرض التفاصيل
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-5 text-center text-secondary">لا يوجد مستخدمون مسجلون.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($users->hasPages())
    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endif
@endsection
