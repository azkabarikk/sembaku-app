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
        <x-ui.alert variant="error">{{ $errors->first('pengadaan') }}</x-ui.alert>
    @endif
    @if ($errors->has('idbarang'))
        <x-ui.alert variant="error">{{ $errors->first('idbarang') }}</x-ui.alert>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="md3-field__label" for="vendor_idvendor">Vendor</label>
            <select class="md3-control md3-control--select @error('vendor_idvendor') md3-control--error @enderror" id="vendor_idvendor" name="vendor_idvendor" required>
                <option value="">Pilih vendor</option>
                @foreach ($daftarVendor as $vendor)
                    <option value="{{ $vendor->idvendor }}" @selected(old('vendor_idvendor', $pengadaan->vendor_idvendor ?? '') == $vendor->idvendor)>
                        {{ $vendor->nama_vendor }}
                    </option>
                @endforeach
            </select>
            @error('vendor_idvendor')
                <div class="md3-field__error">{{ $message }}</div>
            @enderror
        </div>

        @if ($sedangEdit)
            <div class="col-md-6">
                <label class="md3-field__label" for="status">Status penerimaan</label>
                <select class="md3-control md3-control--select @error('status') md3-control--error @enderror" id="status" name="status" required>
                    <option value="A" @selected(old('status', $pengadaan->status) === 'A')>Siap diterima</option>
                    <option value="N" @selected(old('status', $pengadaan->status) === 'N')>Ditahan</option>
                </select>
                @error('status')
                    <div class="md3-field__error">{{ $message }}</div>
                @enderror
            </div>
        @endif
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <h2 class="md3-type-title-large mb-0">Barang Pengadaan</h2>
        <x-ui.button variant="outlined" size="small" id="tambahBaris" type="button">Tambah Barang</x-ui.button>
    </div>

    <div id="daftarBarisBarang" class="mb-3">
        @foreach ($inputBarang as $index => $idBarang)
            <div class="md3-card md3-card--outlined mb-2 baris-barang">
                <div>
                    <div class="md3-order-line">
                        <div>
                            <label class="md3-field__label" for="idbarang-{{ $index }}">Barang</label>
                            <select class="md3-control md3-control--select" id="idbarang-{{ $index }}" name="idbarang[]" required>
                                <option value="">Pilih barang</option>
                                @foreach ($daftarBarang as $barang)
                                    <option value="{{ $barang->idbarang }}" data-harga="{{ $barang->harga }}" @selected(is_scalar($idBarang) && (string) $idBarang === (string) $barang->idbarang)>
                                        {{ $barang->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($errors->has("idbarang.$index"))
                                <div class="md3-field__error small mt-1">{{ $errors->first("idbarang.$index") }}</div>
                            @endif
                        </div>
                        <div>
                            <label class="md3-field__label" for="jumlah-{{ $index }}">Jumlah</label>
                            <input class="md3-control hitung-barang" id="jumlah-{{ $index }}" name="jumlah[]" type="number" min="1" step="1" value="{{ is_scalar($inputJumlah[$index] ?? null) ? $inputJumlah[$index] : 1 }}" required>
                            @if ($errors->has("jumlah.$index"))
                                <div class="md3-field__error small mt-1">{{ $errors->first("jumlah.$index") }}</div>
                            @endif
                        </div>
                        <div>
                            <label class="md3-field__label" for="harga-{{ $index }}">Harga Satuan</label>
                            <input class="md3-control harga-satuan" id="harga-{{ $index }}" type="text" value="Rp 0" readonly>
                        </div>
                        <div>
                            <span class="d-block small md3-muted">Subtotal</span>
                            <strong class="subtotal-baris">Rp 0</strong>
                        </div>
                        <div>
                            <x-ui.button variant="outlined" size="small" class="hapus-baris md3-action--error" type="button">Hapus</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="md3-card md3-card--filled mb-4">
        <div class="md3-totals">
            <div class="d-flex justify-content-between gap-4"><span>Subtotal</span><strong id="totalSubtotal">Rp 0</strong></div>
            <div class="d-flex justify-content-between gap-4"><span>PPN ({{ $persenPpn }}%)</span><strong id="totalPpn">Rp 0</strong></div>
            <hr class="my-2">
            <div class="d-flex justify-content-between gap-4"><span>Total</span><strong id="totalAkhir">Rp 0</strong></div>
        </div>
    </div>

    <template id="templateBarisBarang">
        <div class="md3-card md3-card--outlined mb-2 baris-barang">
            <div>
                <div class="md3-order-line">
                    <div>
                        <label class="md3-field__label">Barang</label>
                        <select class="md3-control md3-control--select" aria-label="Barang" name="idbarang[]" required>
                            <option value="">Pilih barang</option>
                            @foreach ($daftarBarang as $barang)
                                <option value="{{ $barang->idbarang }}" data-harga="{{ $barang->harga }}">{{ $barang->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="md3-field__label">Jumlah</label>
                        <input class="md3-control hitung-barang" aria-label="Jumlah" name="jumlah[]" type="number" min="1" step="1" value="1" required>
                    </div>
                    <div>
                        <label class="md3-field__label">Harga Satuan</label>
                        <input class="md3-control harga-satuan" aria-label="Harga Satuan" type="text" value="Rp 0" readonly>
                    </div>
                    <div>
                        <span class="d-block small md3-muted">Subtotal</span>
                        <strong class="subtotal-baris">Rp 0</strong>
                    </div>
                    <div>
                        <x-ui.button variant="outlined" size="small" class="hapus-baris md3-action--error" type="button">Hapus</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <div class="md3-cluster">
        <x-ui.button variant="filled" type="submit">{{ $sedangEdit ? 'Simpan Perubahan' : 'Simpan Pengadaan' }}</x-ui.button>
        <x-ui.button variant="tonal" :href="route('pengadaan.index')">Batal</x-ui.button>
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
