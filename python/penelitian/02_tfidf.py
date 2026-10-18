# =============================================================================
# SKRIPSI: Analisis dan Pengelompokan Layanan Masyarakat Menggunakan K-Means
# Instansi: DICITABINTAR Kota Bandung
# =============================================================================
# FILE    : 02_tfidf.py
# FUNGSI  : Tahap 2 - TF-IDF (Term Frequency - Inverse Document Frequency)
# TUJUAN  : Mengubah teks hasil preprocessing menjadi vektor angka
#           agar bisa diproses oleh algoritma K-Means
# CATATAN : Jalankan 01_preprocessing.py terlebih dahulu
# =============================================================================

import math
import pickle
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer

# =============================================================================
# BAGIAN 1 — LOAD DATA HASIL PREPROCESSING
# =============================================================================

print("=" * 60)
print("LANGKAH 1: MEMUAT DATA HASIL PREPROCESSING")
print("=" * 60)

df = pd.read_csv("hasil_preprocessing.csv")

# Kolom teks yang dipakai adalah hasil akhir preprocessing (tahap5_stemming)
df["teks_preprocessed"] = df["tahap5_stemming"]

# Pertahankan seluruh data hasil preprocessing
df["teks_preprocessed"] = (
    df["teks_preprocessed"]
    .fillna("")
    .astype(str)
    .str.strip()
)

jumlah_data = len(df)
jumlah_kosong = (df["teks_preprocessed"] == "").sum()

print(f"Total data          : {jumlah_data} baris")
print(f"Teks tidak kosong   : {jumlah_data - jumlah_kosong} baris")
print(f"Teks kosong         : {jumlah_kosong} baris")
print(f"Data dipertahankan  : {jumlah_data} baris")

print("\nContoh teks preprocessed yang akan diubah ke TF-IDF:")
for _, row in df.head(5).iterrows():
    print(f"  '{row['teks_preprocessed']}'  ←  (asli: '{row['teks']}')")


# =============================================================================
# BAGIAN 2 — PENJELASAN KONSEP TF-IDF
# =============================================================================

print("\n" + "=" * 60)
print("LANGKAH 2: PENJELASAN KONSEP TF-IDF")
print("=" * 60)

print("""
TF-IDF = Term Frequency × Inverse Document Frequency

Tujuannya: memberi bobot tinggi pada kata yang PENTING di dokumen tertentu
           tapi JARANG muncul di dokumen lain.

Contoh pada data layanan kita:
  - Kata "krk"  → muncul di banyak dokumen KRK, tapi tidak di Pemakaman
                   → bobot SEDANG (penting untuk KRK)
  - Kata "syarat" → muncul hampir di semua kategori (KRK, PBG, IRK, SLF)
                   → bobot RENDAH (tidak membedakan cluster)
  - Kata "reklame" → hanya muncul di beberapa dokumen PBG
                   → bobot TINGGI (sangat membedakan cluster PBG Reklame)

Rumus:
  TF(t, d)  = jumlah kemunculan kata t di dokumen d
               ÷ jumlah seluruh kata di dokumen d

  IDF(t)    = log( jumlah seluruh dokumen
               ÷ jumlah dokumen yang mengandung kata t ) + 1

  TF-IDF(t, d) = TF(t, d) × IDF(t)
""")


# =============================================================================
# BAGIAN 3 — PERHITUNGAN TF-IDF SECARA MANUAL (5 DOKUMEN SAMPEL)
# =============================================================================

print("=" * 60)
print("LANGKAH 3: PERHITUNGAN TF-IDF MANUAL (5 DOKUMEN SAMPEL KRK)")
print("=" * 60)
print("(Ini untuk membuktikan perhitungan — sama dengan hasil sklearn)\n")

# Ambil 5 dokumen yang beragam dari kategori KRK sebagai sampel manual
sampel_manual = [
    "syarat krk",
    "cek revisi krk",
    "berkas krk",
    "ambil krk",
    "syarat krk rumah tinggal",
]

print("Dokumen sampel:")
for i, dok in enumerate(sampel_manual):
    print(f"  D{i+1}: '{dok}'")

# --- Hitung TF ---
print("\n--- PERHITUNGAN TF (Term Frequency) ---")
print("Rumus: TF(kata, dokumen) = jumlah kata muncul ÷ total kata dalam dokumen\n")

