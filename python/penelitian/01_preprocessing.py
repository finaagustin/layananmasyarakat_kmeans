# =============================================================================
# SKRIPSI: Analisis dan Pengelompokan Layanan Masyarakat Menggunakan K-Means
# Instansi: DICITABINTAR Kota Bandung
# =============================================================================
# FILE    : 01_preprocessing.py
# FUNGSI  : Tahap 1 - Text Preprocessing
# LANGKAH : Case Folding → Tokenizing → Stopword Removal → Stemming
# =============================================================================

import re
import pandas as pd
import openpyxl
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
from Sastrawi.StopWordRemover.StopWordRemoverFactory import StopWordRemoverFactory

# =============================================================================
# BAGIAN 1 — LOAD DATA DARI 4 FILE EXCEL
# =============================================================================
# Setiap file memiliki struktur kolom yang berbeda.
# Kita ambil 3 kolom saja dari masing-masing file:
#   - tanggal  : untuk keperluan temporal split (data latih vs data uji)
#   - teks     : isi permohonan/pengaduan → INPUT utama K-Means
#   - kategori : label layanan → GROUND TRUTH untuk evaluasi
# =============================================================================

print("=" * 60)
print("LANGKAH 1: MEMUAT DATA DARI 4 FILE EXCEL")
print("=" * 60)

rows = []

# --- File 1: Data Gabungan Maret 2026 ---
# Kolom F (index 5) = PERMOHONAN/PENGADUAN  ← teks
# Kolom E (index 4) = KATEGORI              ← label
print("\n[1/4] Membaca: Data_Gabungan_Maret_2026.xlsx ...")
wb = openpyxl.load_workbook(
    "Data_Gabungan_Maret_2026.xlsx",
    data_only=True
)
ws = wb["Maret 2026"]
for row in ws.iter_rows(min_row=2, values_only=True):
    teks      = row[5]   # kolom F: PERMOHONAN/PENGADUAN
    kategori  = row[4]   # kolom E: KATEGORI
    tanggal   = row[0]   # kolom A: TANGGAL
    if teks:
        rows.append({
            "sumber"   : "Maret2026",
            "tanggal"  : tanggal,
            "teks"     : str(teks).strip(),
            "kategori" : str(kategori).strip() if kategori else "Tidak Diketahui"
        })

# --- File 2: REKAP April 2026 ---
# Kolom G (index 6) = DESKRIPSI INFORMASI  ← teks
# Kolom F (index 5) = KLASIFIKASI          ← label
print("[2/4] Membaca: REKAP_FO_LOKET_BULAN_April_2026.xlsx ...")
wb = openpyxl.load_workbook(
    "REKAP_FO_LOKET_BULAN_April_2026.xlsx",
    data_only=True
)
ws = wb["Sheet7"]
for row in ws.iter_rows(min_row=7, values_only=True):
    teks      = row[6]   # kolom G: DESKRIPSI INFORMASI
    kategori  = row[5]   # kolom F: KLASIFIKASI
    tanggal   = row[0]   # kolom A: TANGGAL
    if teks:
        rows.append({
            "sumber"   : "April2026",
            "tanggal"  : tanggal,
            "teks"     : str(teks).strip(),
            "kategori" : str(kategori).strip() if kategori else "Tidak Diketahui"
        })

# --- File 3: REKAP Mei 2026 ---
# Struktur kolom identik dengan File 2
print("[3/4] Membaca: REKAP_FO_LOKET_BULAN_MEI_2026.xlsx ...")
wb = openpyxl.load_workbook(
    "REKAP_FO_LOKET_BULAN_MEI_2026.xlsx",
    data_only=True
)
ws = wb["MEI 2026 - LAPORAN DATA PELAYAN"]
for row in ws.iter_rows(min_row=7, values_only=True):
    teks      = row[6]   # kolom G: DESKRIPSI INFORMASI
    kategori  = row[5]   # kolom F: KLASIFIKASI
    tanggal   = row[0]   # kolom A: TANGGAL
    if teks:
        rows.append({
            "sumber"   : "Mei2026",
            "tanggal"  : tanggal,
            "teks"     : str(teks).strip(),
            "kategori" : str(kategori).strip() if kategori else "Tidak Diketahui"
        })

