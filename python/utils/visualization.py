"""
Modul visualisasi hasil analisis K-Means.

Modul ini HANYA membuat grafik (PNG) dari hasil Elbow Method,
K-Means, dan Silhouette Score. Modul ini tidak menghitung ulang
apa pun -- seluruh angka (inertia, label cluster, skor silhouette)
diambil dari hasil proses yang sudah dijalankan di
engine/clustering.py dan engine/evaluation.py.

PENTING: backend matplotlib di-set ke "Agg" SEBELUM
"import matplotlib.pyplot" karena proses ini dijalankan oleh
Laravel (Symfony Process) tanpa tampilan layar (headless).
Backend default matplotlib di Windows dapat mencoba membuka
jendela GUI dan membuat proses Python menggantung / gagal saat
dipanggil dari web.
"""

import numpy as np

import matplotlib
matplotlib.use("Agg")

import matplotlib.pyplot as plt

from sklearn.decomposition import TruncatedSVD
from sklearn.metrics import silhouette_samples


# ==========================================================
# GRAFIK 1: ELBOW METHOD
# ==========================================================

def buat_grafik_elbow(
    elbow_results,
    k_optimal,
    jumlah_data,
    output_path,
):
    """
    Membuat grafik garis Inertia vs Jumlah Cluster (k),
    dengan penanda bintang pada titik k_optimal.

    elbow_results: list of dict [{"k": .., "inertia": ..}, ...]
    """

    k_values = [
        int(item["k"])
        for item in elbow_results
    ]

    inertia_values = [
        float(item["inertia"])
        for item in elbow_results
    ]

    index_optimal = k_values.index(
        int(k_optimal)
    )

    plt.figure(figsize=(9, 6))

    plt.plot(
        k_values,
        inertia_values,
        marker="o",
        linewidth=2,
        label="Inertia",
    )

    plt.axvline(
        x=k_optimal,
        linestyle="--",
        alpha=0.7,
    )

    plt.scatter(
        [k_optimal],
        [inertia_values[index_optimal]],
        marker="*",
        s=220,
        zorder=5,
        label=f"K Optimal = {k_optimal}",
    )

    plt.title(
        "Elbow Method Penentuan K Optimal\n"
        f"Data Layanan Masyarakat DICIPTABINTAR Kota Bandung "
        f"(n={jumlah_data})",
        fontweight="bold",
    )

    plt.xlabel("Jumlah Cluster (k)")
    plt.ylabel("Inertia")

    plt.xticks(k_values)

    plt.grid(
        True,
        linestyle="--",
        alpha=0.4,
    )

    plt.legend()

    plt.tight_layout()

    plt.savefig(
        output_path,
        dpi=150,
        bbox_inches="tight",
    )

    plt.close()


# ==========================================================
# GRAFIK 2: HASIL CLUSTERING (SCATTER 2 DIMENSI)
# ==========================================================

def buat_grafik_cluster(
    X,
    labels,
    k_optimal,
    jumlah_data,
    nama_cluster_map,
    output_path,
):
    """
    Mereduksi matriks TF-IDF (ratusan/ribuan dimensi) menjadi
    2 dimensi menggunakan TruncatedSVD, lalu menampilkan
    sebaran data tiap cluster dalam bentuk scatter plot.

    nama_cluster_map: dict {cluster_id (int): nama_cluster (str)}
    """

    labels = np.asarray(labels)

    svd = TruncatedSVD(
        n_components=2,
        random_state=42,
    )

    X_2d = svd.fit_transform(X)

    plt.figure(figsize=(12, 8))

    for cluster_id in range(k_optimal):

        mask = labels == cluster_id

        nama = nama_cluster_map.get(
            cluster_id,
            f"Cluster {cluster_id}",
        )

        plt.scatter(
            X_2d[mask, 0],
            X_2d[mask, 1],
            label=nama,
            alpha=0.65,
            s=35,
        )

    plt.title(
        "Hasil Clustering K-Means Data Layanan Masyarakat\n"
        f"DICIPTABINTAR Kota Bandung (K={k_optimal}, n={jumlah_data})",
        fontsize=13,
        fontweight="bold",
    )

    plt.xlabel("Komponen 1")
    plt.ylabel("Komponen 2")

    plt.legend(
        loc="best",
        fontsize=9,
    )

    plt.grid(
        True,
        linestyle="--",
        alpha=0.3,
    )

    plt.tight_layout()

    plt.savefig(
        output_path,
        dpi=150,
        bbox_inches="tight",
    )

    plt.close()


# ==========================================================
# GRAFIK 3: SILHOUETTE PLOT
# ==========================================================

def buat_grafik_silhouette(
    X,
    labels,
    jumlah_cluster,
    k_optimal,
    jumlah_data,
    skor_global,
    nama_cluster_map,
    output_path,
):
    """
    Membuat silhouette plot standar (satu "bilah" horizontal
    per cluster, diurutkan dari skor terendah ke tertinggi),
    lengkap dengan garis rata-rata Silhouette Score.
    """

    labels = np.asarray(labels)

    skor_per_data = silhouette_samples(
        X,
        labels,
        metric="euclidean",
    )

    unique_labels = sorted(
        np.unique(labels)
    )

    fig, ax = plt.subplots(
        figsize=(12, 8)
    )

    y_lower = 10

    for cluster_id in unique_labels:

        cluster_values = skor_per_data[
            labels == cluster_id
        ]

        cluster_values.sort()

        ukuran_cluster = cluster_values.shape[0]

        y_upper = y_lower + ukuran_cluster

        warna = plt.cm.nipy_spectral(
            float(cluster_id) / jumlah_cluster
        )

        ax.fill_betweenx(
            np.arange(y_lower, y_upper),
            0,
            cluster_values,
            facecolor=warna,
            edgecolor=warna,
            alpha=0.7,
        )

        nama = nama_cluster_map.get(
            int(cluster_id),
            f"Cluster {int(cluster_id)}",
        )

        # Label dipersingkat agar tidak tumpang tindih pada grafik.
        label_singkat = (
            nama
            if len(nama) <= 24
            else nama[:21] + "..."
        )

        ax.text(
            -0.09,
            y_lower + 0.5 * ukuran_cluster,
            label_singkat,
            fontsize=8,
        )

        y_lower = y_upper + 10

    ax.axvline(
        x=skor_global,
        linestyle="--",
        linewidth=2,
        label=f"Silhouette Score = {skor_global:.4f}",
    )

    ax.set_title(
        "Silhouette Plot Hasil Clustering K-Means\n"
        f"Data Layanan Masyarakat DICIPTABINTAR Kota Bandung "
        f"(K={k_optimal}, n={jumlah_data})",
        fontsize=14,
        fontweight="bold",
    )

    ax.set_xlabel("Silhouette Coefficient")
    ax.set_ylabel("Data dalam Cluster")

    ax.set_xlim([-0.2, 1.0])
    ax.set_yticks([])

    ax.legend(loc="upper right")

    ax.grid(
        axis="x",
        linestyle="--",
        alpha=0.3,
    )

    plt.tight_layout()

    plt.savefig(
        output_path,
        dpi=150,
        bbox_inches="tight",
    )

    plt.close()
