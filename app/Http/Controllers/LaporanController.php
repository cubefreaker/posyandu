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

        $startDateTime = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $endDateTime = Carbon::parse($endDate)->endOfDay()->toDateTimeString();
        $periodeLabel = Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM YYYY') . ' - ' . Carbon::parse($endDate)->locale('id')->isoFormat('D MMMM YYYY');

        if ($tipe === 'harian') {
            $tanggal = $startDate;
            $startOfDay = Carbon::parse($tanggal)->startOfDay()->toDateTimeString();
            $endOfDay = Carbon::parse($tanggal)->endOfDay()->toDateTimeString();
            $tanggalLabel = Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY');

            $pelayananHariIni = Penimbangan::with(['anak.ibu'])
                ->whereBetween('tanggal_pelayanan', [$startOfDay, $endOfDay])
                ->orderBy('created_at', 'asc')
                ->get();

            $anakIds = $pelayananHariIni->pluck('anak_id')->unique();

            $imunisasiHariIni = Imunisasi::with('jenisImunisasi')
                ->whereIn('anak_id', $anakIds)
                ->whereBetween('tanggal_imunisasi', [$startOfDay, $endOfDay])
                ->get()
                ->groupBy('anak_id');

            $vitaminHariIni = Vitamin::whereIn('anak_id', $anakIds)
                ->whereBetween('tanggal_pemberian', [$startOfDay, $endOfDay])
                ->get()
                ->groupBy('anak_id');

            $pemeriksaanIbuHariIni = \App\Models\PemeriksaanKehamilan::with(['kehamilan.ibu'])
                ->whereBetween('tanggal_periksa', [$startOfDay, $endOfDay])
                ->get();

            $statusGizi = [
                'baik'   => $pelayananHariIni->where('status_bbu', 'baik')->count(),
                'kurang' => $pelayananHariIni->where('status_bbu', 'kurang')->count(),
                'buruk'  => $pelayananHariIni->where('status_bbu', 'buruk')->count(),
                'lebih'  => $pelayananHariIni->where('status_bbu', 'lebih')->count(),
            ];

            $statusStunting = [
                'normal'        => $pelayananHariIni->where('status_tbu', 'normal')->count(),
                'pendek'        => $pelayananHariIni->where('status_tbu', 'pendek')->count(),
                'sangat_pendek' => $pelayananHariIni->where('status_tbu', 'sangat_pendek')->count(),
                'tinggi'        => $pelayananHariIni->where('status_tbu', 'tinggi')->count(),
            ];

            $totalImunisasi = $imunisasiHariIni->flatten()->count();
            $totalVitamin = $vitaminHariIni->flatten()->count();

            $data = [
                'tanggal'               => $tanggal,
                'tanggalLabel'          => $tanggalLabel,
                'pelayananHariIni'      => $pelayananHariIni,
                'imunisasiHariIni'      => $imunisasiHariIni,
                'vitaminHariIni'        => $vitaminHariIni,
                'pemeriksaanIbuHariIni' => $pemeriksaanIbuHariIni,
                'statusGizi'            => $statusGizi,
                'statusStunting'        => $statusStunting,
                'totalImunisasi'        => $totalImunisasi,
                'totalVitamin'          => $totalVitamin,
            ];

            $pdf = Pdf::loadView('laporan.pdf-harian', $data)->setPaper('a4', 'landscape');
            return $pdf->download("laporan-pelayanan-posyandu-{$tanggal}.pdf");
        }

        if ($tipe === 'ibu' && $id) {
            $dataIbu = Ibu::with(['anak' => function($q) use ($startDateTime, $endDateTime) {
                $q->with([
                    'penimbangan' => fn($q) => $q->whereBetween('tanggal_pelayanan', [$startDateTime, $endDateTime])->latest('tanggal_pelayanan'),
                    'imunisasi' => fn($q) => $q->whereBetween('tanggal_imunisasi', [$startDateTime, $endDateTime]),
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
                'penimbangan' => fn($q) => $q->whereBetween('tanggal_pelayanan', [$startDateTime, $endDateTime])->orderBy('tanggal_pelayanan', 'desc'),
                'imunisasi.jenisImunisasi',
                'imunisasi' => fn($q) => $q->whereBetween('tanggal_imunisasi', [$startDateTime, $endDateTime])->orderBy('tanggal_imunisasi', 'desc'),
                'vitamin' => fn($q) => $q->whereBetween('tanggal_pemberian', [$startDateTime, $endDateTime])->orderBy('tanggal_pemberian', 'desc')
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
            ->whereBetween('tanggal_pelayanan', [$startDateTime, $endDateTime])
            ->get();

        $statusGizi = ['buruk' => 0, 'kurang' => 0, 'baik' => 0, 'lebih' => 0];
        foreach ($penimbanganPeriode->groupBy('anak_id') as $perAnak) {
            $terakhir = $perAnak->sortByDesc('tanggal_pelayanan')->first();
            if ($terakhir && isset($statusGizi[$terakhir->status_bbu])) {
                $statusGizi[$terakhir->status_bbu]++;
            }
        }

        $imunisasiPeriode = Imunisasi::with('jenisImunisasi')
            ->whereBetween('tanggal_imunisasi', [$startDateTime, $endDateTime])
            ->get()
            ->groupBy('jenisImunisasi.nama');

        $vitaminPeriode = Vitamin::whereBetween('tanggal_pemberian', [$startDateTime, $endDateTime])
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
