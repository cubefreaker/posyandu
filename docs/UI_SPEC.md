# 🎨 Spesifikasi UI/UX — Sistem Informasi Posyandu Neiska

## 1. Design Philosophy

### Prinsip Desain

| Prinsip | Rasional |
| ------- | -------- |
| **Warm & Trustworthy** | Posyandu adalah layanan kesehatan komunitas untuk ibu dan anak — desain harus terasa hangat, ramah, dan terpercaya, bukan klinis/steril |
| **Clean & Scannable** | Kader posyandu bukan power-user IT. UI harus bersih, mudah dipindai, dengan hierarki visual yang jelas |
| **Data-Dense but Approachable** | Banyak tabel dan form — desain harus menyajikan data secara padat namun tetap tidak overwhelming |
| **Mobile-Conscious** | Kader sering menggunakan HP saat di lapangan. Layout harus responsive dan touch-friendly |

### Design Style: **Soft Modern / Friendly Dashboard**

Menghindari corporate-sterile look. Menggunakan rounded corners, soft shadows, whitespace yang generous, dan warna-warna yang menenangkan namun profesional.

---

## 2. Color Scheme

### Rasional Pemilihan Warna

Warna dipilih berdasarkan konteks:
- **Teal/Cyan** sebagai primary — asosiasi kesehatan, ketenangan, kepercayaan (digunakan luas di branding kesehatan Indonesia seperti Puskesmas, BPJS)
- **Warm Coral** sebagai accent — kehangatan, keibuan, perhatian terhadap anak
- **Semantic colors** yang jelas untuk status gizi — ini kritikal karena kader harus bisa langsung membaca status gizi anak

### 2.1 Core Palette

```
┌─────────────────────────────────────────────────────┐
│  PRIMARY (Teal)                                     │
│  ┌────────┬────────┬────────┬────────┬────────┐     │
│  │  50    │  100   │  500   │  600   │  900   │     │
│  │#F0FDFA │#CCFBF1 │#14B8A6 │#0D9488 │#134E4A │     │
│  │ bg     │ hover  │ main   │ dark   │ text   │     │
│  └────────┴────────┴────────┴────────┴────────┘     │
│                                                     │
│  SECONDARY (Warm Coral)                             │
│  ┌────────┬────────┬────────┬────────┐              │
│  │  50    │  100   │  500   │  600   │              │
│  │#FFF1F2 │#FFE4E6 │#F43F5E │#E11D48 │              │
│  │ bg     │ hover  │ main   │ dark   │              │
│  └────────┴────────┴────────┴────────┘              │
│                                                     │
│  NEUTRAL (Slate)                                    │
│  ┌────────┬────────┬────────┬────────┬────────┐     │
│  │  50    │  100   │  300   │  600   │  900   │     │
│  │#F8FAFC │#F1F5F9 │#CBD5E1 │#475569 │#0F172A │     │
│  │ page   │ card   │ border │ body   │ heading│     │
│  └────────┴────────┴────────┴────────┴────────┘     │
└─────────────────────────────────────────────────────┘
```

### 2.2 Semantic Colors — Status Gizi (KRITIKAL)

Ini adalah elemen paling penting secara visual karena kader harus bisa membaca status gizi anak **dalam hitungan detik**.

| Status | Background | Text/Border | Hex (bg / text) | Ikon |
| ------ | ---------- | ----------- | --------------- | ---- |
| 🔴 **Gizi Buruk** | Merah muda lembut | Merah tua | `#FEF2F2` / `#DC2626` | ⚠️ Triangle alert |
| 🟠 **Gizi Kurang** | Amber muda | Amber tua | `#FFFBEB` / `#D97706` | ⚡ Warning |
| 🟢 **Gizi Baik** | Hijau muda | Hijau tua | `#F0FDF4` / `#16A34A` | ✅ Check circle |
| 🔵 **Gizi Lebih** | Biru muda | Biru tua | `#EFF6FF` / `#2563EB` | ℹ️ Info circle |

### 2.3 Functional Colors

| Fungsi | Warna | Hex | Penggunaan |
| ------ | ----- | --- | ---------- |
| Success | Green | `#16A34A` | Notifikasi berhasil, data tersimpan |
| Warning | Amber | `#D97706` | Validasi anomali BB/TB, peringatan |
| Error | Red | `#DC2626` | Error form, gagal login, hapus data |
| Info | Blue | `#2563EB` | Tooltip, informasi tambahan |

### 2.4 Grafik KMS — Zona Warna

Untuk kurva pertumbuhan (KMS chart) menggunakan warna zona dengan opacity:

