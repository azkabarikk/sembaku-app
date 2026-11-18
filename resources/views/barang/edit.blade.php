<!DOCTYPE html>
<html>
<head>
    <title>Edit Barang</title>
</head>
<body>

<h1>Edit Barang</h1>

<form method="POST" action="{{ route('barang.update', $barang->idbarang) }}">
    @csrf
    @method('PUT')

    <label>Nama Barang</label><br>
    <input type="text" name="nama"
           value="{{ old('nama', $barang->nama) }}">
    @error('nama')
        <div>{{ $message }}</div>
    @enderror

    <br><br>

    <label>Jenis</label><br>
    <select name="jenis">
        <option value="R" {{ old('jenis', $barang->jenis) == 'R' ? 'selected' : '' }}>
            R
        </option>
        <option value="S" {{ old('jenis', $barang->jenis) == 'S' ? 'selected' : '' }}>
            S
        </option>
    </select>

    @error('jenis')
        <div>{{ $message }}</div>
    @enderror

    <br><br>

    <label>Satuan</label><br>
    <select name="idsatuan">
        @foreach ($daftarSatuan as $satuan)
            <option value="{{ $satuan->idsatuan }}"
                {{ old('idsatuan', $barang->idsatuan) == $satuan->idsatuan ? 'selected' : '' }}>
                {{ $satuan->nama_satuan }}
            </option>
        @endforeach
    </select>

    @error('idsatuan')
        <div>{{ $message }}</div>
    @enderror

    <br><br>

    <label>Harga</label><br>
    <input type="number" name="harga"
           value="{{ old('harga', $barang->harga) }}">

    @error('harga')
        <div>{{ $message }}</div>
    @enderror

    <br><br>

    <label>Status</label><br>
    <select name="status">
        <option value="1" {{ old('status', $barang->status) == 1 ? 'selected' : '' }}>
            Aktif
        </option>
        <option value="0" {{ old('status', $barang->status) == 0 ? 'selected' : '' }}>
            Tidak Aktif
        </option>
    </select>

    <br><br>

    <button type="submit">Simpan Perubahan</button>
    <a href="{{ route('barang.index') }}">Batal</a>
</form>

</body>
</html>
