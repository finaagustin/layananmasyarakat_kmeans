@extends('layouts.user')

@section('title', 'Detail Pengaduan')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-primary text-white">

            <h5 class="mb-0">

                <i class="bi bi-file-earmark-text-fill me-2"></i>

                Detail Pengaduan

            </h5>

        </div>

        <div class="card-body">

            <table class="table">

                <tr>

                    <th width="220">Kode Pengaduan</th>

                    <td>{{ $pengaduan->kode_pengaduan }}</td>

                </tr>

                <tr>

                    <th>Kategori</th>

                    <td>{{ $pengaduan->kategori->nama_kategori ?? '-' }}</td>

                </tr>

                <tr>

                    <th>Judul</th>

                    <td>{{ $pengaduan->judul }}</td>

                </tr>

                <tr>

                    <th>Isi Pengaduan</th>

                    <td>{{ $pengaduan->isi_pengaduan }}</td>

                </tr>

                <tr>

                    <th>Status</th>

                    <td>

                        @if($pengaduan->status=='Diajukan')

                            <span class="badge bg-warning text-dark">

                                Diajukan

                            </span>

                        @elseif($pengaduan->status=='Diproses')

                            <span class="badge bg-primary">

                                Diproses

                            </span>

                        @else

                            <span class="badge bg-success">

                                Selesai

                            </span>

                        @endif

                    </td>

                </tr>

                <tr>

                    <th>Bidang</th>

                    <td>

                        {{ $pengaduan->bidang->nama_bidang ?? '-' }}

                    </td>

                </tr>

                <tr>

                    <th>Tanggal Pengaduan</th>

                    <td>

                        {{ $pengaduan->tanggal_pengaduan->format('d F Y H:i') }}

                    </td>

                </tr>

                <tr>

                    <th>Lampiran</th>

                    <td>

                        @if($pengaduan->lampiran)

                            <a href="{{ asset('storage/'.$pengaduan->lampiran) }}"
                               target="_blank"
                               class="btn btn-success btn-sm">

                                <i class="bi bi-download"></i>

                                Lihat Lampiran

                            </a>

                        @else

                            -

                        @endif

                    </td>

                </tr>

            </table>

        </div>

        <div class="card-footer text-end">

            <a href="{{ route('user.pengaduan.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

        </div>

    </div>

</div>

@endsection