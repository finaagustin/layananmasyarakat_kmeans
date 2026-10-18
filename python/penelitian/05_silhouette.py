# =============================================================================
# SKRIPSI: Analisis dan Pengelompokan Layanan Masyarakat Menggunakan K-Means
# Instansi: DICIPTABINTAR Kota Bandung
# =============================================================================
# FILE    : 05_silhouette.py
# FUNGSI  : Tahap 5 - Silhouette Score (Evaluasi Hasil Clustering)
# TUJUAN  : Mengevaluasi kualitas cluster K-Means terhadap seluruh data
# CATATAN : Jalankan 01, 02, 03, dan 04 terlebih dahulu
# =============================================================================

import json
import pickle
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt

from sklearn.metrics import silhouette_score, silhouette_samples


# =============================================================================
# KONFIGURASI
# =============================================================================

FILE_CLUSTERING = "hasil_clustering.csv"
FILE_DATA = "data_siap_cluster.csv"
FILE_TFIDF = "tfidf_matrix.pkl"
FILE_K = "rekomendasi_k.json"

OUTPUT_CSV = "hasil_silhouette.csv"
OUTPUT_DETAIL = "hasil_silhouette_per_data.csv"
OUTPUT_JSON = "silhouette.json"
OUTPUT_GRAFIK = "grafik_silhouette.png"


# =============================================================================
# FUNGSI INTERPRETASI
# =============================================================================

def interpretasi_silhouette(score):
    """
    Memberikan interpretasi terhadap Silhouette Score.
    """

    if score >= 0.70:
        return "Sangat Baik"
    elif score >= 0.50:
        return "Baik"
    elif score >= 0.25:
        return "Cukup"
    else:
        return "Lemah"


# =============================================================================
# BAGIAN 1 — MEMUAT DATA
# =============================================================================

print("=" * 70)
print("LANGKAH 1: MEMUAT DATA HASIL CLUSTERING")
print("=" * 70)

print(f"Membaca {FILE_CLUSTERING} ...")
df_hasil = pd.read_csv(FILE_CLUSTERING)

print(f"Membaca {FILE_DATA} ...")
df_base = pd.read_csv(FILE_DATA)

print(f"Membaca {FILE_TFIDF} ...")
with open(FILE_TFIDF, "rb") as f:
    X = pickle.load(f)

print(f"Membaca {FILE_K} ...")
with open(FILE_K, "r", encoding="utf-8") as f:
    data_k = json.load(f)


# =============================================================================
# AMBIL K OPTIMAL
# =============================================================================

if isinstance(data_k, dict):

    if "k_optimal" in data_k:
        k_optimal = int(data_k["k_optimal"])

    elif "k" in data_k:
        k_optimal = int(data_k["k"])

    elif len(data_k) == 1:
        k_optimal = int(list(data_k.values())[0])

    else:
        raise ValueError(
            "Format rekomendasi_k.json tidak dikenali."
        )

else:
    k_optimal = int(data_k)


# =============================================================================
# VALIDASI DATA
# =============================================================================

jumlah_base = len(df_base)
jumlah_hasil = len(df_hasil)
jumlah_tfidf = X.shape[0]

if "cluster" not in df_hasil.columns:
    raise ValueError(
        "Kolom 'cluster' tidak ditemukan "
        "pada hasil_clustering.csv."
    )

labels = df_hasil["cluster"].astype(int).to_numpy()

unique_labels = np.unique(labels)
jumlah_cluster = len(unique_labels)


print()
print(f"Data preprocessing / TF-IDF : {jumlah_base}")
print(f"Data hasil clustering       : {jumlah_hasil}")
print(f"Vektor TF-IDF               : {jumlah_tfidf}")
print(f"K optimal                   : {k_optimal}")
print(f"Cluster terbentuk           : {unique_labels.tolist()}")


if not (
    jumlah_base
    == jumlah_hasil
    == jumlah_tfidf
):
    raise ValueError(
        "\nJumlah data tidak konsisten.\n"
        f"Data dasar       : {jumlah_base}\n"
        f"Hasil clustering : {jumlah_hasil}\n"
        f"TF-IDF           : {jumlah_tfidf}"
    )


