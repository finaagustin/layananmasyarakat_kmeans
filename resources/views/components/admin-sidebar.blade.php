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
        Administrator
    </small>

</div>

<h6 class="menu-title">MAIN MENU</h6>

<ul class="sidebar-menu">

    <li>
        <a href="{{ route('admin.dashboard') }}"
           class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
    </li>

</ul>

<h6 class="menu-title">MASTER DATA</h6>

<ul class="sidebar-menu">

    <li>
        <a href="{{ route('admin.kategori-layanan.index') }}"
           class="{{ request()->routeIs('admin.kategori-layanan.*') ? 'active' : '' }}">
            <i class="bi bi-tags-fill"></i>
            <span>Kategori Layanan</span>
        </a>
    </li>

    <li>
        <a href="{{ route('admin.bidang.index') }}"
           class="{{ request()->routeIs('admin.bidang.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3-fill"></i>
            <span>Bidang</span>
        </a>
    </li>

</ul>

<h6 class="menu-title">LAYANAN</h6>

<ul class="sidebar-menu">

    <li>
        <a href="{{ route('admin.pengaduan.index') }}"
           class="{{ request()->routeIs('admin.pengaduan.*') ? 'active' : '' }}">
            <i class="bi bi-chat-dots-fill"></i>
            <span>Pengaduan</span>
        </a>
    </li>

</ul>

<h6 class="menu-title">ANALISIS DATA</h6>

<ul class="sidebar-menu">

    <li>
        <a href="{{ route('admin.dataset.index') }}"
           class="{{ request()->routeIs('admin.dataset.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-excel-fill"></i>
            <span>Dataset</span>
        </a>
    </li>

</ul>

<h6 class="menu-title">LAPORAN</h6>

<ul class="sidebar-menu">

    <li>
        <a href="{{ route('admin.monitoring.index') }}"
           class="{{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-data-fill"></i>
            <span>Monitoring</span>
        </a>
    </li>

    <li>
        <a href="{{ route('admin.export.index') }}"
           class="{{ request()->routeIs('admin.export.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-arrow-down-fill"></i>
            <span>Export Dataset</span>
        </a>
    </li>

    <li>
        <a href="{{ route('admin.laporan.index') }}"
           class="{{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-pdf-fill"></i>
            <span>Laporan</span>
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