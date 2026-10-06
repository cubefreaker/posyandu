<?php

use App\Models\Kehamilan;
use App\Models\PemeriksaanKehamilan;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;

new class extends Component
{
    public Kehamilan $kehamilan;
    public ?int $pemeriksaanId = null;

    public string $tanggal_periksa = '';
    public int $usia_kehamilan_minggu = 0;
    public int $trimester = 1;
    public string $berat_badan = '';
    public string $kenaikan_bb = '';
    public string $tekanan_darah_sistol = '120';
    public string $tekanan_darah_diastol = '80';
    public string $lila = '';
    public string $tinggi_fundus = '';
    public string $djj = '';
    public string $letak_janin = 'Kepala (Preskep)';
    public string $status_tt = '';
    public string $tablet_fe = '30';
    public string $hb = '';
    public string $protein_urin = 'negatif';
    public string $gula_darah = '';
    public string $keluhan = '';
    public string $tindakan_nasihat = '';

    public function mount(int $kehamilanId, ?int $id = null): void
    {
        $this->kehamilan = Kehamilan::with(['ibu', 'pemeriksaan'])->findOrFail($kehamilanId);

        if ($id) {
            $p = PemeriksaanKehamilan::findOrFail($id);
            $this->pemeriksaanId = $p->id;
            $this->tanggal_periksa = $p->tanggal_periksa->format('Y-m-d');
            $this->usia_kehamilan_minggu = $p->usia_kehamilan_minggu;
            $this->trimester = $p->trimester;
            $this->berat_badan = (string) $p->berat_badan;
            $this->kenaikan_bb = (string) $p->kenaikan_bb;
            $this->tekanan_darah_sistol = (string) $p->tekanan_darah_sistol;
            $this->tekanan_darah_diastol = (string) $p->tekanan_darah_diastol;
            $this->lila = $p->lila ? (string) $p->lila : '';
            $this->tinggi_fundus = $p->tinggi_fundus ? (string) $p->tinggi_fundus : '';
            $this->djj = $p->djj ? (string) $p->djj : '';
            $this->letak_janin = $p->letak_janin ?? 'Kepala (Preskep)';
            $this->status_tt = $p->status_tt ?? '';
            $this->tablet_fe = $p->tablet_fe ? (string) $p->tablet_fe : '';
            $this->hb = $p->hb ? (string) $p->hb : '';
            $this->protein_urin = $p->protein_urin ?? 'negatif';
            $this->gula_darah = $p->gula_darah ? (string) $p->gula_darah : '';
            $this->keluhan = $p->keluhan ?? '';
            $this->tindakan_nasihat = $p->tindakan_nasihat ?? '';
        } else {
            $this->tanggal_periksa = now()->format('Y-m-d');
            $this->hitungUsiaGestasi();
            if ($this->kehamilan->lila_awal) {
                $this->lila = (string) $this->kehamilan->lila_awal;
            }
        }
    }

    public function updatedTanggalPeriksa(): void
    {
        $this->hitungUsiaGestasi();
    }

    public function updatedBeratBadan(): void
    {
        if (is_numeric($this->berat_badan) && (float)$this->berat_badan > 0) {
            $bbAwal = (float) $this->kehamilan->bb_sebelum_hamil;
            $this->kenaikan_bb = (string) round((float)$this->berat_badan - $bbAwal, 2);
        }
    }

    public function hitungUsiaGestasi(): void
    {
        if ($this->kehamilan->hpht && $this->tanggal_periksa) {
            $hpht = Carbon::parse($this->kehamilan->hpht);
            $periksa = Carbon::parse($this->tanggal_periksa);
            $minggu = $hpht->diffInWeeks($periksa);
            $this->usia_kehamilan_minggu = max(1, min(42, (int)$minggu));

            if ($this->usia_kehamilan_minggu <= 13) {
                $this->trimester = 1;
            } elseif ($this->usia_kehamilan_minggu <= 27) {
                $this->trimester = 2;
            } else {
                $this->trimester = 3;
            }
        }
    }

    public function save(): void
    {
        $rules = [
            'tanggal_periksa'       => 'required|date',
            'usia_kehamilan_minggu' => 'required|integer|min:1|max:42',
            'trimester'             => 'required|in:1,2,3',
            'berat_badan'           => 'required|numeric|min:30|max:200',
            'tekanan_darah_sistol'  => 'nullable|integer|min:60|max:240',
            'tekanan_darah_diastol' => 'nullable|integer|min:40|max:160',
            'lila'                  => 'nullable|numeric|min:10|max:50',
            'tinggi_fundus'         => 'nullable|numeric|min:5|max:50',
            'djj'                   => 'nullable|integer|min:60|max:220',
            'letak_janin'           => 'nullable|string',
            'status_tt'             => 'nullable|string',
            'tablet_fe'             => 'nullable|integer|min:0|max:120',
            'hb'                    => 'nullable|numeric|min:3|max:20',
            'protein_urin'          => 'nullable|in:negatif,positif_1,positif_2,positif_3',
            'gula_darah'            => 'nullable|integer|min:30|max:500',
            'keluhan'               => 'nullable|string',
            'tindakan_nasihat'      => 'nullable|string',
        ];

        $validated = $this->validate($rules, [
            'tanggal_periksa.required' => 'Tanggal periksa wajib diisi.',
            'berat_badan.required'     => 'Berat badan saat periksa wajib diisi.',
        ]);

        $bbAwal = (float) $this->kehamilan->bb_sebelum_hamil;
        $kenaikan = round((float) $this->berat_badan - $bbAwal, 2);

        $data = array_merge($validated, [
            'kehamilan_id'          => $this->kehamilan->id,
            'kenaikan_bb'           => $kenaikan,
            'tekanan_darah_sistol'  => $this->tekanan_darah_sistol ?: null,
            'tekanan_darah_diastol' => $this->tekanan_darah_diastol ?: null,
            'lila'                  => $this->lila ?: null,
            'tinggi_fundus'         => $this->tinggi_fundus ?: null,
            'djj'                   => $this->djj ?: null,
            'tablet_fe'             => $this->tablet_fe ?: null,
            'hb'                    => $this->hb ?: null,
            'gula_darah'            => $this->gula_darah ?: null,
        ]);

        if ($this->pemeriksaanId) {
            PemeriksaanKehamilan::findOrFail($this->pemeriksaanId)->update($data);
            session()->flash('success', 'Hasil pemeriksaan ANC berhasil diperbarui.');
        } else {
            PemeriksaanKehamilan::create($data);
            session()->flash('success', 'Hasil pemeriksaan ANC berhasil dicatat.');
        }

        $this->redirect(route('kesehatan-ibu.grafik', $this->kehamilan->id));
    }

    public function title(): string
    {
        return 'Pemeriksaan ANC Ibu Hamil — ' . ($this->kehamilan->ibu->nama ?? '');
    }
};
?>

