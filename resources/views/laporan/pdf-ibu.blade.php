<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan {{ config('app.name') }} — Ibu {{ $ibu->nama }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
        .header { background: #0D9488; color: white; padding: 20px 24px; margin-bottom: 24px; }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header p { font-size: 12px; opacity: 0.85; margin-top: 2px; }
        .content { padding: 0 24px 24px; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title { font-size: 13px; font-weight: bold; color: #0D9488; border-bottom: 1.5px solid #99F6E4; padding-bottom: 6px; margin-bottom: 10px; }
        
        .profil-box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 24px; background: #f8fafc; }
        .profil-item { margin-bottom: 8px; }
        .profil-label { font-weight: bold; color: #64748b; width: 100px; display: inline-block; }
        .profil-value { color: #334155; }
        
        /* Data Table */
        table.data-table { width: 100%; border-collapse: collapse; page-break-inside: avoid; }
        table.data-table th { background: #F0FDFA; text-align: left; padding: 8px 10px; font-size: 10px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1.5px solid #99F6E4; }
        table.data-table td { padding: 8px 10px; border-bottom: 1px solid #F1F5F9; font-size: 11px; vertical-align: top; }
        table.data-table tr:nth-child(even) td { background-color: #F8FAFC; }
        
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .badge-buruk   { background: #FEF2F2; color: #DC2626; }
        .badge-kurang  { background: #FFFBEB; color: #D97706; }
        .badge-baik    { background: #F0FDF4; color: #16A34A; }
        .badge-lebih   { background: #EFF6FF; color: #2563EB; }
        
        .footer { border-top: 1px solid #e2e8f0; margin-top: 24px; padding-top: 12px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Kegiatan {{ config('app.name') }} — Per Ibu</h1>
        <p>Periode: {{ $periodeLabel }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="content">
        <div class="profil-box">
            <div class="profil-item">
                <span class="profil-label">Nama Ibu</span>
                <span class="profil-value">: {{ $ibu->nama }}</span>
            </div>
            <div class="profil-item">
                <span class="profil-label">NIK</span>
                <span class="profil-value">: {{ $ibu->nik ?? '-' }}</span>
            </div>
            <div class="profil-item">
                <span class="profil-label">Alamat</span>
                <span class="profil-value">: {{ $ibu->alamat ?? '-' }}</span>
            </div>
            <div class="profil-item">
                <span class="profil-label">Nomor Telepon</span>
                <span class="profil-value">: {{ $ibu->no_hp ?? '-' }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Daftar Anak & Status Terakhir (Periode Ini)</div>
            @if($ibu->anak->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Anak</th>
                            <th>Jenis Kelamin</th>
                            <th>Usia Saat Ini</th>
                            <th>Status Gizi (BB/U) Terakhir</th>
                            <th>Imunisasi Diberikan (Periode Ini)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ibu->anak as $index => $anak)
                            @php
                                $penimbanganTerakhir = $anak->penimbangan->first();
                                $statusBadge = '-';
                                if ($penimbanganTerakhir) {
                                    $s = $penimbanganTerakhir->status_bbu;
                                    if ($s == 'buruk') $statusBadge = '<span class="badge badge-buruk">Gizi Buruk</span>';
                                    elseif ($s == 'kurang') $statusBadge = '<span class="badge badge-kurang">Gizi Kurang</span>';
                                    elseif ($s == 'baik') $statusBadge = '<span class="badge badge-baik">Gizi Baik</span>';
                                    elseif ($s == 'lebih') $statusBadge = '<span class="badge badge-lebih">Gizi Lebih</span>';
                                }

                                $imunisasiText = '-';
                                if ($anak->imunisasi->count() > 0) {
                                    $imunisasiText = $anak->imunisasi->count() . ' kali';
                                }
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $anak->nama }}</td>
                                <td>{{ $anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ $anak->usia }}</td>
                                <td>{!! $statusBadge !!}</td>
                                <td>{{ $imunisasiText }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="font-size: 11px; color: #64748b; padding: 10px 0;">Belum ada data anak yang tercatat untuk ibu ini.</p>
            @endif
        </div>

        <div class="footer">
            {{ config('app.name') }} &nbsp;|&nbsp; Laporan Ibu: {{ $ibu->nama }} &nbsp;|&nbsp; Sistem Informasi Posyandu
        </div>
    </div>
</body>
</html>
