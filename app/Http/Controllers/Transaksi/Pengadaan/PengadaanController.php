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
    public function __construct(
        private readonly PengadaanQuery $query,
        private readonly PengadaanService $service,
    ) {}

    public function index(): View
    {
        return view('pengadaan.index', [
            'daftarPengadaan' => $this->query->daftarPengadaan(),
            'ringkasan' => $this->query->ringkasan(),
        ]);
    }

    public function show(int $id): View
    {
        $pengadaan = $this->query->headerDetail($id);

        abort_if(! $pengadaan, 404);

        return view('pengadaan.show', [
            'pengadaan' => $pengadaan,
            'rincian' => $this->query->rincianPengadaan($id)[$id] ?? [],
        ]);
    }

    public function create(): View
    {
        return view('pengadaan.create', [
            'daftarVendor' => $this->query->daftarVendorAktif(),
            'persenPpn' => PengadaanService::PERSEN_PPN,
            'daftarBarang' => $this->query->daftarBarangAktif(),
            'barisDetail' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->aturanValidasi());
        $idUser = (int) $request->user()->getAuthIdentifier();
        $idPengadaan = $this->service->simpan($validated, $idUser);

        return redirect()
            ->route('pengadaan.show', $idPengadaan)
            ->with('success', 'Pengadaan berhasil ditambahkan.');
    }

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

    public function destroy(int $id): RedirectResponse
    {
        if (! $this->service->hapus($id)) {
            return redirect()->route('pengadaan.index')->with('error', 'Pengadaan tidak dapat dihapus karena sudah memiliki penerimaan.');
        }

        return redirect()->route('pengadaan.index')->with('success', 'Pengadaan berhasil dihapus.');
    }

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