| Zona | Warna Fill | Opacity | Border |
| ---- | ---------- | ------- | ------ |
| Gizi Buruk (< -3 SD) | `#DC2626` | 10% | `#DC2626` 40% |
| Gizi Kurang (-3 s/d -2 SD) | `#D97706` | 10% | `#D97706` 40% |
| Gizi Baik (-2 s/d +2 SD) | `#16A34A` | 8% | `#16A34A` 40% |
| Gizi Lebih (> +2 SD) | `#2563EB` | 10% | `#2563EB` 40% |
| Garis data anak | `#14B8A6` | 100% | — |

---

## 3. Typography

### Font Family

| Penggunaan | Font | Fallback | Rasional |
| ---------- | ---- | -------- | -------- |
| **Heading & UI** | **Plus Jakarta Sans** | `system-ui, sans-serif` | Font modern Indonesia-origin, excellent readability, geometric-humanist |
| **Body & Data** | **Inter** | `system-ui, sans-serif` | Optimal untuk data tabular, angka proporsional, sangat readable di small size |
| **Monospace** (NIK, angka) | **JetBrains Mono** | `monospace` | Untuk menampilkan NIK 16 digit agar mudah diverifikasi |

### Type Scale

```
Heading 1:   28px / 700  — Judul halaman (Dashboard, Data Ibu, dll)
Heading 2:   22px / 600  — Sub-section
Heading 3:   18px / 600  — Card title, form section
Body:        14px / 400  — Teks umum, paragraf
Body Small:  13px / 400  — Label form, helper text
Caption:     12px / 400  — Timestamp, metadata, keterangan tabel
Data Large:  32px / 700  — Angka di summary card (total ibu: 128)
Data Medium: 20px / 600  — Angka di sub-stat
Mono:        14px / 400  — NIK display, kode
```

### Line Height

- Heading: `1.3`
- Body: `1.6`
- Data/Numbers: `1.2`
- Table rows: `1.4`

---

## 4. Spacing & Layout System

### Base Unit: `4px`

```
xs:    4px   — Gap antar ikon dan teks inline
sm:    8px   — Padding dalam badge, gap antar related items
md:   12px   — Padding form input
base: 16px   — Gap standar antar elemen
lg:   20px   — Padding card internal
xl:   24px   — Gap antar section
2xl:  32px   — Margin antar major section
3xl:  48px   — Page padding top/bottom
```

### Layout Grid

| Breakpoint | Lebar | Kolom | Sidebar |
| ---------- | ----- | ----- | ------- |
| Mobile | < 768px | 1 | Hidden (hamburger) |
| Tablet | 768–1024px | 2 | Collapsed (icon only) |
| Desktop | > 1024px | 3–4 | Expanded (icon + label) |

### Page Structure

```
┌──────────────────────────────────────────────────┐
│  Top Bar (56px)                                  │
│  ┌─────┬─────────────────────────────────────┐   │
│  │Logo │  Page Title          🔔  👤 Kader   │   │
│  └─────┴─────────────────────────────────────┘   │
├──────┬───────────────────────────────────────────┤
│      │                                           │
│  S   │  Content Area                             │
│  i   │  ┌─────────────────────────────────────┐  │
│  d   │  │  Page Header + Actions              │  │
│  e   │  ├─────────────────────────────────────┤  │
│  b   │  │                                     │  │
│  a   │  │  Main Content                       │  │
│  r   │  │  (Tables / Forms / Charts)          │  │
│      │  │                                     │  │
│ 240  │  │                                     │  │
│  px  │  └─────────────────────────────────────┘  │
│      │                                           │
└──────┴───────────────────────────────────────────┘
```

---

## 5. Component Specifications

### 5.1 Summary Cards (Dashboard)

```
┌────────────────────────────┐
│  👩 Total Ibu              │  ← Icon + Label (caption, slate-600)
│                            │
│     128                    │  ← Number (data-large, primary-600)
│                            │
│  ↑ 3 dari bulan lalu       │  ← Trend (caption, green/red)
└────────────────────────────┘

Specs:
- Border radius: 16px
- Background: white
- Shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04)
- Padding: 24px
- Border-left: 4px solid primary-500 (accent strip)
- Hover: shadow meningkat, slight translateY(-2px)
- Transition: all 200ms ease
```

### 5.2 Data Tables

```
Specs:
- Header row: bg slate-50, font-weight 600, font-size 13px, uppercase, tracking wider
- Body row: bg white, hover bg primary-50
- Alternating rows: TIDAK (terlalu ramai untuk data kesehatan)
- Row height: 48px (touch-friendly)
- Border: hanya horizontal, slate-100
- Cell padding: 12px 16px
- Border radius table container: 12px
- Status gizi cell: menggunakan badge berwarna (lihat 5.5)
```

