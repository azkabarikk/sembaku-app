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

<form class="md3-form" method="POST" action="{{ $sedangEdit ? route('pengadaan.update', $pengadaan->idpengadaan) : route('pengadaan.store') }}">
    @csrf
    @if ($sedangEdit)
        @method('PUT')
    @endif

    @foreach (['pengadaan', 'idbarang', 'jumlah'] as $field)
        @error($field)
            <x-ui.alert variant="error">{{ $message }}</x-ui.alert>
        @enderror
    @endforeach

    <x-ui.form-section title="Informasi pesanan" icon="clipboard-list">
        <div class="md3-form-grid">
            <x-ui.select label="Vendor" id="vendor_idvendor" name="vendor_idvendor" required>
                <option value="">Pilih vendor</option>
                @foreach ($daftarVendor as $vendor)
                    <option value="{{ $vendor->idvendor }}" @selected(old('vendor_idvendor', $pengadaan->vendor_idvendor ?? '') == $vendor->idvendor)>
                        {{ $vendor->nama_vendor }}
                    </option>
                @endforeach
            </x-ui.select>

            @if ($sedangEdit)
                <x-ui.select label="Status penerimaan" id="status" name="status"
                    :options="['A' => 'Siap diterima', 'N' => 'Ditahan']" :value="$pengadaan->status" required />
            @endif
        </div>
    </x-ui.form-section>

    <x-ui.form-section title="Barang pengadaan" icon="package">
        <div class="md3-form-item__header">
            <p class="md3-form-field-help">Harga satuan terisi saat barang dipilih.</p>
            <x-ui.button variant="tonal" icon-name="add" size="small" id="tambahBaris" type="button">Tambah Barang</x-ui.button>
        </div>
        <div id="daftarBarisBarang" class="md3-stack">
            @foreach ($inputBarang as $index => $idBarang)
                @include('pengadaan._item', [
                    'index' => $index,
                    'idBarang' => $idBarang,
                    'jumlah' => is_scalar($inputJumlah[$index] ?? null) ? $inputJumlah[$index] : 1,
                ])
            @endforeach
        </div>
        <template id="templateBarisBarang">
            @include('pengadaan._item', ['index' => null, 'idBarang' => '', 'jumlah' => 1])
        </template>

        <dl class="md3-form-summary" aria-label="Ringkasan biaya">
            <dt>Subtotal</dt><dd id="totalSubtotal">Rp 0</dd>
            <dt>PPN ({{ $persenPpn }}%)</dt><dd id="totalPpn">Rp 0</dd>
            <dt class="md3-form-summary__total">Total</dt><dd class="md3-form-summary__total" id="totalAkhir">Rp 0</dd>
        </dl>
    </x-ui.form-section>

    <x-ui.form-actions :cancel="route('pengadaan.index')" :submit="$sedangEdit ? 'Simpan Perubahan' : 'Simpan Pengadaan'" />
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
                row.querySelector('.harga-satuan').textContent = formatRupiah(harga);
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
            list.lastElementChild.querySelector('select').focus();
        });

        list.addEventListener('input', hitungTotal);
        list.addEventListener('change', hitungTotal);
        list.addEventListener('click', (event) => {
            const button = event.target.closest('.hapus-baris');
            if (!button) return;

            const row = button.closest('.baris-barang');
            const nextRow = row.nextElementSibling ?? row.previousElementSibling;
            if (nextRow) {
                row.remove();
                nextRow.querySelector('select').focus();
            } else {
                row.querySelector('select').value = '';
                row.querySelector('[name="jumlah[]"]').value = 1;
                row.querySelector('select').focus();
            }

            hitungTotal();
        });

        hitungTotal();
    })();
</script>
