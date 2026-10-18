<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') | DICIPTABINTAR</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body>

<div class="wrapper">

    {{-- Sidebar --}}
    <aside class="sidebar">

        @include('components.admin-sidebar')

    </aside>

    {{-- Main --}}
    <div class="main-content">

        {{-- Navbar --}}
        @include('components.admin-navbar')

        {{-- Content --}}
        <div class="page-content">

            <x-toast />

            @yield('content')

        </div>

        {{-- Footer --}}
        @include('components.admin-footer')

    </div>

</div>

@stack('scripts')

</body>

</html>