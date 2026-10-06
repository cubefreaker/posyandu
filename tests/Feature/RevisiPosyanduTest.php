<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Ibu;
use App\Models\Kehamilan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevisiPosyanduTest extends TestCase
{
    use RefreshDatabase;

    protected User $kader;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kader = User::create([
            'username' => 'kader',
            'password' => bcrypt('kader123'),
            'nama' => 'Kader Posyandu',
            'role' => 'kader',
        ]);

        $this->admin = User::create([
            'username' => 'admin',
            'password' => bcrypt('admin123'),
            'nama' => 'Admin Posyandu',
            'role' => 'admin',
        ]);
    }

    public function test_kader_login_redirects_to_pelayanan()
    {
        $response = $this->post('/login', [
            'username' => 'kader',
            'password' => 'kader123',
        ]);

        $response->assertRedirect(route('pelayanan.index'));
    }

    public function test_admin_login_redirects_to_dashboard()
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_root_redirects_role_appropriately()
    {
        $this->actingAs($this->kader);
        $response = $this->get('/');
        $response->assertRedirect(route('pelayanan.index'));

        $this->actingAs($this->admin);
        $response = $this->get('/');
        $response->assertRedirect(route('dashboard'));
    }

    public function test_pelayanan_terpadu_page_is_accessible()
    {
        $this->actingAs($this->kader);

        $response = $this->get('/pelayanan');
        $response->assertStatus(200);
        $response->assertSee('Pelayanan Posyandu Terpadu');
    }

    public function test_kesehatan_ibu_page_is_accessible()
    {
        $this->actingAs($this->kader);

        $response = $this->get('/kesehatan-ibu');
        $response->assertStatus(200);
        $response->assertSee('Kesehatan Ibu Hamil (Buku KIA)');
    }

    public function test_grafik_kia_page_is_accessible()
    {
        $this->actingAs($this->kader);

        $ibu = Ibu::create([
            'nik' => '3201123456789012',
            'nama' => 'Ibu Siti',
            'tanggal_lahir' => '1995-05-10',
            'alamat' => 'Desa Sukamaju',
        ]);

        $kehamilan = Kehamilan::create([
            'ibu_id' => $ibu->id,
            'kehamilan_ke' => 1,
            'hpht' => '2026-01-01',
            'hpl' => '2026-10-08',
            'bb_sebelum_hamil' => 50,
            'tinggi_badan' => 155,
            'imt_pra_hamil' => 20.81,
            'kategori_imt' => 'normal',
            'lila_awal' => 24.0,
            'status_kek' => false,
            'status_kehamilan' => 'aktif',
        ]);

        $response = $this->get("/kesehatan-ibu/{$kehamilan->id}/grafik");
        $response->assertStatus(200);
        $response->assertSee('Grafik Peningkatan Berat Badan Ibu Hamil');
    }

    public function test_pelayanan_export_pdf_harian_generates_correct_pdf()
    {
        $this->actingAs($this->kader);

        $ibu = Ibu::create([
            'nik' => '3201123456789013',
            'nama' => 'Ibu Rahma',
            'tanggal_lahir' => '1996-03-15',
            'alamat' => 'Desa Sukamaju RT 01',
        ]);

        $anak = Anak::create([
            'ibu_id' => $ibu->id,
            'nama' => 'Ananda Budi',
            'tanggal_lahir' => '2025-01-10',
            'jenis_kelamin' => 'L',
        ]);

        \App\Models\Penimbangan::create([
            'anak_id' => $anak->id,
            'tanggal_pelayanan' => '2026-10-06',
            'berat_badan' => 9.5,
            'tinggi_badan' => 75.0,
            'zscore_bbu' => 0.1,
            'zscore_tbu' => 0.0,
            'zscore_bbtb' => 0.2,
            'status_bbu' => 'baik',
            'status_tbu' => 'normal',
            'status_bbtb' => 'gizi_baik',
        ]);

        $response = $this->get('/laporan/export-pdf?start_date=2026-10-06&tipe=harian');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('laporan-pelayanan-posyandu-2026-10-06.pdf', $response->headers->get('content-disposition'));
    }

    public function test_pelayanan_page_contains_export_button_with_harian_param()
    {
        $this->actingAs($this->kader);

        $response = $this->get('/pelayanan');
        $response->assertStatus(200);
        $response->assertSee('tipe=harian');
        $response->assertSee('Cetak Laporan Hari Ini');
    }
}
