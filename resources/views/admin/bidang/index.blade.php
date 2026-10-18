@extends('layouts.admin')

@section('title', 'Bidang')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h3 class="fw-bold mb-1">
            <i class="bi bi-diagram-3-fill text-primary"></i>
            Bidang
        </h3>

        <small class="text-muted">
            Kelola seluruh data bidang DICIPTABINTAR Kota Bandung.
        </small>

    </div>

    <a href="{{ route('admin.bidang.create') }}"
       class="btn btn-primary">

        <i class="bi bi-plus-circle"></i>

        Tambah Bidang

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

                    Master Data Bidang

                </h5>

            </div>

            <div class="col-md-6 text-md-end mt-3 mt-md-0">

                <span class="badge bg-primary fs-6">

                    Total Data :
                    {{ $bidangs->count() }}

                </span>

            </div>

        </div>

    </div>

    <div class="card-body">

        <div class="row mb-3">

            <div class="col-md-6">

                <form method="GET"
                      action="{{ route('admin.bidang.index') }}"
                      class="d-flex">

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="form-control me-2"
                        placeholder="Cari nama bidang atau deskripsi...">

                    <button type="submit" class="btn btn-primary text-nowrap">
                        <i class="bi bi-search"></i>
                        Cari
                    </button>

                    @if($search)
                    <a href="{{ route('admin.bidang.index') }}"
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

                        <th width="260">Nama Bidang</th>

                        <th>Deskripsi</th>

                        <th width="180" class="text-center">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($bidangs as $item)

                    <tr>

                        <td class="text-center">

                            {{ $loop->iteration }}

                        </td>

                        <td>

                            <strong>

                                {{ $item->nama_bidang }}

                            </strong>

                        </td>

                        <td>

                            {{ $item->deskripsi }}

                        </td>

                        <td class="text-center">

                            <a href="{{ route('admin.bidang.edit', $item->bidang_id) }}"
                               class="btn btn-warning btn-sm">

                                <i class="bi bi-pencil-square"></i>

                            </a>

                            <form action="{{ route('admin.bidang.destroy', $item->bidang_id) }}"
                                  method="POST"
                                  class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus bidang ini?')">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="4" class="text-center py-5">

                            <i class="bi bi-folder-x display-5 text-secondary"></i>

                            <br><br>

                            <h5>

                                {{ $search ? 'Data Tidak Ditemukan' : 'Belum Ada Data' }}

                            </h5>

                            <small class="text-muted">

                                {{ $search
                                    ? 'Tidak ada bidang yang cocok dengan pencarian "' . $search . '".'
                                    : 'Silakan tambahkan bidang baru.'
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