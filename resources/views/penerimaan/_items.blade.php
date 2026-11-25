<x-ui.form-section title="Barang diterima" icon="package-check"
    :description="$sedangEdit ? 'Periksa jumlah dan harga sebelum mencatat draf ke stok.' : 'Kosongkan harga satuan untuk menggunakan harga pada pengadaan.'">
    @forelse ($barisPesanan as $baris)
        @php
            $idBarang = $baris->idbarang;
            $detailDraf = $rincianDraf[$idBarang] ?? null;
        @endphp
        <div class="md3-form-item">
            <div class="md3-form-item__header">
                <h3 class="md3-type-title-medium">{{ $baris->nama_barang }}</h3>
                <x-ui.button variant="tonal" size="small" icon-name="add"
                    aria-controls="terima-{{ $idBarang }}" data-receipt-fill hidden>Isi sisa</x-ui.button>
            </div>
            <dl class="md3-item-stats">
                <div><dt>Dipesan</dt><dd>{{ $baris->jumlah_dipesan }}</dd></div>
                <div><dt>Sudah diterima</dt><dd>{{ $baris->jumlah_diterima }}</dd></div>
                <div><dt>Draf lain</dt><dd>{{ $baris->jumlah_draf }}</dd></div>
                <div class="md3-item-stats__balance"><dt>{{ $sedangEdit ? 'Batas draf' : 'Sisa' }}</dt><dd>{{ $baris->jumlah_sisa }}</dd></div>
            </dl>
            <div class="md3-form-grid">
                <x-ui.text-field id="terima-{{ $idBarang }}" name="jumlah_terima[{{ $idBarang }}]"
                    label="Jumlah diterima" type="number" inputmode="numeric" min="0" :max="$baris->jumlah_sisa"
                    :value="$detailDraf?->jumlah_terima ?? 0" required />
                <x-ui.text-field id="harga-terima-{{ $idBarang }}" name="harga_satuan_terima[{{ $idBarang }}]"
                    label="Harga satuan terima (Rp)" type="number" inputmode="numeric" min="0" max="2147483647" step="1"
                    :placeholder="(string) $baris->harga_pesanan" :value="$detailDraf?->harga_satuan_terima"
                    :helper="'Harga pengadaan Rp '.number_format($baris->harga_pesanan, 0, ',', '.')" />
            </div>
        </div>
    @empty
        <x-ui.empty-state icon="package" title="Tidak ada barang yang masih dapat diterima" />
    @endforelse
</x-ui.form-section>

@pushOnce('scripts')
    <script>
        document.querySelectorAll('[data-receipt-fill]').forEach((button) => {
            const quantity = document.getElementById(button.getAttribute('aria-controls'));
            if (!quantity || Number(quantity.max) < 1) return;

            button.hidden = false;
            button.addEventListener('click', () => {
                quantity.value = quantity.max;
                quantity.focus();
            });
        });
    </script>
@endPushOnce