### 5.3 Form Inputs

```
┌─ Label ──────────────────────────────┐
│  ┌────────────────────────────────┐  │
│  │  Placeholder text              │  │
│  └────────────────────────────────┘  │
│  Helper text (optional)              │
└──────────────────────────────────────┘

Specs:
- Input height: 44px (touch-friendly)
- Border radius: 10px
- Border: 1.5px solid slate-300
- Focus border: 2px solid primary-500
- Focus ring: 0 0 0 3px primary-500/20
- Background: white
- Label: font-weight 500, font-size 13px, margin-bottom 6px
- Error state: border red-500, helper text red
- Disabled: bg slate-50, opacity 0.7
- Transition: border-color 150ms ease, box-shadow 150ms ease
```

### 5.4 Buttons

| Variant | Background | Text | Border | Penggunaan |
| ------- | ---------- | ---- | ------ | ---------- |
| **Primary** | `primary-500` → `primary-600` hover | White | — | Simpan, Submit, Tambah Data |
| **Secondary** | White | `primary-600` | `primary-300` | Cancel, Batal, Filter |
| **Danger** | `red-50` → `red-100` hover | `red-600` | `red-200` | Hapus data |
| **Ghost** | Transparent → `slate-50` hover | `slate-600` | — | Navigasi, close |

```
Specs:
- Height: 40px (default), 36px (small), 44px (large)
- Border radius: 10px
- Padding: 0 20px
- Font weight: 600
- Font size: 14px
- Transition: all 150ms ease
- Active: scale(0.97)
- Icon + text gap: 8px
```

### 5.5 Status Badge (Gizi)

```
┌──────────────────┐
│  ● Gizi Baik     │
└──────────────────┘

Specs:
- Border radius: 20px (pill)
- Padding: 4px 12px
- Font size: 12px
- Font weight: 600
- Dot indicator: 6px circle sebelum teks
- Warna sesuai semantic status gizi (section 2.2)
```

### 5.6 Sidebar Navigation

```
┌──────────────────────────┐
│  🏥 Posyandu Neiska      │  ← Logo area (64px height)
│─────────────────────────── │
│                            │
│  📊  Dashboard             │  ← Active: bg primary-50, 
│                            │     text primary-700,
│  👩  Data Ibu              │     left-border 3px primary-500
│  👶  Data Anak             │
│  ⚖️   Penimbangan          │  ← Default: text slate-600
│  💉  Imunisasi             │
│  💊  Vitamin               │  ← Hover: bg slate-50
│  📄  Laporan               │
│                            │
│─────────────────────────── │
│  👤  Nama Kader            │  ← User info area
│  🚪  Logout                │
└──────────────────────────┘

Specs:
- Width: 240px (expanded), 72px (collapsed)
- Background: white
- Border-right: 1px solid slate-200
- Menu item height: 44px
- Menu item border-radius: 8px
- Menu item margin-x: 8px
- Transition: width 250ms ease
```

### 5.7 Modal / Dialog (Konfirmasi Hapus)

```
┌──────────────────────────────────────┐
│                                      │
│       ⚠️                             │
│  Hapus Data Anak?                    │
│                                      │
│  Data "Ahmad Pratama" akan dihapus   │
│  secara permanen. Tindakan ini       │
│  tidak bisa dibatalkan.              │
│                                      │
│        [ Batal ]  [ Hapus ]          │
│                                      │
└──────────────────────────────────────┘

Specs:
- Overlay: black/50 (50% opacity)
- Card max-width: 440px
- Border radius: 16px
- Padding: 32px
- Shadow: 0 25px 50px rgba(0,0,0,0.15)
- Animation: fadeIn + scaleUp from 95%
```

### 5.8 Dropdown + Search (Pilih Anak / Pilih Ibu)

```
┌────────────────────────────────────┐
│  🔍 Cari nama anak...             │  ← Search input (sticky top)
├────────────────────────────────────┤
│  Ahmad Pratama — Ibu: Siti        │  ← Option row
│  Budi Santoso — Ibu: Ani          │
│  Citra Dewi — Ibu: Rina           │
│  ▼ tampilkan lebih banyak...      │
└────────────────────────────────────┘

Specs:
- Max height: 280px (scrollable)
- Option row height: 40px
- Hover: bg primary-50
- Selected: bg primary-100, check icon
- Border radius dropdown: 12px
- Shadow: 0 10px 25px rgba(0,0,0,0.1)
```

---

## 6. Micro-Interactions & Animations