tf_semua = []
for i, dok in enumerate(sampel_manual):
    tokens = dok.split()
    total_kata = len(tokens)
    tf_dok = {}
    for token in tokens:
        tf_dok[token] = tf_dok.get(token, 0) + 1
    # Normalisasi: bagi dengan total kata
    for kata in tf_dok:
        tf_dok[kata] = round(tf_dok[kata] / total_kata, 4)
    tf_semua.append(tf_dok)
    print(f"  D{i+1} '{dok}'")
    print(f"       Total kata: {total_kata}")
    for kata, nilai in tf_dok.items():
        print(f"       TF('{kata}') = {nilai}")
    print()

# --- Hitung IDF ---
print("\n--- PERHITUNGAN IDF (Inverse Document Frequency) ---")
print("Rumus: IDF(kata) = log(N ÷ df(kata)) + 1")
print("       N   = total dokumen")
print("       df  = jumlah dokumen yang mengandung kata tersebut\n")

N = len(sampel_manual)
# Kumpulkan semua kata unik
semua_kata = set()
for dok in sampel_manual:
    semua_kata.update(dok.split())

idf_semua = {}
for kata in sorted(semua_kata):
    df_kata = sum(1 for dok in sampel_manual if kata in dok.split())
    idf = math.log(N / df_kata) + 1
    idf_semua[kata] = round(idf, 4)
    print(f"  IDF('{kata}') = log({N} ÷ {df_kata}) + 1 = {idf_semua[kata]}")

# --- Hitung TF-IDF ---
print("\n\n--- PERHITUNGAN TF-IDF = TF × IDF ---\n")

hasil_manual = []
for i, (dok, tf_dok) in enumerate(zip(sampel_manual, tf_semua)):
    print(f"  D{i+1}: '{dok}'")
    tfidf_dok = {}
    for kata in tf_dok:
        tfidf = round(tf_dok[kata] * idf_semua[kata], 4)
        tfidf_dok[kata] = tfidf
        print(f"       TF-IDF('{kata}') = {tf_dok[kata]} × {idf_semua[kata]} = {tfidf}")
    hasil_manual.append(tfidf_dok)
    print()

# Tampilkan sebagai tabel matriks
print("--- MATRIKS TF-IDF (HASIL MANUAL) ---")
kata_urut = sorted(semua_kata)
header = f"{'Dokumen':<30}" + "".join(f"{k:<12}" for k in kata_urut)
print(header)
print("-" * len(header))
for i, (dok, tfidf_dok) in enumerate(zip(sampel_manual, hasil_manual)):
    baris = f"D{i+1}: {dok:<26}" + "".join(
        f"{tfidf_dok.get(k, 0.0):<12}" for k in kata_urut
    )
    print(baris)


# =============================================================================
# BAGIAN 4 — TF-IDF MENGGUNAKAN SKLEARN (SELURUH DATA)
# =============================================================================

print("\n" + "=" * 60)
print("LANGKAH 4: TF-IDF OTOMATIS DENGAN SKLEARN (SELURUH DATA)")
print("=" * 60)
print("""
Sklearn menggunakan rumus yang sedikit berbeda (dengan normalisasi L2),
tapi hasilnya konsisten dengan konsep yang sama:
  - Kata yang jarang → bobot tinggi
  - Kata yang ada di mana-mana → bobot rendah

Parameter yang digunakan:
  - max_df=0.90  : abaikan kata yang muncul di >90% dokumen (terlalu umum)
  - min_df=2     : abaikan kata yang muncul di <2 dokumen (terlalu jarang)
  - ngram_range=(1,2) : pakai unigram DAN bigram
                        contoh: "syarat" dan "syarat krk" keduanya jadi fitur
  - sublinear_tf=True : terapkan log pada TF agar tidak terlalu condong
                        ke dokumen yang sangat panjang
""")

vectorizer = TfidfVectorizer(
    max_df=0.90,
    min_df=2,
    ngram_range=(1, 2),
    sublinear_tf=True
)

X = vectorizer.fit_transform(df["teks_preprocessed"])

# Dokumen dengan teks kosong tetap dipertahankan.
# TfidfVectorizer merepresentasikannya sebagai zero vector.
if X.shape[0] != len(df):
    raise ValueError(
        f"Jumlah baris TF-IDF ({X.shape[0]}) "
        f"tidak sama dengan jumlah data ({len(df)})."
    )

