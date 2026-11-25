@extends('layouts.app')

@section('title', 'Barang')

@section('page-header')
    <x-ui.page-header title="Daftar Barang" subtitle="Data barang persediaan." icon="package">
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
        <x-ui.data-table compact label="Daftar Barang" :filters="['Jenis' => 2, 'Status' => 5]" :paginator="$halamanBarang">
            <x-slot:head>
                <tr>
                    <th scope="col" class="md3-table__identity">Nama Barang</th>
                    <th scope="col">ID</th>
                    <th scope="col">Jenis</th>
                    <th scope="col">Satuan</th>
                    <th scope="col" class="md3-table__numeric">Harga Barang</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="md3-table__action-column">Aksi</th>
                </tr>
            </x-slot:head>
            @forelse ($daftarBarang as $barang)
                <tr @if ($loop->iteration < $halamanBarang->firstItem() || $loop->iteration > $halamanBarang->lastItem()) hidden @endif>
                    <td class="md3-table__identity">{{ $barang->nama }}</td>
                    <td>{{ $barang->idbarang }}</td>
                    <td>{{ $barang->jenis === 'S' ? 'Sembako' : 'Rumah Tangga' }}</td>
                    <td>{{ $barang->nama_satuan }}</td>
                    <td class="md3-table__numeric">Rp {{ number_format($barang->harga, 0, ',', '.') }}</td>
                    <td>
                        <x-ui.chip :variant="$barang->status == 1 ? 'success' : 'neutral'">
                            {{ $barang->status == 1 ? 'Aktif' : 'Tidak Aktif' }}
                        </x-ui.chip>
                    </td>
                    <td class="md3-table__action-column">
                        <div class="md3-cluster md3-table__actions">
                            <x-ui.button variant="outlined" size="small" icon-name="edit" :href="route('barang.edit', $barang->idbarang)"><span class="md3-table__action-label">Edit</span></x-ui.button>

                            @if ($barang->status == 1)
                                <form
                                    method="POST"
                                    action="{{ route('barang.destroy', $barang->idbarang) }}"
                                    data-md3-confirm="Nonaktifkan barang “{{ $barang->nama }}”? Barang tetap tercatat sebagai tidak aktif."
                                    data-md3-confirm-action="Nonaktifkan"
                                    onsubmit="return confirm('Nonaktifkan barang ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="outlined" size="small" class="md3-action--error" type="submit" icon-name="archive"><span class="md3-table__action-label">Nonaktifkan</span></x-ui.button>
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
        </x-ui.data-table>

    </x-ui.card>
@endsection
