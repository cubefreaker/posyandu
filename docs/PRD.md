# PRD — Sistem Informasi Posyandu Neiska

## 1. Ringkasan Proyek

| Item | Detail |
| ---- | ------ |
| **Nama Proyek** | Sistem Informasi Posyandu Neiska |
| **Tujuan** | Digitalisasi pencatatan dan pelaporan kegiatan posyandu untuk menggantikan proses manual (buku register) |
| **Cakupan Pelayanan** | Anak (balita) |
| **Skala** | Satu posyandu |
| **Pengguna** | Kader dan Admin posyandu |

---

## 2. Hak Akses & Autentikasi

### 2.1 Role Pengguna

Sistem memiliki dua role: **Admin** dan **Kader**. Keduanya memiliki **hak akses yang sama** (full access) terhadap seluruh fitur, termasuk tambah, edit, dan hapus data.

### 2.2 Alur Login

1. Pengguna membuka halaman login
2. Memasukkan **username** dan **password**
3. Sistem memvalidasi kredensial dan menentukan role berdasarkan data akun
4. Jika valid, pengguna diarahkan ke **Dashboard**
5. Jika gagal, tampilkan pesan error

### 2.3 Keamanan

- Session otomatis berakhir setelah periode tidak aktif (session timeout)
- Halaman selain login hanya bisa diakses oleh pengguna yang sudah terautentikasi
- Password disimpan dalam bentuk terenkripsi

---

## 3. Relasi Data

Data Anak **berelasi** dengan Data Ibu menggunakan **NIK Ibu** sebagai penghubung:

- Satu ibu bisa memiliki banyak anak
- Setiap anak wajib terhubung ke satu data ibu
- Data ibu tidak bisa dihapus jika masih memiliki anak yang terdaftar
- Data anak tidak bisa dihapus jika masih memiliki riwayat penimbangan, imunisasi, atau vitamin

---

## 4. Halaman & Fitur

### 4.1 Dashboard

| Komponen | Detail |
| -------- | ------ |
| Kartu ringkasan | Total ibu terdaftar, total anak aktif, jumlah penimbangan bulan ini, jumlah imunisasi bulan ini |
| Statistik & indikator | Distribusi status gizi anak (chart), daftar anak yang belum ditimbang bulan ini |
| Navigasi | Sidebar menu untuk akses ke semua modul |

---

### 4.2 Halaman Data Ibu

#### Field Data

| Field | Tipe | Keterangan |
| ----- | ---- | ---------- |
| NIK | Teks (16 digit) | Wajib, unik |
| Nama Ibu | Teks | Wajib |
| Tanggal Lahir | Tanggal | Wajib |
| Alamat | Teks | Wajib |

#### Fitur

| Fitur | Detail |
| ----- | ------ |
| Tabel data | Menampilkan daftar semua ibu dengan kolom NIK, Nama, Tanggal Lahir, Alamat |
| Pencarian | Search bar untuk mencari berdasarkan nama atau NIK |
| Tambah data | Form input untuk menambahkan data ibu baru |
| Edit data | Mengubah data ibu yang sudah ada |
| Hapus data | Menghapus data ibu dengan konfirmasi. Tidak bisa dihapus jika masih memiliki anak terdaftar |
| Detail ibu | Menampilkan profil ibu beserta daftar anak yang terkait |

---

### 4.3 Halaman Data Anak

#### Field Data

| Field | Tipe | Keterangan |
| ----- | ---- | ---------- |
| Nama Anak | Teks | Wajib |
| NIK Ibu | Dropdown + search | Wajib, memilih dari data ibu yang sudah terdaftar |
| Tanggal Lahir | Tanggal | Wajib |
| Jenis Kelamin | Pilihan (L/P) | Wajib |

#### Fitur

| Fitur | Detail |
| ----- | ------ |
| Tabel data | Menampilkan daftar anak dengan kolom Nama Anak, Nama Ibu, Tanggal Lahir, Jenis Kelamin |
| Pencarian | Search bar untuk mencari berdasarkan nama anak atau nama ibu |
| Tambah data | Form input untuk menambahkan data anak baru |
| Edit data | Mengubah data anak yang sudah ada |
| Hapus data | Menghapus data anak dengan konfirmasi. Tidak bisa dihapus jika masih memiliki riwayat penimbangan/imunisasi/vitamin |
| Detail anak | Menampilkan profil anak, info ibu, dan ringkasan riwayat (penimbangan terakhir, status gizi, status imunisasi) |

#### Logika

- Usia anak dihitung **otomatis** dari tanggal lahir

---

### 4.4 Halaman Penimbangan & Status Gizi

#### Field Input

| Field | Tipe | Keterangan |
| ----- | ---- | ---------- |
| Pilih Anak | Dropdown + search | Wajib, memilih dari data anak yang sudah terdaftar |
| Tanggal Pelayanan | Tanggal | Wajib |
| Berat Badan | Angka (kg) | Wajib |
| Tinggi Badan | Angka (cm) | Wajib |
| Lingkar Kepala | Angka (cm) | Opsional |
| LILA | Angka (cm) | Opsional |

#### Fitur