<div>
    <div class="max-w-3xl mx-auto">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('kesehatan-ibu.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            <div>
                <h2 class="font-heading font-bold text-xl text-slate-900">Pemeriksaan ANC (Antenatal Care 10T)</h2>
                <p class="text-xs text-slate-500">Ibu: <strong class="text-slate-800">{{ $kehamilan->ibu->nama }}</strong> · G{{ $kehamilan->kehamilan_ke }} · BB Pra-Hamil: {{ $kehamilan->bb_sebelum_hamil }} kg</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <form wire:submit="save" class="space-y-6">

                {{-- WAKTU & USIA GESTASI --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-xl bg-pink-50/50 border border-pink-100">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Kunjungan ANC <span class="text-red-500">*</span></label>
                        <input type="date" wire:model.live="tanggal_periksa" class="w-full h-10 px-3 border rounded-xl text-sm bg-white border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Usia Kehamilan (Minggu) <span class="text-red-500">*</span></label>
                        <input type="number" wire:model="usia_kehamilan_minggu" min="1" max="42" class="w-full h-10 px-3 border rounded-xl text-sm bg-white font-bold border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Trimester Ke-</label>
                        <select wire:model="trimester" class="w-full h-10 px-3 border rounded-xl text-sm bg-white border-slate-300">
                            <option value="1">Trimester 1 (0-13 mgg)</option>
                            <option value="2">Trimester 2 (14-27 mgg)</option>
                            <option value="3">Trimester 3 (&ge; 28 mgg)</option>
                        </select>
                    </div>
                </div>

                {{-- PEMERIKSAAN FISIK & KENAIKAN BB (STANDAR BUKU KIA) --}}
                <div>
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">1. Pengukuran Fisik & Kenaikan Berat Badan</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">BB Saat Ini (kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" wire:model.live.debounce.300ms="berat_badan" placeholder="cth: 55.5"
                                   class="w-full h-11 px-3 border-[1.5px] rounded-xl text-base font-bold text-slate-900 {{ $errors->has('berat_badan') ? 'border-red-400' : 'border-slate-300 focus:border-pink-500' }} outline-none">
                            @error('berat_badan') <span class="text-[11px] text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kenaikan BB (kg)</label>
                            <div class="w-full h-11 px-3 border rounded-xl bg-slate-100 flex items-center text-sm font-bold {{ (float)$kenaikan_bb < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                                {{ $kenaikan_bb ? ($kenaikan_bb > 0 ? "+{$kenaikan_bb}" : $kenaikan_bb) . ' kg' : '0 kg' }}
                            </div>
                            <span class="text-[10px] text-slate-400">Dari BB pra-hamil</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tensi Sistol (mmHg)</label>
                            <input type="number" wire:model="tekanan_darah_sistol" placeholder="120"
                                   class="w-full h-11 px-3 border rounded-xl text-sm border-slate-300 {{ (int)$tekanan_darah_sistol >= 140 ? 'border-red-500 bg-red-50 text-red-700' : '' }}">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tensi Diastol (mmHg)</label>
                            <input type="number" wire:model="tekanan_darah_diastol" placeholder="80"
                                   class="w-full h-11 px-3 border rounded-xl text-sm border-slate-300 {{ (int)$tekanan_darah_diastol >= 90 ? 'border-red-500 bg-red-50 text-red-700' : '' }}">
                        </div>
                    </div>

                    @if((int)$tekanan_darah_sistol >= 140 || (int)$tekanan_darah_diastol >= 90)
                        <div class="mt-2.5 p-3 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span><strong>Peringatan Preeklampsia:</strong> Tekanan darah tinggi (&ge; 140/90 mmHg). Rujuk segera ke Bidan / Puskesmas.</span>
                        </div>
                    @endif
                </div>

                {{-- PEMERIKSAAN KEBIDANAN (TFU, DJJ, LETAK JANIN, LILA) --}}
                <div class="pt-2 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">2. Kondisi Rahim & Janin</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Tinggi Fundus / TFU (cm)</label>
                            <input type="number" step="0.5" wire:model="tinggi_fundus" placeholder="cth: 24" class="w-full h-10 px-3 border rounded-xl text-sm border-slate-300">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">DJJ (Denyut Jantung Janin)</label>
                            <input type="number" wire:model="djj" placeholder="cth: 140 dpm" class="w-full h-10 px-3 border rounded-xl text-sm border-slate-300">
                            <span class="text-[10px] text-slate-400">Normal: 120-160 dpm</span>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Letak / Presentasi Janin</label>
                            <select wire:model="letak_janin" class="w-full h-10 px-3 border rounded-xl text-sm bg-white border-slate-300">
                                <option value="Kepala (Preskep)">Kepala (Preskep)</option>
                                <option value="Sungsang (Bokong)">Sungsang (Bokong)</option>
                                <option value="Lintang">Lintang</option>
                                <option value="Belum Teraba">Belum Teraba (&lt; 20 mgg)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Lingkar Lengan (LiLA cm)</label>
                            <input type="number" step="0.1" wire:model="lila" placeholder="cm" class="w-full h-10 px-3 border rounded-xl text-sm border-slate-300">
                        </div>
                    </div>
                </div>

                {{-- PELAYANAN MEDIS & LABORATORIUM (IMUNISASI TT, TABLET FE, HB, PROTEIN URIN) --}}
                <div class="pt-2 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">3. Imunisasi, Suplementasi & Laboratorium Sederhana</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Status Imunisasi TT</label>
                            <select wire:model="status_tt" class="w-full h-10 px-3 border rounded-xl text-sm bg-white border-slate-300">
                                <option value="">— Pilih TT —</option>
                                <option value="T1">TT 1</option>
                                <option value="T2">TT 2</option>
                                <option value="T3">TT 3</option>
                                <option value="T4">TT 4</option>
                                <option value="T5">TT 5 (Lengkap)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Tablet Tambah Darah (Fe)</label>
                            <input type="number" wire:model="tablet_fe" placeholder="cth: 30" class="w-full h-10 px-3 border rounded-xl text-sm border-slate-300">
                            <span class="text-[10px] text-slate-400">Jml butir diberikan</span>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Kadar Hb (g/dL)</label>
                            <input type="number" step="0.1" wire:model="hb" placeholder="cth: 11.5" class="w-full h-10 px-3 border rounded-xl text-sm border-slate-300">
                            <span class="text-[10px] text-slate-400">&lt; 11: Anemia</span>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Protein Urin</label>
                            <select wire:model="protein_urin" class="w-full h-10 px-3 border rounded-xl text-sm bg-white border-slate-300">
                                <option value="negatif">Negatif (-)</option>
                                <option value="positif_1">Positif 1 (+)</option>
                                <option value="positif_2">Positif 2 (++)</option>
                                <option value="positif_3">Positif 3 (+++)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- KELUHAN & NASIHAT --}}
                <div class="pt-2 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Keluhan Ibu Hamil</label>
                        <textarea wire:model="keluhan" rows="2" placeholder="cth: Mual pusing di pagi hari, bengkak pada kaki"
                                  class="w-full p-3 border rounded-xl text-sm border-slate-300 resize-none outline-none focus:border-pink-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Tindakan / Nasihat Kader & Bidan</label>
                        <textarea wire:model="tindakan_nasihat" rows="2" placeholder="cth: Istirahat cukup, minum tablet Fe rutin saat malam, makan tinggi protein"
                                  class="w-full p-3 border rounded-xl text-sm border-slate-300 resize-none outline-none focus:border-pink-500"></textarea>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 h-11 px-6 bg-pink-600 hover:bg-pink-700 text-white font-bold text-sm rounded-xl transition-all shadow-md active:scale-[0.98]">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Simpan Hasil Pemeriksaan ANC
                    </button>
                    <a href="{{ route('kesehatan-ibu.index') }}" class="inline-flex items-center h-11 px-5 border border-slate-300 text-slate-600 font-semibold text-sm rounded-xl hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
