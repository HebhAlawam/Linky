@include('dashboard.layouts.header')

<style>
  .page-wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .page-body {
    flex: 1 0 auto;
  }

  .page-wrapper > .footer {
    margin-top: auto;
  }
</style>

@include('dashboard.layouts.sidebar')




<!-- NavBar -->
@include('dashboard.layouts.navbar')
<!-- Navbar -->

   {{-- Page Body --}}
      <div class="page-body">
        <div class="container-xl">
          @yield('content')
        </div>
      </div>
@include('partials.confirm-delete')

@include('dashboard.layouts.footer')