print(f"Jumlah dokumen (baris)   : {X.shape[0]}")
print(f"Jumlah fitur/term unik   : {X.shape[1]}")
print(f"Ukuran matriks           : {X.shape[0]} × {X.shape[1]}")
print(f"Kepadatan matriks        : {X.nnz / (X.shape[0] * X.shape[1]) * 100:.2f}%")
print("(Matriks sangat jarang/sparse karena setiap dok hanya punya sedikit kata)")

# Tampilkan 20 kata dengan bobot IDF tertinggi (paling diskriminatif)
print("\n--- 20 KATA DENGAN BOBOT IDF TERTINGGI (paling membedakan cluster) ---")
fitur_names  = vectorizer.get_feature_names_out()
idf_values   = vectorizer.idf_
idf_df       = pd.DataFrame({
    "kata"  : fitur_names,
    "idf"   : idf_values
}).sort_values("idf", ascending=False)

print(idf_df.head(20).to_string(index=False))

# Tampilkan 20 kata dengan bobot IDF terendah (paling umum/tidak diskriminatif)
print("\n--- 20 KATA DENGAN BOBOT IDF TERENDAH (paling umum, kurang berguna) ---")
print(idf_df.tail(20).to_string(index=False))

# Tampilkan contoh vektor untuk 3 dokumen
print("\n--- CONTOH VEKTOR TF-IDF UNTUK 3 DOKUMEN ---")
for idx in range(3):
    teks_dok  = df.iloc[idx]["teks_preprocessed"]
    teks_asli = df.iloc[idx]["teks"]
    vektor    = X[idx]

    # Ambil kata dengan nilai TF-IDF > 0
    kata_aktif = [
        (fitur_names[j], round(vektor[0, j], 4))
        for j in vektor.nonzero()[1]
    ]
    kata_aktif.sort(key=lambda x: x[1], reverse=True)

    print(f"\n  Dokumen {idx+1}")
    print(f"  Teks asli     : '{teks_asli}'")
    print(f"  Teks processed: '{teks_dok}'")
    print(f"  Vektor TF-IDF : {kata_aktif}")


# =============================================================================
# BAGIAN 5 — VERIFIKASI: BANDINGKAN MANUAL vs SKLEARN
# =============================================================================

print("\n" + "=" * 60)
print("LANGKAH 5: VERIFIKASI — MANUAL vs SKLEARN")
print("=" * 60)

# Jalankan sklearn pada 5 sampel manual yang sama
vec_manual = TfidfVectorizer(
    norm=None,          # tanpa normalisasi L2, agar sebanding dengan manual
    use_idf=True,
    smooth_idf=False,   # tanpa smoothing, agar rumus persis sama dengan manual
    sublinear_tf=False
)
X_manual = vec_manual.fit_transform(sampel_manual)
fitur_manual = vec_manual.get_feature_names_out()

print("Hasil TF-IDF sklearn (5 dokumen sampel, tanpa normalisasi):\n")
header_sk = f"{'Dokumen':<30}" + "".join(f"{k:<14}" for k in sorted(fitur_manual))
print(header_sk)
print("-" * len(header_sk))
for i in range(len(sampel_manual)):
    baris = f"D{i+1}: {sampel_manual[i]:<26}"
    for k in sorted(fitur_manual):
        if k in fitur_manual:
            idx_k  = list(fitur_manual).index(k)
            nilai  = round(X_manual[i, idx_k], 4)
        else:
            nilai  = 0.0
        baris += f"{nilai:<14}"
    print(baris)

print("""
Catatan: Nilai sklearn mungkin sedikit berbeda dari manual karena:
  1. Sklearn menggunakan log(1 + N/df) bukan log(N/df) + 1
  2. Sklearn menerapkan normalisasi L2 secara default
  Tapi URUTAN kata berbobot tinggi/rendah tetap KONSISTEN.
""")


# =============================================================================
# BAGIAN 6 — SIMPAN HASIL TF-IDF
# =============================================================================

print("=" * 60)
print("LANGKAH 6: MENYIMPAN HASIL TF-IDF")
print("=" * 60)

import numpy as np

# --- 6a. Simpan matriks dan vectorizer ke pickle ---
# Format pickle dipakai oleh script Python berikutnya (03_elbow.py, 04_kmeans.py)
# File ini tidak bisa dibuka di Excel — hanya untuk Python
with open("tfidf_matrix.pkl", "wb") as f:
    pickle.dump(X, f)
print("Tersimpan: tfidf_matrix.pkl  (dipakai script Python berikutnya)")

with open("tfidf_vectorizer.pkl", "wb") as f:
    pickle.dump(vectorizer, f)