| Elemen | Animasi | Duration | Easing |
| ------ | ------- | -------- | ------ |
| Page transition | Fade in + slide up 8px | 250ms | ease-out |
| Card hover | Slight lift (translateY -2px) + shadow increase | 200ms | ease |
| Button press | Scale down to 0.97 | 100ms | ease |
| Modal appear | Overlay fade in + card scale from 0.95 | 200ms | ease-out |
| Toast notification | Slide in from top-right | 300ms | spring |
| Table row hover | Background color transition | 150ms | ease |
| Sidebar collapse | Width transition | 250ms | ease |
| Form error shake | Horizontal shake 3x | 300ms | ease |
| Status badge | Subtle pulse on new data | 500ms | ease-in-out |
| Chart data point | Scale up on hover | 150ms | ease |

---

## 7. Iconography

### Style: **Lucide Icons** (Recommended)

- Stroke width: 1.75px
- Size: 20px (default), 16px (small/inline), 24px (navigation)
- Warna mengikuti konteks teks

### Icon Map

| Konteks | Ikon |
| ------- | ---- |
| Dashboard | `LayoutDashboard` |
| Data Ibu | `Users` |
| Data Anak | `Baby` |
| Penimbangan | `Scale` |
| Imunisasi | `Syringe` |
| Vitamin | `Pill` |
| Laporan | `FileText` |
| Tambah | `Plus` |
| Edit | `Pencil` |
| Hapus | `Trash2` |
| Search | `Search` |
| Filter | `SlidersHorizontal` |
| Export PDF | `FileDown` |
| Logout | `LogOut` |
| Profil | `UserCircle` |
| Calendar | `Calendar` |
| Alert | `AlertTriangle` |
| Success | `CheckCircle2` |
| Trend up | `TrendingUp` |
| Trend down | `TrendingDown` |

---

## 8. Border Radius System

| Elemen | Radius | Rasional |
| ------ | ------ | -------- |
| Button | 10px | Rounded tapi tetap tegas |
| Input | 10px | Konsisten dengan button |
| Card | 16px | Lembut, modern |
| Badge | 20px (pill) | Supaya terasa sebagai tag/label |
| Modal | 16px | Konsisten dengan card |
| Table container | 12px | Sedikit lebih kecil dari card |
| Avatar | 50% (circle) | Standar avatar |
| Sidebar menu item | 8px | Subtle rounding |

---

## 9. Shadow System

```css
--shadow-xs:  0 1px 2px rgba(0, 0, 0, 0.05);
--shadow-sm:  0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
--shadow-md:  0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.04);
--shadow-lg:  0 10px 15px rgba(0, 0, 0, 0.08), 0 4px 6px rgba(0, 0, 0, 0.04);
--shadow-xl:  0 20px 25px rgba(0, 0, 0, 0.10), 0 10px 10px rgba(0, 0, 0, 0.04);
```

| Penggunaan | Shadow |
| ---------- | ------ |
| Card default | `--shadow-sm` |
| Card hover | `--shadow-md` |
| Dropdown menu | `--shadow-lg` |
| Modal | `--shadow-xl` |
| Table | `--shadow-xs` |
| Sidebar | `--shadow-xs` (right edge only) |

---

## 10. Responsive Behavior

### Mobile Adaptations (< 768px)

| Elemen | Desktop | Mobile |
| ------ | ------- | ------ |
| Sidebar | Fixed, 240px | Hidden, slide-in overlay dari kiri |
| Tabel | Full columns | Horizontal scroll atau card-view |
| Summary cards | 4 kolom grid | 2 kolom grid, stacked |
| Form layout | 2 kolom | 1 kolom full-width |
| Button | Inline | Full-width stacked |
| Grafik KMS | Full width | Full width, scroll horizontal |
| Modal | Centered | Bottom sheet (slide up dari bawah) |

### Touch Targets

- Minimum touch target: **44px × 44px** (Apple HIG standard)
- Berlaku untuk: button, table row, menu item, dropdown option, checkbox, icon button

---

## 11. Empty States & Loading

### Empty State

```
┌────────────────────────────────────┐
│                                    │
│         📋                         │  ← Ikon besar (48px), slate-300
│                                    │
│   Belum ada data anak              │  ← Heading 3, slate-700
│   Mulai dengan menambahkan         │  ← Body, slate-500
│   data anak pertama                │
│                                    │
│      [ + Tambah Anak ]             │  ← Primary button
│                                    │
└────────────────────────────────────┘
```

### Loading States

