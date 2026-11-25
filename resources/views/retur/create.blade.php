@extends('layouts.app')

@section('title', 'Tambah Retur')
@section('heading', 'Tambah Retur')
@section('description', 'Pilih barang dari penerimaan dan isi jumlah yang dikembalikan.')

@section('content')
    @if ($errors->any())
        <x-ui.alert variant="error" title="Retur belum tersimpan">
            <ul class="mb-0">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <x-ui.form-section title="Penerimaan asal" icon="receipt">
        <dl class="md3-form-meta">
            <div><dt>Penerimaan</dt><dd>#{{ $penerimaan->idpenerimaan }}</dd></div>
            <div><dt>Pengadaan</dt><dd>#{{ $penerimaan->idpengadaan }}</dd></div>
            <div><dt>Vendor</dt><dd>{{ $penerimaan->nama_vendor ?? '—' }}</dd></div>
            <div><dt>Tanggal</dt><dd>{{ $penerimaan->created_at }}</dd></div>
        </dl>
    </x-ui.form-section>

    <form class="md3-form" method="POST" action="{{ route('retur.store') }}" id="retur-form">
        @csrf
        <input type="hidden" name="idpenerimaan" value="{{ $penerimaan->idpenerimaan }}">

        <x-ui.form-section title="Barang yang diretur" icon="rotate-left"
            description="Centang barang untuk mengisi jumlah dan alasan retur.">
            @php($barangTersedia = array_filter($rincian, static fn (object $baris): bool => $baris->sisa_retur > 0))
            @forelse ($barangTersedia as $index => $baris)
                @php($dipilih = old('items.'.$index.'.iddetail_penerimaan') == $baris->iddetail_penerimaan)

                <div class="md3-form-item" data-retur-row>
                    <div class="md3-form-item__header">
                        <div>
                            <h3 class="md3-type-title-medium">{{ $baris->nama_barang }}</h3>
                            <span class="md3-muted">{{ $baris->nama_satuan }}</span>
                        </div>
                        <label class="md3-choice">
                            <input type="checkbox" class="md3-choice__input" data-retur-toggle
                                aria-label="Pilih {{ $baris->nama_barang }}" aria-controls="retur-fields-{{ $index }}" @checked($dipilih)>
                            <span>Pilih barang</span>
                        </label>
                    </div>
                    <dl class="md3-item-stats">
                        <div><dt>Diterima</dt><dd>{{ $baris->jumlah_terima }}</dd></div>
                        <div><dt>Sudah diretur</dt><dd>{{ $baris->jumlah_diretur }}</dd></div>
                        <div class="md3-item-stats__balance"><dt>Sisa</dt><dd>{{ $baris->sisa_retur }}</dd></div>
                    </dl>
                    <div class="md3-form-grid md3-form-grid--retur" id="retur-fields-{{ $index }}" data-retur-fields {{ $dipilih ? '' : 'hidden' }}>
                        <input type="hidden" name="items[{{ $index }}][iddetail_penerimaan]" value="{{ $baris->iddetail_penerimaan }}" @disabled(! $dipilih)>
                        <x-ui.text-field id="retur-jumlah-{{ $index }}" name="items[{{ $index }}][jumlah]" label="Jumlah retur"
                            type="number" inputmode="numeric" min="1" :max="$baris->sisa_retur" :disabled="! $dipilih" required />
                        <x-ui.textarea id="retur-alasan-{{ $index }}" name="items[{{ $index }}][alasan]" label="Alasan retur"
                            placeholder="Contoh: kemasan rusak" rows="2" maxlength="200" :disabled="! $dipilih" required />
                    </div>
                </div>
            @empty
                <x-ui.empty-state icon="package" title="Tidak ada barang yang tersisa untuk diretur" />
            @endforelse
        </x-ui.form-section>

        <x-ui.form-actions :cancel="route('retur.index')" submit="Simpan Retur" />
    </form>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-retur-row]').forEach((row) => {
            const toggle = row.querySelector('[data-retur-toggle]');
            const fields = row.querySelector('[data-retur-fields]');

            toggle.addEventListener('change', () => {
                fields.hidden = !toggle.checked;
                fields.querySelectorAll('[name]').forEach((field) => {
                    field.disabled = !toggle.checked;
                });
            });
        });
    </script>
@endpush
