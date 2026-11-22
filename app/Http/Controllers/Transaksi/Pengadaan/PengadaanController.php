<?php

namespace App\Http\Controllers\Transaksi\Pengadaan;

use App\Http\Controllers\Controller;
use App\Queries\Transaksi\PengadaanQuery;
use App\Services\Transaksi\PengadaanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengadaanController extends Controller
{
    // Menyiapkan PengadaanQuery untuk membaca pesanan dan PengadaanService untuk menyimpan perubahannya
    public function __construct(
        private readonly PengadaanQuery $query,
        private readonly PengadaanService $service,
    ) {}

    // PengadaanQuery mengambil jumlah pesanan dan sisa yang belum diterima
    // Halaman daftar menampilkan ringkasan tersebut untuk setiap pengadaan
    public function index(): View
    {
        return view('pengadaan.index', [
            'daftarPengadaan' => $this->query->daftarPengadaan(),
            'ringkasan' => $this->query->ringkasan(),
        ]);
    }

    // Mencari pesanan dengan PengadaanQuery dan mengembalikan 404 jika ID tidak ditemukan
    // Jika ditemukan, rincian barang diambil dan ditampilkan di halaman detail pengadaan
    public function show(int $id): View
    {
        $pengadaan = $this->query->headerDetail($id);

        abort_if(! $pengadaan, 404);

        return view('pengadaan.show', [
            'pengadaan' => $pengadaan,
            'rincian' => $this->query->rincianPengadaan($id)[$id] ?? [],
        ]);
    }

    // Menyiapkan formulir pesanan baru dengan pilihan vendor dan barang aktif dari PengadaanQuery
    // Formulir dibuka dengan rincian kosong dan tarif PPN dari PengadaanService
    public function create(): View
    {
        return view('pengadaan.create', [
            'daftarVendor' => $this->query->daftarVendorAktif(),
            'persenPpn' => PengadaanService::PERSEN_PPN,
            'daftarBarang' => $this->query->daftarBarangAktif(),
            'barisDetail' => [],
        ]);
    }

    // Memvalidasi vendor, barang, dan jumlah, lalu mengambil ID petugas yang sedang masuk
    // PengadaanService memeriksa vendor dan barang, menyimpan harga saat pemesanan, serta menghitung subtotal, PPN 11%, dan total
    // Setelah pesanan tersimpan, pengguna diarahkan ke detail pengadaan
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->aturanValidasi());
        $idUser = (int) $request->user()->getAuthIdentifier();
        $idPengadaan = $this->service->simpan($validated, $idUser);

        return redirect()
            ->route('pengadaan.show', $idPengadaan)
            ->with('success', 'Pengadaan berhasil ditambahkan.');
    }

    // Mencari pesanan melalui PengadaanQuery dan menolak pengeditan jika sudah ada penerimaan
    // Jika pesanan ditemukan dan boleh diubah, formulir menampilkan vendor, pilihan barang, dan rincian pesanan
    public function edit(int $id): View
    {
        $pengadaan = $this->query->headerUntukEdit($id);

        if (! $pengadaan) {
            abort(404);
        }

        if ($this->query->sudahMemilikiPenerimaan($id)) {
            abort(403, 'Pengadaan tidak dapat diubah setelah memiliki penerimaan.');
        }

        return view('pengadaan.edit', [
            'pengadaan' => $pengadaan,
            'daftarVendor' => $this->query->daftarVendorAktif((int) $pengadaan->vendor_idvendor),
            'persenPpn' => PengadaanService::PERSEN_PPN,
            'daftarBarang' => $this->query->daftarBarangUntukEdit($id),
            'barisDetail' => $this->query->detailUntukEdit($id),
        ]);
    }

    // Memvalidasi vendor, barang, jumlah, dan status A atau N sebelum PengadaanService mengunci pesanan
    // Perubahan ditolak jika sudah ada penerimaan dan harga pesanan lama dipertahankan untuk barang yang sama
    // Setelah rincian, PPN, dan total diperbarui, pengguna kembali ke daftar
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            ...$this->aturanValidasi(),
            'status' => 'required|in:A,N',
        ]);

        $this->service->perbarui($id, $validated);

        return redirect()
            ->route('pengadaan.index')
            ->with('success', 'Pengadaan berhasil diperbarui.');
    }

    // PengadaanService mengunci pesanan dan memeriksa apakah sudah ada penerimaan
    // Jika belum ada, rincian dan pesanan dihapus lalu pengguna kembali ke daftar
    // Jika sudah ada penerimaan, halaman daftar menampilkan alasan penghapusan ditolak
    public function destroy(int $id): RedirectResponse
    {
        if (! $this->service->hapus($id)) {
            return redirect()->route('pengadaan.index')->with('error', 'Pengadaan tidak dapat dihapus karena sudah memiliki penerimaan.');
        }

        return redirect()->route('pengadaan.index')->with('success', 'Pengadaan berhasil dihapus.');
    }

    // Mengembalikan aturan untuk membuat dan mengubah pesanan dengan ID vendor berupa bilangan bulat
    // Daftar barang dan jumlah wajib diisi, ID barang harus unik, dan setiap jumlah harus bilangan bulat dari 1 sampai 2147483647
    private function aturanValidasi(): array
    {
        return [
            'vendor_idvendor' => 'required|integer',
            'idbarang' => 'required|array|list|min:1',
            'idbarang.*' => 'required|integer|distinct',
            'jumlah' => 'required|array|list|min:1',
            'jumlah.*' => 'required|integer|min:1|max:2147483647',
        ];
    }
}
