@php
    $barangError = $index === null ? null : $errors->first("idbarang.$index");
    $jumlahError = $index === null ? null : $errors->first("jumlah.$index");
@endphp

<div class="md3-form-item md3-order-line baris-barang">
    <label class="md3-field">
        <span class="md3-field__label">Barang</span>
        <select class="md3-control md3-control--select @if ($barangError) md3-control--error @endif"
            name="idbarang[]" required @if ($barangError) aria-invalid="true" aria-describedby="barang-error-{{ $index }}" @endif>
            <option value="">Pilih barang</option>
            @foreach ($daftarBarang as $barang)
                <option value="{{ $barang->idbarang }}" data-harga="{{ $barang->harga }}"
                    @selected(is_scalar($idBarang) && (string) $idBarang === (string) $barang->idbarang)>
                    {{ $barang->nama }}
                </option>
            @endforeach
        </select>
        @if ($barangError)
            <span class="md3-field__error" id="barang-error-{{ $index }}">{{ $barangError }}</span>
        @endif
    </label>
    <label class="md3-field">
        <span class="md3-field__label">Jumlah</span>
        <input class="md3-control @if ($jumlahError) md3-control--error @endif" name="jumlah[]" type="number"
            inputmode="numeric" min="1" step="1" value="{{ $jumlah }}" required
            @if ($jumlahError) aria-invalid="true" aria-describedby="jumlah-error-{{ $index }}" @endif>
        @if ($jumlahError)
            <span class="md3-field__error" id="jumlah-error-{{ $index }}">{{ $jumlahError }}</span>
        @endif
    </label>
    <div class="md3-form-item__amount">
        <span class="md3-type-label-large md3-muted">Harga Satuan</span>
        <strong class="harga-satuan">Rp 0</strong>
    </div>
    <div class="md3-form-item__amount">
        <span class="md3-type-label-large md3-muted">Subtotal</span>
        <strong class="subtotal-baris">Rp 0</strong>
    </div>
    <x-ui.icon-button variant="outlined" size="small" class="hapus-baris md3-action--error"
        icon="trash" label="Hapus baris barang" title="Hapus baris barang" />
</div>
