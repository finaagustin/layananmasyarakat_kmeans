import numpy as np
import pandas as pd

from sklearn.cluster import KMeans
from sklearn.feature_extraction.text import TfidfVectorizer

from engine.kategori_bidang import (
    URUTAN_BIDANG,
    tentukan_bidang,
)


# ==========================================================
# KONFIGURASI TF-IDF
# ==========================================================

TFIDF_CONFIG = {
    "max_df": 0.90,
    "min_df": 2,
    "ngram_range": (1, 2),
    "sublinear_tf": True,
}


# ==========================================================
# KONFIGURASI K-MEANS
# ==========================================================

KMEANS_CONFIG = {
    "random_state": 42,
    "n_init": 10,
    "max_iter": 300,
}


# ==========================================================
# NAMA CLUSTER HASIL INTERPRETASI PENELITIAN
# ==========================================================

NAMA_CLUSTER = {
    0: "Layanan Administrasi dan Informasi Umum",
    1: "Persyaratan dan Konsultasi KRK/SLF",
    2: "Layanan PBG dan Reklame",
    3: "Layanan Data dan Informasi Pemakaman",
}


# ==========================================================
# MEMBUAT TF-IDF
# ==========================================================

def build_tfidf(df: pd.DataFrame):
    """
    Membentuk matriks TF-IDF dari seluruh data hasil
    preprocessing pada kolom teks_preprocessed.

    Seluruh baris tetap dipertahankan.
    Teks kosong menghasilkan vektor nol dan tidak dihapus.
    """

    if df is None or df.empty:
        raise ValueError(
            "Dataset preprocessing kosong."
        )

    if "teks_preprocessed" not in df.columns:
        raise ValueError(
            "Kolom 'teks_preprocessed' tidak ditemukan."
        )

    texts = (
        df["teks_preprocessed"]
        .fillna("")
        .astype(str)
        .str.strip()
    )

    if len(texts) < 2:
        raise ValueError(
            "Data teks tidak mencukupi untuk TF-IDF."
        )

    if (texts != "").sum() == 0:
        raise ValueError(
            "Seluruh teks preprocessing kosong."
        )

    vectorizer = TfidfVectorizer(
        **TFIDF_CONFIG
    )

    X = vectorizer.fit_transform(texts)

    if X.shape[0] != len(df):
        raise ValueError(
            "Jumlah vektor TF-IDF tidak sama "
            "dengan jumlah data."
        )

    if X.shape[1] == 0:
        raise ValueError(
            "TF-IDF tidak menghasilkan fitur."
        )

    return X, vectorizer


# ==========================================================
# ELBOW METHOD
# ==========================================================

def calculate_elbow(
    X,
    n_data: int,
    k_min: int = 2,
    k_max: int = 8,
):
    """
    Menghitung inertia untuk K=2 sampai K=8
    menggunakan seluruh dataset.
    """

    if X is None:
        raise ValueError(
            "Matriks TF-IDF tidak tersedia."
        )

    if X.shape[0] != n_data:
        raise ValueError(
            "Jumlah data TF-IDF tidak sesuai "
            "dengan jumlah dataset."
        )

    hasil = []

    for k in range(
        k_min,
        k_max + 1,
    ):

        if k >= n_data:
            break

        model = KMeans(
            n_clusters=k,
            **KMEANS_CONFIG,
        )

        model.fit(X)

        hasil.append({
            "k": int(k),
            "inertia": round(
                float(model.inertia_),
                4,
            ),
        })

    return hasil


# ==========================================================
# MENENTUKAN K OPTIMAL
# ==========================================================