if jumlah_cluster < 2:
    raise ValueError(
        "Silhouette Score membutuhkan minimal 2 cluster."
    )


if jumlah_cluster >= jumlah_hasil:
    raise ValueError(
        "Jumlah cluster harus lebih kecil "
        "daripada jumlah data."
    )


if jumlah_cluster != k_optimal:
    print()
    print(
        "[PERHATIAN] Jumlah cluster yang terbentuk "
        f"({jumlah_cluster}) berbeda dengan "
        f"K optimal ({k_optimal})."
    )


print()
print(
    f"VALIDASI BERHASIL: seluruh {jumlah_hasil} data "
    "akan dievaluasi."
)


# =============================================================================
# BAGIAN 2 — PENJELASAN SILHOUETTE SCORE
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 2: PENJELASAN SILHOUETTE SCORE")
print("=" * 70)

print("""
Silhouette Score digunakan untuk mengevaluasi kualitas hasil
pengelompokan K-Means.

Untuk setiap data i dihitung:

    s(i) = (b(i) - a(i)) / max(a(i), b(i))

Keterangan:

a(i) = rata-rata jarak data i terhadap data lain
       dalam cluster yang sama.

b(i) = rata-rata jarak data i terhadap data
       pada cluster tetangga terdekat.

Rentang Silhouette Score adalah -1 sampai 1.

Semakin mendekati 1:
    data semakin sesuai dengan cluster-nya.

Mendekati 0:
    data berada pada batas antar-cluster.

Nilai negatif:
    data kemungkinan lebih dekat dengan cluster lain.

Pada penelitian ini Silhouette Score dihitung terhadap
SELURUH 1.000 data hasil K-Means, bukan berdasarkan
kategori awal.
""")


# =============================================================================
# BAGIAN 3 — MENGHITUNG SILHOUETTE SCORE
# =============================================================================

print("=" * 70)
print("LANGKAH 3: MENGHITUNG SILHOUETTE SCORE SELURUH DATA")
print("=" * 70)

print()
print(f"Jumlah data    : {jumlah_hasil}")
print(f"Jumlah cluster : {jumlah_cluster}")
print(f"K optimal      : {k_optimal}")

print("\nMenghitung Silhouette Score...")

skor_global = silhouette_score(
    X,
    labels,
    metric="euclidean"
)

skor_per_data = silhouette_samples(
    X,
    labels,
    metric="euclidean"
)

interpretasi_global = interpretasi_silhouette(
    skor_global
)


print("\nHASIL EVALUASI:")
print("-" * 70)

print(
    f"Silhouette Score keseluruhan : "
    f"{skor_global:.4f}"
)

print(
    f"Interpretasi                 : "
    f"{interpretasi_global}"
)


# =============================================================================
# BAGIAN 4 — SILHOUETTE SCORE PER CLUSTER
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 4: SILHOUETTE SCORE PER CLUSTER")
print("=" * 70)

hasil_cluster = []

print()
print(
    f"{'Cluster':<12}"
    f"{'Jumlah Data':<15}"
    f"{'Persentase':<15}"
    f"{'Silhouette':<15}"
    f"{'Interpretasi'}"
)

print("-" * 75)


for cluster_id in sorted(unique_labels):

    mask = labels == cluster_id

    jumlah_data_cluster = int(
        np.sum(mask)
    )

    persentase = (
        jumlah_data_cluster
        / jumlah_hasil
        * 100
    )

    skor_cluster = float(
        np.mean(
            skor_per_data[mask]
        )
    )

    interpretasi_cluster = (
        interpretasi_silhouette(
            skor_cluster
        )
    )

    hasil_cluster.append({
        "cluster": int(cluster_id),
        "jumlah_data": jumlah_data_cluster,
        "persentase": round(
            persentase,
            2
        ),
        "silhouette_cluster": round(
            skor_cluster,
            4
        ),
        "interpretasi": interpretasi_cluster,
    })

    print(
        f"Cluster {cluster_id:<4}"
        f"{jumlah_data_cluster:<15}"
        f"{persentase:<14.2f}%"
        f"{skor_cluster:<15.4f}"
        f"{interpretasi_cluster}"
    )


