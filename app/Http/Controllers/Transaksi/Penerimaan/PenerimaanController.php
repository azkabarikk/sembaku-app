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
    public function __construct(
        private readonly PenerimaanQuery $query,
        private readonly PenerimaanService $service,
    ) {}

    public function index(Request $request): View
    {
        $idPengadaan = filter_var($request->query('pengadaan'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

        return view('penerimaan.index', [
            'daftarPenerimaan' => $this->query->daftarPenerimaan($idPengadaan),
            'idPengadaan' => $idPengadaan,
            'pengadaanTersisa' => $this->query->pengadaanTersisa(),
        ]);
    }

    public function pilihPengadaan(Request $request): RedirectResponse
    {
        $id = filter_var($request->query('pengadaan'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($id === false) {
            return redirect()->route('penerimaan.index')->with('error', 'Pilih pengadaan yang akan diterima.');
        }

        return redirect()->route('penerimaan.create', $id);
    }

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

    public function show(int $id): View
    {
        $penerimaan = $this->query->headerDetail($id);
        abort_if(! $penerimaan, 404);

        $rincian = $this->query->detailPenerimaan($id, (int) $penerimaan->idpengadaan);

        return view('penerimaan.show', ['penerimaan' => $penerimaan, 'rincian' => $rincian]);
    }

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

    public function destroy(int $id): RedirectResponse
    {
        $this->service->hapusDraf($id);

        return redirect()->route('penerimaan.index')->with('success', 'Draf penerimaan berhasil dihapus.');
    }
}