- **Skeleton loading** untuk tabel dan card (bukan spinner)
- Warna skeleton: `slate-200` pulse ke `slate-100`
- Animasi pulse: 1.5s infinite ease-in-out

### Toast Notifications

```
┌─────────────────────────────────────┐
│  ✅  Data penimbangan berhasil      │
│     disimpan                        │
└─────────────────────────────────────┘

Position: top-right, fixed
Duration: 4 detik lalu auto-dismiss
Border-radius: 12px
Shadow: --shadow-lg
```

---

## 12. CSS Custom Properties (Design Tokens)

```css
:root {
  /* Primary - Teal */
  --color-primary-50:  #F0FDFA;
  --color-primary-100: #CCFBF1;
  --color-primary-200: #99F6E4;
  --color-primary-300: #5EEAD4;
  --color-primary-400: #2DD4BF;
  --color-primary-500: #14B8A6;
  --color-primary-600: #0D9488;
  --color-primary-700: #0F766E;
  --color-primary-800: #115E59;
  --color-primary-900: #134E4A;

  /* Secondary - Warm Coral/Rose */
  --color-secondary-50:  #FFF1F2;
  --color-secondary-100: #FFE4E6;
  --color-secondary-500: #F43F5E;
  --color-secondary-600: #E11D48;

  /* Neutral - Slate */
  --color-slate-50:  #F8FAFC;
  --color-slate-100: #F1F5F9;
  --color-slate-200: #E2E8F0;
  --color-slate-300: #CBD5E1;
  --color-slate-400: #94A3B8;
  --color-slate-500: #64748B;
  --color-slate-600: #475569;
  --color-slate-700: #334155;
  --color-slate-800: #1E293B;
  --color-slate-900: #0F172A;

  /* Semantic - Status Gizi */
  --color-gizi-buruk-bg:   #FEF2F2;
  --color-gizi-buruk-text: #DC2626;
  --color-gizi-kurang-bg:   #FFFBEB;
  --color-gizi-kurang-text: #D97706;
  --color-gizi-baik-bg:     #F0FDF4;
  --color-gizi-baik-text:   #16A34A;
  --color-gizi-lebih-bg:    #EFF6FF;
  --color-gizi-lebih-text:  #2563EB;

  /* Functional */
  --color-success: #16A34A;
  --color-warning: #D97706;
  --color-error:   #DC2626;
  --color-info:    #2563EB;

  /* Typography */
  --font-heading: 'Plus Jakarta Sans', system-ui, sans-serif;
  --font-body:    'Inter', system-ui, sans-serif;
  --font-mono:    'JetBrains Mono', monospace;

  /* Spacing */
  --space-xs:  4px;
  --space-sm:  8px;
  --space-md:  12px;
  --space-base: 16px;
  --space-lg:  20px;
  --space-xl:  24px;
  --space-2xl: 32px;
  --space-3xl: 48px;

  /* Border Radius */
  --radius-sm:   8px;
  --radius-md:   10px;
  --radius-lg:   12px;
  --radius-xl:   16px;
  --radius-pill:  9999px;
  --radius-full:  50%;

  /* Shadows */
  --shadow-xs:  0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-sm:  0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
  --shadow-md:  0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.04);
  --shadow-lg:  0 10px 15px rgba(0, 0, 0, 0.08), 0 4px 6px rgba(0, 0, 0, 0.04);
  --shadow-xl:  0 20px 25px rgba(0, 0, 0, 0.10), 0 10px 10px rgba(0, 0, 0, 0.04);

  /* Transitions */
  --transition-fast:   150ms ease;
  --transition-base:   200ms ease;
  --transition-slow:   300ms ease;

  /* Layout */
  --sidebar-width:          240px;
  --sidebar-width-collapsed: 72px;
  --topbar-height:           56px;
  --content-max-width:       1200px;
}
```

---

## 13. Ringkasan Visual Identity

| Aspek | Keputusan |
| ----- | --------- |
| **Mood** | Hangat, terpercaya, profesional tapi ramah |
| **Style** | Soft Modern Dashboard |
| **Primary Color** | Teal `#14B8A6` — kesehatan + ketenangan |
| **Accent Color** | Warm Coral `#F43F5E` — keibuan + perhatian |
| **Neutral** | Slate — clean, tidak terlalu abu |
| **Typography** | Plus Jakarta Sans (heading) + Inter (body) |
| **Icons** | Lucide, 1.75px stroke |
| **Corners** | Generously rounded (10–16px) |
| **Shadows** | Soft, multi-layer, subtle |
| **Density** | Comfortable — bukan compact, bukan spacious |
| **Dark Mode** | Tidak diperlukan (konteks penggunaan lapangan, siang hari) |
