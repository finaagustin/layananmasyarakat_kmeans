from pathlib import Path
import sys

# ==========================================================
# ROOT PROJECT
# ==========================================================

BASE_DIR = Path(__file__).resolve().parent

# ==========================================================
# FOLDER
# ==========================================================

INPUT_DIR = BASE_DIR / "input"
OUTPUT_DIR = BASE_DIR / "output"
MODEL_DIR = BASE_DIR / "models"
LOG_DIR = BASE_DIR / "logs"
TEMP_DIR = BASE_DIR / "temp"
SCRIPT_DIR = BASE_DIR / "scripts"

DIRECTORIES = [
    INPUT_DIR,
    OUTPUT_DIR,
    MODEL_DIR,
    LOG_DIR,
    TEMP_DIR,
]

for folder in DIRECTORIES:
    folder.mkdir(parents=True, exist_ok=True)

# ==========================================================
# OUTPUT PREPROCESSING
# ==========================================================

PREPROCESSING_FILE = OUTPUT_DIR / "hasil_preprocessing.csv"

# ==========================================================
# OUTPUT TF-IDF
# ==========================================================

DATA_CLUSTER_FILE = OUTPUT_DIR / "data_siap_cluster.csv"

TFIDF_RINGKASAN_FILE = OUTPUT_DIR / "tfidf_ringkasan.csv"

# ==========================================================
# OUTPUT ELBOW METHOD
# ==========================================================

ELBOW_FILE = OUTPUT_DIR / "hasil_elbow.csv"

ELBOW_JSON = OUTPUT_DIR / "rekomendasi_k.json"

ELBOW_VISUAL = OUTPUT_DIR / "grafik_elbow.png"

# ==========================================================
# OUTPUT K-MEANS
# ==========================================================

CLUSTERING_FILE = OUTPUT_DIR / "hasil_clustering.csv"

CLUSTER_VISUAL = OUTPUT_DIR / "grafik_cluster.png"

# ==========================================================
# OUTPUT SILHOUETTE SCORE
# ==========================================================

SILHOUETTE_FILE = OUTPUT_DIR / "hasil_silhouette.csv"

SILHOUETTE_JSON = OUTPUT_DIR / "silhouette.json"

SILHOUETTE_VISUAL = OUTPUT_DIR / "grafik_silhouette.png"

# ==========================================================
# MODEL
# ==========================================================

TFIDF_MODEL = MODEL_DIR / "tfidf_vectorizer.pkl"

TFIDF_MATRIX = MODEL_DIR / "tfidf_matrix.pkl"

KMEANS_MODEL = MODEL_DIR / "kmeans_models.pkl"

# ==========================================================
# LOG
# ==========================================================

LOG_FILE = LOG_DIR / "analysis.log"

# ==========================================================
# PARAMETER PREPROCESSING
# ==========================================================

TEXT_COLUMN = "PERMOHONAN/PENGADUAN"

MIN_TEXT_LENGTH = 3

# ==========================================================
# PARAMETER K-MEANS
# ==========================================================

DEFAULT_RANDOM_STATE = 42

DEFAULT_MAX_ITER = 300

DEFAULT_N_INIT = 10

MIN_CLUSTER = 2

MAX_CLUSTER = 8

# ==========================================================
# PYTHON
# ==========================================================

PYTHON_EXE = sys.executable

# ==========================================================
# FOLDER GRAFIK DASHBOARD (STORAGE LARAVEL)
# ==========================================================
#
# Grafik Elbow, K-Means, dan Silhouette disimpan LANGSUNG ke
# folder public storage Laravel (bukan ke python/output) agar
# bisa langsung ditampilkan di halaman dashboard menggunakan
# asset('storage/...'), mengikuti pola yang sama dengan lampiran
# pengaduan (storage/app/public/lampiran_pengaduan).
#
# BASE_DIR = .../layanan_app/python
# LARAVEL_ROOT = .../layanan_app
#
LARAVEL_ROOT = BASE_DIR.parent


def storage_analisis_dir(dataset_id):
    """
    Mengembalikan folder:
    storage/app/public/analisis/{dataset_id}/

    Folder dibuat otomatis jika belum ada. Setiap dataset
    mempunyai foldernya sendiri (dipisah berdasarkan dataset_id)
    supaya grafik dataset lama tidak tertimpa oleh dataset baru.
    """

    folder = (
        LARAVEL_ROOT
        / "storage"
        / "app"
        / "public"
        / "analisis"
        / str(dataset_id)
    )

    folder.mkdir(
        parents=True,
        exist_ok=True,
    )

    return folder


# ==========================================================
# UTILITIES
# ==========================================================

def reset_output():
    """
    Menghapus file output analisis sebelumnya agar hasil
    yang digunakan selalu berasal dari proses terbaru.

    File model tidak dihapus di sini karena akan ditimpa
    kembali oleh proses TF-IDF dan K-Means.
    """

    output_files = [
        PREPROCESSING_FILE,
        DATA_CLUSTER_FILE,
        TFIDF_RINGKASAN_FILE,

        ELBOW_FILE,
        ELBOW_JSON,
        ELBOW_VISUAL,

        CLUSTERING_FILE,
        CLUSTER_VISUAL,

        SILHOUETTE_FILE,
        SILHOUETTE_JSON,
        SILHOUETTE_VISUAL,
    ]

    for file in output_files:
        if file.exists():
            file.unlink()