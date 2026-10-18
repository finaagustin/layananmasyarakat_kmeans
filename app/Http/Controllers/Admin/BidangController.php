<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BidangController extends Controller
{
    // Menampilkan daftar bidang
    public function index(Request $request)
    {
        $search = $request->query('search');

        $bidangs = Bidang::when($search, function ($query, $search) {

                $query->where(
                    'nama_bidang',
                    'like',
                    '%' . $search . '%'
                )->orWhere(
                    'deskripsi',
                    'like',
                    '%' . $search . '%'
                );
            })
            ->orderBy(
                'bidang_id',
                'asc'
            )->get();

        return view(
            'admin.bidang.index',
            compact('bidangs', 'search')
        );
    }

    // Menampilkan form tambah bidang
    public function create()
    {
        return view('admin.bidang.create');
    }

    // Menyimpan data bidang
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bidang' => [
                'required',
                'string',
                'max:50',
                'unique:bidang_layanans,nama_bidang',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],
        ], [
            'nama_bidang.required' =>
                'Nama bidang wajib diisi.',

            'nama_bidang.string' =>
                'Nama bidang harus berupa teks.',

            'nama_bidang.unique' =>
                'Nama bidang sudah digunakan.',

            'nama_bidang.max' =>
                'Nama bidang maksimal 50 karakter.',

            'deskripsi.string' =>
                'Deskripsi harus berupa teks.',
        ]);

        Bidang::create($validated);

        return redirect()
            ->route('admin.bidang.index')
            ->with(
                'success',
                'Data bidang berhasil ditambahkan.'
            );
    }

    // Menampilkan form edit bidang
    public function edit(Bidang $bidang)
    {
        return view(
            'admin.bidang.edit',
            compact('bidang')
        );
    }

    // Memperbarui data bidang
    public function update(
        Request $request,
        Bidang $bidang
    ) {
        $validated = $request->validate([
            'nama_bidang' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'bidang_layanans',
                    'nama_bidang'
                )->ignore(
                    $bidang->bidang_id,
                    'bidang_id'
                ),
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],
        ], [
            'nama_bidang.required' =>
                'Nama bidang wajib diisi.',

            'nama_bidang.string' =>
                'Nama bidang harus berupa teks.',

            'nama_bidang.unique' =>
                'Nama bidang sudah digunakan.',

            'nama_bidang.max' =>
                'Nama bidang maksimal 50 karakter.',

            'deskripsi.string' =>
                'Deskripsi harus berupa teks.',
        ]);

        $bidang->update($validated);

        return redirect()
            ->route('admin.bidang.index')
            ->with(
                'success',
                'Data bidang berhasil diperbarui.'
            );
    }

    // Menghapus data bidang
    public function destroy(Bidang $bidang)
    {
        // Cek apakah bidang masih digunakan

        if ($bidang->pengaduans()->exists()) {

            return redirect()
                ->route('admin.bidang.index')
                ->with(
                    'error',
                    'Bidang tidak dapat dihapus karena masih digunakan pada data pengaduan.'
                );
        }

        $bidang->delete();

        return redirect()
            ->route('admin.bidang.index')
            ->with(
                'success',
                'Data bidang berhasil dihapus.'
            );
    }
}