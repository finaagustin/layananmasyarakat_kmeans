<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use Illuminate\View\View;

class AnalisisController extends Controller
{
    // Menampilkan hasil analisis berdasarkan dataset
    public function index(Dataset $dataset): View
    {
        // Ambil hasil analisis terakhir
        // Satu dataset dapat memiliki beberapa hasil analisis
        // Hasil terbaru ditentukan berdasarkan analisis_id

        $analisis = $dataset->analisis()
            ->latest('analisis_id')
            ->first();

        // Seluruh dataset historis yang digabungkan pada hasil
        // analisis ini (bisa lebih dari satu dataset, karena
        // dataset historis kini bisa ditambah bertahap).
        $datasetTerpakai = collect();

        if ($analisis && is_array($analisis->dataset_ids_terpakai)) {
            $datasetTerpakai = Dataset::whereIn(
                'dataset_id',
                $analisis->dataset_ids_terpakai
            )
                ->orderBy('dataset_id')
                ->get();
        }

        // Tampilkan halaman hasil analisis

        return view(
            'admin.analisis.index',
            [
                'dataset'         => $dataset,
                'analisis'        => $analisis,
                'datasetTerpakai' => $datasetTerpakai,
            ]
        );
    }
}