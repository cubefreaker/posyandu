# Breakdown Modul, Estimasi Biaya & Pertanyaan Client

---

## Ringkasan Biaya

| # | Modul                           |                  Biaya |
| - | ------------------------------- | ---------------------: |
| 1 | Autentikasi & Login             |             Rp 200.000 |
| 2 | Dashboard                       |             Rp 300.000 |
| 3 | Modul Data Ibu                  |             Rp 300.000 |
| 4 | Modul Data Anak                 |             Rp 300.000 |
| 5 | Modul Penimbangan & Status Gizi |             Rp 500.000 |
| 6 | Modul Imunisasi                 |             Rp 300.000 |
| 7 | Modul Vitamin                   |             Rp 200.000 |
| 8 | Modul Laporan                   |             Rp 400.000 |
|   | **TOTAL**                 | **Rp 2.500.000** |

---

## Detail Per Modul

---

### 1. Autentikasi & Login

| Sub-item                   | Detail                                              |
| -------------------------- | --------------------------------------------------- |
| Halaman Login              | Form username & password, validasi input            |
| Sistem session & otorisasi | Login/logout, proteksi halaman, session timeout     |
| Hak akses berbasis role    | Pembatasan menu/aksi berdasarkan role (Admin/Kader) |

**Catatan:** Role didefinisikan oleh sistem, **bukan dipilih user saat login** (berbeda dari PRD awal).

---

### 2. Dashboard

| Sub-item              | Detail                                                                  |
| --------------------- | ----------------------------------------------------------------------- |
| Kartu ringkasan       | Total ibu, total anak aktif, penimbangan bulan ini, imunisasi bulan ini |
| Statistik & indikator | Distribusi status gizi (pie/bar chart), anak belum ditimbang bulan ini  |
| Layout & navigasi     | Sidebar menu, header, navigasi antar modul                              |

---

### 3. Modul Data Ibu

| Sub-item               | Detail                                                                               |
| ---------------------- | ------------------------------------------------------------------------------------ |
| Tabel data & pencarian | Daftar ibu, search by nama/NIK, pagination                                           |
| Form tambah & edit     | Input NIK, Nama, Tanggal Lahir, Alamat + validasi                                    |
| Hapus data             | Konfirmasi hapus, cek relasi anak (tidak bisa hapus jika masih punya anak terdaftar) |
| Detail ibu             | Tampilkan profil ibu beserta daftar anak yang terkait                                |

**Field data:**

- NIK (wajib)
- Nama Ibu (wajib)
- Tanggal Lahir (wajib)
- Alamat (wajib)

---

### 4. Modul Data Anak

| Sub-item               | Detail                                                                                  |
| ---------------------- | --------------------------------------------------------------------------------------- |
| Tabel data & pencarian | Daftar anak, search by nama/nama ibu, filter jenis kelamin                              |
| Form tambah & edit     | Input Nama Anak, Pilih Ibu (dropdown + search), Tanggal Lahir, Jenis Kelamin + validasi |
| Hapus data             | Konfirmasi hapus, cek relasi penimbangan/imunisasi/vitamin                              |
| Detail anak            | Profil anak, info ibu, ringkasan riwayat (penimbangan terakhir, status imunisasi)       |

**Field data:**

- Nama Anak (wajib)
- NIK Ibu / Pilih Ibu (wajib)
- Tanggal Lahir (wajib)
- Jenis Kelamin (wajib)

**Logika:**

- Usia anak dihitung otomatis dari tanggal lahir

---

### 5. Modul Penimbangan & Status Gizi

| Sub-item                       | Detail                                                                                                                  |
| ------------------------------ | ----------------------------------------------------------------------------------------------------------------------- |
| Form input penimbangan         | Pilih anak (dropdown + search), tanggal pelayanan, BB (kg), TB (cm)                                                     |
| Kalkulasi status gizi otomatis | Hitung z-score BB/U, TB/U, BB/TB berdasarkan tabel WHO/Kemenkes, tentukan kategori (Gizi Buruk / Kurang / Baik / Lebih) |
| Grafik KMS                     | Kurva pertumbuhan anak (BB/U, TB/U) dengan garis standar WHO, zona warna status gizi, interaktif                        |
| Tabel riwayat penimbangan      | Riwayat per anak, search, filter periode, termasuk kolom status gizi                                                    |
| Edit & hapus riwayat           | Edit data penimbangan, hapus dengan konfirmasi                                                                          |
| Validasi                       | Cek rentang wajar BB/TB sesuai usia, peringatan jika data anomali                                                       |

**Catatan:**

- Standar z-score (WHO atau Kemenkes) ditentukan oleh jawaban B2
- Parameter tambahan (lingkar kepala, LILA) ditentukan oleh jawaban B1

---

### 6. Modul Imunisasi

