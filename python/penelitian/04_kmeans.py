# =============================================================================
# SKRIPSI: Analisis dan Pengelompokan Layanan Masyarakat Menggunakan K-Means
# Instansi: DICIPTABINTAR Kota Bandung
# =============================================================================
# FILE    : 04_kmeans.py
# FUNGSI  : Tahap 4 - K-Means Clustering
# TUJUAN  : Mengelompokkan seluruh data layanan berdasarkan kemiripan teks
# CATATAN : Jalankan 01_preprocessing.py, 02_tfidf.py, dan 03_elbow.py
# =============================================================================

import json
import pickle
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt

from sklearn.cluster import KMeans
from sklearn.decomposition import TruncatedSVD


# =============================================================================
# BAGIAN 1 — MEMUAT DATA
# =============================================================================

print("=" * 70)
print("LANGKAH 1: MEMUAT DATA")
print("=" * 70)

# Matriks TF-IDF hasil 02_tfidf.py
with open("tfidf_matrix.pkl", "rb") as f:
    X = pickle.load(f)

# Vectorizer hasil 02_tfidf.py
with open("tfidf_vectorizer.pkl", "rb") as f:
    vectorizer = pickle.load(f)

# Data hasil preprocessing
df = pd.read_csv("data_siap_cluster.csv")

# Samakan nama kolom hasil preprocessing
if "tahap5_stemming" in df.columns:
    df["teks_preprocessed"] = (
        df["tahap5_stemming"]
        .fillna("")
        .astype(str)
    )
elif "teks_preprocessed" in df.columns:
    df["teks_preprocessed"] = (
        df["teks_preprocessed"]
        .fillna("")
        .astype(str)
    )
else:
    raise ValueError(
        "Kolom hasil preprocessing tidak ditemukan."
    )

# Rekomendasi K hasil 03_elbow.py
with open("rekomendasi_k.json", "r") as f:
    rekomendasi = json.load(f)


print(f"Jumlah data       : {len(df)}")
print(f"Jumlah TF-IDF     : {X.shape[0]}")
print(f"Jumlah fitur      : {X.shape[1]}")


# =============================================================================
# BAGIAN 2 — VALIDASI DATA
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 2: VALIDASI DATA")
print("=" * 70)

if len(df) != X.shape[0]:
    raise ValueError(
        f"Jumlah data tidak sama dengan jumlah vektor TF-IDF. "
        f"DataFrame={len(df)}, TF-IDF={X.shape[0]}"
    )

if len(df) != 1000:
    print(
        f"[PERHATIAN] Dataset saat ini berisi {len(df)} data, "
        "bukan 1.000 data."
    )
else:
    print("Validasi berhasil: 1.000 data tersedia.")

print(f"Data preprocessing : {len(df)}")
print(f"Vektor TF-IDF      : {X.shape[0]}")

print("""
Seluruh data akan diproses menggunakan K-Means.

Kolom kategori dari dataset TIDAK digunakan sebagai input
untuk menentukan cluster.

Input K-Means hanya berupa matriks TF-IDF yang berasal
dari teks/deskripsi layanan setelah preprocessing.
""")


# =============================================================================
# BAGIAN 3 — MEMBACA K OPTIMAL HASIL ELBOW
# =============================================================================

print("=" * 70)
print("LANGKAH 3: MEMBACA K OPTIMAL HASIL ELBOW METHOD")
print("=" * 70)

# Mendukung beberapa kemungkinan format rekomendasi_k.json
if isinstance(rekomendasi, dict):

    if "k_optimal" in rekomendasi:
        k_optimal = int(rekomendasi["k_optimal"])

    elif "k" in rekomendasi:
        k_optimal = int(rekomendasi["k"])

    elif len(rekomendasi) == 1:
        k_optimal = int(list(rekomendasi.values())[0])

    else:
        raise ValueError(
            "Format rekomendasi_k.json tidak dikenali. "
            "Pastikan file berasal dari 03_elbow.py terbaru."
        )

elif isinstance(rekomendasi, (int, float)):
    k_optimal = int(rekomendasi)

else:
    raise ValueError(
        "Format rekomendasi_k.json tidak valid."
    )


if k_optimal < 2:
    raise ValueError(
        "K optimal harus minimal 2."
    )

if k_optimal >= len(df):
    raise ValueError(
        "K optimal harus lebih kecil daripada jumlah data."
    )