# --- File 4: Pelayanan Informasi Pemakaman ---
# Kolom I (index 8)  = Ringkasan Pelayanan  ← teks
# Kolom M (index 12) = Klasifikasi Layanan  ← label
# Kolom H (index 7)  = Jenis Layanan        ← filter (ambil "Informasi" saja)
print("[4/4] Membaca: Pelayanan_Informasi_Pemakaman_-_Responses.xlsx ...")
wb = openpyxl.load_workbook(
    "Pelayanan_Informasi_Pemakaman_-_Responses.xlsx",
    data_only=True
)
ws = wb["Form Responses 1"]
for row in ws.iter_rows(min_row=2, values_only=True):
    jenis     = row[7]   # kolom H: Jenis Layanan
    teks      = row[8]   # kolom I: Ringkasan Pelayanan
    kategori  = row[12]  # kolom M: Klasifikasi Layanan
    tanggal   = row[0]   # kolom A: Timestamp
    # Hanya ambil yang bertipe "Informasi" (bukan "Aduan")
    if teks and str(jenis).strip() == "Informasi":
        rows.append({
            "sumber"   : "Pemakaman",
            "tanggal"  : tanggal,
            "teks"     : str(teks).strip(),
            "kategori" : str(kategori).strip() if kategori else "Pemakaman Umum"
        })

# Buat DataFrame gabungan
df = pd.DataFrame(rows)
print(f"\nTotal data terkumpul: {len(df)} baris")
print("\nJumlah data per sumber:")
print(df["sumber"].value_counts().to_string())
print("\nJumlah data per kategori (top 15):")
print(df["kategori"].value_counts().head(15).to_string())


# =============================================================================
# BAGIAN 2 — PERSIAPAN TOOLS PREPROCESSING
# =============================================================================

print("\n" + "=" * 60)
print("LANGKAH 2: PERSIAPAN TOOLS PREPROCESSING")
print("=" * 60)

# --- Stopword Bahasa Indonesia (dari library Sastrawi) ---
stop_factory = StopWordRemoverFactory()
stopwords_id  = set(stop_factory.get_stop_words())

# --- Tambahan stopword khusus domain DICITABINTAR ---
# Kata-kata ini muncul di hampir semua dokumen sehingga tidak membedakan cluster
stopwords_domain = {
    "informasi", "pemohon", "mengenai", "terkait", "perihal",
    "dijelaskan", "diinformasikan", "dilakukan", "dicek", "dilihat",
    "secara", "informatif", "jelas", "kepada", "poin", "silakan",
    "agar", "bahwa", "telah", "sudah", "akan", "dapat", "bisa",
    "mohon", "tolong", "minta", "ingin", "mau"
}
stopwords_gabungan = stopwords_id | stopwords_domain

print(f"Jumlah stopword Bahasa Indonesia : {len(stopwords_id)}")
print(f"Jumlah stopword domain tambahan  : {len(stopwords_domain)}")
print(f"Total stopword gabungan          : {len(stopwords_gabungan)}")

# --- Inisialisasi Stemmer Bahasa Indonesia (Sastrawi) ---
stemmer_factory = StemmerFactory()
stemmer         = stemmer_factory.create_stemmer()
print("\nStemmer Sastrawi berhasil diinisialisasi.")


# =============================================================================
# BAGIAN 3 — FUNGSI PREPROCESSING (4 TAHAP)
# =============================================================================

print("\n" + "=" * 60)
print("LANGKAH 3: DEFINISI FUNGSI PREPROCESSING")
print("=" * 60)

def step1_case_folding(teks: str) -> str:
    """
    Tahap 1: Case Folding
    Mengubah seluruh huruf menjadi huruf kecil (lowercase).
    Contoh: "Cek Revisi KRK" → "cek revisi krk"
    """
    return teks.lower()


