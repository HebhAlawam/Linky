<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>{{ $page->title }}</title>

<link rel="stylesheet" href="https://mocafecoffee.com/assets/css/app.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
 <link rel="stylesheet" type="text/css" media="all" href="https://mocafecoffee.com/assets/css/app_ar.css?id=22c933e3002edc8172e8ca45c26c2ef1"> 

<style>
:root{
    --main : #09b0a2;
    --mainhover : color-mix(in srgb, #09b0a2 70%, black);
}
body.links-tree {
    background: #09b0a2;
}
</style>

</head>

<body class="index" id="content">

<div id="loading"><i class="fa-solid fa-circle-notch fa-spin"></i></div>

<!-- HEADER -->
<div class="header-section sticky">
    <div class="bottom">
        <div class="container">
            <div class="row">
                <div class="logo">
                    <a href="/">
                        <img src="{{ asset($page->logo) }}" alt="">
                    </a>
                </div>

                <div class="navbar">
                    <div class="overlay"></div>

                    <div class="links">
                        <ul class="menu">
                            <li>
                                <a href="#">{{ $page->title }}</a>
                            </li>
                        </ul>
                    </div>

                    <div class="basket-card">
                        <div class="language">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    عربي
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">عربي</a></li>
                                    <li><a class="dropdown-item" href="#">English</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- SLIDER (static حاليا) -->
<section class="slider-section">
  <div class="container">
    <div class="owl-carousel owl-theme main-slider" id="slider">

        <div class="item">
            <a href="#!">
                <div class="image">
                    <img src="https://mocafecoffee.com/storage/sliders/6dc5dbfc.jpg">
                </div>
            </a>
        </div>

        <div class="item">
            <a href="#!">
                <div class="image">
                    <img src="https://mocafecoffee.com/storage/sliders/ebe62f7c.jpg">
                </div>
            </a>
        </div>

    </div>
  </div>
</section>

<!-- CATEGORIES -->
<div class="categories-carousel" id="categories">
  <div class="container">
    <div class="categories-storey">
      <div class="main-title">
        <h3>التصنيفات</h3>
      </div>

      <div class="row cat-list">

        @foreach($page->categories as $category)
        <div class="item col-md-3 col-xl-2 col-sm-4 col-4 {{ $loop->first ? 'active' : '' }}">
            <div class="content">
                <a href="#">
                    <div class="img">
                        <img src="{{ asset($category->image) }} ">
                    </div>
                    <div class="title">
                        <h3>{{ $category->name }}</h3>
                    </div>
                </a>
            </div>
        </div>
        @endforeach

      </div>
    </div>
  </div>
</div>

<!-- PRODUCTS -->
<section class="best-products" id="product">
  <div class="container">
    <div class="main-title">
        <h3>المنتجات</h3>
    </div>

    <div class="boxes">
      <div class="row">

        @foreach($page->items as $item)
        <div class="col-12 col-sm-12 col-md-6 col-lg-4 col-xl-4 item">
            <div class="content">

                <div class="image">
                    <a href="#!">
                        <img src="{{ asset($item->image) }} ">
                    </a>
                </div>

                <div class="text">

                    <div class="title">
                        <a href="#!">{{ $item->title }}</a>
                    </div>

                    @if($item->short_description)
                    <p>{{ $item->short_description }}</p>
                    @endif

                    <div class="q-sec">
                        <div class="price">
                            <h3>
                                {{ $item->price }} {{ $page->settings['currency'] ?? 'ل.س' }}
                            </h3>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @endforeach

      </div>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section class="contact-info">
  <div class="container">
    <div class="main-title">
      <h3>معلومات التواصل</h3>
    </div>

    <div class="details">

      <div class="item">
        <ul>
          <li>
            <a href="tel:{{ $page->settings['phone'] ?? '' }}">
              <i class="fa-solid fa-phone"></i>
              <span>{{ $page->settings['phone'] ?? '' }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="item">
        <ul>
          <li>
            <a href="#">
              <i class="fa-solid fa-location-dot"></i>
              <span>{{ $page->settings['address'] ?? '' }}</span>
            </a>
          </li>
        </ul>
      </div>

    </div>
  </div>
</section>

<!-- FOOTER -->
<div class="footer-section">
    <div class="logo">
        <img src="{{ asset($page->logo) }}" alt="">
    </div>

    <div class="text">
        <p>{{ $page->settings['footer_text'] ?? '' }}</p>
    </div>

    <div class="copyrighted">
        <div class="container">
            <p>
                {{ date('Y') }} جميع الحقوق محفوظة
            </p>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/pjax/pjax.min.js"></script>
<script src="https://mocafecoffee.com/assets/js/app.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
