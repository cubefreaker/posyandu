# PRD — Sistem Informasi Posyandu Neiska

## 1. Ringkasan Proyek

| Item | Detail |
| ---- | ------ |
| **Nama Proyek** | Sistem Informasi Posyandu Neiska |
| **Tujuan** | Digitalisasi pencatatan dan pelaporan kegiatan posyandu untuk menggantikan proses manual (buku register) |
| **Cakupan Pelayanan** | Balita (Tumbuh Kembang, Imunisasi, Vitamin A) dan Ibu Hamil (Antenatal Care / Buku KIA) |
| **Skala** | Satu posyandu |
| **Pengguna** | Kader Posyandu (Petugas Lapangan) dan Admin Posyandu (Koordinator / Pengelola) |

---

## 2. Hak Akses & Autentikasi (RBAC)

### 2.1 Role Pengguna

Sistem membedakan secara tegas hak akses antara dua role:

1. **Kader Posyandu (Operator Lapangan):**
   - Bertugas melakukan input operasional harian posyandu secara cepat.
   - Menggunakan **Form Pelayanan Terpadu** (pendaftaran cepat, penimbangan balita, imunisasi, vitamin, dan rekap harian dalam 1 halaman).
   - Memperbarui data kontak/domisili warga binaan dan mencatat pemeriksaan ANC ibu hamil.
   - **Dibatasi haknya**: Tidak dapat menghapus data master (mencegah kehilangan data historis), tidak dapat mengakses manajemen akun pengguna, dan tidak dapat mengubah konfigurasi sistem.
   - Saat login, diarahkan langsung ke halaman **Pelayanan Terpadu**.

2. **Admin Posyandu (Koordinator / Bidan Desa):**
   - Bertugas melakukan pengawasan, audit data, analisis indikator kesehatan posyandu, dan tata kelola akun.
   - Memiliki akses penuh (Full Control): Dashboard Analitik Eksekutif, Master Data (Ibu, Anak, Penimbangan, Imunisasi, Vitamin, Kesehatan Ibu Hamil), hak hapus data, Laporan Rekapitulasi Puskesmas, dan **User Management**.
   - Saat login, diarahkan langsung ke **Dashboard Analitik**.

### 2.2 Alur Login & Redirection

1. Pengguna membuka halaman login (`/login`)
2. Memasukkan **username** dan **password**
3. Sistem memvalidasi kredensial dan memeriksa role pengguna:
   - Jika role **Kader** $\rightarrow$ diarahkan langsung ke **Pelayanan Terpadu** (`/pelayanan`)
   - Jika role **Admin** $\rightarrow$ diarahkan langsung ke **Dashboard Analitik** (`/dashboard`)
4. Jika login gagal, tampilkan pesan error yang sesuai

### 2.3 Keamanan

- Session timeout otomatis setelah periode tidak aktif
- Halaman selain login dilindungi middleware `auth` dan hak akses spesifik admin dilindungi middleware `IsAdmin`
- Tombol aksi berbahaya (penghapusan data) diproteksi di tingkat antarmuka dan backend controller/Livewire

---

## 3. Relasi Data

- **Ibu $\leftrightarrow$ Anak**: Satu ibu bisa memiliki banyak anak. Data ibu tidak bisa dihapus jika masih memiliki data anak.
- **Ibu $\leftrightarrow$ Kehamilan**: Satu ibu bisa memiliki riwayat kehamilan (G1, G2, dst).
- **Kehamilan $\leftrightarrow$ Pemeriksaan ANC**: Satu kehamilan memiliki banyak catatan kunjungan periksa berkala standar 10T Buku KIA.
- **Anak $\leftrightarrow$ Penimbangan, Imunisasi, Vitamin**: Setiap anak memiliki riwayat penimbangan (z-score), imunisasi, dan pemberian vitamin A.

---

## 4. Halaman & Fitur

### 4.1 Dashboard (Admin)

| Komponen | Detail |
| -------- | ------ |
| Kartu ringkasan | Total ibu terdaftar, total ibu hamil aktif, total anak, jumlah penimbangan bulan ini, jumlah imunisasi bulan ini |
| Statistik & indikator | Distribusi status gizi anak (chart), daftar anak yang belum ditimbang bulan ini |
| Navigasi | Sidebar menu terstruktur untuk akses ke semua modul manajerial |

---

### 4.2 Halaman Pelayanan Terpadu (One-Stop Posyandu Service)

Halaman kerja utama kader yang mengintegrasikan seluruh alur operasional Posyandu dalam satu form ringkas:

| Bagian | Fitur & Field | Keterangan |
| ------ | ------------- | ---------- |
| **Identitas Warga** | Quick search balita terdaftar ATAU inline pendaftaran cepat Ibu + Anak baru | Memungkinkan pendaftaran warga baru tanpa meninggalkan halaman |
| **Penimbangan Fisik** | Berat Badan (kg), Tinggi/PJ (cm), LK (cm), LiLA (cm) | Live preview status gizi Kemenkes (BB/U, TB/U, BB/TB, z-score) |
| **Pelayanan Medis** | Checklist Imunisasi (dropdown vaksin) & Checklist Vitamin A (auto-detect biru/merah) | Otomatis merekomendasikan kapsul vitamin sesuai usia |
| **Simpan Terpadu** | Tombol "Simpan Pelayanan Hari Ini" | Menyimpan transaksi secara atomic ke database |
| **Rekapitulasi Harian** | Tabel riwayat pelayanan hari ini, statistik harian, dan tombol **Export/Cetak PDF Laporan Hari Ini** | Laporan langsung tersedia di halaman yang sama |

---

### 4.3 Halaman Kesehatan Ibu Hamil (Buku KIA)

Modul pemantauan kesehatan ibu hamil mengadopsi standar **Buku KIA (Buku Pink Kemenkes RI)**:

#### Profil Kehamilan
- **Field**: HPHT, Taksiran Persalinan (HPL - Rumus Naegele), Usia Gestasi (Minggu), BB Pra-Hamil, Tinggi Badan, IMT Pra-Hamil & Kategori (Kurus, Normal, Lebih, Obesitas), Skrining LiLA Awal (deteksi risiko KEK $< 23.5\text{ cm}$), Status Kehamilan (aktif/melahirkan/keguguran), Catatan Risiko.

#### Pemeriksaan Kunjungan ANC (Standar 10T)
- **Field**: Tanggal Periksa, Usia Kehamilan saat periksa (minggu & trimester), Berat Badan saat ini, Kenaikan BB (dari pra-hamil), Tekanan Darah (Sistol/Diastol - alert jika $\ge 140/90$ mmHg untuk waspada preeklampsia), Tinggi Fundus Uteri (TFU dalam cm), Denyut Jantung Janin (DJJ dalam dpm), Letak Janin (Kepala/Sungsang/Lintang), Status Imunisasi TT, Tablet Tambah Darah (Fe), Hasil Lab (Hb, Protein Urin, GDS), Keluhan & Tindakan.

#### Grafik Peningkatan Berat Badan Ibu Hamil (Buku KIA)
- Visualisasi interaktif berbasis Chart.js.
- Kurva acuan standar Kemenkes/IOM (rentang target kenaikan berat badan ideal dari minggu 0 s/d 40 berdasarkan IMT pra-hamil).
- Plot titik kenaikan berat badan riwayat ANC ibu untuk deteksi dini risiko kehamilan (kurang gizi/BBLR vs risiko preeklampsia/makrosomia).

---

### 4.4 Halaman Data Ibu

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
