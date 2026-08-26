# Tech Spec — Sistem Informasi Posyandu Neiska

## Tech Stack

| Layer                   | Teknologi                        | Versi                |
| ----------------------- | -------------------------------- | -------------------- |
| **Backend**       | Laravel (PHP)                    | 13.x                 |
| **Frontend**      | Livewire + Blade                 | 4.x                  |
| **Database**      | SQLite                           | 3.x                  |
| **CSS Framework** | Tailwind CSS                     | 3.x (bawaan Laravel) |
| **Charting**      | Chart.js atau ApexCharts         | -                    |
| **PDF Export**    | DomPDF (barryvdh/laravel-dompdf) | -                    |
| **Auth**          | Laravel Breeze / built-in Auth   | -                    |

---

## Arsitektur Aplikasi

```
posyandu-neiska/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Ibu.php
│   │   ├── Anak.php
│   │   ├── Penimbangan.php
│   │   ├── JenisImunisasi.php
│   │   ├── Imunisasi.php
│   │   └── Vitamin.php
│   ├── Livewire/
│   │   ├── Dashboard.php
│   │   ├── DataIbu/
│   │   │   ├── Index.php
│   │   │   └── Form.php
│   │   ├── DataAnak/
│   │   │   ├── Index.php
│   │   │   └── Form.php
│   │   ├── Penimbangan/
│   │   │   ├── Index.php
│   │   │   ├── Form.php
│   │   │   └── GrafikKms.php
│   │   ├── Imunisasi/
│   │   │   ├── Index.php
│   │   │   └── Form.php
│   │   ├── Vitamin/
│   │   │   ├── Index.php
│   │   │   └── Form.php
│   │   └── Laporan/
│   │       └── Index.php
│   └── Services/
│       └── StatusGiziService.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   │   └── JenisImunisasiSeeder.php
│   └── database.sqlite
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php
│   ├── livewire/
│   │   ├── dashboard.blade.php
│   │   ├── data-ibu/
│   │   ├── data-anak/
│   │   ├── penimbangan/
│   │   ├── imunisasi/
│   │   ├── vitamin/
│   │   └── laporan/
│   └── components/
│       ├── sidebar.blade.php
│       ├── search-bar.blade.php
│       └── confirm-modal.blade.php
└── routes/
    └── web.php
```

---

## Pola Arsitektur

### Livewire Component Pattern

Setiap modul memiliki **2 komponen Livewire**:

| Komponen      | Fungsi                                                     |
| ------------- | ---------------------------------------------------------- |
| `Index.php` | Menampilkan tabel data, pencarian, pagination, tombol aksi |
| `Form.php`  | Form tambah & edit data (reusable untuk create/update)     |

### Service Layer

Logika bisnis yang kompleks dipisahkan ke `Services/`:

| Service               | Fungsi                                                                   |
| --------------------- | ------------------------------------------------------------------------ |
| `StatusGiziService` | Kalkulasi z-score dan penentuan status gizi berdasarkan standar Kemenkes |

---

## Routing

| Route                            | Method   | Komponen              | Keterangan                   |
| -------------------------------- | -------- | --------------------- | ---------------------------- |
| `/login`                       | GET/POST | Auth                  | Halaman login                |
| `/logout`                      | POST     | Auth                  | Logout                       |
| `/dashboard`                   | GET      | Dashboard             | Halaman utama setelah login  |
| `/data-ibu`                    | GET      | DataIbu\Index         | Daftar data ibu              |
| `/data-ibu/tambah`             | GET      | DataIbu\Form          | Form tambah ibu              |
| `/data-ibu/{id}/edit`          | GET      | DataIbu\Form          | Form edit ibu                |
| `/data-anak`                   | GET      | DataAnak\Index        | Daftar data anak             |
| `/data-anak/tambah`            | GET      | DataAnak\Form         | Form tambah anak             |
| `/data-anak/{id}/edit`         | GET      | DataAnak\Form         | Form edit anak               |
| `/penimbangan`                 | GET      | Penimbangan\Index     | Daftar & riwayat penimbangan |
| `/penimbangan/tambah`          | GET      | Penimbangan\Form      | Form input penimbangan       |
| `/penimbangan/{id}/edit`       | GET      | Penimbangan\Form      | Form edit penimbangan        |
| `/penimbangan/grafik/{anakId}` | GET      | Penimbangan\GrafikKms | Grafik KMS per anak          |
| `/imunisasi`                   | GET      | Imunisasi\Index       | Daftar & riwayat imunisasi   |
| `/imunisasi/tambah`            | GET      | Imunisasi\Form        | Form input imunisasi         |
| `/imunisasi/{id}/edit`         | GET      | Imunisasi\Form        | Form edit imunisasi          |
| `/vitamin`                     | GET      | Vitamin\Index         | Daftar & riwayat vitamin     |
| `/vitamin/tambah`              | GET      | Vitamin\Form          | Form input vitamin           |
| `/vitamin/{id}/edit`           | GET      | Vitamin\Form          | Form edit vitamin            |
| `/laporan`                     | GET      | Laporan\Index         | Halaman laporan              |
| `/laporan/export-pdf`          | GET      | -                     | Download laporan PDF         |

Semua route (kecuali `/login`) dilindungi middleware `auth`.

---

## Model & Relasi (Eloquent)

```php
// Ibu.php
class Ibu extends Model {
    public function anak(): HasMany;
}

// Anak.php
class Anak extends Model {
    public function ibu(): BelongsTo;
    public function penimbangan(): HasMany;
    public function imunisasi(): HasMany;
    public function vitamin(): HasMany;
    // Accessor
    public function getUsiaAttribute(): string; // hitung dari tanggal_lahir
}

// Penimbangan.php
class Penimbangan extends Model {
    public function anak(): BelongsTo;
}

// Imunisasi.php
class Imunisasi extends Model {
    public function anak(): BelongsTo;
    public function jenisImunisasi(): BelongsTo;
}

// JenisImunisasi.php
class JenisImunisasi extends Model {
    public function imunisasi(): HasMany;
}

// Vitamin.php
class Vitamin extends Model {
    public function anak(): BelongsTo;
}
```

---

## Environment & Deployment

| Item                    | Detail                                 |
| ----------------------- | -------------------------------------- |
| **PHP**           | >= 8.3                                 |
| **Node.js**       | >= 18 (untuk build asset Tailwind)     |
| **Database file** | `database/database.sqlite`           |
| **Dev server**    | `php artisan serve` (localhost:8000) |
| **Asset build**   | `npm run dev` (Vite)                 |
