@extends('layouts.app')

@section('title', 'Barang')

@section('page-header')
    <x-ui.page-header title="Daftar Barang" subtitle="Data barang persediaan.">
        <x-slot:actions>
            <x-ui.button :href="route('barang.create')">
                <x-slot:icon><x-ui.icon name="add" /></x-slot:icon>
                Tambah Barang
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>
@endsection

@section('content')
    @php
        $perHalaman = 20;
        $jumlahBarang = count($daftarBarang);
        $halamanTerakhir = max(1, (int) ceil($jumlahBarang / $perHalaman));
        $halamanAktif = min(\Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage(), $halamanTerakhir);
        $halamanBarang = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($daftarBarang, ($halamanAktif - 1) * $perHalaman, $perHalaman),
            $jumlahBarang,
            $perHalaman,
            $halamanAktif,
            ['path' => request()->url()],
        );
        $halamanBarang->withQueryString();
    @endphp

    @if (session('success'))
        <x-ui.snackbar variant="success">{{ session('success') }}</x-ui.snackbar>
    @endif

    <x-ui.card variant="outlined">
        <x-slot:header>
            <span class="md3-type-label-large md3-muted">Jumlah data <strong>{{ $jumlahBarang }}</strong></span>
        </x-slot:header>

        <x-ui.table label="Daftar Barang">
            <x-slot:head>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nama Barang</th>
                    <th scope="col">Jenis</th>
                    <th scope="col">Satuan</th>
                    <th scope="col" class="md3-table__numeric">Harga Barang</th>
                    <th scope="col">Status</th>
                    <th scope="col">Aksi</th>
                </tr>
            </x-slot:head>
            @forelse ($halamanBarang as $barang)
                <tr>
                    <td>{{ $barang->idbarang }}</td>
                    <td>{{ $barang->nama }}</td>
                    <td>{{ $barang->jenis === 'S' ? 'Sembako' : 'Rumah Tangga' }}</td>
                    <td>{{ $barang->nama_satuan }}</td>
                    <td class="md3-table__numeric">Rp {{ number_format($barang->harga, 0, ',', '.') }}</td>
                    <td>
                        <x-ui.chip :variant="$barang->status == 1 ? 'success' : 'neutral'">
                            {{ $barang->status == 1 ? 'Aktif' : 'Tidak Aktif' }}
                        </x-ui.chip>
                    </td>
                    <td>
                        <div class="md3-cluster md3-table__actions">
                            <x-ui.button variant="outlined" size="small" :href="route('barang.edit', $barang->idbarang)">Edit</x-ui.button>

                            @if ($barang->status == 1)
                                <form
                                    method="POST"
                                    action="{{ route('barang.destroy', $barang->idbarang) }}"
                                    onsubmit="return confirm('Nonaktifkan barang ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="outlined" size="small" class="md3-action--error" type="submit">Nonaktifkan</x-ui.button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"><x-ui.empty-state title="Belum ada data barang." /></td>
                </tr>
            @endforelse
        </x-ui.table>

        @if ($halamanBarang->hasPages())
            <x-ui.pagination label="Halaman daftar barang" class="md3-cluster">
                <x-ui.button variant="outlined" size="small" :href="$halamanBarang->previousPageUrl()" :disabled="$halamanBarang->onFirstPage()">
                    Sebelumnya
                </x-ui.button>
                <span class="md3-type-label-large">Halaman {{ $halamanBarang->currentPage() }} dari {{ $halamanBarang->lastPage() }}</span>
                <x-ui.button variant="outlined" size="small" :href="$halamanBarang->nextPageUrl()" :disabled="! $halamanBarang->hasMorePages()">
                    Berikutnya
                </x-ui.button>
            </x-ui.pagination>
        @endif
    </x-ui.card>
@endsection
