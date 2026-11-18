@extends('layouts.app')

@section('title', 'Edit Margin Penjualan')
@section('heading', 'Edit Margin Penjualan')
@section('description', 'Perbarui persentase, status, atau user pencatatnya.')

@section('content')
    <form method="POST" action="{{ route('margin-penjualan.update', $margin->idmargin_penjualan) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label" for="persen">Persentase</label>
            <div class="input-group">
                <input
                    class="form-control @error('persen') is-invalid @enderror"
                    id="persen"
                    name="persen"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    value="{{ old('persen', $margin->persen) }}"
                    required
                >
                <span class="input-group-text">%</span>
                @error('persen')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="status">Status</label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="1" @selected(old('status', $margin->status) == 1)>Aktif</option>
                <option value="0" @selected(old('status', $margin->status) == 0)>Tidak Aktif</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="iduser">User</label>
            <select class="form-select @error('iduser') is-invalid @enderror" id="iduser" name="iduser" required>
                @foreach ($daftarUser as $user)
                    <option value="{{ $user->iduser }}" @selected(old('iduser', $margin->iduser) == $user->iduser)>
                        {{ $user->username }}
                    </option>
                @endforeach
            </select>
            @error('iduser')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            <a class="btn btn-secondary" href="{{ route('margin-penjualan.index') }}">Batal</a>
        </div>
    </form>
@endsection
