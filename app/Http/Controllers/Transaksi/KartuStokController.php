<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KartuStokController extends Controller
{
    // Membaca filter barang, penerimaan, jenis transaksi, dan tanggal untuk menyaring mutasi stok
    // Halaman kartu stok menampilkan mutasi terbaru lebih dulu, sebanyak 50 baris per halaman
    public function index(Request $request): View
    {
        $idBarang = filter_var($request->query('barang'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $idPenerimaan = filter_var($request->query('penerimaan'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
        $jenisInput = $request->query('jenis');
        $jenis = is_string($jenisInput) && strlen($jenisInput) === 1 ? $jenisInput : null;
        $tanggalInput = $request->query('tanggal');
        $tanggal = is_string($tanggalInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $tanggalInput)
            && $this->tanggalValid($tanggalInput)
            ? $tanggalInput : null;
        $daftarJenis = array_column(DB::select('SELECT DISTINCT jenis_transaksi FROM kartu_stok ORDER BY jenis_transaksi'), 'jenis_transaksi');
        $daftarBarang = DB::select('SELECT idbarang, nama, stok FROM v_stok_barang ORDER BY nama, idbarang');
        $barangDipilih = array_column($daftarBarang, null, 'idbarang')[$idBarang] ?? null;

        $sql = "SELECT k.idkartu_stok, k.created_at, k.jenis_transaksi, k.idtransaksi,
                       k.masuk, k.keluar, k.stock, b.nama AS nama_barang, p.idpenerimaan
                FROM kartu_stok k
                LEFT JOIN v_master_barang b ON b.idbarang = k.idbarang
                LEFT JOIN penerimaan p ON p.idpenerimaan = k.idtransaksi AND k.jenis_transaksi IN ('M', 'P', 'B')
                WHERE 1 = 1";
        $bindings = [];

        if ($idBarang !== null) {
            $sql .= ' AND k.idbarang = ?';
            $bindings[] = $idBarang;
        }

        if ($idPenerimaan !== null) {
            $sql .= ' AND p.idpenerimaan = ?';
            $bindings[] = $idPenerimaan;
        }

        if ($jenis !== null) {
            $sql .= ' AND k.jenis_transaksi = ?';
            $bindings[] = $jenis;
        }

        if ($tanggal !== null) {
            $sql .= ' AND k.created_at >= ? AND k.created_at < DATE_ADD(?, INTERVAL 1 DAY)';
            $bindings[] = $tanggal;
            $bindings[] = $tanggal;
        }

        $perHalaman = 50;
        $halaman = min(Paginator::resolveCurrentPage(), intdiv(PHP_INT_MAX, $perHalaman));
        $mutasi = DB::select($sql.' ORDER BY k.idkartu_stok DESC LIMIT ? OFFSET ?', [
            ...$bindings,
            $perHalaman + 1,
            ($halaman - 1) * $perHalaman,
        ]);

        return view('kartu_stok.index', [
            'daftarBarang' => $daftarBarang,
            'barangDipilih' => $barangDipilih,
            'idBarang' => $idBarang,
            'idPenerimaan' => $idPenerimaan,
            'jenis' => $jenis,
            'tanggal' => $tanggal,
            'daftarJenis' => $daftarJenis,
            'mutasi' => (new Paginator($mutasi, $perHalaman, $halaman, ['path' => $request->url()]))
                ->appends(['barang' => $idBarang, 'penerimaan' => $idPenerimaan, 'jenis' => $jenis, 'tanggal' => $tanggal]),
        ]);
    }

    // Menjumlahkan stok masuk dan keluar per barang, lalu mengambil saldo dari mutasi terakhir
    // Halaman ringkasan menampilkan kedua saldo, termasuk nilai nol untuk barang tanpa mutasi
    public function ringkasan(): View
    {
        $stok = DB::select(
            'SELECT b.idbarang, b.nama, b.nama_satuan,
                    COALESCE(m.total_masuk, 0) AS total_masuk,
                    COALESCE(m.total_keluar, 0) AS total_keluar,
                    COALESCE(m.total_masuk, 0) - COALESCE(m.total_keluar, 0) AS saldo,
                    COALESCE(k.stock, 0) AS saldo_kartu
             FROM v_master_barang b
             LEFT JOIN (
                 SELECT idbarang, SUM(masuk) AS total_masuk, SUM(keluar) AS total_keluar,
                        MAX(idkartu_stok) AS id_mutasi_terakhir
                 FROM kartu_stok GROUP BY idbarang
             ) m ON m.idbarang = b.idbarang
             LEFT JOIN kartu_stok k ON k.idkartu_stok = m.id_mutasi_terakhir
             ORDER BY b.idbarang'
        );

        return view('kartu_stok.ringkasan', ['stok' => $stok]);
    }

    // Membaca tanggal dalam format tahun-bulan-hari dan mencocokkannya kembali dengan masukan
    // Hasilnya benar hanya jika tanggal tersebut ada di kalender dan formatnya tepat
    private function tanggalValid(string $tanggal): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal);

        return $parsed !== false && $parsed->format('Y-m-d') === $tanggal;
    }
}
