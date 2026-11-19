@php
    $opsiJenis = ['S' => 'Sembako', 'R' => 'Rumah Tangga'];
    $opsiSatuan = [];

    if ($barang === null) {
        $opsiJenis = ['' => 'Pilih jenis'] + $opsiJenis;
        $opsiSatuan[''] = 'Pilih satuan';
    }

    foreach ($daftarSatuan as $satuan) {
        $opsiSatuan[$satuan->idsatuan] = $satuan->nama_satuan;
        if ($barang !== null && $satuan->status != 1) {
            $opsiSatuan[$satuan->idsatuan] .= ' (Tidak Aktif)';
        }
    }
@endphp

<x-ui.text-field id="nama" name="nama" label="Nama Barang" maxlength="45" :value="$barang->nama ?? null" required />
<x-ui.select id="jenis" name="jenis" label="Jenis" :options="$opsiJenis" :value="$barang->jenis ?? null" required />
<x-ui.select id="idsatuan" name="idsatuan" label="Satuan" :options="$opsiSatuan" :value="$barang->idsatuan ?? null" required />
<x-ui.text-field
    id="harga"
    name="harga"
    :label="$hargaTerkunci ? 'Harga beli terakhir' : 'Harga beli awal'"
    type="number"
    min="0"
    step="1"
    :value="$barang->harga ?? null"
    :use-old="! $hargaTerkunci"
    :readonly="$hargaTerkunci"
    :required="! $hargaTerkunci"
/>
<x-ui.select id="status" name="status" label="Status" :options="[1 => 'Aktif', 0 => 'Tidak Aktif']" :value="$barang->status ?? 1" required />
