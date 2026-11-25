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

<div class="md3-form-grid">
    <div class="md3-form-grid__wide">
        <x-ui.text-field id="nama" name="nama" label="Nama Barang" leading-icon="package" maxlength="45" :value="$barang->nama ?? null" required />
    </div>
    <x-ui.select id="jenis" name="jenis" label="Jenis" :options="$opsiJenis" :value="$barang->jenis ?? null" required />
    <x-ui.select id="idsatuan" name="idsatuan" label="Satuan" :options="$opsiSatuan" :value="$barang->idsatuan ?? null" required />
    <x-ui.text-field
        id="harga"
        name="harga"
        :label="$hargaTerkunci ? 'Harga beli terakhir (Rp)' : 'Harga beli awal (Rp)'"
        :leading-icon="$hargaTerkunci ? 'lock' : null"
        :helper="$hargaTerkunci ? 'Harga mengikuti penerimaan barang terakhir.' : null"
        type="number"
        inputmode="numeric"
        min="0"
        step="1"
        :value="$barang->harga ?? null"
        :use-old="! $hargaTerkunci"
        :readonly="$hargaTerkunci"
        :required="! $hargaTerkunci"
    />
    <x-ui.select id="status" name="status" label="Status" :options="[1 => 'Aktif', 0 => 'Tidak Aktif']" :value="$barang->status ?? 1" required />
</div>
