import json
import pickle
import sys
import time
from pathlib import Path

import pandas as pd

from config import (
    CLUSTERING_FILE,
    ELBOW_FILE,
    ELBOW_JSON,
    KMEANS_MODEL,
    LOG_FILE,
    PREPROCESSING_FILE,
    SILHOUETTE_FILE,
    SILHOUETTE_JSON,
    TFIDF_MODEL,
    reset_output,
    storage_analisis_dir,
)

from engine.clustering import run_clustering, run_clustering_per_bidang
from engine.evaluation import evaluate_clustering
from engine.preprocessing import preprocess_dataset
from utils.file_utils import combine_datasets
from utils.visualization import (
    buat_grafik_cluster,
    buat_grafik_elbow,
    buat_grafik_silhouette,
)


# ==========================================================
# LOG PROGRES
# ==========================================================

def log_progress(pesan: str, waktu_mulai: float = None) -> None:
    """
    Mencatat progres tahap analisis ke python/logs/analysis.log
    beserta durasi tahap tersebut (jika waktu_mulai diberikan).

    Tujuan: agar proses upload & analisis yang berjalan lama
    tetap terlihat progresnya (tidak seperti "hang"), dan
    memudahkan mengetahui tahap mana yang paling memakan waktu.
    """

    durasi = (
        f" ({time.time() - waktu_mulai:.2f} detik)"
        if waktu_mulai is not None
        else ""
    )

    baris = (
        f"[{time.strftime('%Y-%m-%d %H:%M:%S')}] "
        f"{pesan}{durasi}"
    )

    try:
        with open(LOG_FILE, "a", encoding="utf-8") as file:
            file.write(baris + "\n")
    except OSError:
        # Logging tidak boleh menggagalkan proses analisis.
        pass

    # Ikut ditampilkan ke stderr agar tidak tercampur
    # dengan output JSON yang dibaca Laravel di stdout.
    print(baris, file=sys.stderr, flush=True)


# ==========================================================
# VALIDASI ARGUMENT
# ==========================================================

def validate_arguments():
    """
    Format:
    analisis.py <dataset_id> <file1> [file2] [file3] ...

    Minimal satu file Excel harus diberikan.
    """

    if len(sys.argv) < 3:
        raise ValueError(
            "Penggunaan: analisis.py "
            "<dataset_id> <file1> [file2] [file3] ..."
        )

    dataset_id = sys.argv[1]

    if not dataset_id.isdigit():
        raise ValueError(
            "dataset_id harus berupa angka."
        )

    dataset_id = int(dataset_id)

    if dataset_id <= 0:
        raise ValueError(
            "dataset_id harus lebih besar dari 0."
        )

    dataset_paths = []

    for argument in sys.argv[2:]:

        path = Path(argument).resolve()

        if not path.exists():
            raise FileNotFoundError(
                f"Dataset tidak ditemukan: {path}"
            )

        if not path.is_file():
            raise ValueError(
                f"Path dataset bukan file: {path}"
            )

        if path.suffix.lower() not in [
            ".xlsx",
            ".xls",
        ]:
            raise ValueError(
                f"Dataset harus berformat Excel: "
                f"{path.name}"
            )

        dataset_paths.append(path)

    if not dataset_paths:
        raise ValueError(
            "Tidak ada dataset yang diberikan."
        )

    return dataset_paths, dataset_id


# ==========================================================
# SIMPAN HASIL PREPROCESSING
# ==========================================================

def save_preprocessing(df):
    """
    Menyimpan hasil preprocessing seluruh dataset.
    """

    df.to_csv(
        PREPROCESSING_FILE,
        index=False,
        encoding="utf-8-sig",
    )


# ==========================================================
# SIMPAN HASIL ELBOW
# ==========================================================

