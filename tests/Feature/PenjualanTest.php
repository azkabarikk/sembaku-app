<?php

namespace Tests\Feature;

use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class PenjualanTest extends TestCase
{
    private int $barang;

    private int $kasir;

    private string $nama;

    private bool $transaksiUji = false;

    protected function setUp(): void
    {
        parent::setUp();
        $database = getenv('SEMBAKU_TEST_DB');
        if (! is_string($database) || ! preg_match('/^db_web_sembako_tahap2_test_[0-9]+$/D', $database)) {
            $this->markTestSkipped('Gunakan database uji MySQL SembaKu');
        }
        config(['database.default' => 'mysql', 'database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
        DB::purge('mysql');
        $this->assertSame($database, DB::selectOne('SELECT DATABASE() AS nama')->nama);
        DB::beginTransaction();
        $this->transaksiUji = true;

        $this->nama = 'POS '.bin2hex(random_bytes(5));
        $role = DB::selectOne('SELECT idrole FROM role WHERE nama_role = ? LIMIT 1', ['Kasir']);
        DB::insert('INSERT INTO `user` (username, password, idrole) VALUES (?, ?, ?)', [$this->nama, 'fixture', $role->idrole]);
        $this->kasir = (int) DB::getPdo()->lastInsertId();
        $this->actingAs(Auth::createUserProvider('users')->retrieveById($this->kasir));
        DB::update('UPDATE margin_penjualan SET status = 0 WHERE status = 1');
        DB::insert('INSERT INTO margin_penjualan (persen, status, iduser) VALUES (?, ?, ?)', [12, 1, $this->kasir]);
        DB::insert('INSERT INTO satuan (nama_satuan, status) VALUES (?, ?)', ['Unit', 1]);
        $satuan = (int) DB::getPdo()->lastInsertId();
        DB::insert('INSERT INTO barang (nama, jenis, idsatuan, status, harga) VALUES (?, ?, ?, ?, ?)', [$this->nama, 'S', $satuan, 1, 10000]);
        $this->barang = (int) DB::getPdo()->lastInsertId();
        DB::insert('INSERT INTO kartu_stok (idbarang, jenis_transaksi, masuk, keluar, idtransaksi) VALUES (?, ?, ?, ?, ?)', [$this->barang, 'M', 3, 0, 1]);
    }

    protected function tearDown(): void
    {
        if ($this->transaksiUji) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function pembayaran(): array
    {
        $katalog = $this->getJson(route('penjualan.index', ['q' => $this->nama]))->assertOk()
            ->assertJsonCount(1, 'daftarBarang')->assertJsonPath('daftarBarang.0.idbarang', $this->barang);

        return ['token_pembayaran' => $katalog->json('tokenPembayaran'), 'uang_diterima' => 30000,
            'items' => [['idbarang' => $this->barang, 'jumlah' => 2]]];
    }

    public function test_kasir_mencari_barang_dan_membayar_dengan_kembalian(): void
    {
        $this->getJson(route('penjualan.index', ['q' => $this->barang]))->assertOk()
            ->assertJsonFragment(['idbarang' => $this->barang, 'nama' => $this->nama]);
        $input = $this->pembayaran();
        $input['iduser'] = 0;
        $input['items'][0]['harga_satuan'] = 1;
        $hasil = $this->postJson(route('penjualan.store'), $input)->assertCreated()
            ->assertJson(['subtotal' => 22400, 'ppn' => 2464, 'total' => 24864, 'uang_diterima' => 30000, 'kembalian' => 5136]);
        $id = $hasil->json('idpenjualan');
        $this->postJson(route('penjualan.store'), $input)->assertOk()->assertExactJson($hasil->json());
        $header = DB::selectOne('SELECT iduser, subtotal_nilai, ppn, total_nilai FROM penjualan WHERE idpenjualan = ?', [$id]);
        $this->assertSame([$this->kasir, 22400, 2464, 24864], array_values((array) $header));
        $detail = DB::selectOne('SELECT harga_satuan, jumlah, subtotal FROM detail_penjualan WHERE penjualan_idpenjualan = ? AND idbarang = ?', [$id, $this->barang]);
        $this->assertSame([11200, 2, 22400], array_values((array) $detail));
        $mutasi = DB::select('SELECT masuk, keluar, stock FROM kartu_stok WHERE jenis_transaksi = ? AND idtransaksi = ? AND idbarang = ?', ['J', $id, $this->barang]);
        $this->assertCount(1, $mutasi);
        $this->assertSame([0, 2, 1], array_values((array) $mutasi[0]));
    }

    public function test_form_kasir_mempertahankan_input_dan_menampilkan_bukti_kembalian(): void
    {
        $url = route('penjualan.index');
        $form = $this->get($url)->assertOk()->assertSee('name="uang_diterima"', false);
        $input = [
            'token_pembayaran' => $form->viewData('tokenPembayaran'),
            'items' => [['idbarang' => $this->barang, 'jumlah' => 2]],
            'uang_diterima' => 20000,
        ];
        $this->from($url)->post(route('penjualan.store'), $input)->assertRedirect($url)
            ->assertSessionHasErrors('uang_diterima')->assertSessionHasInput('items', $input['items']);
        $this->get($url)->assertOk()->assertSee('value="20000"', false)
            ->assertViewHas('tokenPembayaran', $input['token_pembayaran']);

        $input['uang_diterima'] = 30000;
        $this->post(route('penjualan.store'), $input)->assertRedirect($url)->assertSessionHasNoErrors();
        $this->get($url)->assertOk()->assertSeeInOrder([
            'Total pembayaran', 'Rp 24.864', 'Uang diterima', 'Rp 30.000', 'Kembalian', 'Rp 5.136',
        ]);
    }

    public function test_stok_kurang_ditolak(): void
    {
        $input = $this->pembayaran();
        $input['items'][0]['jumlah'] = 4;
        $input['uang_diterima'] = 100000;
        $this->postJson(route('penjualan.store'), $input)->assertUnprocessable()->assertJsonValidationErrors('items');
        $this->pastikanBelumTerjual();
    }

    public function test_pencarian_mempertahankan_token_dan_memerlukan_satu_margin_aktif(): void
    {
        $input = $this->pembayaran();
        $this->getJson(route('penjualan.index', ['q' => $this->barang]))->assertOk()
            ->assertJsonPath('tokenPembayaran', $input['token_pembayaran']);
        DB::insert('INSERT INTO margin_penjualan (persen, status, iduser) VALUES (?, ?, ?)', [9, 1, $this->kasir]);
        $this->getJson(route('penjualan.index', ['q' => $this->nama]))->assertOk()->assertJsonCount(0, 'daftarBarang');
        $this->postJson(route('penjualan.store'), $input)->assertUnprocessable()->assertJsonValidationErrors('penjualan');
        $this->pastikanBelumTerjual();
    }

    public function test_hanya_admin_dan_kasir_boleh_mengakses_pos(): void
    {
        $roleAdmin = DB::selectOne('SELECT idrole FROM role WHERE nama_role = ? LIMIT 1', ['Admin']);
        DB::update('UPDATE `user` SET idrole = ? WHERE iduser = ?', [$roleAdmin->idrole, $this->kasir]);
        $this->actingAs(Auth::createUserProvider('users')->retrieveById($this->kasir));
        $this->get(route('penjualan.index'))->assertOk()->assertViewIs('penjualan.index');
        $input = $this->pembayaran();
        $roleGudang = DB::selectOne('SELECT idrole FROM role WHERE nama_role = ? LIMIT 1', ['Petugas Gudang']);
        DB::update('UPDATE `user` SET idrole = ? WHERE iduser = ?', [$roleGudang->idrole, $this->kasir]);
        $this->actingAs(Auth::createUserProvider('users')->retrieveById($this->kasir));
        $this->getJson(route('penjualan.index'))->assertForbidden();
        $this->postJson(route('penjualan.store'), $input)->assertForbidden();
        $this->pastikanBelumTerjual();
    }

    public function test_pembayaran_kurang_dapat_diulang_dengan_uang_pas(): void
    {
        $input = $this->pembayaran();
        $input['uang_diterima'] = 24863;
        $this->postJson(route('penjualan.store'), $input)->assertUnprocessable()->assertJsonValidationErrors('uang_diterima');
        $this->pastikanBelumTerjual();
        $input['uang_diterima'] = 24864;
        $this->postJson(route('penjualan.store'), $input)->assertCreated()->assertJsonPath('kembalian', 0);
    }

    public function test_gagal_mencatat_mutasi_membatalkan_header_dan_detail(): void
    {
        $input = $this->pembayaran();
        $gagal = false;
        DB::connection()->beforeExecuting(function (string $sql, array $bindings, Connection $connection) use (&$gagal): void {
            if (str_starts_with($sql, 'INSERT INTO kartu_stok') && $bindings[0] === 'J') {
                $gagal = true;
                throw new QueryException($connection->getName(), $sql, $bindings, new RuntimeException('Simulasi gagal mencatat mutasi'));
            }
        });
        $this->postJson(route('penjualan.store'), $input)->assertServerError();
        $this->assertTrue($gagal);
        $this->pastikanBelumTerjual();
    }

    private function pastikanBelumTerjual(): void
    {
        $this->assertNull(DB::selectOne('SELECT idpenjualan FROM penjualan WHERE iduser = ?', [$this->kasir]));
        $this->assertNull(DB::selectOne('SELECT idbarang FROM detail_penjualan WHERE idbarang = ?', [$this->barang]));
        $this->assertNull(DB::selectOne('SELECT idbarang FROM kartu_stok WHERE idbarang = ? AND jenis_transaksi = ?', [$this->barang, 'J']));
    }
}
