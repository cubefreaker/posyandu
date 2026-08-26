<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan {{ config('app.name') }} — {{ $periodeLabel }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
        .header { background: #0D9488; color: white; padding: 20px 24px; margin-bottom: 24px; }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header p { font-size: 12px; opacity: 0.85; margin-top: 2px; }
        .content { padding: 0 24px 24px; }
        .section { margin-bottom: 20px; page-break-inside: avoid; }
        .section-title { font-size: 13px; font-weight: bold; color: #0D9488; border-bottom: 1.5px solid #99F6E4; padding-bottom: 6px; margin-bottom: 10px; }
        
        /* Stats Layout Table */
        table.layout-stats { width: 100%; border: none; margin-bottom: 20px; table-layout: fixed; }
        table.layout-stats td { border: none; padding: 0 6px; width: 25%; vertical-align: top; background: transparent !important; }
        table.layout-stats td:first-child { padding-left: 0; }
        table.layout-stats td:last-child { padding-right: 0; }
        
        .stat-box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; text-align: center; page-break-inside: avoid; }
        .stat-box .label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .stat-box .value { font-size: 20px; font-weight: bold; color: #0D9488; }
        
        /* Columns Layout Table */
        table.layout-columns { width: 100%; border: none; table-layout: fixed; }
        table.layout-columns td { border: none; padding: 0 8px; width: 50%; vertical-align: top; background: transparent !important; }
        table.layout-columns td:first-child { padding-left: 0; }
        table.layout-columns td:last-child { padding-right: 0; }
        
        /* Data Table */
        table.data-table { width: 100%; border-collapse: collapse; page-break-inside: avoid; }
        table.data-table th { background: #F0FDFA; text-align: left; padding: 8px 10px; font-size: 10px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1.5px solid #99F6E4; }
        table.data-table td { padding: 8px 10px; border-bottom: 1px solid #F1F5F9; font-size: 11px; }
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
        <h1>Laporan Kegiatan {{ config('app.name') }}</h1>
        <p>Periode: {{ $periodeLabel }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="content">
        {{-- Stats --}}
        <table class="layout-stats">
            <tr>
                <td>
                    <div class="stat-box"><div class="label">Total Ibu</div><div class="value" style="color:#0D9488">{{ $totalIbu }}</div></div>
                </td>
                <td>
                    <div class="stat-box"><div class="label">Total Anak</div><div class="value" style="color:#E11D48">{{ $totalAnak }}</div></div>
                </td>
                <td>
                    <div class="stat-box"><div class="label">Ditimbang</div><div class="value" style="color:#16A34A">{{ $totalPenimbangan }}</div></div>
                </td>
                <td>
                    <div class="stat-box"><div class="label">Coverage</div><div class="value" style="color:#475569">{{ $totalAnak > 0 ? round($totalPenimbangan / $totalAnak * 100) : 0 }}%</div></div>
                </td>
            </tr>
        </table>

        <table class="layout-columns">
            <tr>
                <td>
                    {{-- Status Gizi --}}
                    <div class="section">
                        <div class="section-title">Status Gizi (BB/U)</div>
                        <table class="data-table">
                            <thead><tr><th>Status</th><th>Jumlah</th></tr></thead>
                            <tbody>
                                <tr><td><span class="badge badge-buruk">Gizi Buruk</span></td><td>{{ $statusGizi['buruk'] ?? 0 }} anak</td></tr>
                                <tr><td><span class="badge badge-kurang">Gizi Kurang</span></td><td>{{ $statusGizi['kurang'] ?? 0 }} anak</td></tr>
                                <tr><td><span class="badge badge-baik">Gizi Baik</span></td><td>{{ $statusGizi['baik'] ?? 0 }} anak</td></tr>
                                <tr><td><span class="badge badge-lebih">Gizi Lebih</span></td><td>{{ $statusGizi['lebih'] ?? 0 }} anak</td></tr>
                            </tbody>
                        </table>
                    </div>
                </td>
                <td>
                    {{-- Vitamin --}}
                    <div class="section">
                        <div class="section-title">Pemberian Vitamin A</div>
                        <table class="data-table">
                            <thead><tr><th>Jenis</th><th>Jumlah</th></tr></thead>
                            <tbody>
                                <tr><td>Kapsul Biru (6-11 bln)</td><td>{{ $vitaminBiru }} anak</td></tr>
                                <tr><td>Kapsul Merah (12-59 bln)</td><td>{{ $vitaminMerah }} anak</td></tr>
                                <tr><td><strong>Total</strong></td><td><strong>{{ $vitaminBiru + $vitaminMerah }} anak</strong></td></tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Imunisasi --}}
        @if($imunisasiPeriode->count() > 0)
        <div class="section">
            <div class="section-title">Rincian Imunisasi</div>
            <table class="data-table">
                <thead><tr><th>Jenis Imunisasi</th><th>Jumlah Pemberian</th></tr></thead>
                <tbody>
                    @foreach($imunisasiPeriode as $nama => $items)
                        <tr><td>{{ $nama }}</td><td>{{ $items->count() }}×</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer">
            {{ config('app.name') }} &nbsp;|&nbsp; Laporan {{ $periodeLabel }} &nbsp;|&nbsp; Sistem Informasi Posyandu
        </div>
    </div>
</body>
</html>
