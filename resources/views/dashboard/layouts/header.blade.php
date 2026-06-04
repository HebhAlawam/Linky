<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'LinkPage')</title>


  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

  @vite(['resources/js/app-tabler.js'])

  @stack('styles')

</head>

<body>
  {{-- <div class="page"> --}}