def save_elbow(clustering_result):
    """
    Menyimpan hasil Elbow Method seluruh dataset.
    """

    elbow_results = clustering_result.get(
        "elbow",
        [],
    )

    k_optimal = clustering_result.get(
        "k_optimal"
    )

    jumlah_data = clustering_result.get(
        "jumlah_data"
    )

    rows = []

    for item in elbow_results:

        rows.append({
            "jumlah_data": jumlah_data,
            "k": item["k"],
            "inertia": item["inertia"],
            "k_optimal": k_optimal,
        })

    pd.DataFrame(rows).to_csv(
        ELBOW_FILE,
        index=False,
        encoding="utf-8-sig",
    )

    json_result = {
        "jumlah_data": jumlah_data,
        "k_optimal": k_optimal,
        "elbow": elbow_results,
    }

    with open(
        ELBOW_JSON,
        "w",
        encoding="utf-8",
    ) as file:

        json.dump(
            json_result,
            file,
            indent=2,
            ensure_ascii=False,
        )


# ==========================================================
# SIMPAN HASIL CLUSTERING
# ==========================================================

def save_clustering(clustering_result):
    """
    Menyimpan:
    - hasil clustering
    - TF-IDF vectorizer
    - model K-Means
    """

    df_hasil = clustering_result[
        "dataframe"
    ]

    # ------------------------------------------------------
    # Hasil clustering
    # ------------------------------------------------------

    df_hasil.to_csv(
        CLUSTERING_FILE,
        index=False,
        encoding="utf-8-sig",
    )

    # ------------------------------------------------------
    # TF-IDF Vectorizer
    # ------------------------------------------------------

    with open(
        TFIDF_MODEL,
        "wb",
    ) as file:

        pickle.dump(
            clustering_result["vectorizer"],
            file,
        )

    # ------------------------------------------------------
    # Model K-Means
    # ------------------------------------------------------

    model_data = {
        "model":
            clustering_result["model"],

        "k_optimal":
            clustering_result["k_optimal"],

        "jumlah_cluster":
            clustering_result["jumlah_cluster"],

        "top_terms":
            clustering_result["top_terms"],
    }

    with open(
        KMEANS_MODEL,
        "wb",
    ) as file:

        pickle.dump(
            model_data,
            file,
        )


# ==========================================================
# SIMPAN HASIL SILHOUETTE
# ==========================================================

def save_evaluation(evaluation_result):
    """
    Menyimpan hasil Silhouette Score global
    dan Silhouette Score setiap cluster.
    """

    rows = []

    for cluster_id, cluster in (
        evaluation_result[
            "per_cluster"
        ].items()
    ):

        rows.append({
            "cluster": cluster_id,

            "nama_cluster":
                cluster["nama_cluster"],

            "jumlah_data":
                cluster["jumlah_data"],

            "silhouette_cluster":
                cluster["silhouette_score"],

            "interpretasi_cluster":
                cluster["interpretasi"],

            "silhouette_global":
                evaluation_result[
                    "silhouette_score"
                ],

            "interpretasi_global":
                evaluation_result[
                    "interpretasi"
                ],
        })

    pd.DataFrame(rows).to_csv(
        SILHOUETTE_FILE,
        index=False,
        encoding="utf-8-sig",
    )

    json_result = {
        "jumlah_data":
            evaluation_result[
                "jumlah_data"
            ],

        "jumlah_cluster":
            evaluation_result[
                "jumlah_cluster"
            ],

        "silhouette_score":
            evaluation_result[
                "silhouette_score"
            ],

        "interpretasi":
            evaluation_result[
                "interpretasi"
            ],

        "per_cluster":
            evaluation_result[
                "per_cluster"
            ],
    }

    with open(
        SILHOUETTE_JSON,
        "w",
        encoding="utf-8",
    ) as file:

        json.dump(
            json_result,
            file,
            indent=2,
            ensure_ascii=False,
        )


# ==========================================================
# MEMBUAT GRAFIK DASHBOARD (ELBOW, K-MEANS, SILHOUETTE)
# ==========================================================

