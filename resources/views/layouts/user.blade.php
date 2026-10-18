<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') | DICIPTABINTAR</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body class="bg-light">

<div class="wrapper">

    {{-- Sidebar --}}
    <aside class="sidebar">

        @include('components.user-sidebar')

    </aside>

    {{-- Main --}}
    <main class="main-content">

        {{-- Navbar --}}
        @include('components.user-navbar')

        {{-- Content --}}
        <div class="page-content">

            <x-toast />

            @yield('content')

        </div>

        {{-- Footer --}}
        @include('components.user-footer')

    </main>

</div>

</body>

</html>