def step2_cleaning(teks: str) -> str:
    """
    Tahap 2: Cleaning
    Menghapus karakter yang tidak relevan:
    - Angka (nomor berkas, nomor HP, dsb)
    - Tanda baca dan simbol
    - Spasi berlebih
    Contoh: "cek revisi krk 10.258.7898" → "cek revisi krk"
    """
    teks = re.sub(r"\d+", " ", teks)           # hapus angka
    teks = re.sub(r"[^a-z\s]", " ", teks)      # hapus simbol & tanda baca
    teks = re.sub(r"\s+", " ", teks).strip()    # rapikan spasi
    return teks


def step3_tokenizing(teks: str) -> list:
    """
    Tahap 3: Tokenizing
    Memecah kalimat menjadi daftar kata (token).
    Kata dengan panjang ≤ 2 karakter langsung dibuang (tidak informatif).
    Contoh: "cek revisi krk" → ["cek", "revisi", "krk"]
    """
    tokens = teks.split()
    tokens = [t for t in tokens if len(t) > 2]
    return tokens


def step4_stopword_removal(tokens: list) -> list:
    """
    Tahap 4: Stopword Removal
    Menghapus kata-kata umum yang tidak memberi makna pada clustering.
    Contoh: ["informasi", "mengenai", "persyaratan", "krk"]
            → ["persyaratan", "krk"]
    """
    return [t for t in tokens if t not in stopwords_gabungan]


def step5_stemming(tokens: list) -> list:
    """
    Tahap 5: Stemming (Bahasa Indonesia - Sastrawi)
    Mengubah kata ke bentuk dasarnya.
    Contoh: ["persyaratan", "krk"] → ["syarat", "krk"]
    """
    return [stemmer.stem(t) for t in tokens]


def full_preprocessing(teks: str) -> str:
    """
    Pipeline lengkap: menjalankan seluruh 5 tahap berurutan.
    Output berupa string (kata-kata dipisahkan spasi),
    siap dimasukkan ke TF-IDF.
    """
    teks   = step1_case_folding(teks)
    teks   = step2_cleaning(teks)
    tokens = step3_tokenizing(teks)
    tokens = step4_stopword_removal(tokens)
    tokens = step5_stemming(tokens)
    return " ".join(tokens)


print("Semua fungsi preprocessing telah didefinisikan.")


# =============================================================================
# BAGIAN 4 — DEMONSTRASI PREPROCESSING STEP-BY-STEP
# =============================================================================

print("\n" + "=" * 60)
print("LANGKAH 4: DEMONSTRASI STEP-BY-STEP (5 CONTOH DATA RIIL)")
print("=" * 60)

# Ambil 5 contoh data riil dari dataset
contoh_data = [
    "Cek Revisi KRK 10.258.7898",
    "informasi mengenai persyaratan KRK untuk Rumah Tinggal daerah Dago",
    "persyaratan pbg reklame",
    "Konsultasi persyaratan KRK untuk Ruko daerah Antapani",
    "MENANYAKAN BAHWA MAKAM SUDAH GRATIS"
]

for i, teks_asli in enumerate(contoh_data, 1):
    print(f"\n--- Contoh {i} ---")
    print(f"  Teks Asli          : {teks_asli}")

    hasil_1 = step1_case_folding(teks_asli)
    print(f"  Setelah Case Fold  : {hasil_1}")

    hasil_2 = step2_cleaning(hasil_1)
    print(f"  Setelah Cleaning   : {hasil_2}")

    hasil_3 = step3_tokenizing(hasil_2)
    print(f"  Setelah Tokenizing : {hasil_3}")

    hasil_4 = step4_stopword_removal(hasil_3)
    print(f"  Setelah Stopword   : {hasil_4}")

    hasil_5 = step5_stemming(hasil_4)
    print(f"  Setelah Stemming   : {hasil_5}")

    print(f"  HASIL AKHIR        : '{' '.join(hasil_5)}'")


