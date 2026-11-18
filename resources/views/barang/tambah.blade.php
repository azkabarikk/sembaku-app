<!DOCTYPE html>
<html>
<head>
    <title>Tambah Barang</title>
</head>
<body>

    <h1>Tambah Barang</h1>

    <form method="POST" action="{{ route('barang.store') }}">
        @csrf

        {{-- Nama --}}
        <div>
            <label>Nama Barang</label><br>
            <input
                type="text"
                name="nama"
                value="{{ old('nama') }}"
            >

            @error('nama')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <br>

        {{-- Jenis --}}
        <div>
            <label>Jenis</label><br>

            <select name="jenis">
            <option value="">-- Pilih Jenis --</option>
            <option value="S" {{ old('jenis') == 'S' ? 'selected' : '' }}>
                Sembako
            </option>
            <option value="R" {{ old('jenis') == 'R' ? 'selected' : '' }}>
                Rumah Tangga
            </option>
</select>

@error('jenis')
    <div>{{ $message }}</div>
@enderror
        </div>

        <br>

        {{-- Satuan --}}
        <div>
            <label>Satuan</label><br>

            <select name="idsatuan">
                <option value="">-- Pilih Satuan --</option>

                @foreach ($daftarSatuan as $satuan)
                    <option
                        value="{{ $satuan->idsatuan }}"
                        {{ old('idsatuan') == $satuan->idsatuan ? 'selected' : '' }}
                    >
                        {{ $satuan->nama_satuan }}
                    </option>
                @endforeach
            </select>

            @error('idsatuan')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <br>

        {{-- Harga --}}
        <div>
            <label>Harga</label><br>

            <input
                type="number"
                name="harga"
                value="{{ old('harga') }}"
                min="0"
            >

            @error('harga')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <br>

        {{-- Status --}}
        <div>
            <label>Status</label><br>

            <select name="status">
                <option value="">-- Pilih Status --</option>

                <option value="1" {{ old('status') === '1' ? 'selected' : '' }}>
                    Aktif
                </option>

                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>
                    Tidak Aktif
                </option>
            </select>

            @error('status')
                <div>{{ $message }}</div>
            @enderror
        </div>

        <br>

        <button type="submit">Simpan</button>

        <a href="{{ route('barang.index') }}">
            Batal
        </a>
    </form>

</body>
</html>