def save_grafik_dashboard(
    dataset_id,
    clustering_result,
    evaluation_result,
):
    """
    Membuat 3 grafik untuk halaman dashboard analisis:
    1. Grafik Elbow Method
    2. Grafik hasil clustering K-Means (scatter 2 dimensi)
    3. Grafik Silhouette Plot

    Grafik disimpan ke storage/app/public/analisis/{dataset_id}/
    dan path relatifnya (relatif terhadap folder public Laravel)
    dikembalikan agar Laravel/Blade dapat langsung menampilkannya
    lewat asset('storage/...').
    """

    folder = storage_analisis_dir(
        dataset_id
    )

    nama_cluster_map = clustering_result.get(
        "nama_cluster",
        {},
    )

    jumlah_data = clustering_result[
        "jumlah_data"
    ]

    # ------------------------------------------------------
    # 1. GRAFIK ELBOW
    # ------------------------------------------------------

    path_elbow = folder / "grafik_elbow.png"

    buat_grafik_elbow(
        elbow_results=clustering_result["elbow"],
        k_optimal=clustering_result["k_optimal"],
        jumlah_data=jumlah_data,
        output_path=path_elbow,
    )

    # ------------------------------------------------------
    # 2. GRAFIK CLUSTER (SCATTER)
    # ------------------------------------------------------

    path_cluster = folder / "grafik_cluster.png"

    buat_grafik_cluster(
        X=clustering_result["X"],
        labels=clustering_result["labels"],
        k_optimal=clustering_result["k_optimal"],
        jumlah_data=jumlah_data,
        nama_cluster_map=nama_cluster_map,
        output_path=path_cluster,
    )

    # ------------------------------------------------------
    # 3. GRAFIK SILHOUETTE
    # ------------------------------------------------------

    path_silhouette = folder / "grafik_silhouette.png"

    buat_grafik_silhouette(
        X=clustering_result["X"],
        labels=clustering_result["labels"],
        jumlah_cluster=clustering_result["jumlah_cluster"],
        k_optimal=clustering_result["k_optimal"],
        jumlah_data=jumlah_data,
        skor_global=evaluation_result["silhouette_score"],
        nama_cluster_map=nama_cluster_map,
        output_path=path_silhouette,
    )

    # ------------------------------------------------------
    # PATH RELATIF UNTUK LARAVEL (asset('storage/...'))
    # ------------------------------------------------------

    return {
        "elbow":
            f"storage/analisis/{dataset_id}/grafik_elbow.png",

        "cluster":
            f"storage/analisis/{dataset_id}/grafik_cluster.png",

        "silhouette":
            f"storage/analisis/{dataset_id}/grafik_silhouette.png",
    }


# ==========================================================
# MEMBUAT RESPONSE UNTUK LARAVEL
# ==========================================================

def build_response(
    dataset_id,
    dataset_paths,
    clustering_result,
    evaluation_result,
    duration,
    grafik,
    per_bidang,
):
    """
    Membentuk response akhir JSON
    yang dibaca Laravel.
    """

    jumlah_data = int(
        clustering_result[
            "jumlah_data"
        ]
    )

    jumlah_data_clustering = int(
        clustering_result[
            "jumlah_data_clustering"
        ]
    )

    # ------------------------------------------------------
    # Validasi 1 : 1
    # ------------------------------------------------------

    if jumlah_data != jumlah_data_clustering:
        raise ValueError(
            "Jumlah data preprocessing dan "
            "jumlah data clustering tidak sama."
        )

    return {
        "status": "success",

        "dataset_id":
            dataset_id,

        "datasets": [
            path.name
            for path in dataset_paths
        ],

        "jumlah_file":
            len(dataset_paths),

        "jumlah_data":
            jumlah_data,

        "jumlah_data_clustering":
            jumlah_data_clustering,

        "k_optimal":
            int(
                clustering_result[
                    "k_optimal"
                ]
            ),

        "jumlah_cluster":
            int(
                clustering_result[
                    "jumlah_cluster"
                ]
            ),

        "jumlah_cluster_detail":
            clustering_result[
                "jumlah_cluster_detail"
            ],

        "top_terms":
            clustering_result[
                "top_terms"
            ],

        "silhouette_score":
            evaluation_result[
                "silhouette_score"
            ],

        "interpretasi":
            evaluation_result[
                "interpretasi"
            ],

        "per_cluster":
            evaluation_result[
                "per_cluster"
            ],

        "grafik":
            grafik,

        "per_bidang":
            per_bidang,

        "durasi_detik":
            round(
                duration,
                2,
            ),
    }


