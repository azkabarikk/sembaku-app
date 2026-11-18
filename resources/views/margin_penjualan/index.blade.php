@extends('layouts.app')

@section('title', 'Margin Penjualan')
@section('heading', 'Margin Penjualan')
@section('description', 'Data persentase margin penjualan.')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="mb-0">Jumlah data: <strong>{{ count($daftarMargin) }}</strong></p>
        <a class="btn btn-primary" href="{{ route('margin-penjualan.create') }}">Tambah Margin</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Dibuat</th>
                    <th scope="col">Persentase</th>
                    <th scope="col">Status</th>
                    <th scope="col">User</th>
                    <th scope="col">Diperbarui</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarMargin as $margin)
                    <tr>
                        <td>{{ $margin->idmargin_penjualan }}</td>
                        <td>{{ $margin->created_at }}</td>
                        <td>{{ number_format((float) $margin->persen, 2, ',', '.') }}%</td>
                        <td>
                            @if ($margin->status == 1)
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>{{ $margin->username }}</td>
                        <td>{{ $margin->updated_at ?? '—' }}</td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('margin-penjualan.edit', $margin->idmargin_penjualan) }}">Edit</a>

                            @if ($margin->status == 1)
                                <form
                                    class="d-inline"
                                    method="POST"
                                    action="{{ route('margin-penjualan.destroy', $margin->idmargin_penjualan) }}"
                                    onsubmit="return confirm('Nonaktifkan margin ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Nonaktifkan</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data margin penjualan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
