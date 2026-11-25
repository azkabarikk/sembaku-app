<?php

namespace Tests\Feature;

use App\Services\Transaksi\ReturService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReturTest extends TestCase
{
    private int $admin;

    private int $penerimaan;

    private int $detail;

    private int $barang;

    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('SEMBAKU_TEST_DB');
        if (! is_string($database) || ! preg_match('/^db_web_sembako_tahap2_test_[0-9]+$/D', $database)) {
            $this->markTestSkipped('Gunakan database MySQL pengujian SembaKu.');
        }

        config(['database.default' => 'mysql', 'database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
        DB::purge('mysql');
        $this->assertSame($database, DB::selectOne('SELECT DATABASE() AS nama')->nama);
        DB::beginTransaction();

        $this->admin = $this->buatUser('Admin');
        $this->actingAs(Auth::createUserProvider('users')->retrieveById($this->admin));

        DB::insert('INSERT INTO satuan (nama_satuan, status) VALUES (?, ?)', ['Unit', 1]);
        $satuan = (int) DB::getPdo()->lastInsertId();
        DB::insert('INSERT INTO barang (nama, jenis, idsatuan, status, harga) VALUES (?, ?, ?, ?, ?)', ['Retur Uji', 'S', $satuan, 1, 10000]);
        $this->barang = (int) DB::getPdo()->lastInsertId();
        DB::insert('INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES (?, ?, ?)', ['Vendor Retur Uji', 'B', 'A']);
        $vendor = (int) DB::getPdo()->lastInsertId();
        DB::insert(
            'INSERT INTO pengadaan (user_iduser, vendor_idvendor, status, subtotal_nilai, ppn, total_nilai) VALUES (?, ?, ?, ?, ?, ?)',
            [$this->admin, $vendor, 'A', 100000, 11000, 111000]
        );
        $pengadaan = (int) DB::getPdo()->lastInsertId();
        DB::insert('INSERT INTO penerimaan (status, idpengadaan, iduser) VALUES (?, ?, ?)', ['K', $pengadaan, $this->admin]);
        $this->penerimaan = (int) DB::getPdo()->lastInsertId();
        DB::insert(
            'INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima) VALUES (?, ?, ?, ?, ?)',
            [$this->penerimaan, $this->barang, 10, 10000, 100000]
        );
        $this->detail = (int) DB::getPdo()->lastInsertId();
        DB::insert(
            'INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang) VALUES (?, ?, ?, ?, ?)',
            ['M', 10, 0, $this->penerimaan, $this->barang]
        );
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    public function test_retur_sebagian_dan_seluruhnya_mencatat_stok_satu_kali_per_detail(): void
    {
        $pertama = $this->postJson(route('retur.store'), $this->input(4))->assertCreated()->json('idretur');
        $this->assertSame(4, (int) DB::selectOne('SELECT jumlah FROM detail_retur WHERE idretur = ?', [$pertama])->jumlah);
        $this->assertSame([$this->admin, $this->penerimaan], array_map('intval', array_values((array) DB::selectOne('SELECT iduser, idpenerimaan FROM retur WHERE idretur = ?', [$pertama]))));
        $this->assertSame(6, (int) $this->getJson(route('retur.sumber.show', $this->penerimaan))
            ->assertOk()->json('rincian.0.sisa_retur'));

        $kedua = $this->postJson(route('retur.store'), $this->input(6))->assertCreated()->json('idretur');
        $mutasi = DB::select('SELECT idtransaksi, masuk, keluar, stock FROM kartu_stok WHERE jenis_transaksi = ? AND idbarang = ? ORDER BY idkartu_stok', ['R', $this->barang]);
        $this->assertCount(2, $mutasi);
        $this->assertSame([(int) $pertama, 0, 4, 6], array_map('intval', array_values((array) $mutasi[0])));
        $this->assertSame([(int) $kedua, 0, 6, 0], array_map('intval', array_values((array) $mutasi[1])));
        $this->postJson(route('retur.store'), $this->input(1))->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertSame(2, (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM retur WHERE idpenerimaan = ?', [$this->penerimaan])->jumlah);
    }

    public function test_jumlah_melebihi_penerimaan_dan_stok_ditolak_tanpa_header(): void
    {
        $this->postJson(route('retur.store'), $this->input(11))->assertUnprocessable()->assertJsonValidationErrors('items');
        DB::insert('INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang) VALUES (?, ?, ?, ?, ?)', ['J', 0, 9, 1, $this->barang]);
        $this->postJson(route('retur.store'), $this->input(2))->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->assertSame(0, (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM retur WHERE idpenerimaan = ?', [$this->penerimaan])->jumlah);
    }

    public function test_status_tidak_sah_dan_detail_lain_ditolak(): void
    {
        DB::update('UPDATE penerimaan SET status = ? WHERE idpenerimaan = ?', ['D', $this->penerimaan]);
        $this->postJson(route('retur.store'), $this->input(1))->assertUnprocessable()->assertJsonValidationErrors('idpenerimaan');
        DB::update('UPDATE penerimaan SET status = ? WHERE idpenerimaan = ?', ['K', $this->penerimaan]);
        $input = $this->input(1);
        $input['items'][0]['iddetail_penerimaan']++;
        $this->postJson(route('retur.store'), $input)->assertUnprocessable()->assertJsonValidationErrors('items');
    }

    public function test_akses_role_mengikuti_matriks_retur(): void
    {
        foreach (['Supervisor', 'Petugas Gudang', 'Staf Pengadaan', 'Kasir'] as $role) {
            $this->actingAs(Auth::createUserProvider('users')->retrieveById($this->buatUser($role)));
            $this->getJson(route('retur.index'))->assertStatus($role === 'Kasir' ? 403 : 200);
            $this->getJson(route('retur.sumber'))->assertStatus($role === 'Kasir' ? 403 : 200);
            if ($role !== 'Kasir') {
                $this->get(route('retur.index'))->assertOk()
                    ->assertSee('Retur ke Vendor')
                    ->assertSee(route('retur.index'), false);
            }
            $this->postJson(route('retur.store'), $this->input(1))
                ->assertStatus(in_array($role, ['Staf Pengadaan', 'Kasir'], true) ? 403 : 201);
        }
    }

    public function test_panel_retur_menampilkan_sumber_dan_menyimpan_form(): void
    {
        $this->get(route('retur.index'))->assertOk()
            ->assertSee('Mulai retur dari penerimaan')
            ->assertSee(route('retur.create', $this->penerimaan), false);

        $this->get(route('retur.create', $this->penerimaan))->assertOk()
            ->assertSee('name="items[0][iddetail_penerimaan]"', false)
            ->assertSee('name="items[0][jumlah]"', false);

        $response = $this->from(route('retur.create', $this->penerimaan))
            ->post(route('retur.store'), $this->input(2));
        $idRetur = (int) basename($response->headers->get('Location'));
        $response->assertRedirect(route('retur.show', $idRetur));
        $this->get(route('retur.show', $idRetur))->assertOk()
            ->assertSee('Kemasan rusak')
            ->assertSee('Retur #'.$idRetur);
    }

    public function test_kegagalan_detail_membatalkan_header_retur(): void
    {
        $sebelum = (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM retur WHERE idpenerimaan = ?', [$this->penerimaan])->jumlah;

        try {
            app(ReturService::class)->simpan(
                $this->penerimaan,
                $this->admin,
                [['iddetail_penerimaan' => $this->detail, 'jumlah' => 1, 'alasan' => str_repeat('x', 201)]]
            );
            $this->fail('Detail dengan alasan melebihi kapasitas seharusnya gagal.');
        } catch (QueryException) {
            $this->assertSame($sebelum, (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM retur WHERE idpenerimaan = ?', [$this->penerimaan])->jumlah);
        }
    }

    public function test_kegagalan_mutasi_membatalkan_header_dan_detail_retur(): void
    {
        $gagalkanMutasi = true;
        DB::listen(static function ($query) use (&$gagalkanMutasi): void {
            if ($gagalkanMutasi && str_contains($query->sql, 'INSERT INTO kartu_stok')) {
                throw new \RuntimeException('Simulasi kegagalan setelah mutasi stok.');
            }
        });

        try {
            app(ReturService::class)->simpan(
                $this->penerimaan,
                $this->admin,
                [['iddetail_penerimaan' => $this->detail, 'jumlah' => 1, 'alasan' => 'Uji rollback mutasi']]
            );
            $this->fail('Mutasi yang disimulasikan gagal seharusnya membatalkan transaksi.');
        } catch (\RuntimeException) {
            $this->assertSame(0, (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM retur WHERE idpenerimaan = ?', [$this->penerimaan])->jumlah);
            $this->assertSame(0, (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM detail_retur WHERE iddetail_penerimaan = ?', [$this->detail])->jumlah);
            $this->assertSame(0, (int) DB::selectOne("SELECT COUNT(*) AS jumlah FROM kartu_stok WHERE jenis_transaksi = 'R' AND idbarang = ?", [$this->barang])->jumlah);
        } finally {
            $gagalkanMutasi = false;
        }
    }

    private function buatUser(string $role): int
    {
        $idRole = DB::selectOne('SELECT idrole FROM role WHERE nama_role = ?', [$role])->idrole;
        DB::insert('INSERT INTO `user` (username, password, idrole) VALUES (?, ?, ?)', [uniqid('retur-', true), 'fixture', $idRole]);

        return (int) DB::getPdo()->lastInsertId();
    }

    private function input(int $jumlah): array
    {
        return [
            'idpenerimaan' => $this->penerimaan,
            'items' => [['iddetail_penerimaan' => $this->detail, 'jumlah' => $jumlah, 'alasan' => 'Kemasan rusak']],
        ];
    }
}
