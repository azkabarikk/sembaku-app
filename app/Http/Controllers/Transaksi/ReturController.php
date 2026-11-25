<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReturRequest;
use App\Queries\Transaksi\ReturQuery;
use App\Services\Transaksi\ReturService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturController extends Controller
{
    // Menyiapkan pembacaan retur dan proses penyimpanannya dari layanan yang sudah ada
    public function __construct(
        private readonly ReturQuery $query,
        private readonly ReturService $service,
    ) {}

    // Mengambil riwayat retur dan mengirimkannya sebagai JSON atau membuka halaman retur yang tersedia
    public function index(Request $request): View|JsonResponse
    {
        $daftarRetur = $this->query->daftar();

        return $request->expectsJson()
            ? response()->json(['data' => $daftarRetur])
            : view('retur.index', [
                'daftarRetur' => $daftarRetur,
                'daftarSumber' => $this->query->sumber(),
            ]);
    }

    // Mengambil penerimaan yang sudah membukukan barang agar petugas dapat memilih sumber retur
    public function sumber(): JsonResponse
    {
        return response()->json(['data' => $this->query->sumber()]);
    }

    // Memeriksa penerimaan asal lalu menampilkan jumlah diterima, sudah diretur, dan sisa tiap barang
    public function rincianSumber(int $id): JsonResponse
    {
        $penerimaan = $this->query->headerSumber($id);
        abort_if(! $penerimaan || ! in_array($penerimaan->status, ['K', 'A'], true), 404);

        return response()->json([
            'penerimaan' => $penerimaan,
            'rincian' => $this->query->rincianSumber($id),
        ]);
    }

    // Memeriksa penerimaan yang masih dapat diretur dan menyiapkan rincian barang untuk formulir
    public function create(int $id): View
    {
        $penerimaan = $this->query->headerSumber($id);
        abort_if(! $penerimaan, 404);

        return view('retur.create', [
            'penerimaan' => $penerimaan,
            'rincian' => $this->query->rincianSumber($id),
        ]);
    }

    // Mengambil header dan rincian retur untuk menelusuri penerimaan serta petugas pencatatnya
    public function show(Request $request, int $id): JsonResponse|View
    {
        $retur = $this->query->header($id);
        abort_if(! $retur, 404);

        $rincian = $this->query->rincian($id);

        return $request->expectsJson()
            ? response()->json(['retur' => $retur, 'rincian' => $rincian])
            : view('retur.show', ['retur' => $retur, 'rincian' => $rincian]);
    }

    // Memvalidasi masukan, mengambil ID petugas dari login, lalu menyimpan retur dan mutasi dalam satu transaksi
    public function store(ReturRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        try {
            $id = $this->service->simpan((int) $data['idpenerimaan'], (int) $request->user()->getAuthIdentifier(), $data['items']);
        } catch (QueryException $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Retur gagal disimpan. Seluruh perubahan dibatalkan.'], 500);
            }

            return back()->withInput()->withErrors(['retur' => 'Retur gagal disimpan. Seluruh perubahan dibatalkan.']);
        }

        return $request->expectsJson()
            ? response()->json(['idretur' => $id], 201)
            : redirect()->route('retur.show', $id)->with('success', "Retur #{$id} berhasil disimpan.");
    }
}