print(f"K optimal hasil Elbow Method = {k_optimal}")

if k_optimal == 4:
    print("Sesuai hasil penelitian saat ini: K = 4.")
else:
    print(
        f"[PERHATIAN] Nilai K yang terbaca adalah {k_optimal}. "
        "Periksa kembali hasil 03_elbow.py jika seharusnya K=4."
    )


# =============================================================================
# BAGIAN 4 — PENJELASAN K-MEANS
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 4: PROSES K-MEANS")
print("=" * 70)

print(f"""
K-Means akan dijalankan terhadap SELURUH {len(df)} data.

Parameter:
  Jumlah cluster (K) : {k_optimal}
  Random state       : 42
  n_init             : 10
  max_iter           : 300

Alur:
  TF-IDF
     ↓
  Inisialisasi centroid
     ↓
  Hitung jarak data terhadap centroid
     ↓
  Data ditempatkan pada centroid terdekat
     ↓
  Centroid diperbarui
     ↓
  Proses diulang sampai konvergen
     ↓
  Setiap data memperoleh label cluster
""")


# =============================================================================
# BAGIAN 5 — MENJALANKAN K-MEANS
# =============================================================================

print("=" * 70)
print("LANGKAH 5: MENJALANKAN K-MEANS")
print("=" * 70)

model = KMeans(
    n_clusters=k_optimal,
    random_state=42,
    n_init=10,
    max_iter=300,
)

labels = model.fit_predict(X)

# Tambahkan label cluster ke SELURUH data
df["cluster"] = labels


# =============================================================================
# VALIDASI HASIL CLUSTERING
# =============================================================================

if len(labels) != len(df):
    raise ValueError(
        "Jumlah label cluster tidak sama dengan jumlah data."
    )

if df["cluster"].isna().any():
    raise ValueError(
        "Terdapat data yang tidak memperoleh label cluster."
    )


print("\nK-Means berhasil dijalankan.")
print(f"Jumlah data clustering : {len(labels)}")
print(f"Jumlah cluster         : {k_optimal}")
print(f"Jumlah iterasi         : {model.n_iter_}")
print(f"Inertia akhir          : {model.inertia_:.4f}")

print(
    f"\nVALIDASI: {len(labels)} dari {len(df)} data "
    "telah memperoleh label cluster."
)


# =============================================================================
# BAGIAN 6 — DISTRIBUSI CLUSTER
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 6: DISTRIBUSI HASIL CLUSTER")
print("=" * 70)

distribusi_cluster = []

for cluster_id in range(k_optimal):

    jumlah = int(
        (df["cluster"] == cluster_id).sum()
    )

    persentase = (
        jumlah / len(df) * 100
    )

    distribusi_cluster.append({
        "cluster": cluster_id,
        "jumlah_data": jumlah,
        "persentase": round(persentase, 2),
    })

    print(
        f"Cluster {cluster_id}: "
        f"{jumlah} data ({persentase:.2f}%)"
    )


# Pastikan total tetap 1000
total_cluster = sum(
    item["jumlah_data"]
    for item in distribusi_cluster
)

print("-" * 50)
print(f"Total data cluster : {total_cluster}")

if total_cluster != len(df):
    raise ValueError(
        "Total data pada seluruh cluster tidak sama "
        "dengan jumlah dataset."
    )


# =============================================================================
# BAGIAN 7 — KATA DOMINAN SETIAP CLUSTER
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 7: KATA DOMINAN SETIAP CLUSTER")
print("=" * 70)

feature_names = vectorizer.get_feature_names_out()

top_terms_cluster = {}

TOP_N = 10

for cluster_id in range(k_optimal):

    centroid = model.cluster_centers_[cluster_id]

    top_indices = np.argsort(
        centroid
    )[::-1][:TOP_N]

    top_terms = [
        feature_names[index]
        for index in top_indices
    ]

    top_terms_cluster[cluster_id] = top_terms

    print(
        f"\nCluster {cluster_id}: "
        f"{', '.join(top_terms)}"
    )


# =============================================================================
# BAGIAN 8 — CONTOH DATA SETIAP CLUSTER
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 8: CONTOH DATA SETIAP CLUSTER")
print("=" * 70)

# Tentukan kolom teks asli
if "teks" in df.columns:
    kolom_teks = "teks"
elif "deskripsi" in df.columns:
    kolom_teks = "deskripsi"
