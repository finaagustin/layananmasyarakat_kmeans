<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Models\KategoriLayanan;
use App\Models\Pengaduan;
use Illuminate\Support\Facades\Auth;

// ==========================
// Admin Controllers
// ==========================
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KategoriLayananController;
use App\Http\Controllers\Admin\BidangController;
use App\Http\Controllers\Admin\PengaduanController as AdminPengaduanController;
use App\Http\Controllers\Admin\DatasetController;
use App\Http\Controllers\Admin\AnalisisController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\LaporanController;

// ==========================
// User Controllers
// ==========================
use App\Http\Controllers\User\PengaduanController as UserPengaduanController;


/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| REDIRECT SETELAH LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->get('/dashboard', function (Request $request) {

        return match ($request->user()->role) {

            'admin' => redirect()
                ->route('admin.dashboard'),

            'masyarakat' => redirect()
                ->route('user.dashboard'),

            default => redirect()
                ->route('login'),
        };

    })
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [DashboardController::class, 'index']
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Master Data Kategori Layanan
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'kategori-layanan',
            KategoriLayananController::class
        )->except([
            'show',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Master Data Bidang
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'bidang',
            BidangController::class
        )->except([
            'show',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Pengaduan
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'pengaduan',
            AdminPengaduanController::class
        )->only([
            'index',
            'show',
            'edit',
            'update',
            'destroy',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prediksi Ulang Cluster Pengaduan
        |--------------------------------------------------------------------------
        |
        | Dipakai untuk pengaduan yang cluster-nya masih kosong
        | (mis. dikirim sebelum Admin melatih model TF-IDF/K-Means),
        | atau untuk memprediksi ulang setelah model dilatih ulang.
        |
        */

        Route::post(
            'pengaduan/{pengaduan}/predict-ulang',
            [AdminPengaduanController::class, 'predictUlang']
        )->name('pengaduan.predict-ulang');


        /*
        |--------------------------------------------------------------------------
        | Dataset
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'dataset',
            DatasetController::class
        )->only([
            'index',
            'create',
            'store',
            'destroy',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Jalankan Analisis Dataset
        |--------------------------------------------------------------------------
        |
        | Mengambil seluruh dataset historis yang telah di-upload,
        | kemudian menjalankan pipeline Python:
        |
        | Preprocessing -> TF-IDF -> Elbow -> K-Means
        | -> Silhouette Score
        |
        */

        Route::post(
            'dataset/analyze',
            [DatasetController::class, 'analyze']
        )->name('dataset.analyze');


        /*
        |--------------------------------------------------------------------------
        | Hasil Analisis Dataset
        |--------------------------------------------------------------------------
        */

        Route::get(
            'analisis/{dataset}',
            [AnalisisController::class, 'index']
        )->name('analisis.index');


        /*
        |--------------------------------------------------------------------------
        | Monitoring Status
        |--------------------------------------------------------------------------
        */

        Route::get(
            'monitoring',
            [MonitoringController::class, 'index']
        )->name('monitoring.index');


        /*
        |--------------------------------------------------------------------------
        | Export Dataset
        |--------------------------------------------------------------------------
        */

        Route::get(
            'export',
            [ExportController::class, 'index']
        )->name('export.index');

        Route::get(
            'export/download',
            [ExportController::class, 'download']
        )->name('export.download');


        /*
        |--------------------------------------------------------------------------
        | Laporan
        |--------------------------------------------------------------------------
        */

        Route::get(
            'laporan',
            [LaporanController::class, 'index']
        )->name('laporan.index');
    });


/*
|--------------------------------------------------------------------------
| MASYARAKAT
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:masyarakat'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', function () {

            $userId = Auth::id();

            return view('user.dashboard', [

                'totalPengaduan' => Pengaduan::where(
                    'user_id',
                    $userId
                )->count(),

                'pengaduanDiproses' => Pengaduan::where(
                    'user_id',
                    $userId
                )->where(
                    'status',
                    'Diproses'
                )->count(),

                'pengaduanSelesai' => Pengaduan::where(
                    'user_id',
                    $userId
                )->where(
                    'status',
                    'Selesai'
                )->count(),

                'kategoriLayanans' => KategoriLayanan::orderBy(
                    'nama_kategori',
                    'asc'
                )->get(),

            ]);

        })->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Pengaduan
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'pengaduan',
            UserPengaduanController::class
        )->only([
            'index',
            'create',
            'store',
            'show',
        ]);
    });


/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->group(function () {

        Route::get(
            '/profile',
            [ProfileController::class, 'edit']
        )->name('profile.edit');

        Route::patch(
            '/profile',
            [ProfileController::class, 'update']
        )->name('profile.update');

        Route::delete(
            '/profile',
            [ProfileController::class, 'destroy']
        )->name('profile.destroy');
    });


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';