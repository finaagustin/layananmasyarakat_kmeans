# =============================================================================
# SKRIPSI: Analisis dan Pengelompokan Layanan Masyarakat Menggunakan K-Means
# Instansi: DICIPTABINTAR Kota Bandung
# =============================================================================
# FILE    : 03_elbow.py
# FUNGSI  : Tahap 3 - Elbow Method (Menentukan Nilai K Optimal)
# TUJUAN  : Menentukan jumlah cluster optimal dari seluruh 1.000 data
# CATATAN : Jalankan 01_preprocessing.py dan 02_tfidf.py terlebih dahulu
# =============================================================================

import json
import pickle

import numpy as np
import pandas as pd
import matplotlib.pyplot as plt

from sklearn.cluster import KMeans


# =============================================================================
# KONFIGURASI
# =============================================================================

K_MIN = 2
K_MAX = 8

RANDOM_STATE = 42
N_INIT = 10
MAX_ITER = 300


# =============================================================================
# BAGIAN 1 — MEMUAT DATA HASIL TF-IDF
# =============================================================================

print("=" * 70)
print("LANGKAH 1: MEMUAT DATA HASIL TF-IDF")
print("=" * 70)

with open("tfidf_matrix.pkl", "rb") as f:
    X = pickle.load(f)

df = pd.read_csv("data_siap_cluster.csv")

jumlah_data = X.shape[0]
jumlah_fitur = X.shape[1]

print("Matriks TF-IDF berhasil dimuat.")
print(f"Jumlah data  : {jumlah_data}")
print(f"Jumlah fitur : {jumlah_fitur}")


# =============================================================================
# BAGIAN 2 — VALIDASI DATA
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 2: VALIDASI DATA")
print("=" * 70)

if len(df) != jumlah_data:
    raise ValueError(
        "Jumlah baris data_siap_cluster.csv tidak sama "
        "dengan jumlah vektor TF-IDF."
    )

if jumlah_data != 1000:
    print(
        f"[PERHATIAN] Dataset berisi {jumlah_data} data, "
        "bukan 1.000 data."
    )
else:
    print("Validasi berhasil: 1.000 data tersedia.")

print(f"Data CSV     : {len(df)}")
print(f"Vektor TF-IDF: {jumlah_data}")

print("\nSeluruh data akan digunakan dalam Elbow Method.")
print("Kolom kategori TIDAK digunakan untuk memfilter data.")


# =============================================================================
# BAGIAN 3 — PENJELASAN ELBOW METHOD
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 3: ELBOW METHOD")
print("=" * 70)

print("""
Elbow Method digunakan untuk menentukan jumlah cluster (k)
yang sesuai sebelum proses K-Means dilakukan.

Pada penelitian ini Elbow Method diterapkan terhadap SELURUH
data layanan masyarakat yang telah direpresentasikan menggunakan
TF-IDF.

Tahapan:
1. K-Means dijalankan menggunakan beberapa nilai k.
2. Setiap nilai k menghasilkan nilai inertia.
3. Nilai inertia dibandingkan untuk melihat penurunan antar-k.
4. Titik siku digunakan sebagai rekomendasi jumlah cluster.
""")


# =============================================================================
# BAGIAN 4 — MENGHITUNG INERTIA
# =============================================================================

print("=" * 70)
print("LANGKAH 4: MENGHITUNG INERTIA SELURUH 1.000 DATA")
print("=" * 70)

k_values = list(range(K_MIN, K_MAX + 1))
inertia_values = []

print()
print(f"{'K':<8}{'Inertia':<18}{'Penurunan'}")
print("-" * 55)

for k in k_values:

    model = KMeans(
        n_clusters=k,
        random_state=RANDOM_STATE,
        n_init=N_INIT,
        max_iter=MAX_ITER,
    )

    model.fit(X)

    inertia = float(model.inertia_)
    inertia_values.append(inertia)

    if len(inertia_values) == 1:
        keterangan = "Basis"
    else:
        sebelumnya = inertia_values[-2]

        penurunan = (
            (sebelumnya - inertia)
            / sebelumnya
            * 100
        )

        keterangan = (
            f"Turun {penurunan:.2f}% "
            f"dari k={k - 1}"
        )

    print(
        f"{k:<8}"
        f"{inertia:<18.4f}"
        f"{keterangan}"
    )


# =============================================================================
# BAGIAN 5 — MENENTUKAN K OPTIMAL
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 5: MENENTUKAN K OPTIMAL")
print("=" * 70)

# -------------------------------------------------------------------------
# Metode geometris:
# mencari titik yang memiliki jarak tegak lurus terbesar terhadap
# garis yang menghubungkan titik inertia pertama dan terakhir.
#
# Metode ini lebih sesuai dibanding aturan "< 20%" karena tidak
# menggunakan ambang persentase arbitrer.
# -------------------------------------------------------------------------