else:
    kolom_teks = "teks_preprocessed"


for cluster_id in range(k_optimal):

    print(f"\n--- CLUSTER {cluster_id} ---")

    contoh = (
        df[df["cluster"] == cluster_id]
        [kolom_teks]
        .head(10)
        .tolist()
    )

    for nomor, teks in enumerate(
        contoh,
        start=1
    ):
        print(
            f"{nomor:>2}. {teks}"
        )


# =============================================================================
# BAGIAN 9 — DISTRIBUSI KATEGORI LAMA DI SETIAP CLUSTER
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 9: ANALISIS ISI SETIAP CLUSTER")
print("=" * 70)

print("""
Kategori asli hanya digunakan pada tahap ini sebagai informasi
pendukung untuk memahami karakteristik cluster.

Kategori asli TIDAK digunakan oleh K-Means dalam menentukan
keanggotaan cluster.
""")


if "kategori" in df.columns:

    for cluster_id in range(k_optimal):

        print(
            f"\nDistribusi kategori pada Cluster {cluster_id}:"
        )

        subset_cluster = df[
            df["cluster"] == cluster_id
        ]

        distribusi = (
            subset_cluster["kategori"]
            .fillna("Tidak diketahui")
            .value_counts()
            .head(10)
        )

        for kategori, jumlah in distribusi.items():

            persentase = (
                jumlah
                / len(subset_cluster)
                * 100
            )

            print(
                f"  {kategori:<45} "
                f"{jumlah:>4} data "
                f"({persentase:>6.2f}%)"
            )

else:
    print(
        "Kolom kategori tidak tersedia. "
        "Analisis kategori dilewati."
    )


# =============================================================================
# BAGIAN 10 — PENAMAAN CLUSTER BERDASARKAN HASIL INTERPRETASI
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 10: PENAMAAN CLUSTER")
print("=" * 70)

# Nama cluster ditentukan setelah proses K-Means selesai
# berdasarkan:
# 1. kata dominan centroid,
# 2. contoh teks anggota cluster,
# 3. distribusi kategori asli sebagai informasi pendukung.
#
# Kategori asli TIDAK digunakan sebagai input K-Means.

NAMA_CLUSTER = {
    0: "Layanan Administrasi dan Informasi Umum",
    1: "Persyaratan dan Konsultasi KRK/SLF",
    2: "Layanan PBG dan Reklame",
    3: "Layanan Data dan Informasi Pemakaman",
}

# Validasi jumlah nama cluster
if len(NAMA_CLUSTER) != k_optimal:
    raise ValueError(
        "Jumlah nama cluster tidak sesuai dengan K optimal."
    )

# Tambahkan nama cluster ke seluruh data
df["nama_cluster"] = df["cluster"].map(NAMA_CLUSTER)

# Validasi seluruh data memperoleh nama cluster
if df["nama_cluster"].isna().any():
    raise ValueError(
        "Terdapat cluster yang belum memiliki nama."
    )

print("""
Penamaan cluster dilakukan setelah hasil K-Means dianalisis.
Nama cluster tidak memengaruhi proses pembentukan cluster.

Hasil penamaan:
""")

for cluster_id in range(k_optimal):

    jumlah = int(
        (df["cluster"] == cluster_id).sum()
    )

    persentase = (
        jumlah / len(df) * 100
    )

    print(
        f"Cluster {cluster_id}: "
        f"{NAMA_CLUSTER[cluster_id]} "
        f"({jumlah} data / {persentase:.2f}%)"
    )

print(
    f"\nSeluruh {len(df)} data telah memperoleh "
    "label dan nama cluster."
)

# =============================================================================
# BAGIAN 11 — VISUALISASI CLUSTER
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 11: MEMBUAT VISUALISASI CLUSTER")
print("=" * 70)

print(
    "Mereduksi matriks TF-IDF menjadi 2 dimensi "
    "menggunakan TruncatedSVD..."
)

svd = TruncatedSVD(
    n_components=2,
    random_state=42,
)

X_2d = svd.fit_transform(X)


plt.figure(
    figsize=(12, 8)
)

for cluster_id in range(k_optimal):

    mask = labels == cluster_id

    plt.scatter(
        X_2d[mask, 0],
        X_2d[mask, 1],
        label=f"Cluster {cluster_id}",
        alpha=0.65,
        s=35,
    )


