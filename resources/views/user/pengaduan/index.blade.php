@extends('layouts.user')

@section('title', 'Riwayat Pengaduan')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="bi bi-chat-dots-fill me-2"></i>

                Riwayat Pengaduan

            </h5>

            <a href="{{ route('user.pengaduan.create') }}"
               class="btn btn-light">

                <i class="bi bi-plus-circle-fill me-1"></i>

                Buat Pengaduan

            </a>

        </div>

        <div class="card-body">

            @if($pengaduans->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                        <tr>

                            <th width="60">No</th>

                            <th>Kode</th>

                            <th>Judul</th>

                            <th>Kategori</th>

                            <th>Status</th>

                            <th>Tanggal</th>

                            <th width="120">Aksi</th>

                        </tr>

                        </thead>

                        <tbody>

                        @foreach($pengaduans as $pengaduan)

                            <tr>

                                <td>

                                    {{ $loop->iteration + ($pengaduans->currentPage()-1) * $pengaduans->perPage() }}

                                </td>

                                <td>

                                    {{ $pengaduan->kode_pengaduan }}

                                </td>

                                <td>

                                    {{ $pengaduan->judul }}

                                </td>

                                <td>

                                    {{ $pengaduan->kategori->nama_kategori ?? '-' }}

                                </td>

                                <td>

                                    @if($pengaduan->status == 'Diajukan')

                                        <span class="badge bg-warning text-dark">

                                            Diajukan

                                        </span>

                                    @elseif($pengaduan->status == 'Diproses')

                                        <span class="badge bg-primary">

                                            Diproses

                                        </span>

                                    @else

                                        <span class="badge bg-success">

                                            Selesai

                                        </span>

                                    @endif

                                </td>

                                <td>

                                    {{ $pengaduan->tanggal_pengaduan->format('d-m-Y') }}

                                </td>

                                <td>

                                    <a href="{{ route('user.pengaduan.show', $pengaduan) }}"
                                       class="btn btn-info btn-sm">

                                        <i class="bi bi-eye-fill"></i>

                                        Detail

                                    </a>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="mt-3">

                    {{ $pengaduans->links() }}

                </div>

            @else

                <div class="text-center py-5">

                    <i class="bi bi-inbox display-1 text-secondary"></i>

                    <h5 class="mt-3">

                        Belum Ada Pengaduan

                    </h5>

                    <p class="text-muted">

                        Silakan buat pengaduan baru.

                    </p>

                    <a href="{{ route('user.pengaduan.create') }}"
                       class="btn btn-primary">

                        <i class="bi bi-plus-circle-fill"></i>

                        Buat Pengaduan

                    </a>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection