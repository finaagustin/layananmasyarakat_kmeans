<nav class="top-navbar">

    <div>

        <h4 class="mb-1">

            @yield('title')

        </h4>

        <small class="text-muted">

            Sistem Analisis Pengelompokan Layanan Masyarakat
            Menggunakan Algoritma K-Means

        </small>

    </div>

    <div class="user-info">

        <i class="bi bi-person-circle"></i>

        <div>

            <strong>

                {{ auth()->user()->name }}

            </strong>

            <br>

            <small class="text-muted">

                Masyarakat

            </small>

        </div>

    </div>

</nav>