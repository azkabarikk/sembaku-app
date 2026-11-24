<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenjualanRequest;
use App\Queries\Transaksi\PenjualanQuery;
use App\Services\Transaksi\PenjualanService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PenjualanController extends Controller
{
    // Menyiapkan query katalog dan service untuk memproses pembayaran kasir
    public function __construct(
        private readonly PenjualanQuery $query,
        private readonly PenjualanService $service,
    ) {}

    // Mencari barang aktif berdasarkan nama atau ID dan menyiapkan token untuk pembayaran kasir
    public function index(Request $request): View|JsonResponse
    {
        $input = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $margin = $this->query->marginAktif();

        $data = [
            'daftarBarang' => $margin ? $this->query->daftarBarang((string) $margin->persen, trim($input['q'] ?? '')) : [],
            'margin' => $margin,
            'persenPpn' => PenjualanService::PERSEN_PPN,
            'tokenPembayaran' => $this->siapkanTokenPembayaran($request),
        ];

        return $request->expectsJson() ? response()->json($data) : view('penjualan.index', $data);
    }

    // Memeriksa token kasir, menyimpan penjualan melalui service, lalu mengembalikan bukti pembayaran
    public function store(PenjualanRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $token = $validated['token_pembayaran'];
        $pembayaran = $request->session()->get('penjualan.pembayaran', []);

        if (! array_key_exists($token, $pembayaran)) {
            throw ValidationException::withMessages([
                'penjualan' => 'Sesi pembayaran sudah tidak berlaku. Muat ulang halaman Penjualan.',
            ]);
        }

        if ($pembayaran[$token] !== null) {
            $bukti = $pembayaran[$token];
            // Sesi lama hanya menyimpan ID penjualan
            if (is_int($bukti)) {
                $bukti = ['idpenjualan' => $bukti];
            }

            return $this->hasilPembayaran($request, $bukti);
        }

        try {
            $bukti = $this->service->simpan(
                (int) $request->user()->getAuthIdentifier(),
                $validated
            );
        } catch (QueryException $exception) {
            report($exception);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pembayaran gagal diproses. Seluruh perubahan telah dibatalkan.'], 500);
            }

            return back()->withInput()->withErrors([
                'penjualan' => 'Pembayaran gagal diproses. Seluruh perubahan telah dibatalkan.',
            ]);
        }

        $pembayaran[$token] = $bukti;
        $request->session()->put('penjualan.pembayaran', $pembayaran);

        return $this->hasilPembayaran($request, $bukti, true);
    }

    // Mengirim bukti transaksi sebagai JSON atau menyediakannya di sesi setelah kasir kembali ke halaman POS
    private function hasilPembayaran(Request $request, array $bukti, bool $baru = false): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json($bukti, $baru ? 201 : 200);
        }

        return redirect()->route('penjualan.index')
            ->with('bukti_pembayaran', $bukti)
            ->with('success', "Penjualan #{$bukti['idpenjualan']} berhasil diproses.");
    }

    // Menyiapkan token pembayaran dan mempertahankannya saat validasi gagal
    private function siapkanTokenPembayaran(Request $request): string
    {
        $pembayaran = $request->session()->get('penjualan.pembayaran', []);
        $tokenLama = old('token_pembayaran');
        if ($tokenLama === null && $request->has('q')) {
            $tokenLama = array_search(null, array_reverse($pembayaran, true), true);
        }
        if (is_string($tokenLama) && array_key_exists($tokenLama, $pembayaran) && $pembayaran[$tokenLama] === null) {
            return $tokenLama;
        }

        $token = (string) Str::uuid();
        $pembayaran = array_slice($pembayaran, -9, null, true);
        $pembayaran[$token] = null;
        $request->session()->put('penjualan.pembayaran', $pembayaran);

        return $token;
    }
}
