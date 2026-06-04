{{-- ================= Sidebar (General App Navigation) ================= --}}
<style>
  .navbar-vertical .nav-item.active > .nav-link,
  .navbar-vertical .nav-link.active {
    background: var(--tblr-primary-lt);
    color: var(--tblr-primary);
    font-weight: 700;
  }
  .navbar-vertical .nav-link.active .nav-link-icon {
    color: var(--tblr-primary);
  }
  .navbar-vertical .nav-item.active > .nav-link::before,
  .navbar-vertical .nav-link.active::before {
    opacity: 1;
  }
</style>

<aside class="navbar navbar-vertical navbar-expand-lg">
  <div class="container-fluid">
    <h1 class="navbar-brand navbar-brand-autodark">
      <a href="{{ route('dashboard') }}"><span class="fw-bold">Linky</span></a>
    </h1>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="sidebar-menu">
      @php
        $navItems = [
          ['route' => 'dashboard', 'active' => ['dashboard'], 'icon' => 'ti ti-layout-dashboard', 'label' => 'لوحة التحكم'],
          ['route' => 'dashboard.my-website.index', 'active' => ['dashboard.my-website.*'], 'icon' => 'ti ti-world-www', 'label' => 'موقعي'],
          ['route' => 'dashboard.appearance.index', 'active' => ['dashboard.appearance.*'], 'icon' => 'ti ti-paint', 'label' => 'المظهر والقوالب'],
          ['route' => 'dashboard.categories.index', 'active' => ['dashboard.categories.*'], 'icon' => 'ti ti-category', 'label' => 'التصنيفات'],
          ['route' => 'dashboard.items.index', 'active' => ['dashboard.items.*'], 'icon' => 'ti ti-tools-kitchen-2', 'label' => 'الأطباق والمنتجات'],
          ['route' => 'dashboard.links.index', 'active' => ['dashboard.links.*'], 'icon' => 'ti ti-link', 'label' => 'روابط التواصل'],
          ['route' => 'dashboard.stats.index', 'active' => ['dashboard.stats', 'dashboard.stats.*'], 'icon' => 'ti ti-chart-bar', 'label' => 'الإحصائيات'],
          ['route' => 'profile.edit', 'active' => ['profile.edit', 'profile.*'], 'icon' => 'ti ti-settings', 'label' => 'إعدادات الحساب', 'class' => 'mt-2'],
        ];
      @endphp

      <ul class="navbar-nav pt-lg-3">
        @foreach ($navItems as $item)
          @php($isActive = request()->routeIs(...$item['active']))
          <li class="nav-item {{ $item['class'] ?? '' }} {{ $isActive ? 'active' : '' }}">
            <a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ route($item['route']) }}">
              <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="{{ $item['icon'] }}"></i></span>
              <span class="nav-link-title">{{ $item['label'] }}</span>
            </a>
          </li>
        @endforeach

      </ul>
    </div>
  </div>
</aside>
