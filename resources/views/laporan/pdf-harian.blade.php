<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pelayanan Harian — {{ $tanggalLabel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1e293b; line-height: 1.4; padding: 16px 20px; }
        
        /* Kop Surat */
        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border-bottom: 2px solid #0D9488; padding-bottom: 8px; }
        .kop-table td { vertical-align: middle; border: none; }
        .kop-title { font-size: 14px; font-weight: bold; color: #0D9488; text-transform: uppercase; letter-spacing: 0.5px; }
        .kop-subtitle { font-size: 10px; color: #475569; font-weight: bold; margin-top: 1px; }
        .kop-desc { font-size: 8.5px; color: #64748b; margin-top: 2px; }
        .kop-meta { text-align: right; font-size: 8.5px; color: #64748b; line-height: 1.4; }
        
        /* Judul Dokumen */
        .doc-title-box { text-align: center; margin-bottom: 14px; background: #F0FDFA; border: 1px solid #99F6E4; border-radius: 6px; padding: 8px; }
        .doc-title { font-size: 13px; font-weight: bold; color: #0f766e; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-date { font-size: 10px; color: #334155; font-weight: 600; margin-top: 2px; }
        
        /* Ringkasan Statistik */
        .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; table-layout: fixed; }
        .stats-table td { border: none; padding: 0 4px; vertical-align: top; }
        .stats-table td:first-child { padding-left: 0; }
        .stats-table td:last-child { padding-right: 0; }
        
        .stat-card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; text-align: center; background: #ffffff; }
        .stat-card .val { font-size: 16px; font-weight: bold; color: #0D9488; line-height: 1.2; }
        .stat-card .lbl { font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-top: 2px; }
        .stat-card .sub { font-size: 7.5px; color: #94a3b8; margin-top: 1px; }
        
        /* Section Titles */
        .section-header { font-size: 10.5px; font-weight: bold; color: #0D9488; border-bottom: 1px solid #CCFBF1; padding-bottom: 4px; margin-bottom: 6px; margin-top: 10px; }
        
        /* Data Tables */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5px; }
        table.data-table th { background: #0D9488; color: #ffffff; text-align: left; padding: 5px 6px; font-size: 8px; text-transform: uppercase; letter-spacing: 0.3px; border: 1px solid #0f766e; }
        table.data-table td { padding: 4.5px 6px; border: 1px solid #E2E8F0; vertical-align: middle; }
        table.data-table tr:nth-child(even) td { background-color: #F8FAFC; }
        
        .badge { display: inline-block; padding: 1.5px 5px; border-radius: 10px; font-size: 7.5px; font-weight: bold; text-transform: capitalize; }
        .badge-baik    { background: #DCFCE7; color: #166534; }
        .badge-kurang  { background: #FEF3C7; color: #92400E; }
        .badge-buruk   { background: #FEE2E2; color: #991B1B; }
        .badge-lebih   { background: #DBEAFE; color: #1E40AF; }
        
        .badge-normal        { background: #DCFCE7; color: #166534; }
        .badge-pendek        { background: #FEF3C7; color: #92400E; }
        .badge-sangat_pendek { background: #FEE2E2; color: #991B1B; }
        .badge-tinggi        { background: #DBEAFE; color: #1E40AF; }

        .tag-vax { background: #E0E7FF; color: #3730A3; padding: 1.5px 4px; border-radius: 4px; font-size: 7.5px; display: inline-block; margin: 1px; }
        .tag-vit { background: #FCE7F3; color: #9D174D; padding: 1.5px 4px; border-radius: 4px; font-size: 7.5px; display: inline-block; margin: 1px; }
        
        /* Tanda Tangan */
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 18px; page-break-inside: avoid; }
        .signature-table td { border: none; vertical-align: top; width: 50%; text-align: center; font-size: 9px; line-height: 1.5; }
        
        .footer { font-size: 7.5px; color: #94a3b8; text-align: right; margin-top: 10px; border-top: 1px dashed #E2E8F0; padding-top: 4px; }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td style="width: 70%;">
                <div class="kop-title">SISTEM INFORMASI POSYANDU TERPADU</div>
                <div class="kop-subtitle">LAYANAN KESEHATAN IBU DAN ANAK (KIA) — STANDAR KEMENKES RI</div>
                <div class="kop-desc">Pos Pelayanan Terpadu &nbsp;|&nbsp; Rekapitulasi Pelayanan Terintegrasi Satu Pintu</div>
            </td>
            <td style="width: 30%;">
                <div class="kop-meta">
                    <strong>Dokumen Resmi Posyandu</strong><br>
                    Waktu Cetak: {{ now()->format('d/m/Y H:i') }} WIB<br>
                    Petugas: {{ auth()->user()->nama ?? 'Kader Posyandu' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- JUDUL LAPORAN --}}
    <div class="doc-title-box">
        <div class="doc-title">REGISTER &amp; LAPORAN PELAYANAN HARIAN POSYANDU</div>
        <div class="doc-date">Hari &amp; Tanggal Pelaksanaan: {{ $tanggalLabel }}</div>
    </div>

    {{-- RINGKASAN METRIK PELAYANAN HARI INI --}}
    <table class="stats-table">
        <tr>
            <td>
                <div class="stat-card">
                    <div class="val">{{ $pelayananHariIni->unique('anak_id')->count() }}</div>
                    <div class="lbl">Balita Ditimbang</div>
                    <div class="sub">Total sasaran anak hadir</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="val" style="color:#16A34A;">{{ $statusGizi['baik'] }}</div>
                    <div class="lbl">Gizi Baik (BB/U)</div>
                    <div class="sub">Kurang: {{ $statusGizi['kurang'] }} | Buruk: {{ $statusGizi['buruk'] }}</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="val" style="color:#0284C7;">{{ $statusStunting['normal'] }}</div>
                    <div class="lbl">Tinggi Normal (TB/U)</div>
                    <div class="sub">Pendek/Stunting: {{ $statusStunting['pendek'] + $statusStunting['sangat_pendek'] }}</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="val" style="color:#6366F1;">{{ $totalImunisasi }}</div>
                    <div class="lbl">Imunisasi Diberikan</div>
                    <div class="sub">Vaksinasi terlayani hari ini</div>
                </div>
            </td>
            <td>
                <div class="stat-card">
                    <div class="val" style="color:#D946EF;">{{ $totalVitamin }}</div>
                    <div class="lbl">Vitamin A Diberikan</div>
                    <div class="sub">Kapsul Biru &amp; Merah</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- TABEL UTAMA: REGISTER BALITA DILAYANI HARI INI --}}
    <div class="section-header">DAFTAR BALITA DILAYANI HARI INI (BUKU REGISTER PENIMBANGAN &amp; TINDAKAN)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 140px;">Nama Balita &amp; NIK</th>
                <th style="width: 25px; text-align: center;">JK</th>
                <th style="width: 85px;">Tgl Lahir / Usia</th>
                <th style="width: 130px;">Nama Ibu &amp; Alamat</th>
                <th style="width: 50px; text-align: center;">BB (kg)</th>
                <th style="width: 50px; text-align: center;">TB (cm)</th>
                <th style="width: 60px; text-align: center;">LK / LiLA</th>
                <th style="width: 85px; text-align: center;">Status Gizi (BB/U)</th>
                <th style="width: 80px; text-align: center;">Status TB/U</th>
                <th style="width: 95px;">Imunisasi Hari Ini</th>
                <th style="width: 80px;">Vitamin A</th>
                <th style="width: 50px; text-align: center;">Paraf</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelayananHariIni as $index => $p)
                @php
                    $anak = $p->anak;
                    $imunisasiList = $imunisasiHariIni->get($p->anak_id, collect());
                    $vitaminList = $vitaminHariIni->get($p->anak_id, collect());
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $anak->nama ?? '-' }}</strong>
                    </td>
                    <td style="text-align: center; font-weight: bold; color: {{ ($anak->jenis_kelamin ?? 'L') === 'L' ? '#2563EB' : '#DB2777' }};">
                        {{ $anak->jenis_kelamin ?? '-' }}
                    </td>
                    <td>
                        {{ $anak->tanggal_lahir ? $anak->tanggal_lahir->format('d/m/Y') : '-' }}<br>
                        <span style="color: #64748b; font-size: 7.5px;">({{ $anak->usia ?? '-' }})</span>
                    </td>
                    <td>
                        <strong>{{ $anak->ibu->nama ?? '-' }}</strong><br>
                        <span style="color: #64748b; font-size: 7.5px;">{{ $anak->ibu->alamat ?? '-' }}</span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $p->berat_badan }}<br>
                        <span style="font-size: 7.5px; color: #64748b;">(Z: {{ $p->zscore_bbu }})</span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        {{ $p->tinggi_badan }}<br>
                        <span style="font-size: 7.5px; color: #64748b;">(Z: {{ $p->zscore_tbu }})</span>
                    </td>
                    <td style="text-align: center;">
                        LK: {{ $p->lingkar_kepala ? $p->lingkar_kepala . ' cm' : '-' }}<br>
                        LiLA: {{ $p->lila ? $p->lila . ' cm' : '-' }}
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-{{ $p->status_bbu }}">
                            {{ $p->label_status_bbu }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-{{ $p->status_tbu }}">
                            {{ $p->label_status_tbu }}
                        </span>
                    </td>
                    <td>
                        @if($imunisasiList->isNotEmpty())
                            @foreach($imunisasiList as $im)
                                <span class="tag-vax">{{ $im->jenisImunisasi->nama ?? 'Imunisasi' }}</span>
                            @endforeach
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($vitaminList->isNotEmpty())
                            @foreach($vitaminList as $vit)
                                <span class="tag-vit">
                                    {{ $vit->jenis_vitamin === 'kapsul_biru' ? 'Kapsul Biru' : 'Kapsul Merah' }}
                                </span>
                            @endforeach
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                    <td style="text-align: center; color: #cbd5e1;">
                        ✓
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align: center; padding: 20px; color: #94a3b8; font-style: italic;">
                        Tidak ada catatan pelayanan balita pada tanggal {{ $tanggalLabel }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TABEL PELAYANAN IBU HAMIL JIKA ADA --}}
    @if(isset($pemeriksaanIbuHariIni) && $pemeriksaanIbuHariIni->count() > 0)
        <div class="section-header">PELAYANAN KESEHATAN IBU HAMIL HARI INI (STANDAR BUKU KIA)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th style="width: 160px;">Nama Ibu Hamil &amp; NIK</th>
                    <th style="width: 80px; text-align: center;">Usia Hamil</th>
                    <th style="width: 80px; text-align: center;">Trimester</th>
                    <th style="width: 70px; text-align: center;">BB / Kenaikan</th>
                    <th style="width: 70px; text-align: center;">Tensi (mmHg)</th>
                    <th style="width: 60px; text-align: center;">LiLA (cm)</th>
                    <th style="width: 80px; text-align: center;">TFU / DJJ</th>
                    <th style="width: 70px; text-align: center;">Tablet Fe</th>
                    <th>Keluhan &amp; Catatan Medis</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pemeriksaanIbuHariIni as $idxIbu => $periksa)
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ $idxIbu + 1 }}</td>
                        <td>
                            <strong>{{ $periksa->kehamilan->ibu->nama ?? '-' }}</strong><br>
                            <span style="font-size: 7.5px; color: #64748b;">NIK: {{ $periksa->kehamilan->ibu->nik ?? '-' }}</span>
                        </td>
                        <td style="text-align: center;">{{ $periksa->usia_kehamilan_minggu }} Minggu</td>
                        <td style="text-align: center; font-weight: bold;">Trimester {{ $periksa->trimester }}</td>
                        <td style="text-align: center;">
                            {{ $periksa->berat_badan }} kg<br>
                            <span style="font-size: 7.5px; color: #64748b;">(+{{ $periksa->kenaikan_bb ?? 0 }} kg)</span>
                        </td>
                        <td style="text-align: center; font-weight: bold;">{{ $periksa->tekanan_darah }}</td>
                        <td style="text-align: center;">{{ $periksa->lila ? $periksa->lila . ' cm' : '-' }}</td>
                        <td style="text-align: center;">
                            TFU: {{ $periksa->tinggi_fundus ? $periksa->tinggi_fundus . ' cm' : '-' }}<br>
                            DJJ: {{ $periksa->djj ? $periksa->djj . ' bpm' : '-' }}
                        </td>
                        <td style="text-align: center;">{{ $periksa->tablet_fe ? $periksa->tablet_fe . ' butir' : '-' }}</td>
                        <td>{{ $periksa->keluhan ?: '-' }} &nbsp;|&nbsp; <em>{{ $periksa->tindakan_nasihat ?: 'Pemeriksaan rutin' }}</em></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- BLOK TANDA TANGAN PENGESAHAN --}}
    <table class="signature-table">
        <tr>
            <td>
                <p>Mengetahui,</p>
                <p><strong>Bidan Desa / Penanggung Jawab Wilayah</strong></p>
                <br><br><br><br>
                <p><u>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</u></p>
                <p style="font-size: 8px; color: #64748b;">NIP. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
            </td>
            <td>
                <p>Posyandu, {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                <p><strong>Kader Pelaksana Posyandu</strong></p>
                <br><br><br><br>
                <p><u>( {{ auth()->user()->nama ?? 'Kader Posyandu' }} )</u></p>
                <p style="font-size: 8px; color: #64748b;">Petugas Layanan Terpadu</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Informasi Posyandu &nbsp;|&nbsp; {{ $tanggalLabel }}
    </div>

</body>
</html>
