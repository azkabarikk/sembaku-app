<?php

namespace Tests\Feature;

use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AlurPersediaanTest extends TestCase
{
    private int $barang;

    private int $barangKedua;

    private int $vendor;

    private string $akhiran;

    protected function setUp(): void
    {
        parent::setUp();

        $database = getenv('SEMBAKU_TEST_DB');

        if (! is_string($database) || ! preg_match('/^db_web_sembako_tahap2_test_[0-9]+$/D', $database)) {
            $this->markTestSkipped('SEMBAKU_TEST_DB harus menunjuk ke database MySQL pengujian SembaKu.');
        }

        config(['database.default' => 'mysql', 'database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
        DB::purge('mysql');
        $this->assertSame($database, DB::selectOne('SELECT DATABASE() AS db')->db);

        $this->akhiran = bin2hex(random_bytes(5));
        DB::insert('INSERT INTO satuan (nama_satuan, status) VALUES (?, ?)', ['Dus', 1]);
        $satuan = (int) DB::getPdo()->lastInsertId();
        DB::insert('INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES (?, ?, ?)', ['Vendor Uji', 'B', 'A']);
        $this->vendor = (int) DB::getPdo()->lastInsertId();
        DB::insert('INSERT INTO barang (jenis, nama, idsatuan, status, harga) VALUES (?, ?, ?, ?, ?)', ['S', 'Barang Uji '.$this->akhiran, $satuan, 1, 12000]);
        $this->barang = (int) DB::getPdo()->lastInsertId();
        DB::insert('INSERT INTO barang (jenis, nama, idsatuan, status, harga) VALUES (?, ?, ?, ?, ?)', ['S', 'Barang Kedua '.$this->akhiran, $satuan, 1, 14000]);
        $this->barangKedua = (int) DB::getPdo()->lastInsertId();
    }

    public function test_pengadaan_menggunakan_identitas_login_dan_harga_snapshot(): void
    {
        $pencatat = $this->buatUser('Staf Pengadaan', 'pencatat');
        $admin = $this->buatUser('Admin', 'admin');
        $this->post(route('login.store'), ['username' => 'pencatat-'.$this->akhiran, 'password' => 'SembaKu-Uji-2026'])
            ->assertRedirect(route('stok-barang.index'));
        $this->assertAuthenticated();
        $this->get(route('pengadaan.create'))->assertOk()->assertSee('Harga Satuan');

        $id = $this->buatPengadaan([$this->barang], [5], ['user_iduser' => $admin, 'harga_satuan' => [1]]);
        $pengadaan = DB::selectOne('SELECT user_iduser, subtotal_nilai, ppn, total_nilai FROM pengadaan WHERE idpengadaan = ?', [$id]);
        $this->assertSame([$pencatat, 60000, 6600, 66600], array_values((array) $pengadaan));
        $this->get(route('pengadaan.show', $id))->assertOk()->assertSee('Siap diterima');

        DB::update('UPDATE barang SET harga = ? WHERE idbarang = ?', [18000, $this->barang]);
        $this->masukSebagai($admin);
        $this->put(route('pengadaan.update', $id), [
            ...$this->inputPengadaan([$this->barang, $this->barangKedua], [6, 2]), 'status' => 'A', 'user_iduser' => $admin,
        ])->assertRedirect(route('pengadaan.index'));

        $harga = DB::select('SELECT harga_satuan, sub_total FROM detail_pengadaan WHERE idpengadaan = ? ORDER BY idbarang', [$id]);
        $this->assertSame([[12000, 72000], [14000, 28000]], array_map(static fn (object $baris): array => array_values((array) $baris), $harga));
        $this->assertSame($pencatat, DB::selectOne('SELECT user_iduser FROM pengadaan WHERE idpengadaan = ?', [$id])->user_iduser);
        $this->assertSame(0, $this->jumlahMutasi());
        $this->get(route('pengadaan.edit', $id))->assertOk();
        $this->delete(route('pengadaan.destroy', $id))->assertRedirect(route('pengadaan.index'));
        $this->assertNull(DB::selectOne('SELECT idpengadaan FROM pengadaan WHERE idpengadaan = ?', [$id]));
        $this->assertSame(0, (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM detail_pengadaan WHERE idpengadaan = ?', [$id])->jumlah);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_akun_lama_mengubah_format_password_tanpa_mengganti_password(): void
    {
        $id = $this->buatUser('Kasir', 'akun-lama');
        DB::update('UPDATE `user` SET password = ? WHERE iduser = ?', ['PasswordLama!', $id]);

        $this->post(route('login.store'), ['username' => 'akun-lama-'.$this->akhiran, 'password' => 'PasswordLama!'])
            ->assertRedirect(route('stok-barang.index'));
        $hash = DB::selectOne('SELECT password FROM `user` WHERE iduser = ?', [$id])->password;
        $this->assertNotSame('PasswordLama!', $hash);
        $this->assertTrue(Hash::check('PasswordLama!', $hash));
    }

    public function test_penerimaan_bertahap_langsung_mencatat_stok_dan_harga(): void
    {
        $admin = $this->buatUser('Admin', 'admin');
        $gudang = $this->buatUser('Petugas Gudang', 'gudang');
        $this->masukSebagai($admin);
        $pengadaan = $this->buatPengadaan([$this->barang, $this->barangKedua], [10, 2]);
        DB::update('UPDATE barang SET harga = ? WHERE idbarang = ?', [18000, $this->barang]);

        $this->masukSebagai($gudang);
        $this->get(route('penerimaan.create', $pengadaan))->assertOk()->assertSee('12000')->assertViewHas('idTerakhir', 0);
        $input = ['iduser' => $admin, 'penerimaan_terakhir' => 0];
        $pertama = $this->terimaBarang($pengadaan, [$this->barang => 3], $input);
        $this->assertSame('K', $this->statusPenerimaan($pertama));
        $penerimaan = DB::selectOne('SELECT iduser FROM penerimaan WHERE idpenerimaan = ?', [$pertama]);
        $this->assertSame($gudang, $penerimaan->iduser);
        $this->assertSame(12000, DB::selectOne('SELECT harga FROM barang WHERE idbarang = ?', [$this->barang])->harga);
        $this->post(route('penerimaan.store', $pengadaan), [...$input, 'jumlah_terima' => [$this->barang => 3]])
            ->assertSessionHasErrors('penerimaan');
        $this->post(route('penerimaan.store', $pengadaan), ['jumlah_terima' => [$this->barang => 8]])
            ->assertSessionHasErrors('jumlah_terima.'.$this->barang);
        $this->assertSame(1, $this->jumlahMutasi());

        $this->masukSebagai($admin);
        $this->delete(route('pengadaan.destroy', $pengadaan))->assertSessionHas('error');
        $this->get(route('pengadaan.edit', $pengadaan))->assertForbidden();
        $this->put(route('pengadaan.update', $pengadaan), [...$this->inputPengadaan([$this->barang], [20]), 'status' => 'A'])
            ->assertSessionHasErrors('pengadaan');
        $this->get(route('penerimaan.edit', $pertama))->assertForbidden();
        $this->put(route('penerimaan.update', $pertama), ['jumlah_terima' => [$this->barang => 1]])->assertForbidden();
        $this->delete(route('penerimaan.destroy', $pertama))->assertForbidden();
        $this->post("/penerimaan/{$pertama}/konfirmasi")->assertNotFound();

        $kedua = $this->terimaBarang($pengadaan, [$this->barang => 7, $this->barangKedua => 2], [
            'penerimaan_terakhir' => $pertama,
            'harga_satuan_terima' => [$this->barang => 21000, $this->barangKedua => 15000],
        ]);
        $this->get(route('penerimaan.show', $kedua))->assertOk()->assertSee('Terkonfirmasi');
        $this->assertSame(2, $this->jumlahMutasi());
        $detail = DB::selectOne('SELECT harga_satuan_terima, sub_total_terima FROM detail_penerimaan WHERE idpenerimaan = ? AND barang_idbarang = ?', [$kedua, $this->barang]);
        $this->assertSame([21000, 147000], array_values((array) $detail));
        $this->assertSame(21000, DB::selectOne('SELECT harga FROM barang WHERE idbarang = ?', [$this->barang])->harga);
        $this->assertSame(12000, DB::selectOne('SELECT harga_satuan FROM detail_pengadaan WHERE idpengadaan = ? AND idbarang = ?', [$pengadaan, $this->barang])->harga_satuan);
        $mutasi = DB::selectOne('SELECT jenis_transaksi, masuk, keluar, stock FROM kartu_stok WHERE idbarang = ? AND idtransaksi = ?', [$this->barang, $kedua]);
        $this->assertSame(['M', 7, 0, 10], array_values((array) $mutasi));
        $this->assertSame(10, (int) DB::selectOne('SELECT stok FROM v_stok_barang WHERE idbarang = ?', [$this->barang])->stok);
        $this->get(route('kartu-stok.index', ['barang' => $this->barang, 'jenis' => 'M', 'tanggal' => date('Y-m-d')]))->assertOk();
        $this->get(route('stok-barang.index'))->assertOk()->assertSee('Total Masuk');

        $satuan = DB::selectOne('SELECT idsatuan FROM barang WHERE idbarang = ?', [$this->barang])->idsatuan;
        $this->put(route('barang.update', $this->barang), [
            'nama' => 'Barang baru namanya', 'jenis' => 'S', 'idsatuan' => $satuan, 'harga' => 1, 'status' => '1',
        ])->assertRedirect(route('barang.index'));
        $this->assertSame(21000, DB::selectOne('SELECT harga FROM barang WHERE idbarang = ?', [$this->barang])->harga);
    }

    public function test_hak_akses_dan_kepemilikan_draf(): void
    {
        $admin = $this->buatUser('Admin', 'admin');
        $kasir = $this->buatUser('Kasir', 'kasir');
        $staf = $this->buatUser('Staf Pengadaan', 'staf');
        $gudang = $this->buatUser('Petugas Gudang', 'gudang');
        $petugasLain = $this->buatUser('Petugas Gudang', 'gudang-lain');

        $this->get(route('pengadaan.index'))->assertRedirect(route('login'));
        $this->masukSebagai($kasir);
        $this->get(route('stok-barang.index'))->assertOk();
        $this->get(route('kartu-stok.index'))->assertForbidden();
        $this->get(route('pengadaan.index'))->assertForbidden();
        $this->get(route('penerimaan.index'))->assertForbidden();
        $this->get(route('user.index'))->assertForbidden();

        $this->masukSebagai($staf);
        $pengadaan = $this->buatPengadaan([$this->barang], [5]);
        $this->get(route('penerimaan.index'))->assertOk();
        $this->get(route('penerimaan.create', $pengadaan))->assertForbidden();
        $this->post(route('pengadaan.store'), $this->inputPengadaan([], []))->assertSessionHasErrors('idbarang');

        $this->masukSebagai($gudang);
        $this->get(route('pengadaan.index'))->assertOk();
        $this->get(route('pengadaan.create'))->assertForbidden();
        $id = $this->drafLama($pengadaan, $gudang);
        $this->get(route('penerimaan.edit', $id))->assertOk();
        $this->masukSebagai($petugasLain);
        $this->get(route('penerimaan.edit', $id))->assertForbidden();
        $this->put(route('penerimaan.update', $id), ['jumlah_terima' => [$this->barang => 1]])->assertForbidden();
        $this->delete(route('penerimaan.destroy', $id))->assertForbidden();

        $this->masukSebagai($admin);
        $this->put(route('penerimaan.update', $id), [
            'jumlah_terima' => [$this->barang => 2], 'harga_satuan_terima' => [$this->barang => 15000], 'iduser' => $admin,
        ])->assertSessionHas('success');
        $hasil = DB::selectOne('SELECT iduser, status FROM penerimaan WHERE idpenerimaan = ?', [$id]);
        $this->assertSame([$gudang, 'K'], array_values((array) $hasil));
        $this->assertSame(1, $this->jumlahMutasi());
        $this->assertSame(30000, DB::selectOne('SELECT sub_total_terima FROM detail_penerimaan WHERE idpenerimaan = ?', [$id])->sub_total_terima);
        $this->put(route('penerimaan.update', $id), ['jumlah_terima' => [$this->barang => 2]])->assertForbidden();
        $draf = $this->drafLama($pengadaan, $gudang);
        $this->masukSebagai($gudang);
        $this->delete(route('penerimaan.destroy', $draf))->assertSessionHas('success');
        $this->assertNull($this->statusPenerimaan($draf));
        $this->assertSame(0, (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM detail_penerimaan WHERE idpenerimaan = ?', [$draf])->jumlah);
        $this->masukSebagai($staf);
        $this->post(route('penerimaan.store', $pengadaan), ['jumlah_terima' => [$this->barang => 1]])->assertForbidden();
    }

    public function test_validasi_laravel_menolak_saldo_negatif_dan_barang_nonaktif(): void
    {
        $admin = $this->buatUser('Admin', 'admin');
        $this->masukSebagai($admin);
        $pengadaan = $this->buatPengadaan([$this->barang], [2]);
        $id = $this->drafLama($pengadaan, $admin);

        DB::update('UPDATE barang SET status = ? WHERE idbarang = ?', [0, $this->barang]);
        $this->put(route('penerimaan.update', $id), ['jumlah_terima' => [$this->barang => 1]])->assertSessionHasErrors('penerimaan');
        $this->assertSame(0, $this->jumlahMutasi());
        DB::update('UPDATE barang SET status = ? WHERE idbarang = ?', [1, $this->barang]);

        DB::insert(
            'INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang) VALUES (?, ?, ?, ?, ?)',
            ['B', 0, 2, $id, $this->barang]
        );

        $this->put(route('penerimaan.update', $id), ['jumlah_terima' => [$this->barang => 1]])->assertSessionHasErrors('penerimaan');
        $this->assertSame('D', $this->statusPenerimaan($id));
        $this->assertSame(1, $this->jumlahMutasi());
    }

    public function test_penerimaan_menolak_jumlah_nol_dan_memperhitungkan_draf_lama(): void
    {
        $admin = $this->buatUser('Admin', 'admin');
        $this->masukSebagai($admin);
        $pengadaan = $this->buatPengadaan([$this->barang], [2]);
        $id = $this->drafLama($pengadaan, $admin);
        $this->post(route('penerimaan.store', $pengadaan), ['jumlah_terima' => [$this->barang => 0]])->assertSessionHasErrors('jumlah_terima');
        $this->post(route('penerimaan.store', $pengadaan), ['jumlah_terima' => [$this->barang => 2]])->assertSessionHasErrors('jumlah_terima.'.$this->barang);
        $this->assertSame('D', $this->statusPenerimaan($id));
        $this->assertSame(0, $this->jumlahMutasi());
        DB::insert('INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang) VALUES (?, ?, 0, ?, ?)', ['P', 1, $id, $this->barang]);
        $this->put(route('penerimaan.update', $id), ['jumlah_terima' => [$this->barang => 1]])->assertSessionHasErrors('penerimaan');
        $this->delete(route('penerimaan.destroy', $id))->assertSessionHasErrors('penerimaan');
        $this->assertSame('D', $this->statusPenerimaan($id));
        $this->assertSame(1, $this->jumlahMutasi());
    }

    public function test_kegagalan_penyimpanan_mengembalikan_stok_harga_dan_status(): void
    {
        $admin = $this->buatUser('Admin', 'admin');
        $this->masukSebagai($admin);
        $pengadaan = $this->buatPengadaan([$this->barang, $this->barangKedua], [1, 1]);

        $kegagalanTerjadi = false;

        DB::connection()->beforeExecuting(function (string $sql, array $bindings, Connection $connection) use (&$kegagalanTerjadi): void {
            if ($sql === 'UPDATE barang SET harga = ? WHERE idbarang = ?' && $bindings[1] === $this->barangKedua) {
                $kegagalanTerjadi = true;

                throw new QueryException($connection->getName(), $sql, $bindings, new RuntimeException('Simulasi kegagalan penyimpanan harga.'));
            }
        });

        $this->post(route('penerimaan.store', $pengadaan), [
            'jumlah_terima' => [$this->barang => 1, $this->barangKedua => 1],
            'harga_satuan_terima' => [$this->barang => 17500, $this->barangKedua => 15500],
        ])->assertSessionHasErrors('penerimaan');
        $this->assertTrue($kegagalanTerjadi);
        $this->assertNull(DB::selectOne('SELECT idpenerimaan FROM penerimaan WHERE idpengadaan = ?', [$pengadaan]));
        $this->assertSame(0, (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM detail_penerimaan WHERE barang_idbarang IN (?, ?)', [$this->barang, $this->barangKedua])->jumlah);
        $this->assertSame(0, $this->jumlahMutasi());
        $this->assertSame(12000, DB::selectOne('SELECT harga FROM barang WHERE idbarang = ?', [$this->barang])->harga);
        $this->assertSame(14000, DB::selectOne('SELECT harga FROM barang WHERE idbarang = ?', [$this->barangKedua])->harga);
    }

    public function test_daftar_barang_menampilkan_harga_master_saat_margin_aktif_berubah(): void
    {
        $admin = $this->buatUser('Admin', 'admin');
        $this->masukSebagai($admin);
        DB::update('UPDATE barang SET harga = ? WHERE idbarang = ?', [10000, $this->barang]);

        $this->post(route('margin-penjualan.store'), ['persen' => 12, 'status' => '1'])
            ->assertRedirect(route('margin-penjualan.index'));
        $marginPertama = DB::selectOne('SELECT idmargin_penjualan FROM margin_penjualan WHERE status = ? AND iduser = ?', [1, $admin]);
        $this->assertNotNull($marginPertama);
        $this->get(route('barang.index'))->assertOk()->assertSee('Harga Barang')->assertDontSee('Harga Jual')
            ->assertSee('Rp 10.000');

        $this->post(route('margin-penjualan.store'), ['persen' => 20, 'status' => '0'])
            ->assertRedirect(route('margin-penjualan.index'));
        $marginKedua = DB::selectOne('SELECT MAX(idmargin_penjualan) AS id FROM margin_penjualan WHERE iduser = ?', [$admin]);
        $this->put(route('margin-penjualan.update', $marginKedua->id), ['persen' => 20, 'status' => '1'])
            ->assertRedirect(route('margin-penjualan.index'));
        $aktif = DB::select('SELECT idmargin_penjualan FROM margin_penjualan WHERE status = ?', [1]);
        $this->assertSame([$marginKedua->id], array_column($aktif, 'idmargin_penjualan'));
        $this->get(route('barang.index'))->assertOk()->assertSee('Rp 10.000')
            ->assertViewHas('daftarBarang', fn (array $daftar): bool => (int) (array_column($daftar, 'harga', 'idbarang')[$this->barang] ?? 0) === 10000);
        $this->assertSame(10000, DB::selectOne('SELECT harga FROM barang WHERE idbarang = ?', [$this->barang])->harga);
    }

    public function test_margin_mencatat_admin_login_dan_mempertahankan_pembuat_saat_diedit_admin_lain(): void
    {
        $pembuat = $this->buatUser('Admin', 'pembuat');
        $pengubah = $this->buatUser('Admin', 'pengubah');
        $kasir = $this->buatUser('Kasir', 'kasir');

        $this->post(route('margin-penjualan.store'), ['persen' => 12, 'status' => '0', 'iduser' => $pengubah])
            ->assertRedirect(route('login'));
        $this->assertNull(DB::selectOne('SELECT idmargin_penjualan FROM margin_penjualan WHERE iduser = ?', [$pengubah]));

        $this->masukSebagai($kasir);
        $this->post(route('margin-penjualan.store'), ['persen' => 12, 'status' => '0', 'iduser' => $pembuat])->assertForbidden();
        $this->assertNull(DB::selectOne('SELECT idmargin_penjualan FROM margin_penjualan WHERE iduser = ?', [$pembuat]));

        $this->masukSebagai($pembuat);
        $this->get(route('margin-penjualan.create'))->assertOk()->assertDontSee('name="iduser"', false);
        $this->post(route('margin-penjualan.store'), ['persen' => 12, 'status' => '0', 'iduser' => $pengubah])
            ->assertRedirect(route('margin-penjualan.index'));
        $margin = DB::selectOne('SELECT idmargin_penjualan, iduser FROM margin_penjualan WHERE iduser = ?', [$pembuat]);
        $this->assertNotNull($margin);
        $this->assertSame($pembuat, $margin->iduser);

        $this->masukSebagai($pengubah);
        $this->get(route('margin-penjualan.edit', $margin->idmargin_penjualan))->assertOk()->assertDontSee('name="iduser"', false);
        $this->put(route('margin-penjualan.update', $margin->idmargin_penjualan), [
            'persen' => 18,
            'status' => '0',
            'iduser' => $pengubah,
        ])->assertRedirect(route('margin-penjualan.index'));
        $hasil = DB::selectOne('SELECT iduser, persen FROM margin_penjualan WHERE idmargin_penjualan = ?', [$margin->idmargin_penjualan]);
        $this->assertSame($pembuat, $hasil->iduser);
        $this->assertSame(18.0, (float) $hasil->persen);
    }

    public function test_tamu_tidak_bisa_membuat_pengadaan_atau_penerimaan(): void
    {
        $admin = $this->buatUser('Admin', 'admin');
        $this->post(route('pengadaan.store'), [
            ...$this->inputPengadaan([$this->barang], [3]),
            'user_iduser' => $admin,
        ])->assertRedirect(route('login'));
        $this->assertNull(DB::selectOne('SELECT idpengadaan FROM pengadaan WHERE vendor_idvendor = ?', [$this->vendor]));

        $this->masukSebagai($admin);
        $id = $this->buatPengadaan([$this->barang], [3]);
        $this->assertSame($admin, DB::selectOne('SELECT user_iduser FROM pengadaan WHERE idpengadaan = ?', [$id])->user_iduser);
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('penerimaan.store', $id), [
            'jumlah_terima' => [$this->barang => 1],
            'iduser' => $admin,
        ])->assertRedirect(route('login'));
        $this->assertNull(DB::selectOne('SELECT idpenerimaan FROM penerimaan WHERE idpengadaan = ?', [$id]));
    }

    public function test_riwayat_lama_aktif_mengurangi_sisa_dan_batal_tidak(): void
    {
        $admin = $this->buatUser('Admin', 'admin');
        $this->masukSebagai($admin);
        $pengadaan = $this->buatPengadaan([$this->barang], [10]);
        $lama = [];

        foreach (['A', 'N'] as $status) {
            DB::insert('INSERT INTO penerimaan (status, idpengadaan, iduser) VALUES (?, ?, ?)', [$status, $pengadaan, $admin]);
            $id = (int) DB::getPdo()->lastInsertId();
            $lama[$status] = $id;
            DB::insert(
                'INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima) VALUES (?, ?, ?, ?, ?)',
                [$id, $this->barang, 5, 12000, 60000]
            );
        }

        DB::insert(
            'INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, idtransaksi, idbarang) VALUES (?, ?, ?, ?, ?)',
            ['P', 5, 0, $lama['A'], $this->barang]
        );

        $this->get(route('penerimaan.show', $lama['A']))->assertOk()->assertSee('Aktif (lama)');
        $this->get(route('penerimaan.show', $lama['N']))->assertOk()->assertSee('Dibatalkan (lama)');
        $this->post(route('penerimaan.store', $pengadaan), ['jumlah_terima' => [$this->barang => 6]])
            ->assertSessionHasErrors('jumlah_terima.'.$this->barang);
        $id = $this->terimaBarang($pengadaan, [$this->barang => 5]);
        $this->assertSame('K', $this->statusPenerimaan($id));
        $this->assertSame('A', $this->statusPenerimaan($lama['A']));
        $this->assertSame('N', $this->statusPenerimaan($lama['N']));
        $this->assertSame(10, (int) DB::selectOne('SELECT stok FROM v_stok_barang WHERE idbarang = ?', [$this->barang])->stok);
    }

    private function buatUser(string $role, string $nama): int
    {
        $idRole = DB::selectOne('SELECT idrole FROM role WHERE nama_role = ? ORDER BY idrole LIMIT 1', [$role])?->idrole;

        if ($idRole === null) {
            DB::insert('INSERT INTO role (nama_role) VALUES (?)', [$role]);
            $idRole = (int) DB::getPdo()->lastInsertId();
        }

        DB::insert('INSERT INTO `user` (username, password, idrole) VALUES (?, ?, ?)', [$nama.'-'.$this->akhiran, Hash::make('SembaKu-Uji-2026'), $idRole]);

        return (int) DB::getPdo()->lastInsertId();
    }

    private function masukSebagai(int $id): void
    {
        $this->actingAs(Auth::createUserProvider('users')->retrieveById($id));
    }

    private function inputPengadaan(array $barang, array $jumlah): array
    {
        return ['vendor_idvendor' => $this->vendor, 'idbarang' => $barang, 'jumlah' => $jumlah];
    }

    private function buatPengadaan(array $barang, array $jumlah, array $tambahan = []): int
    {
        $response = $this->post(route('pengadaan.store'), [...$this->inputPengadaan($barang, $jumlah), ...$tambahan]);
        $response->assertSessionHas('success');
        $id = (int) basename($response->headers->get('Location'));
        $response->assertRedirect(route('pengadaan.show', $id));

        return $id;
    }

    private function drafLama(int $pengadaan, int $pembuat): int
    {
        DB::insert('INSERT INTO penerimaan (status, idpengadaan, iduser) VALUES (?, ?, ?)', ['D', $pengadaan, $pembuat]);
        $id = (int) DB::getPdo()->lastInsertId();
        DB::insert(
            'INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima) VALUES (?, ?, ?, ?, ?)',
            [$id, $this->barang, 1, 12000, 12000]
        );

        return $id;
    }

    private function terimaBarang(int $pengadaan, array $jumlah, array $tambahan = []): int
    {
        $response = $this->post(route('penerimaan.store', $pengadaan), ['jumlah_terima' => $jumlah, ...$tambahan]);
        $response->assertSessionHas('success');
        $id = (int) basename($response->headers->get('Location'));
        $response->assertRedirect(route('penerimaan.show', $id));

        return $id;
    }

    private function statusPenerimaan(int $id): ?string
    {
        return DB::selectOne('SELECT status FROM penerimaan WHERE idpenerimaan = ?', [$id])?->status;
    }

    private function jumlahMutasi(): int
    {
        return (int) DB::selectOne('SELECT COUNT(*) AS jumlah FROM kartu_stok WHERE idbarang = ?', [$this->barang])->jumlah;
    }
}