# =============================================================================
# VALIDASI JUMLAH ANGGOTA CLUSTER
# =============================================================================

total_cluster = sum(
    item["jumlah_data"]
    for item in hasil_cluster
)

print("-" * 75)

print(
    f"{'TOTAL':<12}"
    f"{total_cluster:<15}"
    f"{100.00:<14.2f}%"
)


if total_cluster != jumlah_hasil:
    raise ValueError(
        "Jumlah anggota seluruh cluster "
        "tidak sama dengan jumlah data."
    )


# =============================================================================
# BAGIAN 5 — STATISTIK SILHOUETTE PER DATA
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 5: STATISTIK SILHOUETTE PER DATA")
print("=" * 70)

jumlah_positif = int(
    np.sum(skor_per_data > 0)
)

jumlah_nol = int(
    np.sum(
        np.isclose(
            skor_per_data,
            0
        )
    )
)

jumlah_negatif = int(
    np.sum(skor_per_data < 0)
)


print(
    f"\nNilai minimum      : "
    f"{np.min(skor_per_data):.4f}"
)

print(
    f"Nilai maksimum     : "
    f"{np.max(skor_per_data):.4f}"
)

print(
    f"Nilai rata-rata    : "
    f"{np.mean(skor_per_data):.4f}"
)

print(
    f"Nilai median       : "
    f"{np.median(skor_per_data):.4f}"
)

print(
    f"\nData skor positif  : "
    f"{jumlah_positif}"
)

print(
    f"Data skor nol      : "
    f"{jumlah_nol}"
)

print(
    f"Data skor negatif  : "
    f"{jumlah_negatif}"
)


# =============================================================================
# BAGIAN 6 — MEMBUAT SILHOUETTE PLOT
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 6: MEMBUAT GRAFIK SILHOUETTE")
print("=" * 70)

fig, ax = plt.subplots(
    figsize=(12, 8)
)

y_lower = 10


for cluster_id in sorted(unique_labels):

    cluster_values = skor_per_data[
        labels == cluster_id
    ]

    cluster_values.sort()

    ukuran_cluster = (
        cluster_values.shape[0]
    )

    y_upper = (
        y_lower
        + ukuran_cluster
    )

    warna = plt.cm.nipy_spectral(
        float(cluster_id)
        / jumlah_cluster
    )

    ax.fill_betweenx(
        np.arange(
            y_lower,
            y_upper
        ),
        0,
        cluster_values,
        facecolor=warna,
        edgecolor=warna,
        alpha=0.7
    )

    ax.text(
        -0.08,
        y_lower
        + 0.5 * ukuran_cluster,
        f"Cluster {cluster_id}"
    )

    y_lower = (
        y_upper
        + 10
    )


# Garis rata-rata Silhouette
ax.axvline(
    x=skor_global,
    linestyle="--",
    linewidth=2,
    label=(
        f"Silhouette Score = "
        f"{skor_global:.4f}"
    )
)


ax.set_title(
    "Silhouette Plot Hasil Clustering K-Means\n"
    f"Data Layanan Masyarakat DICIPTABINTAR Kota Bandung "
    f"(K={k_optimal}, n={jumlah_hasil})",
    fontsize=14,
    fontweight="bold"
)

ax.set_xlabel(
    "Silhouette Coefficient"
)

ax.set_ylabel(
    "Data dalam Cluster"
)

ax.set_xlim(
    [-0.2, 1.0]
)

ax.set_yticks([])

ax.legend(
    loc="upper right"
)

ax.grid(
    axis="x",
    linestyle="--",
    alpha=0.3
)

plt.tight_layout()

plt.savefig(
    OUTPUT_GRAFIK,
    dpi=150,
    bbox_inches="tight"
)

print(
    f"Grafik tersimpan: "
    f"{OUTPUT_GRAFIK}"
)

print(
    "Menampilkan grafik..."
)

plt.show()

plt.close()


# =============================================================================
# BAGIAN 7 — MENYIMPAN HASIL PER CLUSTER
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 7: MENYIMPAN HASIL EVALUASI")
print("=" * 70)

df_skor = pd.DataFrame(
    hasil_cluster
)

df_skor[
    "silhouette_global"
] = round(
    float(skor_global),
    4
)

