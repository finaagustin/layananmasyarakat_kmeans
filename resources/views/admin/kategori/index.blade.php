@extends('layouts.admin')

@section('title', 'Kategori Layanan')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h3 class="fw-bold mb-1">
            <i class="bi bi-tags-fill text-primary"></i>
            Kategori Layanan
        </h3>

        <small class="text-muted">
            Kelola seluruh data kategori layanan DICIPTABINTAR Kota Bandung.
        </small>

    </div>

    <a href="{{ route('admin.kategori-layanan.create') }}"
       class="btn btn-primary">

        <i class="bi bi-plus-circle"></i>

        Tambah Kategori

    </a>

</div>

@if(session('success'))

<div class="alert alert-success alert-dismissible fade show">

    <i class="bi bi-check-circle-fill me-2"></i>

    {{ session('success') }}

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert">
    </button>

</div>

@endif

<div class="card">

    <div class="card-header bg-white">

        <div class="row align-items-center">

            <div class="col-md-6">

                <h5 class="mb-0">

                    Master Data Kategori Layanan

                </h5>

            </div>

            <div class="col-md-6 text-md-end mt-3 mt-md-0">

                <span class="badge bg-primary fs-6">

                    Total Data :
                    {{ $kategori->count() }}

                </span>

            </div>

        </div>

    </div>

    <div class="card-body">

        <div class="row mb-3">

            <div class="col-md-6">

                <form method="GET"
                      action="{{ route('admin.kategori-layanan.index') }}"
                      class="d-flex">

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="form-control me-2"
                        placeholder="Cari nama kategori, deskripsi, atau bidang...">

                    <button type="submit" class="btn btn-primary text-nowrap">
                        <i class="bi bi-search"></i>
                        Cari
                    </button>

                    @if($search)
                    <a href="{{ route('admin.kategori-layanan.index') }}"
                       class="btn btn-outline-secondary ms-2"
                       title="Hapus pencarian">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif

                </form>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th width="70" class="text-center">No</th>

                        <th width="220">Nama Kategori</th>

                        <th>Deskripsi</th>

                        <th width="180">Bidang Terkait</th>

                        <th width="180" class="text-center">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($kategori as $item)

                    <tr>

                        <td class="text-center">

                            {{ $loop->iteration }}

                        </td>

                        <td>

                            <strong>

                                {{ $item->nama_kategori }}

                            </strong>

                        </td>

                        <td>

                            {{ $item->deskripsi }}

                        </td>

                        <td>

                            @if($item->bidang)

                            <span class="badge bg-primary">
                                {{ $item->bidang->nama_bidang }}
                            </span>

                            @else

                            <span class="badge bg-secondary">
                                Belum Ditentukan
                            </span>

                            @endif

                        </td>

                        <td class="text-center">

                            <a href="{{ route('admin.kategori-layanan.edit', $item->kategori_layanan_id) }}"
                               class="btn btn-warning btn-sm">

                                <i class="bi bi-pencil-square"></i>

                            </a>

                            <form action="{{ route('admin.kategori-layanan.destroy', $item->kategori_layanan_id) }}"
                                  method="POST"
                                  class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus kategori ini?')">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="5" class="text-center py-5">

                            <i class="bi bi-folder-x display-5 text-secondary"></i>

                            <br><br>

                            <h5>

                                {{ $search ? 'Data Tidak Ditemukan' : 'Belum Ada Data' }}

                            </h5>

                            <small class="text-muted">

                                {{ $search
                                    ? 'Tidak ada kategori layanan yang cocok dengan pencarian "' . $search . '".'
                                    : 'Silakan tambahkan kategori layanan baru.'
                                }}

                            </small>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection