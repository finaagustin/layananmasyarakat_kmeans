<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\KategoriLayanan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriLayananController extends Controller
{
    // Menampilkan seluruh data kategori layanan
    public function index(Request $request)
    {
        $search = $request->query('search');

        $kategori = KategoriLayanan::with('bidang')
            ->when($search, function ($query, $search) {

                $query->where(function ($query) use ($search) {

                    $query->where(
                        'nama_kategori',
                        'like',
                        '%' . $search . '%'
                    )->orWhere(
                        'deskripsi',
                        'like',
                        '%' . $search . '%'
                    )->orWhereHas(
                        'bidang',
                        function ($query) use ($search) {

                            $query->where(
                                'nama_bidang',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );
                });
            })
            ->orderBy(
                'kategori_layanan_id',
                'asc'
            )->get();

        return view(
            'admin.kategori.index',
            compact('kategori', 'search')
        );
    }

    // Menampilkan form tambah kategori
    public function create()
    {
        $bidangs = Bidang::orderBy('nama_bidang', 'asc')
            ->get();

        return view(
            'admin.kategori.create',
            compact('bidangs')
        );
    }

    // Menyimpan data kategori
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:50',
                'unique:kategori_layanans,nama_kategori',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'bidang_id' => [
                'nullable',
                'exists:bidang_layanans,bidang_id',
            ],
        ], [
            'nama_kategori.required' =>
                'Nama kategori wajib diisi.',

            'nama_kategori.string' =>
                'Nama kategori harus berupa teks.',

            'nama_kategori.unique' =>
                'Nama kategori sudah digunakan.',

            'nama_kategori.max' =>
                'Nama kategori maksimal 50 karakter.',

            'deskripsi.string' =>
                'Deskripsi harus berupa teks.',

            'bidang_id.exists' =>
                'Bidang yang dipilih tidak valid.',
        ]);

        KategoriLayanan::create($validated);

        return redirect()
            ->route('admin.kategori-layanan.index')
            ->with(
                'success',
                'Data kategori berhasil ditambahkan.'
            );
    }

    // Menampilkan form edit kategori
    public function edit(
        KategoriLayanan $kategoriLayanan
    ) {
        $kategori = $kategoriLayanan;

        $bidangs = Bidang::orderBy('nama_bidang', 'asc')
            ->get();

        return view(
            'admin.kategori.edit',
            compact('kategori', 'bidangs')
        );
    }

    // Memperbarui data kategori
    public function update(
        Request $request,
        KategoriLayanan $kategoriLayanan
    ) {
        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'kategori_layanans',
                    'nama_kategori'
                )->ignore(
                    $kategoriLayanan->kategori_layanan_id,
                    'kategori_layanan_id'
                ),
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'bidang_id' => [
                'nullable',
                'exists:bidang_layanans,bidang_id',
            ],
        ], [
            'nama_kategori.required' =>
                'Nama kategori wajib diisi.',

            'nama_kategori.string' =>
                'Nama kategori harus berupa teks.',

            'nama_kategori.unique' =>
                'Nama kategori sudah digunakan.',

            'nama_kategori.max' =>
                'Nama kategori maksimal 50 karakter.',

            'deskripsi.string' =>
                'Deskripsi harus berupa teks.',

            'bidang_id.exists' =>
                'Bidang yang dipilih tidak valid.',
        ]);

        $kategoriLayanan->update($validated);

        return redirect()
            ->route('admin.kategori-layanan.index')
            ->with(
                'success',
                'Data kategori berhasil diperbarui.'
            );
    }

    // Menghapus data kategori
    public function destroy(
        KategoriLayanan $kategoriLayanan
    ) {
        // Cek apakah kategori masih digunakan

        if ($kategoriLayanan->pengaduans()->exists()) {

            return redirect()
                ->route('admin.kategori-layanan.index')
                ->with(
                    'error',
                    'Kategori tidak dapat dihapus karena masih digunakan pada data pengaduan.'
                );
        }

        $kategoriLayanan->delete();

        return redirect()
            ->route('admin.kategori-layanan.index')
            ->with(
                'success',
                'Data kategori berhasil dihapus.'
            );
    }
}