<div class="sidebar-header">

    <div class="logo">
        <i class="bi bi-buildings-fill"></i>
    </div>

    <h4>DICIPTABINTAR</h4>

    <small>Kota Bandung</small>

</div>

<hr>

<div class="mb-4 text-center">

    <div class="fw-semibold text-white">
        {{ auth()->user()->name }}
    </div>

    <small class="text-light">
        Masyarakat
    </small>

</div>

<h6 class="menu-title">MENU UTAMA</h6>

<ul class="sidebar-menu">

    <li>

        <a href="{{ route('user.dashboard') }}"
           class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">

            <i class="bi bi-speedometer2"></i>

            <span>Dashboard</span>

        </a>

    </li>

    <li>

        <a href="{{ route('user.pengaduan.create') }}"
           class="{{ request()->routeIs('user.pengaduan.create') ? 'active' : '' }}">

            <i class="bi bi-plus-circle-fill"></i>

            <span>Buat Pengaduan</span>

        </a>

    </li>

    <li>

        <a href="{{ route('user.pengaduan.index') }}"
           class="{{ request()->routeIs('user.pengaduan.index') ? 'active' : '' }}">

            <i class="bi bi-chat-dots-fill"></i>

            <span>Riwayat Pengaduan</span>

        </a>

    </li>

    <li>

        <a href="{{ route('profile.edit') }}"
           class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">

            <i class="bi bi-person-fill"></i>

            <span>Profil</span>

        </a>

    </li>

</ul>

<div class="mt-auto">

    <hr>

    <form method="POST" action="{{ route('logout') }}">

        @csrf

        <button type="submit" class="btn btn-danger w-100">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </button>

    </form>

</div>