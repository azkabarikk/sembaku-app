@php
    $sedangEdit = isset($pengadaan);
    $inputBarang = old('idbarang', array_map(static fn ($baris) => $baris->idbarang, $barisDetail));
    $inputJumlah = old('jumlah', array_map(static fn ($baris) => $baris->jumlah, $barisDetail));

    if (! is_array($inputBarang) || $inputBarang === []) {
        $inputBarang = [''];
        $inputJumlah = [1];
    }

    $inputJumlah = is_array($inputJumlah) ? $inputJumlah : [];
@endphp

<form method="POST" action="{{ $sedangEdit ? route('pengadaan.update', $pengadaan->idpengadaan) : route('pengadaan.store') }}">
    @csrf
    @if ($sedangEdit)
        @method('PUT')
    @endif

    @if ($errors->has('pengadaan'))
        <div class="alert alert-danger">{{ $errors->first('pengadaan') }}</div>
    @endif
    @if ($errors->has('idbarang'))
        <div class="alert alert-danger">{{ $errors->first('idbarang') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label" for="vendor_idvendor">Vendor</label>
            <select class="form-select @error('vendor_idvendor') is-invalid @enderror" id="vendor_idvendor" name="vendor_idvendor" required>
                <option value="">Pilih vendor</option>
                @foreach ($daftarVendor as $vendor)
                    <option value="{{ $vendor->idvendor }}" @selected(old('vendor_idvendor', $pengadaan->vendor_idvendor ?? '') == $vendor->idvendor)>
                        {{ $vendor->nama_vendor }}
                    </option>
                @endforeach
            </select>
            @error('vendor_idvendor')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @if ($sedangEdit)
            <div class="col-md-6">
                <label class="form-label" for="status">Status penerimaan</label>
                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                    <option value="A" @selected(old('status', $pengadaan->status) === 'A')>Siap diterima</option>
                    <option value="N" @selected(old('status', $pengadaan->status) === 'N')>Ditahan</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        @endif
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h2 class="h5 mb-0">Barang Pengadaan</h2>
        <button class="btn btn-outline-primary btn-sm" id="tambahBaris" type="button">Tambah Barang</button>
    </div>

    <div id="daftarBarisBarang" class="mb-3">
        @foreach ($inputBarang as $index => $idBarang)
            <div class="card mb-2 baris-barang">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-5">
                            <label class="form-label" for="idbarang-{{ $index }}">Barang</label>
                            <select class="form-select" id="idbarang-{{ $index }}" name="idbarang[]" required>
                                <option value="">Pilih barang</option>
                                @foreach ($daftarBarang as $barang)
                                    <option value="{{ $barang->idbarang }}" data-harga="{{ $barang->harga }}" @selected(is_scalar($idBarang) && (string) $idBarang === (string) $barang->idbarang)>
                                        {{ $barang->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has("idbarang.$index"))
                                <div class="text-danger small mt-1">{{ $errors->first("idbarang.$index") }}</div>
                            @endif
                        </div>
                        <div class="col-sm-4 col-lg-2">
                            <label class="form-label" for="jumlah-{{ $index }}">Jumlah</label>
                            <input class="form-control hitung-barang" id="jumlah-{{ $index }}" name="jumlah[]" type="number" min="1" step="1" value="{{ is_scalar($inputJumlah[$index] ?? null) ? $inputJumlah[$index] : 1 }}" required>
                            @if ($errors->has("jumlah.$index"))
                                <div class="text-danger small mt-1">{{ $errors->first("jumlah.$index") }}</div>
                            @endif
                        </div>
                        <div class="col-sm-5 col-lg-3">
                            <label class="form-label" for="harga-{{ $index }}">Harga Satuan</label>
                            <input class="form-control harga-satuan" id="harga-{{ $index }}" type="text" value="Rp 0" readonly>
                        </div>
                        <div class="col-sm-8 col-lg-1">
                            <span class="d-block small text-muted">Subtotal</span>
                            <strong class="subtotal-baris">Rp 0</strong>
                        </div>
                        <div class="col-sm-4 col-lg-1 text-end">
                            <button class="btn btn-outline-danger btn-sm hapus-baris" type="button">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card bg-light mb-4">
        <div class="card-body ms-auto" style="min-width: 260px;">
            <div class="d-flex justify-content-between gap-4"><span>Subtotal</span><strong id="totalSubtotal">Rp 0</strong></div>
            <div class="d-flex justify-content-between gap-4"><span>PPN ({{ $persenPpn }}%)</span><strong id="totalPpn">Rp 0</strong></div>
            <hr class="my-2">
            <div class="d-flex justify-content-between gap-4"><span>Total</span><strong id="totalAkhir">Rp 0</strong></div>
        </div>
    </div>

    <template id="templateBarisBarang">
        <div class="card mb-2 baris-barang">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label">Barang</label>
                        <select class="form-select" name="idbarang[]" required>
                            <option value="">Pilih barang</option>
                            @foreach ($daftarBarang as $barang)
                                <option value="{{ $barang->idbarang }}" data-harga="{{ $barang->harga }}">{{ $barang->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4 col-lg-2">
                        <label class="form-label">Jumlah</label>
                        <input class="form-control hitung-barang" name="jumlah[]" type="number" min="1" step="1" value="1" required>
                    </div>
                    <div class="col-sm-5 col-lg-3">
                        <label class="form-label">Harga Satuan</label>
                        <input class="form-control harga-satuan" aria-label="Harga Satuan" type="text" value="Rp 0" readonly>
                    </div>
                    <div class="col-sm-8 col-lg-1">
                        <span class="d-block small text-muted">Subtotal</span>
                        <strong class="subtotal-baris">Rp 0</strong>
                    </div>
                    <div class="col-sm-4 col-lg-1 text-end">
                        <button class="btn btn-outline-danger btn-sm hapus-baris" type="button">Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">{{ $sedangEdit ? 'Simpan Perubahan' : 'Simpan Pengadaan' }}</button>
        <a class="btn btn-secondary" href="{{ route('pengadaan.index') }}">Batal</a>
    </div>
</form>

<script>
    (() => {
        const list = document.getElementById('daftarBarisBarang');
        const template = document.getElementById('templateBarisBarang');
        const rupiah = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });

        const formatRupiah = (value) => `Rp ${rupiah.format(value)}`;

        const hitungTotal = () => {
            let subtotal = 0;

            list.querySelectorAll('.baris-barang').forEach((row) => {
                const jumlah = Number(row.querySelector('[name="jumlah[]"]').value || 0);
                const barang = row.querySelector('select').selectedOptions[0];
                const harga = Number(barang?.dataset.harga || 0);
                row.querySelector('.harga-satuan').value = formatRupiah(harga);
                const nilaiBaris = jumlah * harga;
                subtotal += nilaiBaris;
                row.querySelector('.subtotal-baris').textContent = formatRupiah(nilaiBaris);
            });

            const ppn = Math.floor((subtotal * {{ $persenPpn }} + 50) / 100);
            document.getElementById('totalSubtotal').textContent = formatRupiah(subtotal);
            document.getElementById('totalPpn').textContent = formatRupiah(ppn);
            document.getElementById('totalAkhir').textContent = formatRupiah(subtotal + ppn);
        };

        document.getElementById('tambahBaris').addEventListener('click', () => {
            list.append(template.content.cloneNode(true));
            hitungTotal();
        });

        list.addEventListener('input', hitungTotal);
        list.addEventListener('change', hitungTotal);
        list.addEventListener('click', (event) => {
            if (! event.target.classList.contains('hapus-baris')) {
                return;
            }

            if (list.querySelectorAll('.baris-barang').length > 1) {
                event.target.closest('.baris-barang').remove();
            } else {
                const row = event.target.closest('.baris-barang');
                row.querySelector('select').value = '';
                row.querySelector('[name="jumlah[]"]').value = 1;
            }

            hitungTotal();
        });

        hitungTotal();
    })();
</script>
