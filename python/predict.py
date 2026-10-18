import json
import pickle
import sys
from pathlib import Path

from config import (
    KMEANS_MODEL,
    TFIDF_MODEL,
)

from engine.clustering import NAMA_CLUSTER
from engine.preprocessing import preprocess_text


# ==========================================================
# VALIDASI ARGUMENT
# ==========================================================

def validate_arguments():
    """
    Format pemanggilan:

    python predict.py <path_file_teks_pengaduan>

    File berisi isi_pengaduan (judul + isi digabung)
    dalam bentuk teks biasa (UTF-8), ditulis oleh Laravel
    ke file sementara agar aman dari karakter kutip,
    baris baru, dsb.
    """

    if len(sys.argv) < 2:
        raise ValueError(
            "Argument path file teks pengaduan tidak diberikan."
        )

    path_teks = Path(sys.argv[1])

    if not path_teks.exists():
        raise ValueError(
            f"File teks pengaduan tidak ditemukan: {path_teks}"
        )

    teks = path_teks.read_text(
        encoding="utf-8"
    ).strip()

    if teks == "":
        raise ValueError(
            "Isi pengaduan kosong, tidak dapat diprediksi."
        )

    return teks


# ==========================================================
# MUAT MODEL
# ==========================================================

def load_models():
    """
    Memuat TF-IDF vectorizer dan model K-Means yang telah
    dilatih dan disimpan pada proses "Jalankan Analisis"
    (python/analisis.py -> save_clustering()).

    Apabila model belum tersedia (Admin belum pernah
    menjalankan analisis dataset), prediksi tidak dapat
    dilakukan. Ini BUKAN error fatal bagi pengajuan
    pengaduan itu sendiri -- Laravel yang memutuskan
    bagaimana menangani kondisi ini.
    """

    if not TFIDF_MODEL.exists() or not KMEANS_MODEL.exists():
        raise ValueError(
            "Model TF-IDF/K-Means belum tersedia. "
            "Admin perlu menjalankan analisis dataset "
            "historis terlebih dahulu sebelum prediksi "
            "cluster pengaduan baru dapat dilakukan."
        )

    with open(TFIDF_MODEL, "rb") as file:
        vectorizer = pickle.load(file)

    with open(KMEANS_MODEL, "rb") as file:
        model_data = pickle.load(file)

    return vectorizer, model_data


# ==========================================================
# PREDIKSI CLUSTER
# ==========================================================

def predict_cluster(teks: str, vectorizer, model_data):
    """
    Menjalankan preprocessing yang SAMA PERSIS dengan
    preprocessing dataset historis, lalu mentransformasi
    teks menggunakan TF-IDF vectorizer yang sudah dilatih,
    dan memprediksi cluster menggunakan model K-Means
    yang sudah dilatih (bukan melatih ulang).
    """

    hasil_preprocessing = preprocess_text(teks)

    if hasil_preprocessing == "":
        raise ValueError(
            "Teks pengaduan menjadi kosong setelah "
            "preprocessing (mis. hanya berisi angka/simbol)."
        )

    # Vectorizer HANYA transform, tidak fit ulang,
    # supaya konsisten dengan ruang fitur (vocabulary)
    # hasil pelatihan pada dataset historis.
    X = vectorizer.transform(
        [hasil_preprocessing]
    )

    model = model_data["model"]

    cluster = int(
        model.predict(X)[0]
    )

    k_optimal = int(
        model_data["k_optimal"]
    )

    nama_cluster = (
        NAMA_CLUSTER.get(
            cluster,
            f"Cluster {cluster}",
        )
        if k_optimal == 4
        else f"Cluster {cluster}"
    )

    top_terms = (
        model_data
        .get("top_terms", {})
        .get(cluster, [])
    )

    return {
        "hasil_preprocessing": hasil_preprocessing,
        "cluster": cluster,
        "nama_cluster": nama_cluster,
        "k_optimal": k_optimal,
        "top_terms": top_terms,
    }


# ==========================================================
# ENTRY POINT
# ==========================================================

def main():

    teks = validate_arguments()

    vectorizer, model_data = load_models()

    hasil = predict_cluster(
        teks,
        vectorizer,
        model_data,
    )

    response = {
        "status": "success",
        **hasil,
    }

    print(
        json.dumps(
            response,
            ensure_ascii=False,
        )
    )


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

        print(
            json.dumps(
                error_response,
                ensure_ascii=False,
            )
        )

        sys.exit(1)