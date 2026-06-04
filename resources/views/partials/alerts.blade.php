{{-- Tabler Alerts (Flash + Validation) --}}

{{-- Success --}}
@if (session('success'))
  <div class="alert alert-success alert-close-left" role="alert">
    <div class="alert-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
           viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
           class="icon alert-icon icon-2">
        <path d="M5 12l5 5l10 -10" />
      </svg>
    </div>
    <div>
      <h4 class="alert-heading">تم بنجاح</h4>
      <div class="alert-description">
        {{ session('success') }}
      </div>
    </div>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
  </div>
@endif

{{-- Info --}}
@if (session('info'))
  <div class="alert alert-info alert-close-left" role="alert">
    <div class="alert-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
           viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
           class="icon alert-icon icon-2">
        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
        <path d="M12 9h.01" />
        <path d="M11 12h1v4h1" />
      </svg>
    </div>
    <div>
      <h4 class="alert-heading">معلومة</h4>
      <div class="alert-description">
        {{ session('info') }}
      </div>
    </div>
    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
  </div>
@endif

{{-- Warning --}}
@if (session('warning'))
  <div class="alert alert-warning alert-close-left" role="alert">
    <div class="alert-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
           viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
           class="icon alert-icon icon-2">
        <path d="M12 9v4" />
        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
        <path d="M12 16h.01" />
      </svg>
    </div>
    <div>
      <h4 class="alert-heading">تنبيه</h4>
      <div class="alert-description">
        {{ session('warning') }}
      </div>
    </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
  </div>
@endif

{{-- Error / Danger --}}
@if (session('error'))
  <div class="alert alert-danger alert-close-left" role="alert">
    <div class="alert-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
           viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
           class="icon alert-icon icon-2">
        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
        <path d="M12 8v4" />
        <path d="M12 16h.01" />
      </svg>
    </div>
    <div>
      <h4 class="alert-heading">خطأ</h4>
      <div class="alert-description">
        {{ session('error') }}
      </div>
    </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>

  </div>
@endif

{{-- Validation Errors --}}
@if ($errors->any())
  <div class="alert alert-danger alert-close-left" role="alert">
    <div class="alert-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
           viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
           class="icon alert-icon icon-2">
        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
        <path d="M12 8v4" />
        <path d="M12 16h.01" />
      </svg>
    </div>
    <div>
      <h4 class="alert-heading">تحقق من البيانات</h4>
      <div class="alert-description">
        <ul class="mb-0">
          @foreach ($errors->all() as $msg)
            <li>{{ $msg }}</li>
          @endforeach
        </ul>
      </div>
    </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
  </div>
@endif
