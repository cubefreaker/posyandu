<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan {{ config('app.name') }} — Anak {{ $anak->nama }}</title>
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
        
        /* Layout columns */
        table.layout-columns { width: 100%; border: none; table-layout: fixed; margin-bottom: 20px; }
        table.layout-columns td { border: none; padding: 0 8px; width: 50%; vertical-align: top; background: transparent !important; }
        table.layout-columns td:first-child { padding-left: 0; }
        table.layout-columns td:last-child { padding-right: 0; }

        /* Data Table */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
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
        <h1>Laporan Kegiatan {{ config('app.name') }} — Per Anak</h1>
        <p>Periode: {{ $periodeLabel }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="content">
        <div class="profil-box">
            <div class="profil-item">
                <span class="profil-label">Nama Anak</span>
                <span class="profil-value">: {{ $anak->nama }}</span>
            </div>
            <div class="profil-item">
                <span class="profil-label">Jenis Kelamin</span>
                <span class="profil-value">: {{ $anak->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
            </div>
            <div class="profil-item">
                <span class="profil-label">Tanggal Lahir</span>
                <span class="profil-value">: {{ $anak->tanggal_lahir ? $anak->tanggal_lahir->format('d/m/Y') : '-' }} (Usia: {{ $anak->usia }})</span>
            </div>
            <div class="profil-item">
                <span class="profil-label">Nama Ibu</span>
                <span class="profil-value">: {{ $anak->ibu->nama ?? '-' }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Riwayat Penimbangan (Periode Ini)</div>
            @if($anak->penimbangan->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Usia</th>
                            <th>Berat Badan</th>
                            <th>Tinggi Badan</th>
                            <th>Lingkar Kepala</th>
                            <th>Status (BB/U)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($anak->penimbangan as $index => $p)
                            @php
                                $statusBadge = '-';
                                $s = $p->status_bbu;
                                if ($s == 'buruk') $statusBadge = '<span class="badge badge-buruk">Gizi Buruk</span>';
                                elseif ($s == 'kurang') $statusBadge = '<span class="badge badge-kurang">Gizi Kurang</span>';
                                elseif ($s == 'baik') $statusBadge = '<span class="badge badge-baik">Gizi Baik</span>';
                                elseif ($s == 'lebih') $statusBadge = '<span class="badge badge-lebih">Gizi Lebih</span>';
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $p->tanggal_pelayanan->format('d/m/Y') }}</td>
                                <td>{{ $p->usia_saat_ukur ?? '-' }} bln</td>
                                <td>{{ $p->berat_badan }} kg</td>
                                <td>{{ $p->tinggi_badan }} cm</td>
                                <td>{{ $p->lingkar_kepala ? $p->lingkar_kepala . ' cm' : '-' }}</td>
                                <td>{!! $statusBadge !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="font-size: 11px; color: #64748b; padding: 10px 0;">Belum ada riwayat penimbangan di periode ini.</p>
            @endif
        </div>

        <table class="layout-columns">
            <tr>
                <td>
                    <div class="section">
                        <div class="section-title">Riwayat Imunisasi (Periode Ini)</div>
                        @if($anak->imunisasi->count() > 0)
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis Imunisasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($anak->imunisasi as $im)
                                        <tr>
                                            <td>{{ $im->tanggal_imunisasi->format('d/m/Y') }}</td>
                                            <td>{{ $im->jenisImunisasi->nama ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p style="font-size: 11px; color: #64748b; padding: 10px 0;">Belum ada riwayat imunisasi di periode ini.</p>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="section">
                        <div class="section-title">Riwayat Vitamin (Periode Ini)</div>
                        @if($anak->vitamin->count() > 0)
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis Vitamin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($anak->vitamin as $vit)
                                        <tr>
                                            <td>{{ $vit->tanggal_pemberian->format('d/m/Y') }}</td>
                                            <td>{{ $vit->jenis_vitamin == 'kapsul_biru' ? 'Kapsul Biru' : 'Kapsul Merah' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p style="font-size: 11px; color: #64748b; padding: 10px 0;">Belum ada riwayat pemberian vitamin di periode ini.</p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            {{ config('app.name') }} &nbsp;|&nbsp; Laporan Anak: {{ $anak->nama }} &nbsp;|&nbsp; Sistem Informasi Posyandu
        </div>
    </div>
</body>
</html>