# =============================================================================
# BAGIAN 5 — TERAPKAN PREPROCESSING KE SELURUH 1.001 DATA
# =============================================================================

print("\n" + "=" * 60)
print("LANGKAH 5: MENERAPKAN PREPROCESSING KE SELURUH DATA")
print("=" * 60)
print("Proses stemming memerlukan waktu beberapa menit, harap tunggu...")

# Terapkan setiap tahap secara terpisah (agar hasil tiap tahap bisa dicek)
df["tahap1_case_folding"]    = df["teks"].apply(step1_case_folding)
df["tahap2_cleaning"]        = df["tahap1_case_folding"].apply(step2_cleaning)

# Tokenizing disimpan sementara sebagai list, lalu jadi string untuk kolom output
df["tahap3_tokenizing"]      = df["tahap2_cleaning"].apply(
                                   lambda x: " ".join(step3_tokenizing(x))
                               )
df["tahap4_stopword"]        = df["tahap3_tokenizing"].apply(
                                   lambda x: " ".join(
                                       step4_stopword_removal(x.split())
                                   )
                               )
df["tahap5_stemming"]        = df["tahap4_stopword"].apply(
                                   lambda x: " ".join(
                                       step5_stemming(x.split())
                                   )
                               )

# Kolom hasil akhir yang akan dipakai di file berikutnya (TF-IDF)
df["teks_preprocessed"]      = df["tahap5_stemming"]

print(f"\nPreprocessing selesai untuk {len(df)} data.")

# Statistik hasil preprocessing
panjang_kata = df["teks_preprocessed"].str.split().apply(len)
print(f"\nStatistik panjang teks setelah preprocessing:")
print(f"  Rata-rata : {panjang_kata.mean():.1f} kata")
print(f"  Minimum   : {panjang_kata.min()} kata")
print(f"  Maksimum  : {panjang_kata.max()} kata")

# Cek data yang hasil preprocessingnya kosong (perlu perhatian)
kosong = df[df["teks_preprocessed"].str.strip() == ""]
if len(kosong) > 0:
    print(f"\n[PERHATIAN] {len(kosong)} data menghasilkan teks kosong setelah preprocessing:")
    print(kosong[["sumber", "teks"]].to_string())
else:
    print("\n[OK] Tidak ada data yang menghasilkan teks kosong.")


# =============================================================================
# BAGIAN 6 — SIMPAN HASIL KE FILE CSV
# =============================================================================

print("\n" + "=" * 60)
print("LANGKAH 6: MENYIMPAN HASIL PREPROCESSING")
print("=" * 60)

# Simpan seluruh kolom (termasuk tiap tahap) untuk keperluan dokumentasi skripsi
output_path = "hasil_preprocessing.csv"
kolom_disimpan = [
    "sumber",
    "tanggal",
    "kategori",
    "teks",                  # teks asli
    "tahap1_case_folding",   # setelah case folding
    "tahap2_cleaning",       # setelah cleaning
    "tahap3_tokenizing",     # setelah tokenizing
    "tahap4_stopword",       # setelah stopword removal
    "tahap5_stemming",       # setelah stemming = teks_preprocessed
]
df[kolom_disimpan].to_csv(output_path, index=False, encoding="utf-8-sig")

print(f"\nFile tersimpan: {output_path}")
print(f"Total baris   : {len(df)}")
print(f"Total kolom   : {len(kolom_disimpan)}")

# Preview 10 baris pertama (kolom paling penting)
print("\nPreview 10 data pertama (teks asli vs hasil akhir):")
print("-" * 70)
for _, row in df.head(10).iterrows():
    print(f"  ASLI   : {row['teks']}")
    print(f"  HASIL  : {row['teks_preprocessed']}")
    print(f"  LABEL  : {row['kategori']}")
    print()

print("=" * 60)
print("PREPROCESSING SELESAI.")
print("Langkah selanjutnya: jalankan 02_tfidf.py")
print("=" * 60)
