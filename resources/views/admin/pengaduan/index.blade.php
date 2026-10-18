@extends('layouts.admin')

@section('title', 'Pengaduan')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-chat-dots-fill text-primary"></i>
            Pengaduan
        </h3>

        <small class="text-muted">
            Kelola seluruh data pengaduan masyarakat DICIPTABINTAR Kota Bandung.
        </small>
    </div>

</div>


{{-- Alert Success --}}
@if(session('success'))

<div class="alert alert-success alert-dismissible fade show" role="alert">

    <i class="bi bi-check-circle-fill me-2"></i>

    {{ session('success') }}

    <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
    </button>

</div>

@endif


{{-- Alert Error --}}
@if(session('error'))

<div class="alert alert-danger alert-dismissible fade show" role="alert">

    <i class="bi bi-exclamation-triangle-fill me-2"></i>

    {{ session('error') }}

    <button type="button"
            class="btn-close"
            data-bs-dismiss="alert">
    </button>

</div>

@endif


<div class="card shadow-sm border-0">

    <div class="card-header bg-white">

        <div class="row align-items-center">

            <div class="col-md-6">

                <h5 class="mb-0">
                    Data Pengaduan
                </h5>

            </div>

            <div class="col-md-6 text-md-end">

                <span class="badge bg-primary fs-6">

                    Total Data :
                    {{ method_exists($pengaduans, 'total')
                        ? $pengaduans->total()
                        : $pengaduans->count()
                    }}

                </span>

            </div>

        </div>

    </div>


    <div class="card-body">

        <div class="row mb-3">

            <div class="col-md-6">

                <form method="GET"
                      action="{{ route('admin.pengaduan.index') }}"
                      class="d-flex">

                    <select
                        name="kategori_layanan_id"
                        class="form-select me-2"
                        onchange="this.form.submit()">

                        <option value="">Semua Kategori Layanan</option>

                        @foreach($kategoriLayanans as $item)

                        <option
                            value="{{ $item->kategori_layanan_id }}"
                            @selected($kategoriLayananId == $item->kategori_layanan_id)>

                            {{ $item->nama_kategori }}

                        </option>

                        @endforeach

                    </select>

                    <noscript>
                        <button type="submit" class="btn btn-primary text-nowrap">
                            Terapkan
                        </button>
                    </noscript>

                    @if($kategoriLayananId)
                    <a href="{{ route('admin.pengaduan.index') }}"
                       class="btn btn-outline-secondary ms-2 text-nowrap"
                       title="Hapus filter">
                        <i class="bi bi-x-lg"></i>
                        Reset Filter
                    </a>
                    @endif

                </form>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-hover table-striped align-middle">

                <thead class="table-light text-center">

                    <tr>

                        <th width="60">No</th>

                        <th>Kode</th>

                        <th>Tanggal</th>

                        <th>Judul</th>

                        <th>Kategori</th>

                        <th>Bidang</th>

                        <th>Status</th>

                        <th width="150">Aksi</th>

                    </tr>

                </thead>


                <tbody>

                @forelse($pengaduans as $item)

                    <tr>

                        {{-- Nomor --}}
                        <td class="text-center">

                            {{ method_exists($pengaduans, 'firstItem')
                                ? $pengaduans->firstItem() + $loop->index
                                : $loop->iteration
                            }}

                        </td>


                        {{-- Kode Pengaduan --}}
                        <td class="text-nowrap">

                            <strong>
                                {{ $item->kode_pengaduan }}
                            </strong>

                        </td>


                        {{-- Tanggal --}}
                        <td class="text-center text-nowrap">

                            {{ optional($item->tanggal_pengaduan)
                                ->format('d-m-Y')
                                ?? '-'
                            }}

                        </td>


                        {{-- Judul --}}
                        <td>

                            {{ $item->judul }}

                        </td>


                        {{-- Kategori --}}
                        <td>

                            {{ optional($item->kategori)
                                ->nama_kategori
                                ?? '-'
                            }}

                        </td>


                        {{-- Bidang --}}
                        <td>

                            {{ optional($item->bidang)
                                ->nama_bidang
                                ?? 'Belum ditentukan'
                            }}

                        </td>


                        {{-- Status --}}
                        <td class="text-center">

                            @switch($item->status)

                                @case('Diajukan')

                                    <span class="badge bg-warning text-dark">
                                        Diajukan
                                    </span>

                                    @break


                                @case('Diproses')

                                    <span class="badge bg-primary">
                                        Diproses
                                    </span>

                                    @break


                                @case('Selesai')

                                    <span class="badge bg-success">
                                        Selesai
                                    </span>

                                    @break


                                @default

                                    <span class="badge bg-secondary">
                                        {{ $item->status }}
                                    </span>

                            @endswitch

                        </td>


                        {{-- Aksi --}}
                        <td class="text-center text-nowrap">

                            {{-- Detail --}}
                            <a
                                href="{{ route(
                                    'admin.pengaduan.show',
                                    $item->pengaduan_id
                                ) }}"
                                class="btn btn-info btn-sm"
                                title="Lihat Detail">

                                <i class="bi bi-eye"></i>

                            </a>


                            {{-- Kelola --}}
                            <a
                                href="{{ route(
                                    'admin.pengaduan.edit',
                                    $item->pengaduan_id
                                ) }}"
                                class="btn btn-warning btn-sm"
                                title="Kelola Pengaduan">

                                <i class="bi bi-pencil-square"></i>

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="8"
                            class="text-center py-5">

                            <i class="bi bi-folder-x display-4 text-secondary"></i>

                            <h5 class="mt-3">
                                {{ $kategoriLayananId
                                    ? 'Data Tidak Ditemukan'
                                    : 'Belum Ada Pengaduan'
                                }}
                            </h5>

                            <small class="text-muted">
                                {{ $kategoriLayananId
                                    ? 'Tidak ada pengaduan pada kategori layanan yang dipilih.'
                                    : 'Data pengaduan masyarakat belum tersedia.'
                                }}
                            </small>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if(method_exists($pengaduans, 'links'))

            <div class="d-flex justify-content-end mt-3">

                {{ $pengaduans->links() }}

            </div>

        @endif

    </div>

</div>

@endsection