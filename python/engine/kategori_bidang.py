"""
Pemetaan kategori mentah (kolom "kategori" pada dataset) ke
Bidang (kelompok besar) untuk kebutuhan "Analisis per Bidang"
pada Dashboard.

Kolom "kategori" pada dataset berisi puluhan nilai teks bebas
(KRK, PBG, Pencarian Data Makam, dsb) yang sebagian besar hanya
berisi beberapa baris data saja -- terlalu sedikit untuk
di-clustering K-Means secara terpisah per kategori.

Sebagai gantinya, kategori-kategori tersebut dikelompokkan lebih
dulu ke salah satu dari 4 Bidang berikut (mengikuti data master
Bidang pada aplikasi, ditambah satu kelompok "Lainnya" untuk
kategori yang tidak jelas termasuk bidang mana):

- Tata Ruang       : KRK, IRK, PKKPR, SITEPLAN, WASDAL
- Bangunan Gedung  : PBG, SLF, REKLAME, SALINAN IMB
- Pemakaman        : seluruh kategori terkait makam/pemakaman
- Lainnya          : kategori yang tidak masuk 3 bidang di atas
  (contoh: "Lainnya", "Tidak Diketahui", "Surat Masuk", "Mahasiswa")

Urutan bidang di bawah ini juga menentukan urutan tampil di
Dashboard.
"""

BIDANG_TATA_RUANG = "Tata Ruang"
BIDANG_BANGUNAN_GEDUNG = "Bangunan Gedung"
BIDANG_PEMAKAMAN = "Pemakaman"
BIDANG_LAINNYA = "Lainnya"

# Urutan tampil di Dashboard.
URUTAN_BIDANG = [
    BIDANG_TATA_RUANG,
    BIDANG_BANGUNAN_GEDUNG,
    BIDANG_PEMAKAMAN,
    BIDANG_LAINNYA,
]

# Pemetaan eksak (kategori mentah -> bidang), dicocokkan setelah
# nilai kategori diseragamkan huruf besar semua.
PEMETAAN_KATEGORI = {
    # --------------------------------------------------------
    # Tata Ruang
    # --------------------------------------------------------
    "KRK": BIDANG_TATA_RUANG,
    "IRK": BIDANG_TATA_RUANG,
    "PKKPR": BIDANG_TATA_RUANG,
    "SITEPLAN": BIDANG_TATA_RUANG,
    "WASDAL": BIDANG_TATA_RUANG,

    # --------------------------------------------------------
    # Bangunan Gedung
    # --------------------------------------------------------
    "PBG": BIDANG_BANGUNAN_GEDUNG,
    "SLF": BIDANG_BANGUNAN_GEDUNG,
    "REKLAME": BIDANG_BANGUNAN_GEDUNG,
    "SALINAN IMB": BIDANG_BANGUNAN_GEDUNG,

    # --------------------------------------------------------
    # Pemakaman
    # --------------------------------------------------------
    "PENCARIAN DATA MAKAM": BIDANG_PEMAKAMAN,
    "PEMAKAMAN BARU": BIDANG_PEMAKAMAN,
    "MENANYAKAN RETRIBUSI PEMAKAMAN": BIDANG_PEMAKAMAN,
    "SOSIALISASI RUMPUTISASI GRATIS (PROGRAM PEMERINTAH)": BIDANG_PEMAKAMAN,
    "PENGAMBILAN KARTU DATA MAKAM": BIDANG_PEMAKAMAN,
    "SOSIALISASI RETRIBUSI GRATIS": BIDANG_PEMAKAMAN,
    "PEMINDAHAN MAKAM": BIDANG_PEMAKAMAN,
    "PEMBUATAN SURAT KETERANGAN PEMAKAMAN": BIDANG_PEMAKAMAN,
    "PEMAKAMAN BARU TUMPANG": BIDANG_PEMAKAMAN,
    "PEMAKAMAN UMUM": BIDANG_PEMAKAMAN,
    "PEMBONGKARAN MAKAM": BIDANG_PEMAKAMAN,
    "REVISI DATA MAKAM": BIDANG_PEMAKAMAN,
    "PEMAKAMAN": BIDANG_PEMAKAMAN,
    "RUMPUTISASI": BIDANG_PEMAKAMAN,

    # --------------------------------------------------------
    # Lainnya (tidak jelas termasuk bidang mana)
    # --------------------------------------------------------
    "LAINNYA": BIDANG_LAINNYA,
    "TIDAK DIKETAHUI": BIDANG_LAINNYA,
    "SURAT MASUK": BIDANG_LAINNYA,
    "MAHASISWA": BIDANG_LAINNYA,
}

# Kata kunci cadangan (dipakai jika nilai kategori tidak persis
# sama dengan daftar di atas, misalnya karena variasi penulisan).
KATA_KUNCI_BIDANG = [
    (BIDANG_PEMAKAMAN, ["makam", "pemakaman", "rumputisasi"]),
    (BIDANG_BANGUNAN_GEDUNG, ["pbg", "imb", "slf", "reklame", "bangunan"]),
    (BIDANG_TATA_RUANG, ["krk", "irk", "pkkpr", "siteplan", "wasdal", "tata ruang"]),
]


def tentukan_bidang(kategori) -> str:
    """
    Menentukan Bidang dari satu nilai kategori mentah.

    Alur:
    1. Cocokkan persis (setelah diseragamkan huruf besar) dengan
       PEMETAAN_KATEGORI.
    2. Jika tidak cocok, cari berdasarkan kata kunci pada
       KATA_KUNCI_BIDANG.
    3. Jika masih tidak cocok, masuk ke kelompok "Lainnya".
    """

    if kategori is None:
        return BIDANG_LAINNYA

    nilai = str(kategori).strip().upper()

    if nilai == "":
        return BIDANG_LAINNYA

    if nilai in PEMETAAN_KATEGORI:
        return PEMETAAN_KATEGORI[nilai]

    nilai_lower = nilai.lower()

    for bidang, kata_kunci_list in KATA_KUNCI_BIDANG:
        for kata_kunci in kata_kunci_list:
            if kata_kunci in nilai_lower:
                return bidang

    return BIDANG_LAINNYA
