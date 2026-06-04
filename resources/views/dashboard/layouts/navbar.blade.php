@php
    $user = auth()->user();
    $previewPage = $user?->pages()->select('id', 'slug')->orderBy('id')->first();
@endphp

<div class="page-wrapper">
  {{-- Top Navbar --}}
  <header class="navbar navbar-expand-md d-print-none">
    <div class="container-fluid">
      <div class="navbar-nav">
        <div class="nav-item d-flex align-items-center">
          <span class="text-secondary small fw-medium">
            @yield('navbar-title', 'لوحة التحكم')
          </span>
        </div>
      </div>

      <div class="navbar-nav flex-row order-md-last align-items-center gap-2">
        @if ($previewPage)
          <div class="nav-item d-none d-sm-flex">
            <a href="{{ url('/' . $previewPage->slug) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
              <i class="ti ti-external-link ms-1"></i>
              معاينة الموقع
            </a>
          </div>
        @endif

        {{-- Light/Dark toggle --}}
        <div class="nav-item d-none d-md-flex">
          <a href="#" class="nav-link px-2"
             onclick="__toggleTheme(); return false;"
             data-bs-toggle="tooltip"
             data-bs-placement="bottom"
             aria-label="تبديل النمط"
             title="تبديل النمط">

            {{-- Light icon --}}
            <svg xmlns="http://www.w3.org/2000/svg"
                 width="24" height="24" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round"
                 class="icon icon-1 hide-theme-light">
              <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"></path>
            </svg>

            {{-- Dark icon --}}
            <svg xmlns="http://www.w3.org/2000/svg"
                 width="24" height="24" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round"
                 class="icon icon-1 hide-theme-dark">
              <path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
              <path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7"></path>
            </svg>
          </a>
        </div>

        {{-- User dropdown --}}
        <div class="nav-item dropdown">
          <a href="#" class="nav-link d-flex lh-1 text-reset p-0 align-items-center"
             data-bs-toggle="dropdown"
             aria-label="قائمة المستخدم">
            <span class="avatar avatar-sm">
              {{ strtoupper(mb_substr($user?->name ?? 'U', 0, 1)) }}
            </span>
            <div class="d-none d-xl-block me-2 text-end">
              <div class="fw-medium">{{ $user?->name ?? 'User' }}</div>
              <div class="mt-1 small text-secondary">{{ $user?->email }}</div>
            </div>
          </a>

          <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <div class="dropdown-header text-end">
              <div class="fw-bold">{{ $user?->name ?? 'User' }}</div>
              <div class="small text-secondary">{{ $user?->email }}</div>
            </div>
            <div class="dropdown-divider"></div>

            @if (\Illuminate\Support\Facades\Route::has('profile.edit'))
              <a class="dropdown-item" href="{{ route('profile.edit') }}">
                <i class="ti ti-settings ms-2"></i>
                إعدادات الحساب
              </a>
            @endif

            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="ti ti-logout ms-2"></i>
              تسجيل الخروج
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
  </form>

  {{-- Page Header (Title + Actions) --}}
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">
            @yield('page-title', 'عنوان الصفحة')
          </h2>
          <div class="text-secondary mt-1">
            @yield('page-subtitle', '')
          </div>
        </div>

        <div class="col-auto ms-auto d-print-none">
          @yield('page-actions')
        </div>
      </div>
    </div>
  </div>
