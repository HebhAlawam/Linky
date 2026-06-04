@extends('dashboard.layouts.main')

@section('title', 'الإحصائيات')
@section('navbar-title', 'الإحصائيات')
@section('page-title', 'الإحصائيات')
@section('page-subtitle', 'تابع أداء موقعك وتفاعل الزوار')

@section('content')
@include('partials.alerts')

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">زيارات اليوم</div>
                <div class="h1 mb-0">{{ $summary['today_visits'] }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">زيارات آخر 7 أيام</div>
                <div class="h1 mb-0">{{ $summary['last_7_visits'] }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">إجمالي الزيارات</div>
                <div class="h1 mb-0">{{ $summary['total_visits'] }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">نقرات روابط التواصل</div>
                <div class="h1 mb-0">{{ $summary['link_clicks'] }}</div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">نقرات الطلب</div>
                <div class="h1 mb-0">{{ $summary['order_clicks'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">آخر 7 أيام</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الزيارات</th>
                        <th>نقرات الروابط</th>
                        <th>نقرات الطلب</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($lastSevenDays as $day)
                        <tr>
                            <td>{{ $day['date']->translatedFormat('Y-m-d') }}</td>
                            <td>{{ $day['visits'] }}</td>
                            <td>{{ $day['link_clicks'] }}</td>
                            <td>{{ $day['item_clicks'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">أكثر العناصر طلباً</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                    <tr>
                        <th>العنصر</th>
                        <th>القسم</th>
                        <th>نقرات الطلب</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($topItems as $item)
                        <tr>
                            <td>{{ $item['title'] }}</td>
                            <td>{{ $item['category'] }}</td>
                            <td>{{ $item['clicks'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-secondary py-4">لا توجد نقرات طلب بعد.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">أكثر روابط التواصل نقرًا</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                    <tr>
                        <th>الرابط</th>
                        <th>النوع</th>
                        <th>النقرات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($topLinks as $link)
                        <tr>
                            <td>{{ $link['title'] }}</td>
                            <td>{{ $link['type'] }}</td>
                            <td>{{ $link['clicks'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-secondary py-4">لا توجد نقرات روابط بعد.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