| Sub-item                    | Detail                                                                                     |
| --------------------------- | ------------------------------------------------------------------------------------------ |
| Master data jenis imunisasi | Daftar imunisasi sesuai program nasional (dropdown, bukan free text)                       |
| Form input imunisasi        | Pilih anak, tanggal imunisasi, jenis imunisasi (dropdown), keterangan                      |
| Tabel riwayat imunisasi     | Riwayat per anak, search, filter                                                           |
| Edit & hapus                | Edit data, hapus dengan konfirmasi                                                         |
| Validasi                    | Cek duplikasi (imunisasi yang sama tidak bisa dicatat dua kali), validasi urutan imunisasi |

---

### 7. Modul Vitamin

| Sub-item              | Detail                                                                                                   |
| --------------------- | -------------------------------------------------------------------------------------------------------- |
| Form input vitamin    | Pilih anak, tanggal pemberian, jenis vitamin (Kapsul Biru / Kapsul Merah), keterangan                    |
| Tabel riwayat vitamin | Riwayat per anak, search, filter                                                                         |
| Edit & hapus          | Edit data, hapus dengan konfirmasi                                                                       |
| Validasi              | Kapsul Biru untuk usia 6–11 bulan, Kapsul Merah untuk 12–59 bulan (validasi otomatis berdasarkan usia) |

**Catatan:** Vitamin A diberikan 2x setahun (Februari & Agustus). Sistem bisa memvalidasi jadwal ini.

---

### 8. Modul Laporan

| Sub-item                  | Detail                                                                       |
| ------------------------- | ---------------------------------------------------------------------------- |
| Filter periode            | Filter bulan/tahun untuk semua rekap                                         |
| Rekap data ibu            | Jumlah ibu terdaftar                                                         |
| Rekap data anak           | Jumlah anak aktif, distribusi usia, distribusi jenis kelamin                 |
| Rekap penimbangan         | Jumlah anak ditimbang, rata-rata BB/TB, anak yang tidak hadir                |
| Rekap status gizi         | Distribusi status gizi (Buruk/Kurang/Baik/Lebih), tren dari bulan sebelumnya |
| Rekap imunisasi & vitamin | Jumlah pemberian per jenis, coverage rate                                    |
| Export PDF                | Generate laporan dalam format PDF, tombol cetak                              |

---

---

# Pertanyaan untuk Client

> Daftar pertanyaan yang **harus dijawab** sebelum development dimulai.

---

## A. Hak Akses & Role Pengguna

| #  | Pertanyaan                                                                                                                | Catatan                                                           | Jawaban                                                          |
| -- | ------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- | ---------------------------------------------------------------- |
| A1 | Apa perbedaan konkret antara**Admin** dan **Kader**? Misalnya: Kader hanya input data, Admin bisa hapus data? | Saat ini PRD menyebutkan 2 role tapi tidak menjelaskan batasannya | Admin dan kader adalah sama, keduanya punya full akses           |
| A2 | Apakah Kader**boleh menghapus** data? Atau hanya bisa tambah & edit?                                                | Ini penting untuk menghindari kehilangan data akibat kesalahan    | Boleh, kader diizinkan menambah, mengubah, hingga menghapus data |

---

## B. Penimbangan & Status Gizi

| #  | Pertanyaan                                                                                                       | Catatan                                                    | Jawaban                                                                         |
| -- | ---------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------- | ------------------------------------------------------------------------------- |
| B1 | Selain Berat Badan dan Tinggi Badan, apakah perlu mencatat**parameter lain**? Contoh: Lingkar Kepala, LILA | Lingkar kepala biasanya dicatat untuk bayi 0–2 tahun      | Ya, tambahkan parameter Lingkar Kepala (cm) dan LILA (cm) pada form penimbangan |
| B2 | Standar penentuan status gizi yang digunakan:**WHO** atau **Kemenkes**?                              | Keduanya punya tabel z-score yang sedikit berbeda          | Menggunakan standar Kemenkes                                                    |
| B3 | Apakah penimbangan dilakukan**setiap bulan** sesuai jadwal posyandu, atau bisa kapan saja?                 | Menentukan apakah perlu validasi "1 penimbangan per bulan" | Bisa kapan saja (fleksibel)                                                     |

---

## C. Imunisasi

| #  | Pertanyaan                                                                                                                            | Catatan                          | Jawaban                                                                     |
| -- | ------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------- | --------------------------------------------------------------------------- |
| C1 | Apakah jenis imunisasi mengikuti**program imunisasi nasional** lengkap? (BCG, DPT-HB-Hib 1–4, Polio 0–4, Campak/MR 1–2, dll) | Menentukan master data imunisasi | Iya, mengikuti program nasional (seperti BCG, DPT, Polio, Campak/MR, dll.)  |
