# CafeBlends

Sistem pencarian (Information Retrieval) review cafe berbasis **TF-IDF** dan **Cosine Similarity**, dibangun dengan **Laravel**. Pengguna memasukkan query bebas (mis. *"cozy cafe with good coffee and dessert"*), sistem melakukan preprocessing, menghitung relevansi tiap dokumen cafe, lalu menampilkan ranking beserta skor relevansinya. Disertai halaman **Evaluasi** (Precision@K, Recall, MAP).

> Proyek UAS mata kuliah **Sistem Temu Kembali Informasi (STKI)** — Universitas Lampung.

## 👥 Anggota Kelompok

| Nama | NPM | Modul |
|------|-----|-------|
| Zidan Ahmad At-Thoriq | 2317051050 | Database, UI & Integrasi |
| Okta Safitri | 2317051013 | — |
| Egista Fatmawati | 2357051002 | — |
| Faiz Ahmad Nadhif | 2357051012 | — |
| Muhammad Zidane Dako | 2357051005 | — |

---

## ✨ Fitur

- **Pencarian full-text** atas isi review cafe dengan ranking berdasarkan relevansi.
- **Pipeline STKI manual** (tanpa library IR siap pakai) agar tiap proses dapat dijelaskan:
  Preprocessing → Indexing (TF-IDF) → Scoring (Cosine Similarity) → Ranking.
- **Halaman Evaluasi** otomatis: Precision@10, Recall, dan MAP atas query uji + ground truth.

---

## 📂 Dataset

- **Sumber:** [Zomato Cafe Reviews — Kaggle](https://www.kaggle.com/datasets/juhibhojani/zomato-cafe-reviews)
- File: `reviews.csv` (sudah disertakan di repo)
- 775 review pelanggan · 299 cafe unik · 10 kota
- **Pemodelan dokumen:** seluruh review milik satu cafe digabung menjadi satu dokumen, lalu hanya cafe dengan **≥100 kata** yang dipakai → **74 dokumen** valid.

---

## 🔧 Pipeline STKI

### 1. Preprocessing — `app/Services/TextPreprocessor.php`
Case folding → cleaning → tokenizing → stopword removal → stemming.

### 2. Indexing (TF-IDF) — `app/Services/TfidfService.php`
- `TF` = frekuensi term dalam dokumen
- `IDF = log(N / df)`
- `bobot = TF × IDF`, disimpan beserta panjang vektor (norm) ke `storage/app/tfidf_index.json`

### 3. Scoring (Cosine Similarity) — `app/Services/SearchService.php`
```
cosine(q, d) = (q · d) / (‖q‖ × ‖d‖)
```

### 4. Evaluasi — `app/Services/EvaluationService.php`
Precision@10, Recall, Average Precision (AP), dan MAP atas 13 query uji + ground truth.

**Hasil:** Precision@10 = **0.846** · Recall = **0.856** · MAP = **0.864**

---

## 🚀 Cara Menjalankan

Prasyarat: PHP 8.2+, Composer, PostgreSQL (mis. via Laragon).

```bash
# 1. Install dependency
composer install

# 2. Setup environment
cp .env.example .env
php artisan key:generate
# atur DB_CONNECTION=pgsql, DB_DATABASE=cafeblends, DB_USERNAME, DB_PASSWORD di .env
# buat database kosong bernama "cafeblends" di PostgreSQL

# 3. Migrasi database
php artisan migrate

# 4. Import dataset cafe (gabung review, filter ≥100 kata)
php artisan import:cafes

# 5. Bangun indeks TF-IDF
php artisan index:build

# 6. Jalankan server
php artisan serve
```

Buka `http://127.0.0.1:8000` untuk pencarian, dan `/evaluation` untuk halaman evaluasi.

> Catatan: isi database **tidak** disimpan di Git. Setelah `git pull`, jalankan ulang `migrate` → `import:cafes` → `index:build` untuk membangun data secara identik dari `reviews.csv`.

---

## 🗂️ Struktur Penting

```
app/
├── Console/Commands/
│   ├── ImportCafes.php      # import:cafes — load & grouping dataset
│   └── BuildIndex.php       # index:build — bangun indeks TF-IDF
├── Http/Controllers/
│   ├── SearchController.php
│   └── EvaluationController.php
├── Models/Cafe.php
└── Services/
    ├── TextPreprocessor.php # preprocessing 5 tahap
    ├── TfidfService.php     # TF-IDF indexing
    ├── SearchService.php    # cosine similarity + ranking
    └── EvaluationService.php# Precision/Recall/MAP
resources/views/
├── layouts/app.blade.php
├── search.blade.php
└── evaluation.blade.php
reviews.csv                  # dataset Zomato (Kaggle)
```

---

## 🛠️ Teknologi

Laravel · PHP 8.2 · PostgreSQL · Tailwind CSS · Blade.
