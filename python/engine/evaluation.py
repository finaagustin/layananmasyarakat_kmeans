import numpy as np

from sklearn.metrics import (
    silhouette_score,
    silhouette_samples,
)


# ==========================================================
# INTERPRETASI SILHOUETTE SCORE
# ==========================================================

def interpret_silhouette(score: float) -> str:
    """
    Memberikan interpretasi terhadap nilai
    Silhouette Score.
    """

    if score >= 0.70:
        return "Sangat Baik"

    if score >= 0.50:
        return "Baik"

    if score >= 0.25:
        return "Cukup"

    return "Lemah"


# ==========================================================
# EVALUASI CLUSTERING
# ==========================================================

def evaluate_clustering(clustering_result):
    """
    Menghitung Silhouette Score terhadap seluruh
    hasil clustering K-Means.

    Evaluasi dilakukan menggunakan:
    - seluruh matriks TF-IDF
    - seluruh label hasil K-Means
    - seluruh data hasil clustering

    Kategori awal dataset tidak digunakan dalam
    proses evaluasi.
    """

    if not clustering_result:
        raise ValueError(
            "Hasil clustering tidak tersedia."
        )

    # ------------------------------------------------------
    # 1. AMBIL HASIL CLUSTERING
    # ------------------------------------------------------

    X = clustering_result.get("X")
    labels = clustering_result.get("labels")
    df_hasil = clustering_result.get("dataframe")

    if X is None:
        raise ValueError(
            "Matriks TF-IDF tidak tersedia."
        )

    if labels is None:
        raise ValueError(
            "Label clustering tidak tersedia."
        )

    if df_hasil is None:
        raise ValueError(
            "DataFrame hasil clustering tidak tersedia."
        )

    labels = np.asarray(labels)

    # ------------------------------------------------------
    # 2. VALIDASI JUMLAH DATA
    # ------------------------------------------------------

    jumlah_data = int(X.shape[0])

    if len(labels) != jumlah_data:
        raise ValueError(
            "Jumlah label clustering tidak sama "
            "dengan jumlah data TF-IDF."
        )

    if len(df_hasil) != jumlah_data:
        raise ValueError(
            "Jumlah DataFrame hasil clustering tidak sama "
            "dengan jumlah data TF-IDF."
        )

    if jumlah_data < 3:
        raise ValueError(
            "Jumlah data tidak mencukupi untuk "
            "menghitung Silhouette Score."
        )

    # ------------------------------------------------------
    # 3. VALIDASI CLUSTER
    # ------------------------------------------------------

    unique_labels = np.unique(labels)

    jumlah_cluster = len(unique_labels)

    if jumlah_cluster < 2:
        raise ValueError(
            "Silhouette Score membutuhkan "
            "minimal 2 cluster."
        )

    if jumlah_cluster >= jumlah_data:
        raise ValueError(
            "Jumlah cluster harus lebih kecil "
            "daripada jumlah data."
        )

    # ------------------------------------------------------
    # 4. SILHOUETTE SCORE GLOBAL
    # ------------------------------------------------------

    global_score = silhouette_score(
        X,
        labels,
        metric="euclidean",
    )

    global_score = float(global_score)

    # ------------------------------------------------------
    # 5. SILHOUETTE SCORE PER DATA
    # ------------------------------------------------------

    sample_scores = silhouette_samples(
        X,
        labels,
        metric="euclidean",
    )

    # ------------------------------------------------------
    # 6. SILHOUETTE SCORE PER CLUSTER
    # ------------------------------------------------------

    per_cluster = {}

    for cluster_id in unique_labels:

        cluster_id_int = int(cluster_id)

        mask = labels == cluster_id

        cluster_score = float(
            sample_scores[mask].mean()
        )

        # --------------------------------------------------
        # Ambil nama cluster dari hasil K-Means
        # --------------------------------------------------

        cluster_rows = df_hasil[
            df_hasil["cluster"] == cluster_id_int
        ]

        if (
            "nama_cluster" in cluster_rows.columns
            and not cluster_rows.empty
        ):
            nama_cluster = str(
                cluster_rows["nama_cluster"].iloc[0]
            )
        else:
            nama_cluster = (
                f"Cluster {cluster_id_int}"
            )

        per_cluster[cluster_id_int] = {
            "nama_cluster":
                nama_cluster,

            "jumlah_data":
                int(mask.sum()),

            "silhouette_score":
                round(
                    cluster_score,
                    4,
                ),

            "interpretasi":
                interpret_silhouette(
                    cluster_score
                ),
        }

    # ------------------------------------------------------
    # 7. VALIDASI TOTAL DATA PER CLUSTER
    # ------------------------------------------------------

    total_data_cluster = sum(
        item["jumlah_data"]
        for item in per_cluster.values()
    )

    if total_data_cluster != jumlah_data:
        raise ValueError(
            "Jumlah data seluruh cluster tidak sama "
            "dengan jumlah data hasil clustering."
        )

    # ------------------------------------------------------
    # 8. STATISTIK SILHOUETTE PER DATA
    # ------------------------------------------------------

    statistik = {
        "minimum":
            round(float(sample_scores.min()), 4),

        "maksimum":
            round(float(sample_scores.max()), 4),

        "rata_rata":
            round(float(sample_scores.mean()), 4),

        "median":
            round(float(np.median(sample_scores)), 4),

        "positif":
            int(np.sum(sample_scores > 0)),

        "nol":
            int(np.sum(sample_scores == 0)),

        "negatif":
            int(np.sum(sample_scores < 0)),
    }

    # ------------------------------------------------------
    # 9. VALIDASI JUMLAH STATISTIK DATA
    # ------------------------------------------------------

    total_statistik = (
        statistik["positif"]
        + statistik["nol"]
        + statistik["negatif"]
    )

    if total_statistik != jumlah_data:
        raise ValueError(
            "Jumlah statistik Silhouette tidak sama "
            "dengan jumlah data."
        )

    # ------------------------------------------------------
    # 10. HASIL EVALUASI
    # ------------------------------------------------------

    return {
        "jumlah_data":
            jumlah_data,

        "jumlah_cluster":
            int(jumlah_cluster),

        "silhouette_score":
            round(
                global_score,
                4,
            ),

        "interpretasi":
            interpret_silhouette(
                global_score
            ),

        "per_cluster":
            per_cluster,

        "statistik":
            statistik,
    }