def determine_k_optimal(elbow_results):
    """
    Menentukan K optimal berdasarkan titik siku kurva Elbow.

    Titik siku ditentukan berdasarkan jarak maksimum
    setiap titik terhadap garis yang menghubungkan
    titik pertama dan titik terakhir kurva Elbow.

    Metode ini disamakan dengan pengujian penelitian
    pada 03_elbow.py.
    """

    if not elbow_results:
        raise ValueError(
            "Hasil Elbow Method kosong."
        )

    if len(elbow_results) < 3:
        return int(
            elbow_results[0]["k"]
        )

    # ------------------------------------------------------
    # AMBIL K DAN INERTIA
    # ------------------------------------------------------

    k_values = np.array(
        [
            item["k"]
            for item in elbow_results
        ],
        dtype=float,
    )

    inertia = np.array(
        [
            item["inertia"]
            for item in elbow_results
        ],
        dtype=float,
    )

    # ------------------------------------------------------
    # NORMALISASI
    # ------------------------------------------------------

    k_min = k_values.min()
    k_max = k_values.max()

    inertia_min = inertia.min()
    inertia_max = inertia.max()

    rentang_k = (
        k_max - k_min
    )

    rentang_inertia = (
        inertia_max - inertia_min
    )

    if rentang_k == 0:
        return int(
            k_values[0]
        )

    if rentang_inertia == 0:
        return int(
            k_values[0]
        )

    x_normalized = (
        (k_values - k_min)
        / rentang_k
    )

    y_normalized = (
        (inertia - inertia_min)
        / rentang_inertia
    )

    points = np.column_stack(
        (
            x_normalized,
            y_normalized,
        )
    )

    # ------------------------------------------------------
    # GARIS TITIK PERTAMA → TITIK TERAKHIR
    # ------------------------------------------------------

    titik_awal = points[0]
    titik_akhir = points[-1]

    garis = (
        titik_akhir
        - titik_awal
    )

    panjang_garis = np.linalg.norm(
        garis
    )

    if panjang_garis == 0:
        return int(
            k_values[0]
        )

    # ------------------------------------------------------
    # JARAK TITIK TERHADAP GARIS
    # ------------------------------------------------------

    distances = []

    for point in points:

        vector = (
            point
            - titik_awal
        )

        # Cross product 2D.
        # Ditulis manual agar kompatibel dengan NumPy baru
        # dan tidak menghasilkan DeprecationWarning.
        cross_value = abs(
            garis[0] * vector[1]
            - garis[1] * vector[0]
        )

        distance = (
            cross_value
            / panjang_garis
        )

        distances.append(
            float(distance)
        )

    # ------------------------------------------------------
    # TITIK DENGAN JARAK TERBESAR = K OPTIMAL
    # ------------------------------------------------------

    elbow_index = int(
        np.argmax(distances)
    )

    k_optimal = int(
        k_values[elbow_index]
    )

    return k_optimal


# ==========================================================
# MENGAMBIL KATA DOMINAN
# ==========================================================

def get_top_terms(
    model,
    feature_names,
    jumlah_kata=5,
):
    """
    Mengambil term dengan bobot centroid tertinggi
    pada setiap cluster.
    """

    hasil = {}

    for cluster_id in range(
        model.n_clusters
    ):

        centroid = (
            model.cluster_centers_[
                cluster_id
            ]
        )

        top_indices = np.argsort(
            centroid
        )[::-1][:jumlah_kata]

        hasil[int(cluster_id)] = [
            str(
                feature_names[index]
            )
            for index
            in top_indices
        ]

    return hasil


# ==========================================================
# CLUSTERING SELURUH DATASET
# ==========================================================

