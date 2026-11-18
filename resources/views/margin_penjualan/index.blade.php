@extends('layouts.app')

@section('title', 'Margin Penjualan')
@section('heading', 'Margin Penjualan')
@section('description', 'Data persentase margin penjualan.')

@section('content')
    <p>Jumlah data: <strong>{{ count($daftarMargin) }}</strong></p>

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
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data margin penjualan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
