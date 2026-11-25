@extends('layouts.app')

@section('title', 'Kartu Stok')
@section('heading', 'Kartu Stok')

@section('content')
    <form method="GET" action="{{ route('kartu-stok.index') }}" class="md3-form">
        @if ($idPenerimaan)
            <input type="hidden" name="penerimaan" value="{{ $idPenerimaan }}">
        @endif

        <x-ui.form-section title="Filter riwayat mutasi" icon="search" description="Filter ini berlaku untuk seluruh riwayat kartu stok.">
            <div class="md3-form-grid">
                <x-ui.select label="Barang" id="barang" name="barang">
                    <option value="">Semua barang</option>
                    @foreach ($daftarBarang as $barang)
                        <option value="{{ $barang->idbarang }}" @selected($idBarang == $barang->idbarang)>
                            #{{ $barang->idbarang }} · {{ $barang->nama }}
                        </option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Jenis transaksi" id="jenis" name="jenis">
                    <option value="">Semua jenis</option>
                    @foreach ($daftarJenis as $kode)
                        <option value="{{ $kode }}" @selected($jenis === $kode)>{{ match ($kode) {
                            'M', 'P' => 'Penerimaan ('.$kode.')',
                            'B' => 'Koreksi Penerimaan (B)',
                            'J' => 'Penjualan (J)',
                            'R' => 'Retur ke Vendor (R)',
                            default => $kode,
                        } }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.text-field label="Tanggal" id="tanggal" name="tanggal" type="date" :value="$tanggal" :use-old="false" />
            </div>
            <div class="md3-form-actions">
                @if ($idBarang || $idPenerimaan || $jenis || $tanggal)
                    <x-ui.button variant="outlined" icon-name="close" :href="route('kartu-stok.index')">Reset</x-ui.button>
                @endif
                <x-ui.button icon-name="search" type="submit">Tampilkan</x-ui.button>
            </div>
        </x-ui.form-section>
    </form>

    @if ($barangDipilih)
        <p>Stok saat ini: <strong>{{ $barangDipilih->stok }}</strong></p>
    @endif
    @if ($idPenerimaan && Gate::allows('penerimaan.baca'))
        <p><a href="{{ route('penerimaan.show', $idPenerimaan) }}">Penerimaan #{{ $idPenerimaan }}</a></p>
    @endif

    <x-ui.data-table compact label="Kartu Stok" :paginator="$mutasi" :server="true">
        <x-slot:head>
            <tr>
                <th scope="col" class="md3-table__identity">Barang</th>
                <th scope="col">Tanggal</th>
                <th scope="col">Jenis</th>
                <th scope="col">Transaksi</th>
                <th scope="col">Masuk</th>
                <th scope="col">Keluar</th>
                <th scope="col">Saldo</th>
            </tr>
        </x-slot:head>
        @forelse ($mutasi as $baris)
            <tr>
                <td class="md3-table__identity">{{ $baris->nama_barang ?? '—' }}</td>
                <td>{{ $baris->created_at }}</td>
                <td>{{ match ($baris->jenis_transaksi) {
                    'M', 'P' => 'Penerimaan',
                    'B' => 'Koreksi Penerimaan',
                    'J' => 'Penjualan',
                    'R' => 'Retur ke Vendor',
                    default => $baris->jenis_transaksi,
                } }}</td>
                <td>
                    @if ($baris->idpenerimaan && Gate::allows('penerimaan.baca'))
                        <a href="{{ route('penerimaan.show', $baris->idpenerimaan) }}">#{{ $baris->idtransaksi }}</a>
                    @elseif ($baris->jenis_transaksi === 'R' && Gate::allows('retur.baca'))
                        <a href="{{ route('retur.show', $baris->idtransaksi) }}">#{{ $baris->idtransaksi }}</a>
                    @else
                        #{{ $baris->idtransaksi }}
                    @endif
                </td>
                <td>{{ $baris->masuk }}</td>
                <td>{{ $baris->keluar }}</td>
                <td>{{ $baris->stock }}</td>
            </tr>
        @empty
            <tr>
                <td class="text-center" colspan="7">Belum ada mutasi stok untuk pilihan ini.</td>
            </tr>
        @endforelse
    </x-ui.data-table>

@endsection