def run_clustering(df: pd.DataFrame):
    """
    Menjalankan clustering terhadap seluruh dataset.

    Alur penelitian:

    1. Data hasil preprocessing
    2. TF-IDF seluruh data
    3. Elbow Method seluruh data
    4. Menentukan K optimal
    5. K-Means seluruh data
    6. Memberikan label cluster
    7. Memberikan nama cluster
    8. Mengambil kata dominan cluster

    Kolom kategori tidak digunakan sebagai variabel
    pembentuk cluster.
    """

    if df is None or df.empty:
        raise ValueError(
            "Dataset kosong."
        )

    if "teks_preprocessed" not in df.columns:
        raise ValueError(
            "Kolom 'teks_preprocessed' tidak ditemukan."
        )

    # ------------------------------------------------------
    # SALIN SELURUH DATA
    # ------------------------------------------------------

    df_hasil = (
        df.copy()
        .reset_index(drop=True)
    )

    jumlah_data = len(
        df_hasil
    )

    if jumlah_data < 3:
        raise ValueError(
            "Jumlah data tidak mencukupi "
            "untuk proses clustering."
        )

    # ------------------------------------------------------
    # 1. TF-IDF SELURUH DATA
    # ------------------------------------------------------

    X, vectorizer = build_tfidf(
        df_hasil
    )

    feature_names = (
        vectorizer
        .get_feature_names_out()
    )

    if X.shape[0] != jumlah_data:
        raise ValueError(
            "Jumlah data TF-IDF tidak sama "
            "dengan jumlah dataset."
        )

    # ------------------------------------------------------
    # 2. ELBOW METHOD
    # ------------------------------------------------------

    elbow_results = calculate_elbow(
        X=X,
        n_data=jumlah_data,
        k_min=2,
        k_max=8,
    )

    if not elbow_results:
        raise ValueError(
            "Elbow Method tidak menghasilkan "
            "nilai inertia."
        )

    # ------------------------------------------------------
    # 3. MENENTUKAN K OPTIMAL
    # ------------------------------------------------------

    k_optimal = determine_k_optimal(
        elbow_results
    )

    if k_optimal is None:
        raise ValueError(
            "K optimal tidak dapat ditentukan."
        )

    if k_optimal < 2:
        raise ValueError(
            "K optimal minimal harus 2."
        )

    if k_optimal >= jumlah_data:
        raise ValueError(
            "K optimal harus lebih kecil "
            "daripada jumlah data."
        )

    # ------------------------------------------------------
    # 4. K-MEANS SELURUH DATA
    # ------------------------------------------------------

    model = KMeans(
        n_clusters=k_optimal,
        **KMEANS_CONFIG,
    )

    labels = model.fit_predict(
        X
    )

    if len(labels) != jumlah_data:
        raise ValueError(
            "Jumlah label cluster tidak sama "
            "dengan jumlah data."
        )

    # ------------------------------------------------------
    # 5. SIMPAN LABEL CLUSTER
    # ------------------------------------------------------

    df_hasil["cluster"] = (
        labels.astype(int)
    )

        # ------------------------------------------------------
    # 6. NAMA CLUSTER
    # ------------------------------------------------------
    # Penamaan cluster dilakukan SETELAH proses K-Means.
    # Nama cluster hanya digunakan untuk interpretasi hasil
    # dan tidak memengaruhi pembentukan cluster.

    if k_optimal == 4:

        df_hasil["nama_cluster"] = (
            df_hasil["cluster"]
            .apply(
                lambda cluster:
                NAMA_CLUSTER.get(
                    int(cluster),
                    f"Cluster {int(cluster)}"
                )
            )
        )

    else:

        df_hasil["nama_cluster"] = (
            df_hasil["cluster"]
            .apply(
                lambda cluster:
                f"Cluster {int(cluster)}"
            )
        )
        
    # ------------------------------------------------------
    # 7. KATA DOMINAN
    # ------------------------------------------------------

    top_terms = get_top_terms(
        model=model,
        feature_names=feature_names,
        jumlah_kata=5,
    )

    # ------------------------------------------------------
    # 8. JUMLAH DATA PER CLUSTER
    # ------------------------------------------------------

    jumlah_cluster_detail = (
        df_hasil["cluster"]
        .value_counts()
        .sort_index()
        .to_dict()
    )

    jumlah_cluster_detail = {
        int(cluster): int(jumlah)
        for cluster, jumlah
        in jumlah_cluster_detail.items()
    }

    # ------------------------------------------------------
    # 9. VALIDASI JUMLAH DATA
    # ------------------------------------------------------

    jumlah_data_clustering = len(
        df_hasil
    )

    if jumlah_data_clustering != jumlah_data:
        raise ValueError(
            "Jumlah data hasil clustering "
            "tidak sama dengan jumlah dataset."
        )

    total_cluster = sum(
        jumlah_cluster_detail.values()
    )

    if total_cluster != jumlah_data:
        raise ValueError(
            "Total anggota seluruh cluster "
            "tidak sama dengan jumlah dataset."
        )

    # ------------------------------------------------------
    # RETURN
    # ------------------------------------------------------

    return {
        "dataframe": df_hasil,

        "vectorizer": vectorizer,

        "model": model,

        "X": X,

        "labels": labels,

        "jumlah_data": int(
            jumlah_data
        ),

        "jumlah_data_clustering": int(
            jumlah_data_clustering
        ),

        "jumlah_fitur": int(
            X.shape[1]
        ),

        "k_optimal": int(
            k_optimal
        ),

        "jumlah_cluster": int(
            k_optimal
        ),

        "elbow": elbow_results,

        "top_terms": top_terms,

        "jumlah_cluster_detail":
            jumlah_cluster_detail,

        "nama_cluster":
            NAMA_CLUSTER
            if k_optimal == 4
            else {},
    }


# ==========================================================
# CLUSTERING PER BIDANG
# ==========================================================

# Jumlah data minimal agar satu Bidang tetap di-clustering
# secara terpisah. Di bawah nilai ini, Elbow Method (K=2..8)
# dan Silhouette Score tidak lagi bermakna, sehingga Bidang
# tersebut dilewati (statusnya dicatat sebagai "dilewati").
MIN_DATA_PER_BIDANG = 10