print("Tersimpan: tfidf_vectorizer.pkl  (dipakai sistem web untuk data baru)")

# --- 6b. Simpan DataFrame lengkap untuk jembatan antar script ---
df.to_csv("data_siap_cluster.csv", index=False, encoding="utf-8-sig")
print("Tersimpan: data_siap_cluster.csv  (jembatan antar script)")

# --- 6c. Simpan matriks LENGKAP seluruh data ke CSV ---
jumlah_baris = len(df)
nama_lengkap = f"tfidf_lengkap_{jumlah_baris}baris.csv"

print(f"\nMenyimpan matriks lengkap {jumlah_baris} baris ke CSV...")
X_arr = X.toarray()
df_lengkap = pd.DataFrame(X_arr, columns=fitur_names)
df_lengkap.insert(0, "kategori", df["kategori"].values)
df_lengkap.insert(0, "teks_preprocessed", df["teks_preprocessed"].values)
df_lengkap.insert(0, "teks_asli", df["teks"].values)
df_lengkap.to_csv(nama_lengkap, index=False, encoding="utf-8-sig")

print(f"Tersimpan: {nama_lengkap}")
print(f"           → {df_lengkap.shape[0]} baris × {df_lengkap.shape[1]} kolom")
print("           → Teks kosong tetap dipertahankan sebagai zero vector.")

# --- 6d. Simpan ringkasan seluruh data ---
nama_ringkasan = f"tfidf_ringkasan_{jumlah_baris}baris.csv"

print("\nMenyimpan ringkasan bobot tertinggi per dokumen...")
ringkasan = []

for i in range(len(df)):
    bobot = X_arr[i]
    idx_nonzero = np.where(bobot > 0)[0]
    idx_sort = idx_nonzero[np.argsort(bobot[idx_nonzero])[::-1]]
    top5 = [
        (fitur_names[j], round(float(bobot[j]), 4))
        for j in idx_sort[:5]
    ]

    baris = {
        "no": i + 1,
        "teks_asli": df.iloc[i]["teks"],
        "teks_preprocessed": df.iloc[i]["teks_preprocessed"],
        "kategori": df.iloc[i]["kategori"],
    }

    for urut, (kata, nilai) in enumerate(top5, 1):
        baris[f"kata_bobot_{urut}"] = kata
        baris[f"nilai_{urut}"] = nilai

    # Jika dokumen menjadi zero vector, record tetap ada.
    for urut in range(len(top5) + 1, 6):
        baris[f"kata_bobot_{urut}"] = ""
        baris[f"nilai_{urut}"] = 0.0

    ringkasan.append(baris)

df_ringkasan = pd.DataFrame(ringkasan)
df_ringkasan.to_csv(
    nama_ringkasan,
    index=False,
    encoding="utf-8-sig"
)

print(f"Tersimpan: {nama_ringkasan}")
print(f"           → {len(df_ringkasan)} baris, tiap baris = 1 dokumen")

# Validasi akhir: jumlah data tidak boleh berkurang.
if X.shape[0] != jumlah_data or len(df_ringkasan) != jumlah_data:
    raise ValueError(
        f"Jumlah data berubah: dataset={jumlah_data}, "
        f"TF-IDF={X.shape[0]}, ringkasan={len(df_ringkasan)}"
    )

print("\nPreview 10 baris pertama:")
print("-" * 80)
kolom_preview = [
    "no",
    "teks_asli",
    "teks_preprocessed",
    "kata_bobot_1",
    "nilai_1",
    "kata_bobot_2",
    "nilai_2",
]
print(df_ringkasan[kolom_preview].head(10).to_string(index=False))

print("\n" + "=" * 60)
print("RINGKASAN FILE YANG DIHASILKAN:")
print("=" * 60)
print("  tfidf_matrix.pkl          → untuk script Python berikutnya")
print("  tfidf_vectorizer.pkl      → untuk script Python berikutnya")
print("  data_siap_cluster.csv     → untuk script Python berikutnya")
print(f"  {nama_lengkap} → matriks TF-IDF seluruh data")
print(f"  {nama_ringkasan} → ringkasan bobot seluruh data")
print()
print(f"VALIDASI AKHIR: {jumlah_data} DATA → {X.shape[0]} VEKTOR TF-IDF")
print("=" * 60)
print("TF-IDF SELESAI.")
print("Langkah selanjutnya: jalankan 03_elbow.py")
print("=" * 60)