| Fitur | Detail |
| ----- | ------ |
| Form input | Formulir pencatatan penimbangan dengan field di atas |
| Kalkulasi status gizi otomatis | Sistem menghitung z-score **BB/U, TB/U, BB/TB** berdasarkan **standar Kemenkes** dan menentukan kategori: Gizi Buruk, Gizi Kurang, Gizi Baik, Gizi Lebih |
| Grafik KMS | Kurva pertumbuhan anak (BB/U, TB/U) dengan garis standar Kemenkes, zona warna per kategori status gizi, interaktif (hover untuk detail) |
| Tabel riwayat | Riwayat penimbangan per anak dengan kolom tanggal, BB, TB, Lingkar Kepala, LILA, status gizi |
| Pencarian & filter | Search dan filter berdasarkan periode |
| Edit data | Mengubah data penimbangan yang sudah ada |
| Hapus data | Menghapus data penimbangan dengan konfirmasi |
| Validasi | Cek rentang wajar BB/TB sesuai usia, peringatan jika data anomali |

#### Logika

- Penimbangan bersifat **fleksibel** — bisa dilakukan kapan saja, tidak terbatas 1x per bulan
- Status gizi dihitung otomatis setiap kali data penimbangan disimpan

---

### 4.5 Halaman Imunisasi

#### Field Input

| Field | Tipe | Keterangan |
| ----- | ---- | ---------- |
| Pilih Anak | Dropdown + search | Wajib |
| Tanggal Imunisasi | Tanggal | Wajib |
| Jenis Imunisasi | Dropdown | Wajib, mengacu pada program imunisasi nasional |
| Keterangan | Teks | Opsional |

#### Master Data Jenis Imunisasi

Mengikuti **program imunisasi nasional**, daftar jenis imunisasi yang tersedia sebagai dropdown (bukan free text):

| Imunisasi | Usia Pemberian |
| --------- | -------------- |
| Hepatitis B-0 | 0–24 jam |
| BCG | 1 bulan |
| Polio 1 (OPV) | 1 bulan |
| DPT-HB-Hib 1 | 2 bulan |
| Polio 2 (OPV) | 2 bulan |
| DPT-HB-Hib 2 | 3 bulan |
| Polio 3 (OPV) | 3 bulan |
| DPT-HB-Hib 3 | 4 bulan |
| Polio 4 (OPV) | 4 bulan |
| IPV | 4 bulan |
| Campak/MR 1 | 9 bulan |
| DPT-HB-Hib Lanjutan | 18 bulan |
| Campak/MR 2 | 18 bulan |

#### Fitur

| Fitur | Detail |
| ----- | ------ |
| Form input | Formulir pencatatan imunisasi dengan field di atas |
| Tabel riwayat | Riwayat imunisasi per anak dengan kolom tanggal, jenis imunisasi, keterangan |
| Pencarian & filter | Search dan filter berdasarkan nama anak atau jenis imunisasi |
| Edit data | Mengubah data imunisasi yang sudah ada |
| Hapus data | Menghapus data imunisasi dengan konfirmasi |
| Validasi duplikasi | Imunisasi yang sama tidak bisa dicatat dua kali untuk anak yang sama |

---

### 4.6 Halaman Vitamin

#### Field Input

| Field | Tipe | Keterangan |
| ----- | ---- | ---------- |
| Pilih Anak | Dropdown + search | Wajib |
| Tanggal Pemberian | Tanggal | Wajib |
| Jenis Vitamin | Dropdown | Wajib: Kapsul Biru atau Kapsul Merah |
| Keterangan | Teks | Opsional |

#### Fitur

| Fitur | Detail |
| ----- | ------ |
| Form input | Formulir pencatatan pemberian vitamin dengan field di atas |
| Tabel riwayat | Riwayat pemberian vitamin per anak dengan kolom tanggal, jenis vitamin, keterangan |
| Pencarian & filter | Search dan filter berdasarkan nama anak |
| Edit data | Mengubah data vitamin yang sudah ada |
| Hapus data | Menghapus data vitamin dengan konfirmasi |
| Validasi usia | Kapsul Biru untuk usia 6–11 bulan, Kapsul Merah untuk usia 12–59 bulan (otomatis berdasarkan usia anak) |

#### Logika

- Vitamin A diberikan **2x setahun** (Februari & Agustus)
- Sistem memvalidasi kesesuaian jenis kapsul berdasarkan usia anak

---

### 4.7 Halaman Laporan

#### Fitur

| Fitur | Detail |
| ----- | ------ |
| Filter periode | Filter berdasarkan bulan dan tahun |
| Rekap data ibu | Jumlah ibu terdaftar |
| Rekap data anak | Jumlah anak aktif, distribusi usia, distribusi jenis kelamin |
| Rekap penimbangan | Jumlah anak yang ditimbang pada periode tersebut, rata-rata BB/TB, daftar anak yang tidak hadir |
| Rekap status gizi | Distribusi status gizi (Gizi Buruk / Kurang / Baik / Lebih), tren dibanding bulan sebelumnya |
| Rekap imunisasi | Jumlah pemberian per jenis imunisasi, coverage rate |
| Rekap vitamin | Jumlah pemberian per jenis vitamin |
| Cetak laporan | Export laporan ke format **PDF** untuk dicetak |