def run_clustering_per_bidang(
    df: pd.DataFrame,
    kolom_kategori: str = "kategori",
    min_data: int = MIN_DATA_PER_BIDANG,
):
    """
    Menjalankan clustering K-Means SECARA TERPISAH untuk
    setiap Bidang (Tata Ruang, Bangunan Gedung, Pemakaman,
    Lainnya), sebagai pelengkap hasil clustering gabungan
    (run_clustering) yang menggabungkan seluruh data sekaligus.

    Setiap Bidang diperlakukan sebagai dataset tersendiri:
    TF-IDF, Elbow Method, K-Means, dan Silhouette Score
    dihitung ulang khusus untuk data pada Bidang itu saja,
    sehingga kelompok yang terbentuk benar-benar
    mencerminkan pola pada Bidang tersebut (bukan sekadar
    memecah hasil clustering gabungan).

    Bidang dengan jumlah data di bawah `min_data` dilewati
    (hasil Elbow/K-Means/Silhouette untuk jumlah data yang
    terlalu sedikit tidak dapat diandalkan).

    Kegagalan pada satu Bidang tidak menggagalkan Bidang
    lainnya -- masing-masing Bidang diproses independen.
    """

    if df is None or df.empty:
        raise ValueError(
            "Dataset kosong."
        )

    if kolom_kategori not in df.columns:
        raise ValueError(
            f"Kolom '{kolom_kategori}' tidak ditemukan "
            "pada dataset."
        )

    df_kerja = df.copy().reset_index(drop=True)

    df_kerja["bidang"] = (
        df_kerja[kolom_kategori]
        .apply(tentukan_bidang)
    )

    hasil_per_bidang = {}

    for nama_bidang in URUTAN_BIDANG:

        subset = (
            df_kerja[
                df_kerja["bidang"] == nama_bidang
            ]
            .copy()
            .reset_index(drop=True)
        )

        jumlah_data_bidang = len(subset)

        # ----------------------------------------------
        # Bidang tanpa data sama sekali tidak ditampilkan
        # ----------------------------------------------
        if jumlah_data_bidang == 0:
            continue

        # ----------------------------------------------
        # Data terlalu sedikit untuk clustering bermakna
        # ----------------------------------------------
        if jumlah_data_bidang < min_data:

            hasil_per_bidang[nama_bidang] = {
                "status": "dilewati",
                "alasan": (
                    f"Jumlah data hanya {jumlah_data_bidang} "
                    f"(minimal {min_data} data diperlukan "
                    "agar Elbow Method dan Silhouette Score "
                    "bermakna)."
                ),
                "jumlah_data": jumlah_data_bidang,
            }

            continue

        # ----------------------------------------------
        # Clustering khusus untuk Bidang ini
        # ----------------------------------------------
        try:

            X_bidang, vectorizer_bidang = build_tfidf(
                subset
            )

            feature_names_bidang = (
                vectorizer_bidang
                .get_feature_names_out()
            )

            elbow_bidang = calculate_elbow(
                X=X_bidang,
                n_data=jumlah_data_bidang,
                k_min=2,
                k_max=8,
            )

            if not elbow_bidang:
                raise ValueError(
                    "Elbow Method tidak menghasilkan "
                    "nilai inertia."
                )

            k_optimal_bidang = determine_k_optimal(
                elbow_bidang
            )

            if (
                k_optimal_bidang is None
                or k_optimal_bidang < 2
                or k_optimal_bidang >= jumlah_data_bidang
            ):
                raise ValueError(
                    "K optimal Bidang tidak valid."
                )

            model_bidang = KMeans(
                n_clusters=k_optimal_bidang,
                **KMEANS_CONFIG,
            )

            labels_bidang = (
                model_bidang.fit_predict(X_bidang)
            )

            subset["cluster"] = (
                labels_bidang.astype(int)
            )

            subset["nama_cluster"] = (
                subset["cluster"]
                .apply(lambda c: f"Cluster {int(c)}")
            )

            top_terms_bidang = get_top_terms(
                model=model_bidang,
                feature_names=feature_names_bidang,
                jumlah_kata=5,
            )

            # Import lokal untuk menghindari import
            # melingkar (evaluation.py tidak mengimpor
            # clustering.py).
            from engine.evaluation import evaluate_clustering

            evaluasi_bidang = evaluate_clustering({
                "X": X_bidang,
                "labels": labels_bidang,
                "dataframe": subset,
            })

            hasil_per_bidang[nama_bidang] = {
                "status": "berhasil",
                "jumlah_data": jumlah_data_bidang,
                "k_optimal": int(k_optimal_bidang),
                "jumlah_cluster": int(k_optimal_bidang),
                "silhouette_score": evaluasi_bidang[
                    "silhouette_score"
                ],
                "interpretasi": evaluasi_bidang[
                    "interpretasi"
                ],
                "top_terms": top_terms_bidang,
                "per_cluster": evaluasi_bidang[
                    "per_cluster"
                ],
            }

        except Exception as error:

            hasil_per_bidang[nama_bidang] = {
                "status": "gagal",
                "alasan": (
                    f"{type(error).__name__}: {error}"
                ),
                "jumlah_data": jumlah_data_bidang,
            }

    return hasil_per_bidang