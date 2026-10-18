import re

import pandas as pd
from Sastrawi.Stemmer.StemmerFactory import StemmerFactory
from Sastrawi.StopWordRemover.StopWordRemoverFactory import (
    StopWordRemoverFactory,
)


# ==========================================================
# STOPWORD
# ==========================================================

# Stopword Bahasa Indonesia dari Sastrawi
stop_factory = StopWordRemoverFactory()
stopwords_id = set(stop_factory.get_stop_words())

# Stopword khusus domain DICIPTABINTAR.
# Disamakan dengan preprocessing pada script penelitian.
stopwords_domain = {
    "informasi",
    "pemohon",
    "mengenai",
    "terkait",
    "perihal",
    "dijelaskan",
    "diinformasikan",
    "dilakukan",
    "dicek",
    "dilihat",
    "secara",
    "informatif",
    "jelas",
    "kepada",
    "poin",
    "silakan",
    "agar",
    "bahwa",
    "telah",
    "sudah",
    "akan",
    "dapat",
    "bisa",
    "mohon",
    "tolong",
    "minta",
    "ingin",
    "mau",
}

stopwords_gabungan = stopwords_id | stopwords_domain


# ==========================================================
# STEMMER
# ==========================================================

stemmer_factory = StemmerFactory()
stemmer = stemmer_factory.create_stemmer()


# ==========================================================
# 1. CASE FOLDING
# ==========================================================

def step1_case_folding(teks: str) -> str:
    """
    Mengubah seluruh huruf menjadi huruf kecil.
    """

    if teks is None or pd.isna(teks):
        return ""

    return str(teks).lower()


# ==========================================================
# 2. CLEANING
# ==========================================================

def step2_cleaning(teks: str) -> str:
    """
    Menghapus angka, tanda baca, simbol,
    dan merapikan spasi.
    """

    teks = re.sub(r"\d+", " ", teks)
    teks = re.sub(r"[^a-z\s]", " ", teks)
    teks = re.sub(r"\s+", " ", teks).strip()

    return teks


# ==========================================================
# 3. TOKENIZING
# ==========================================================

def step3_tokenizing(teks: str) -> list:
    """
    Memecah teks menjadi token dan menghapus
    token dengan panjang <= 2 karakter.
    """

    tokens = teks.split()

    return [
        token
        for token in tokens
        if len(token) > 2
    ]


# ==========================================================
# 4. STOPWORD REMOVAL
# ==========================================================

def step4_stopword_removal(tokens: list) -> list:
    """
    Menghapus stopword Bahasa Indonesia dan
    stopword khusus domain DICIPTABINTAR.
    """

    return [
        token
        for token in tokens
        if token not in stopwords_gabungan
    ]


# ==========================================================
# 5. STEMMING
# ==========================================================

# Cache hasil stemming per kata unik.
#
# Sastrawi tidak melakukan caching internal, sehingga kata
# yang sama akan dihitung ulang dari nol setiap kali muncul.
# Pada data layanan masyarakat, banyak kata yang berulang
# (mis. "pelayanan", "informasi", "warga", "surat"), sehingga
# cache ini secara signifikan mempercepat proses tanpa
# mengubah hasil stemming sama sekali (fungsi stem bersifat
# deterministik: kata yang sama selalu menghasilkan kata
# dasar yang sama).
_stem_cache: dict = {}


def _stem_cached(token: str) -> str:
    """
    Wrapper stemming dengan cache per token.
    """

    hasil = _stem_cache.get(token)

    if hasil is None:
        hasil = stemmer.stem(token)
        _stem_cache[token] = hasil

    return hasil


def step5_stemming(tokens: list) -> list:
    """
    Mengubah token menjadi bentuk kata dasar
    menggunakan Sastrawi (dengan cache).
    """

    return [
        _stem_cached(token)
        for token in tokens
    ]


# ==========================================================
# FULL PREPROCESSING SATU TEKS
# ==========================================================

def preprocess_text(teks: str) -> str:
    """
    Menjalankan seluruh tahapan preprocessing:

    Case Folding
    -> Cleaning
    -> Tokenizing
    -> Stopword Removal
    -> Stemming

    Output berupa string yang siap digunakan
    pada proses TF-IDF.
    """

    # 1. Case Folding
    teks = step1_case_folding(teks)

    # 2. Cleaning
    teks = step2_cleaning(teks)

    # 3. Tokenizing
    tokens = step3_tokenizing(teks)

    # 4. Stopword Removal
    tokens = step4_stopword_removal(tokens)

    # 5. Stemming
    tokens = step5_stemming(tokens)

    return " ".join(tokens)


# ==========================================================
# PREPROCESSING SELURUH DATASET
# ==========================================================

def preprocess_dataset(
    df: pd.DataFrame,
    text_column: str,
) -> pd.DataFrame:
    """
    Melakukan preprocessing terhadap seluruh teks
    pada dataset tanpa menghapus baris data.

    Parameter:
    - df          : DataFrame dataset
    - text_column : nama kolom yang berisi teks layanan

    Output:
    - DataFrame dengan kolom 'teks_preprocessed'
    """

    # ------------------------------------------------------
    # 1. VALIDASI DATAFRAME
    # ------------------------------------------------------

    if df is None or df.empty:
        raise ValueError(
            "Dataset kosong atau tidak memiliki data."
        )

    # ------------------------------------------------------
    # 2. VALIDASI KOLOM TEKS
    # ------------------------------------------------------

    if text_column not in df.columns:
        raise ValueError(
            f"Kolom teks '{text_column}' tidak ditemukan "
            "pada dataset."
        )

    # Salin agar DataFrame asli tidak berubah
    result = df.copy()

    # ------------------------------------------------------
    # 3. NORMALISASI TEKS
    # ------------------------------------------------------

    # Data tidak dihapus apabila kosong/NaN.
    # Nilai tersebut cukup diubah menjadi string kosong.
    result[text_column] = (
        result[text_column]
        .fillna("")
        .astype(str)
        .str.strip()
    )

    # ------------------------------------------------------
    # 4. PREPROCESSING
    # ------------------------------------------------------

    result["teks_preprocessed"] = (
        result[text_column]
        .apply(preprocess_text)
    )

    # ------------------------------------------------------
    # 5. VALIDASI HASIL
    # ------------------------------------------------------

    # Baris yang menghasilkan teks kosong tetap
    # dipertahankan agar jumlah data tidak berubah.
    jumlah_kosong = (
        result["teks_preprocessed"]
        .fillna("")
        .str.strip()
        .eq("")
        .sum()
    )

    # Minimal harus terdapat satu teks yang dapat
    # digunakan pada proses berikutnya.
    if jumlah_kosong == len(result):
        raise ValueError(
            "Seluruh teks menjadi kosong setelah preprocessing."
        )

    # ------------------------------------------------------
    # 6. RESET INDEX
    # ------------------------------------------------------

    result.reset_index(
        drop=True,
        inplace=True,
    )

    return result