plt.title(
    "Hasil Clustering K-Means Data Layanan Masyarakat\n"
    f"DICIPTABINTAR Kota Bandung (K={k_optimal}, n={len(df)})",
    fontsize=13,
    fontweight="bold",
)

plt.xlabel(
    "Komponen 1"
)

plt.ylabel(
    "Komponen 2"
)

plt.legend()

plt.grid(
    True,
    linestyle="--",
    alpha=0.3,
)

plt.tight_layout()

plt.savefig(
    "grafik_cluster.png",
    dpi=150,
    bbox_inches="tight",
)

print(
    "Grafik tersimpan: grafik_cluster.png"
)

plt.show()
plt.close()


# =============================================================================
# BAGIAN 12 — MENYIMPAN HASIL CLUSTERING
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 12: MENYIMPAN HASIL CLUSTERING")
print("=" * 70)

# Simpan seluruh data
df.to_csv(
    "hasil_clustering.csv",
    index=False,
    encoding="utf-8-sig",
)

print(
    f"Tersimpan: hasil_clustering.csv "
    f"({len(df)} baris)"
)


# Simpan model K-Means
with open(
    "kmeans_model.pkl",
    "wb"
) as f:
    pickle.dump(
        model,
        f
    )

print(
    "Tersimpan: kmeans_model.pkl"
)


# Untuk kompatibilitas dengan sistem web
with open(
    "kmeans_models.pkl",
    "wb"
) as f:

    pickle.dump(
        {
            "model": model,
            "k": k_optimal,
            "jumlah_data": len(df),
        },
        f
    )

print(
    "Tersimpan: kmeans_models.pkl"
)


# Simpan informasi cluster
cluster_info = {
    "jumlah_data": int(len(df)),
    "jumlah_cluster": int(k_optimal),
    "inertia": round(
        float(model.inertia_),
        4
    ),
    "jumlah_iterasi": int(
        model.n_iter_
    ),
    "distribusi_cluster": distribusi_cluster,
    "top_terms": {
        str(cluster_id): terms
        for cluster_id, terms
        in top_terms_cluster.items()
    },
}


with open(
    "hasil_kmeans.json",
    "w",
    encoding="utf-8"
) as f:

    json.dump(
        cluster_info,
        f,
        ensure_ascii=False,
        indent=4,
    )

print(
    "Tersimpan: hasil_kmeans.json"
)


# =============================================================================
# BAGIAN 13 — PREVIEW HASIL
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 13: PREVIEW HASIL CLUSTERING")
print("=" * 70)

kolom_preview = []

for kolom in [
    "sumber",
    "tanggal",
    "kategori",
    kolom_teks,
    "teks_preprocessed",
    "cluster",
    "nama_cluster",
]:
    if (
        kolom in df.columns
        and kolom not in kolom_preview
    ):
        kolom_preview.append(kolom)


print(
    df[kolom_preview]
    .head(20)
    .to_string(index=False)
)


# =============================================================================
# BAGIAN 14 — VALIDASI AKHIR
# =============================================================================

print("\n" + "=" * 70)
print("VALIDASI AKHIR")
print("=" * 70)

print(
    f"Data preprocessing : {len(df)}"
)

print(
    f"Vektor TF-IDF      : {X.shape[0]}"
)

print(
    f"Data K-Means       : {len(labels)}"
)

print(
    f"K optimal          : {k_optimal}"
)

print(
    f"Jumlah cluster     : {len(np.unique(labels))}"
)

print(
    f"Total anggota      : {total_cluster}"
)


if (
    len(df)
    == X.shape[0]
    == len(labels)
    == total_cluster
):

    print(
        f"\nBERHASIL: seluruh {len(df)} data "
        "telah diproses menggunakan K-Means."
    )

else:

    raise ValueError(
        "Validasi akhir gagal. "
        "Jumlah data tidak konsisten."
    )


print("\n" + "=" * 70)
print("K-MEANS CLUSTERING SELESAI")
print("=" * 70)

print(f"""
Alur data:

1. Excel              : {len(df)} data
2. Preprocessing      : {len(df)} data
3. TF-IDF             : {X.shape[0]} vektor
4. Elbow Method       : K optimal = {k_optimal}
5. K-Means            : {len(labels)} data
6. Jumlah cluster     : {k_optimal}

Seluruh data telah memperoleh label cluster.

Langkah selanjutnya:
jalankan 05_silhouette.py.
""")