df_skor[
    "interpretasi_global"
] = interpretasi_global


df_skor.to_csv(
    OUTPUT_CSV,
    index=False,
    encoding="utf-8-sig"
)


print(
    f"\nTersimpan: "
    f"{OUTPUT_CSV}"
)

print("\nPreview:")
print(
    df_skor.to_string(
        index=False
    )
)


# =============================================================================
# BAGIAN 8 — MENYIMPAN SILHOUETTE SETIAP DATA
# =============================================================================

df_detail = df_hasil.copy()

df_detail[
    "silhouette_score"
] = np.round(
    skor_per_data,
    4
)


df_detail.to_csv(
    OUTPUT_DETAIL,
    index=False,
    encoding="utf-8-sig"
)


print(
    f"\nTersimpan: "
    f"{OUTPUT_DETAIL}"
)


# =============================================================================
# BAGIAN 9 — SIMPAN JSON UNTUK INTEGRASI LARAVEL
# =============================================================================

hasil_json = {
    "jumlah_data": int(
        jumlah_hasil
    ),
    "jumlah_cluster": int(
        jumlah_cluster
    ),
    "k_optimal": int(
        k_optimal
    ),
    "silhouette_score": round(
        float(skor_global),
        4
    ),
    "interpretasi": (
        interpretasi_global
    ),
    "per_cluster": hasil_cluster,
}


with open(
    OUTPUT_JSON,
    "w",
    encoding="utf-8"
) as f:

    json.dump(
        hasil_json,
        f,
        ensure_ascii=False,
        indent=4
    )


print(
    f"Tersimpan: "
    f"{OUTPUT_JSON}"
)


# =============================================================================
# BAGIAN 10 — VALIDASI AKHIR
# =============================================================================

print("\n" + "=" * 70)
print("VALIDASI AKHIR")
print("=" * 70)

print(
    f"Data preprocessing : "
    f"{jumlah_base}"
)

print(
    f"Vektor TF-IDF      : "
    f"{jumlah_tfidf}"
)

print(
    f"Data K-Means       : "
    f"{jumlah_hasil}"
)

print(
    f"Data Silhouette    : "
    f"{len(skor_per_data)}"
)

print(
    f"Jumlah cluster     : "
    f"{jumlah_cluster}"
)

print(
    f"K optimal          : "
    f"{k_optimal}"
)

print(
    f"Silhouette Score   : "
    f"{skor_global:.4f}"
)

print(
    f"Interpretasi       : "
    f"{interpretasi_global}"
)


if (
    jumlah_base
    == jumlah_tfidf
    == jumlah_hasil
    == len(skor_per_data)
    == total_cluster
):

    print(
        "\nBERHASIL: seluruh "
        f"{jumlah_hasil} data digunakan "
        "dalam evaluasi Silhouette Score."
    )

else:

    raise ValueError(
        "Validasi akhir gagal. "
        "Jumlah data tidak konsisten."
    )


# =============================================================================
# RINGKASAN PIPELINE
# =============================================================================

print("\n" + "=" * 70)
print("SILHOUETTE SCORE SELESAI")
print("=" * 70)

print("""
Alur pengujian penelitian:

1. Excel
   ↓
   1.000 data

2. Preprocessing
   ↓
   1.000 data

3. TF-IDF
   ↓
   1.000 vektor

4. Elbow Method
   ↓
   K optimal

5. K-Means
   ↓
   1.000 data memiliki label cluster

6. Silhouette Score
   ↓
   Evaluasi seluruh 1.000 data
""")

print("=" * 70)
print("FILE YANG DIHASILKAN")
print("=" * 70)

print(
    f"{OUTPUT_GRAFIK:<35}"
    "→ Grafik Silhouette"
)

print(
    f"{OUTPUT_CSV:<35}"
    "→ Ringkasan evaluasi per cluster"
)

print(
    f"{OUTPUT_DETAIL:<35}"
    "→ Silhouette Score setiap data"
)

print(
    f"{OUTPUT_JSON:<35}"
    "→ Hasil evaluasi untuk Laravel"
)

print("=" * 70)
print("SELURUH PIPELINE PENGUJIAN PYTHON SELESAI.")
print("=" * 70)