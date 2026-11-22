<?php

namespace App\Http\Controllers\Transaksi\Penerimaan;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenerimaanRequest;
use App\Queries\Transaksi\PenerimaanQuery;
use App\Services\Transaksi\PenerimaanService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PenerimaanController extends Controller
{
    // Menyiapkan PenerimaanQuery untuk membaca pesanan dan penerimaan serta PenerimaanService untuk mencatat barang masuk dan mengelola draf lama
    public function __construct(
        private readonly PenerimaanQuery $query,
        private readonly PenerimaanService $service,
    ) {}

    // Membaca ID pengadaan dari URL untuk menyaring daftar penerimaan jika ID berupa bilangan positif
    // Jika filter kosong atau tidak valid, semua penerimaan ditampilkan
    // PenerimaanQuery juga menyediakan pilihan pengadaan yang masih bisa diterima
    public function index(Request $request): View
    {
        $idPengadaan = filter_var($request->query('pengadaan'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

        return view('penerimaan.index', [
            'daftarPenerimaan' => $this->query->daftarPenerimaan($idPengadaan),
            'idPengadaan' => $idPengadaan,
            'pengadaanTersisa' => $this->query->pengadaanTersisa(),
        ]);
    }

    // Memeriksa apakah ID pengadaan yang dipilih berupa bilangan positif sebelum membuka formulir penerimaan
    // Jika pilihan tidak valid, pengguna kembali ke daftar dengan pesan untuk memilih pengadaan
    public function pilihPengadaan(Request $request): RedirectResponse
    {
        $id = filter_var($request->query('pengadaan'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($id === false) {
            return redirect()->route('penerimaan.index')->with('error', 'Pilih pengadaan yang akan diterima.');
        }

        return redirect()->route('penerimaan.create', $id);
    }

    // Mencari pengadaan aktif melalui PenerimaanQuery dan mengembalikan 404 jika tidak ditemukan
    // ID penerimaan terakhir disiapkan untuk mendeteksi pengiriman ulang dan sisa pesanan dibaca per barang
    // Formulir dibuka jika ada sisa barang, sedangkan pesanan yang sudah lengkap mengarahkan pengguna kembali ke daftar
    public function create(int $idpengadaan): View|RedirectResponse
    {
        $pengadaan = $this->query->pengadaanAktif($idpengadaan);

        if (! $pengadaan) {
            abort(404);
        }

        $idTerakhir = $this->query->idTerakhir($idpengadaan);
        $barisPesanan = $this->query->barisPesanan($idpengadaan);

        if ($barisPesanan === []) {
            return redirect()
                ->route('penerimaan.index')
                ->with('error', 'Pengadaan ini tidak memiliki sisa barang untuk diterima.');
        }

        return view('penerimaan.create', [
            'pengadaan' => $pengadaan,
            'barisPesanan' => $barisPesanan,
            'idTerakhir' => $idTerakhir,
        ]);
    }

    // Mencari penerimaan berdasarkan ID melalui PenerimaanQuery dan mengembalikan 404 jika tidak ditemukan
    // Jika ditemukan, halaman detail menampilkan barang yang diterima beserta jumlah dan harganya
    public function show(int $id): View
    {
        $penerimaan = $this->query->headerDetail($id);
        abort_if(! $penerimaan, 404);

        $rincian = $this->query->detailPenerimaan($id, (int) $penerimaan->idpengadaan);

        return view('penerimaan.show', ['penerimaan' => $penerimaan, 'rincian' => $rincian]);
    }

    // Mengambil ID petugas yang sedang masuk dan data yang sudah divalidasi oleh PenerimaanRequest
    // PenerimaanService memeriksa sisa pesanan, mencatat stok masuk, memperbarui harga beli terakhir, dan menyimpan status K dalam satu transaksi
    // Pengguna diarahkan ke detail jika berhasil atau kembali ke formulir dengan input dan pesan jika terjadi kesalahan SQL
    public function store(PenerimaanRequest $request, int $idpengadaan): RedirectResponse
    {
        $idUser = (int) $request->user()->getAuthIdentifier();
        try {
            $id = $this->service->simpan($idpengadaan, $idUser, $request->validated());
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput()->withErrors(['penerimaan' => 'Penerimaan gagal disimpan. Perubahan dibatalkan.']);
        }

        return redirect()->route('penerimaan.show', $id)->with('success', 'Penerimaan berhasil disimpan dan stok bertambah.');
    }

    // Mencari penerimaan melalui PenerimaanQuery, lalu memeriksa status draf dan hak pengguna untuk mengeditnya
    // Formulir menampilkan rincian lama dan sisa pesanan yang dihitung dengan mengecualikan draf ini
    public function edit(int $id): View
    {
        $penerimaan = $this->query->headerUntukEdit($id);
        abort_if(! $penerimaan, 404);
        Gate::authorize('penerimaan.ubah-draf', $penerimaan);

        return view('penerimaan.edit', [
            'penerimaan' => $penerimaan,
            'barisPesanan' => $this->query->barisPesanan((int) $penerimaan->idpengadaan, $id),
            'rincianDraf' => $this->query->rincianDraf($id),
        ]);
    }

    // Meneruskan input dari PenerimaanRequest ke PenerimaanService untuk memproses draf lama
    // Service memeriksa hak pengguna, status, sisa pesanan, mutasi sebelumnya, dan referensi retur
    // Rincian diganti, stok ditambahkan, harga beli diperbarui, dan status menjadi K dalam satu transaksi
    // Pengguna diarahkan ke detail jika berhasil atau kembali ke formulir dengan input dan pesan jika terjadi kesalahan SQL
    public function update(PenerimaanRequest $request, int $id): RedirectResponse
    {
        try {
            $this->service->perbaruiDraf($id, $request->validated());
        } catch (QueryException $exception) {
            report($exception);

            return back()->withInput()->withErrors(['penerimaan' => 'Penerimaan gagal disimpan. Perubahan dibatalkan.']);
        }

        return redirect()->route('penerimaan.show', $id)->with('success', 'Penerimaan berhasil disimpan dan stok bertambah.');
    }

    // PenerimaanService mengunci transaksi dan memeriksa status draf, hak pengguna, mutasi stok, dan referensi retur
    // Jika semua syarat terpenuhi, rincian dan penerimaan dihapus lalu pengguna kembali ke daftar dengan pesan berhasil
    public function destroy(int $id): RedirectResponse
    {
        $this->service->hapusDraf($id);

        return redirect()->route('penerimaan.index')->with('success', 'Draf penerimaan berhasil dihapus.');
    }
}