# ==========================================================
# MAIN PIPELINE
# ==========================================================

def main():

    start_time = time.time()

    log_progress("=== Mulai proses analisis ===")

    # ------------------------------------------------------
    # 1. Validasi argument
    # ------------------------------------------------------

    dataset_paths, dataset_id = (
        validate_arguments()
    )

    # ------------------------------------------------------
    # 2. Bersihkan output sebelumnya
    # ------------------------------------------------------

    reset_output()

    # ------------------------------------------------------
    # 3. Gabungkan seluruh dataset Excel
    # ------------------------------------------------------

    tahap_mulai = time.time()

    df = combine_datasets(
        dataset_paths
    )

    if df.empty:
        raise ValueError(
            "Dataset gabungan tidak memiliki data."
        )

    jumlah_data_awal = len(df)

    log_progress(
        f"1/5 Gabungkan dataset selesai "
        f"({jumlah_data_awal} baris)",
        tahap_mulai,
    )

    # file_utils.py menyeragamkan kolom teks
    # menjadi kolom "teks".
    text_column = "teks"

    # ------------------------------------------------------
    # 4. PREPROCESSING
    # ------------------------------------------------------

    tahap_mulai = time.time()

    df_preprocessed = preprocess_dataset(
        df,
        text_column,
    )

    jumlah_data_preprocessing = len(
        df_preprocessed
    )

    log_progress(
        "2/5 Preprocessing & stemming selesai",
        tahap_mulai,
    )

    # Penelitian menggunakan seluruh data.
    if (
        jumlah_data_preprocessing
        != jumlah_data_awal
    ):
        raise ValueError(
            "Jumlah data berubah setelah preprocessing. "
            f"Data awal: {jumlah_data_awal}, "
            f"hasil preprocessing: "
            f"{jumlah_data_preprocessing}."
        )

    save_preprocessing(
        df_preprocessed
    )

    # ------------------------------------------------------
    # 5. TF-IDF + ELBOW + K-MEANS
    # ------------------------------------------------------

    tahap_mulai = time.time()

    clustering_result = run_clustering(
        df_preprocessed
    )

    log_progress(
        "3/5 TF-IDF + Elbow Method + K-Means selesai "
        f"(K optimal = {clustering_result.get('k_optimal')})",
        tahap_mulai,
    )

    # ------------------------------------------------------
    # 6. VALIDASI JUMLAH DATA CLUSTERING
    # ------------------------------------------------------

    jumlah_data_clustering = (
        clustering_result[
            "jumlah_data_clustering"
        ]
    )

    if (
        jumlah_data_clustering
        != jumlah_data_awal
    ):
        raise ValueError(
            "Tidak seluruh data masuk proses K-Means. "
            f"Data awal: {jumlah_data_awal}, "
            f"data clustering: "
            f"{jumlah_data_clustering}."
        )

    # ------------------------------------------------------
    # 7. SIMPAN ELBOW
    # ------------------------------------------------------

    save_elbow(
        clustering_result
    )

    # ------------------------------------------------------
    # 8. SIMPAN CLUSTERING DAN MODEL
    # ------------------------------------------------------

    save_clustering(
        clustering_result
    )

    # ------------------------------------------------------
    # 9. SILHOUETTE SCORE
    # ------------------------------------------------------

    tahap_mulai = time.time()

    evaluation_result = (
        evaluate_clustering(
            clustering_result
        )
    )

    save_evaluation(
        evaluation_result
    )

    log_progress(
        "4/5 Silhouette Score selesai "
        f"(score = {evaluation_result.get('silhouette_score')})",
        tahap_mulai,
    )

    # ------------------------------------------------------
    # 9A. ANALISIS TERPISAH PER BIDANG
    # ------------------------------------------------------
    #
    # Selain hasil clustering gabungan (seluruh data sekaligus),
    # dashboard juga menampilkan hasil clustering terpisah untuk
    # tiap Bidang (Tata Ruang, Bangunan Gedung, Pemakaman,
    # Lainnya). Proses ini menggunakan kolom "kategori" yang
    # masih tersimpan pada df_preprocessed.
    #
    # Kegagalan pada tahap ini tidak boleh menggagalkan seluruh
    # analisis (hasil gabungan tetap harus tersimpan), sehingga
    # dibungkus try/except dan hanya dicatat ke log.
    #

    tahap_mulai = time.time()

    try:

        per_bidang = run_clustering_per_bidang(
            df_preprocessed
        )

        log_progress(
            "4B/5 Analisis per Bidang selesai "
            f"({len(per_bidang)} bidang)",
            tahap_mulai,
        )

    except Exception as error:

        per_bidang = {}

        log_progress(
            "4B/5 GAGAL menjalankan analisis per Bidang "
            f"(analisis tetap dilanjutkan): "
            f"{type(error).__name__} - {error}"
        )

    # ------------------------------------------------------
    # 9B. GRAFIK DASHBOARD (ELBOW, K-MEANS, SILHOUETTE)
    # ------------------------------------------------------
    #
    # Grafik dibuat SETELAH seluruh perhitungan selesai supaya
    # tidak memengaruhi proses clustering/evaluasi itu sendiri.
    # Kegagalan membuat grafik tidak boleh menggagalkan seluruh
    # analisis (data & skor tetap harus tersimpan), sehingga
    # dibungkus try/except dan hanya dicatat ke log.
    #

    tahap_mulai = time.time()

    try:

        grafik = save_grafik_dashboard(
            dataset_id=dataset_id,
            clustering_result=clustering_result,
            evaluation_result=evaluation_result,
        )

        log_progress(
            "4C/5 Grafik dashboard (Elbow, K-Means, "
            "Silhouette) berhasil dibuat",
            tahap_mulai,
        )

    except Exception as error:

        grafik = {
            "elbow": None,
            "cluster": None,
            "silhouette": None,
        }

        log_progress(
            "4C/5 GAGAL membuat grafik dashboard "
            f"(analisis tetap dilanjutkan): "
            f"{type(error).__name__} - {error}"
        )

    # ------------------------------------------------------
    # 10. DURASI
    # ------------------------------------------------------

    duration = (
        time.time()
        - start_time
    )

    log_progress(
        f"5/5 Selesai. Total durasi: {duration:.2f} detik"
    )

    # ------------------------------------------------------
    # 11. RESPONSE LARAVEL
    # ------------------------------------------------------

    response = build_response(
        dataset_id=dataset_id,
        dataset_paths=dataset_paths,
        clustering_result=clustering_result,
        evaluation_result=evaluation_result,
        duration=duration,
        grafik=grafik,
        per_bidang=per_bidang,
    )

    # ------------------------------------------------------
    # OUTPUT JSON
    # ------------------------------------------------------

    print(
        json.dumps(
            response,
            ensure_ascii=False,
        )
    )


# ==========================================================
# ENTRY POINT
# ==========================================================

if __name__ == "__main__":

    try:

        main()

    except Exception as error:

        error_response = {
            "status": "error",

            "error_type":
                type(error).__name__,

            "message":
                str(error),
        }

        log_progress(
            f"GAGAL: {type(error).__name__} - {error}"
        )

        print(
            json.dumps(
                error_response,
                ensure_ascii=False,
            )
        )

        sys.exit(1)