points = np.column_stack(
    (
        np.array(k_values, dtype=float),
        np.array(inertia_values, dtype=float),
    )
)

titik_awal = points[0]
titik_akhir = points[-1]

garis = titik_akhir - titik_awal

panjang_garis = np.linalg.norm(garis)

if panjang_garis == 0:
    raise ValueError(
        "Tidak dapat menentukan titik Elbow karena "
        "nilai inertia tidak berubah."
    )

jarak = []

for titik in points:

    vector = titik - titik_awal

    distance = abs(
        np.cross(garis, vector)
    ) / panjang_garis

    jarak.append(float(distance))

index_optimal = int(np.argmax(jarak))

k_optimal = int(
    k_values[index_optimal]
)

print("Jarak titik terhadap garis Elbow:")

for k, distance in zip(k_values, jarak):
    tanda = " <-- titik siku" if k == k_optimal else ""

    print(
        f"k={k}: "
        f"{distance:.4f}"
        f"{tanda}"
    )

print()
print(f"K optimal yang direkomendasikan = {k_optimal}")


# =============================================================================
# BAGIAN 6 — MEMBUAT GRAFIK ELBOW
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 6: MEMBUAT GRAFIK ELBOW")
print("=" * 70)

plt.figure(figsize=(9, 6))

plt.plot(
    k_values,
    inertia_values,
    marker="o",
    linewidth=2,
    label="Inertia",
)

# Titik optimal
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
    "Data Layanan Masyarakat DICIPTABINTAR Kota Bandung",
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
    "grafik_elbow.png",
    dpi=150,
    bbox_inches="tight",
)

print("Grafik tersimpan: grafik_elbow.png")

plt.show()
plt.close()


# =============================================================================
# BAGIAN 7 — MENYIMPAN HASIL ELBOW
# =============================================================================

print("\n" + "=" * 70)
print("LANGKAH 7: MENYIMPAN HASIL ELBOW METHOD")
print("=" * 70)

rows = []

for k, inertia, distance in zip(
    k_values,
    inertia_values,
    jarak,
):

    rows.append({
        "jumlah_data": jumlah_data,
        "jumlah_fitur": jumlah_fitur,
        "k": k,
        "inertia": round(inertia, 4),
        "jarak_elbow": round(distance, 4),
        "k_optimal": k_optimal,
        "adalah_optimal": (
            "YA"
            if k == k_optimal
            else ""
        ),
    })

df_elbow = pd.DataFrame(rows)

df_elbow.to_csv(
    "hasil_elbow.csv",
    index=False,
    encoding="utf-8-sig",
)

print("Tersimpan: hasil_elbow.csv")

print()
print(df_elbow.to_string(index=False))


# =============================================================================
# BAGIAN 8 — MENYIMPAN REKOMENDASI K
# =============================================================================

hasil_json = {
    "jumlah_data": int(jumlah_data),
    "jumlah_fitur": int(jumlah_fitur),
    "k_min": K_MIN,
    "k_max": K_MAX,
    "k_optimal": int(k_optimal),
    "metode": "Elbow Method",
}

with open(
    "rekomendasi_k.json",
    "w",
    encoding="utf-8",
) as f:

    json.dump(
        hasil_json,
        f,
        indent=4,
        ensure_ascii=False,
    )

print()
print("Tersimpan: rekomendasi_k.json")


# =============================================================================
# BAGIAN 9 — VALIDASI AKHIR
# =============================================================================

print("\n" + "=" * 70)
print("VALIDASI AKHIR")
print("=" * 70)

print(f"Data preprocessing : {len(df)}")
print(f"Vektor TF-IDF      : {X.shape[0]}")
print(f"Data Elbow         : {jumlah_data}")
print(f"Rentang K          : {K_MIN} - {K_MAX}")
print(f"K optimal          : {k_optimal}")

if len(df) == X.shape[0] == jumlah_data:
    print()
    print(
        f"BERHASIL: seluruh {jumlah_data} data "
        "digunakan dalam Elbow Method."
    )
else:
    raise ValueError(
        "Jumlah data tidak konsisten."
    )


print("\n" + "=" * 70)
print("ELBOW METHOD SELESAI")
print("=" * 70)

print("""
Alur data saat ini:

1. Excel                 : 1.000 data
2. Preprocessing         : 1.000 data
3. TF-IDF                : 1.000 vektor
4. Elbow Method          : 1.000 data
5. K optimal             : ditentukan dari seluruh data

Langkah selanjutnya:
jalankan 04_kmeans.py setelah hasil Elbow diperiksa.
""")