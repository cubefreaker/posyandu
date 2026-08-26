<?php

namespace App\Http\Controllers;

use App\Livewire\Laporan\Index as LaporanIndex;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Ibu;
use App\Models\Anak;
use App\Models\Imunisasi;
use App\Models\Penimbangan;
use App\Models\Vitamin;

class LaporanController extends Controller
{
    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfMonth()->format('Y-m-d'));
        $tipe = $request->query('tipe', 'periode');
        $id = $request->query('id');

        $periodeLabel = Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM YYYY') . ' - ' . Carbon::parse($endDate)->locale('id')->isoFormat('D MMMM YYYY');

        if ($tipe === 'ibu' && $id) {
            $dataIbu = Ibu::with(['anak' => function($q) use ($startDate, $endDate) {
                $q->with([
                    'penimbangan' => fn($q) => $q->whereBetween('tanggal_pelayanan', [$startDate, $endDate])->latest('tanggal_pelayanan'),
                    'imunisasi' => fn($q) => $q->whereBetween('tanggal_imunisasi', [$startDate, $endDate]),
                ]);
            }])->findOrFail($id);

            $data = [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'periodeLabel' => $periodeLabel,
                'ibu' => $dataIbu
            ];
            $pdf = Pdf::loadView('laporan.pdf-ibu', $data)->setPaper('a4');
            return $pdf->download("laporan-ibu-{$dataIbu->nama}-{$startDate}-sampai-{$endDate}.pdf");
        } 
        
        if ($tipe === 'anak' && $id) {
            $dataAnak = Anak::with([
                'ibu',
                'penimbangan' => fn($q) => $q->whereBetween('tanggal_pelayanan', [$startDate, $endDate])->orderBy('tanggal_pelayanan', 'desc'),
                'imunisasi.jenisImunisasi',
                'imunisasi' => fn($q) => $q->whereBetween('tanggal_imunisasi', [$startDate, $endDate])->orderBy('tanggal_imunisasi', 'desc'),
                'vitamin' => fn($q) => $q->whereBetween('tanggal_pemberian', [$startDate, $endDate])->orderBy('tanggal_pemberian', 'desc')
            ])->findOrFail($id);

            $data = [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'periodeLabel' => $periodeLabel,
                'anak' => $dataAnak
            ];
            $pdf = Pdf::loadView('laporan.pdf-anak', $data)->setPaper('a4');
            return $pdf->download("laporan-anak-{$dataAnak->nama}-{$startDate}-sampai-{$endDate}.pdf");
        }

        // Default: tipe = periode
        $penimbanganPeriode = Penimbangan::with('anak')
            ->whereBetween('tanggal_pelayanan', [$startDate, $endDate])
            ->get();

        $statusGizi = ['buruk' => 0, 'kurang' => 0, 'baik' => 0, 'lebih' => 0];
        foreach ($penimbanganPeriode->groupBy('anak_id') as $perAnak) {
            $terakhir = $perAnak->sortByDesc('tanggal_pelayanan')->first();
            if ($terakhir && isset($statusGizi[$terakhir->status_bbu])) {
                $statusGizi[$terakhir->status_bbu]++;
            }
        }

        $imunisasiPeriode = Imunisasi::with('jenisImunisasi')
            ->whereBetween('tanggal_imunisasi', [$startDate, $endDate])
            ->get()
            ->groupBy('jenisImunisasi.nama');

        $vitaminPeriode = Vitamin::whereBetween('tanggal_pemberian', [$startDate, $endDate])
            ->get()
            ->groupBy('jenis_vitamin');

        $data = [
            'totalIbu'          => Ibu::count(),
            'totalAnak'         => Anak::count(),
            'totalPenimbangan'  => $penimbanganPeriode->unique('anak_id')->count(),
            'statusGizi'        => $statusGizi,
            'imunisasiPeriode'  => $imunisasiPeriode,
            'vitaminBiru'       => $vitaminPeriode->get('kapsul_biru', collect())->count(),
            'vitaminMerah'      => $vitaminPeriode->get('kapsul_merah', collect())->count(),
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'periodeLabel'      => $periodeLabel,
        ];

        $pdf = Pdf::loadView('laporan.pdf', $data)->setPaper('a4');

        return $pdf->download("laporan-posyandu-{$startDate}-sampai-{$endDate}.pdf");
    }
}
