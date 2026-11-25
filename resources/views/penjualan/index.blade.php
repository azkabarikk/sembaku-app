@extends('layouts.app')

@section('title', 'Penjualan')
@section('heading', 'Penjualan')

@section('content')
    @if (session('success'))
        <x-ui.alert variant="success" :title="session('success')">
            @if (session()->has('bukti_pembayaran.kembalian'))
                @php($bukti = session('bukti_pembayaran'))
                <dl class="md3-pos-summary">
                    <dt>Total pembayaran</dt><dd>Rp {{ number_format($bukti['total'], 0, ',', '.') }}</dd>
                    <dt>Uang diterima</dt><dd>Rp {{ number_format($bukti['uang_diterima'], 0, ',', '.') }}</dd>
                    <dt>Kembalian</dt><dd>Rp {{ number_format($bukti['kembalian'], 0, ',', '.') }}</dd>
                </dl>
            @endif
        </x-ui.alert>
    @endif

    @if ($errors->any())
        <x-ui.alert variant="error" title="Pembayaran belum dapat diproses">
            <ul>
                @foreach (array_unique($errors->all()) as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @unless ($margin)
        <x-ui.alert variant="warning">Penjualan memerlukan tepat satu margin aktif.</x-ui.alert>
    @endunless

    <div class="md3-pos" data-mobile-panel="{{ $errors->any() ? 'cart' : 'catalog' }}">
        <section aria-labelledby="catalog-title">
            <x-ui.card variant="outlined">
                <x-slot:header><h2 id="catalog-title" class="md3-type-title-large">Pilih barang</h2></x-slot:header>
                <x-ui.data-list label="Katalog barang" empty-id="search-empty">
                    <x-slot:search>
                        <div class="md3-pos-search">
                            <x-ui.search-field label="Cari barang" id="product-search" data-search placeholder="Cari nama atau ID barang..." autocomplete="off" />
                            <x-ui.icon name="search" class="md3-pos-search-icon" />
                            <x-ui.icon-button id="clear-search" icon="close" label="Hapus pencarian" size="small" hidden />
                        </div>
                    </x-slot:search>
                    <div class="md3-pos-products">
                        @forelse ($daftarBarang as $barang)
                            <button class="md3-action md3-pos-product" type="button"
                                data-product-card data-id="{{ $barang->idbarang }}" data-name="{{ $barang->nama }}"
                                data-unit="{{ $barang->nama_satuan }}" data-price="{{ $barang->harga_jual }}"
                                data-stock="{{ $barang->stok }}" @disabled($barang->stok < 1)>
                                <span class="md3-pos-product-meta md3-type-label-medium md3-muted">
                                    #{{ $barang->idbarang }}
                                    @if ($barang->stok >= 1)
                                        <x-ui.icon name="add" class="md3-pos-add" />
                                    @endif
                                </span>
                                <strong class="md3-type-title-medium">{{ $barang->nama }}</strong>
                                <span class="md3-type-body-small md3-muted">{{ $barang->stok < 1 ? 'Stok habis' : 'Stok '.$barang->stok.' '.$barang->nama_satuan }}</span>
                                <strong class="md3-pos-price">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</strong>
                            </button>
                        @empty
                            <x-ui.empty-state title="Belum ada barang yang dapat dijual" />
                        @endforelse
                    </div>
                </x-ui.data-list>
            </x-ui.card>
        </section>

        <x-ui.button id="open-cart" class="md3-pos-mobile-action" aria-controls="pos-cart-panel"
            aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
            <x-slot:icon><x-ui.icon name="shopping-cart" /></x-slot:icon>
            Lihat keranjang · <span id="mobile-count">0 item</span>
        </x-ui.button>
        <aside id="pos-cart-panel" aria-labelledby="cart-title">
            <x-ui.card variant="outlined">
                <x-slot:header>
                    <div class="md3-pos-between">
                        <h2 class="md3-type-title-large" id="cart-title" tabindex="-1">Keranjang</h2>
                        <x-ui.badge id="cart-count">0 item</x-ui.badge>
                        <x-ui.button size="small" variant="text" id="back-to-catalog" class="md3-pos-mobile-action">Kembali ke katalog</x-ui.button>
                    </div>
                </x-slot:header>
                <form class="md3-stack" method="POST" action="{{ route('penjualan.store') }}" id="payment-form">
                    @csrf
                    <input type="hidden" name="token_pembayaran" value="{{ $tokenPembayaran }}">
                    <div id="cart-items" role="region" aria-label="Barang dalam keranjang" tabindex="0"></div>
                    <x-ui.empty-state id="empty-cart" icon="shopping-cart" title="Keranjang masih kosong"
                        description="Pilih barang dari katalog untuk menambahkannya ke keranjang." />
                    <dl class="md3-pos-summary">
                        <dt>Subtotal</dt><dd id="subtotal-value">Rp 0</dd>
                        <dt>PPN {{ $persenPpn }}%</dt><dd id="tax-value">Rp 0</dd>
                        <dt class="md3-pos-total">Total</dt><dd class="md3-pos-total md3-type-title-large" id="total-value">Rp 0</dd>
                    </dl>
                    <x-ui.text-field id="cash" name="uang_diterima" label="Uang diterima (Rp)" type="number"
                        inputmode="numeric" min="0" step="1" required />
                    <div class="md3-pos-between" aria-live="polite">
                        <span id="change-label">Kembalian</span><strong id="change-value">—</strong>
                    </div>
                    <x-ui.button id="payment-button" type="submit" full-width disabled>
                        <span>Proses Pembayaran</span>
                    </x-ui.button>
                </form>
            </x-ui.card>
        </aside>
    </div>

    <template id="cart-line">
        <div class="md3-pos-line">
            <div class="md3-pos-between">
                <strong data-name></strong><strong data-subtotal></strong>
            </div>
            <span class="md3-muted md3-type-body-small" data-price></span>
            <div class="md3-pos-between">
                <div class="md3-cluster">
                    <x-ui.button variant="tonal" size="small" class="md3-icon-button" data-decrease>−</x-ui.button>
                    <input type="number" min="1" class="md3-control md3-pos-quantity" data-quantity>
                    <x-ui.button variant="tonal" size="small" class="md3-icon-button" data-increase>+</x-ui.button>
                </div>
                <x-ui.button variant="text" size="small" data-remove>Hapus</x-ui.button>
            </div>
            <input type="hidden" data-id-field>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const taxPercent = {{ $persenPpn }};
            const initialItems = {{ Illuminate\Support\Js::from(old('items', [])) }};
            const cards = Array.from(document.querySelectorAll('[data-product-card]'));
            const products = new Map(cards.map((card) => [Number(card.dataset.id), {
                id: Number(card.dataset.id),
                name: card.dataset.name,
                unit: card.dataset.unit,
                price: Number(card.dataset.price),
                stock: Number(card.dataset.stock),
            }]));
            const cart = new Map();
            const cartItems = document.getElementById('cart-items');
            const emptyCart = document.getElementById('empty-cart');
            const cartCount = document.getElementById('cart-count');
            const cash = document.getElementById('cash');
            let total = 0;
            const paymentButton = document.getElementById('payment-button');
            const currency = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            });

            const formatCurrency = (value) => currency.format(value).replace(/\s/g, ' ');

            const updatePayment = () => {
                const received = cash.valueAsNumber;
                const validCash = cash.validity.valid && Number.isSafeInteger(received);
                const hasAmount = cart.size > 0 && validCash;
                const difference = received - total;
                document.getElementById('change-label').textContent = hasAmount && difference < 0 ? 'Kekurangan' : 'Kembalian';
                document.getElementById('change-value').textContent = hasAmount ? formatCurrency(Math.abs(difference)) : '—';
                paymentButton.disabled = !hasAmount || difference < 0 || {{ $margin ? 'false' : 'true' }};
            };

            cash.addEventListener('input', updatePayment);

            const changeQuantity = (id, quantity) => {
                const product = products.get(id);

                if (!product || product.stock < 1) {
                    return;
                }

                const safeQuantity = Math.max(1, Math.min(product.stock, Math.trunc(Number(quantity)) || 1));
                cart.set(id, safeQuantity);
                renderCart();
            };

            const renderCart = () => {
                cartItems.replaceChildren();
                let subtotal = 0;
                let itemCount = 0;

                Array.from(cart.entries()).forEach(([id, quantity], index) => {
                    const product = products.get(id);
                    const lineTotal = product.price * quantity;
                    subtotal += lineTotal;
                    itemCount += quantity;

                    const row = document.getElementById('cart-line').content.firstElementChild.cloneNode(true);
                    row.querySelector('[data-name]').textContent = product.name;
                    row.querySelector('[data-price]').textContent = `${formatCurrency(product.price)} / ${product.unit}`;
                    row.querySelector('[data-subtotal]').textContent = formatCurrency(lineTotal);

                    const quantityInput = row.querySelector('[data-quantity]');
                    quantityInput.name = `items[${index}][jumlah]`;
                    quantityInput.max = String(product.stock);
                    quantityInput.value = String(quantity);
                    quantityInput.required = true;
                    quantityInput.setAttribute('aria-label', `Jumlah ${product.name}`);
                    quantityInput.addEventListener('change', () => changeQuantity(id, quantityInput.value));

                    const decrease = row.querySelector('[data-decrease]');
                    decrease.disabled = quantity <= 1;
                    decrease.setAttribute('aria-label', `Kurangi ${product.name}`);
                    decrease.addEventListener('click', () => changeQuantity(id, quantity - 1));
                    const increase = row.querySelector('[data-increase]');
                    increase.disabled = quantity >= product.stock;
                    increase.setAttribute('aria-label', `Tambah ${product.name}`);
                    increase.addEventListener('click', () => changeQuantity(id, quantity + 1));
                    const remove = row.querySelector('[data-remove]');
                    remove.setAttribute('aria-label', `Hapus ${product.name}`);
                    remove.addEventListener('click', () => {
                        cart.delete(id);
                        renderCart();
                    });

                    const idInput = row.querySelector('[data-id-field]');
                    idInput.name = `items[${index}][idbarang]`;
                    idInput.value = String(id);
                    cartItems.append(row);
                });

                const tax = Math.floor((subtotal * taxPercent + 50) / 100);
                total = subtotal + tax;
                document.getElementById('subtotal-value').textContent = formatCurrency(subtotal);
                document.getElementById('tax-value').textContent = formatCurrency(tax);
                document.getElementById('total-value').textContent = formatCurrency(subtotal + tax);
                cartCount.textContent = `${itemCount} item`;
                document.getElementById('mobile-count').textContent = cartCount.textContent;
                emptyCart.hidden = cart.size > 0;
                cartItems.hidden = cart.size === 0;
                updatePayment();
            };

            cards.forEach((card) => {
                card.addEventListener('click', () => {
                    const id = Number(card.dataset.id);
                    changeQuantity(id, (cart.get(id) || 0) + 1);
                });
            });

            (Array.isArray(initialItems) ? initialItems : []).forEach((item) => {
                const id = Number(item?.idbarang);

                if (products.get(id)?.stock > 0) {
                    const product = products.get(id);
                    cart.set(id, Math.max(1, Math.min(product.stock, Math.trunc(Number(item.jumlah)) || 1)));
                }
            });

            const search = document.getElementById('product-search');
            const clearSearch = document.getElementById('clear-search');
            clearSearch.addEventListener('click', () => {
                search.value = '';
                search.dispatchEvent(new Event('input'));
                search.focus();
            });
            search.addEventListener('input', () => {
                clearSearch.hidden = !search.value;
            });

            const pos = document.querySelector('.md3-pos');
            const openCart = document.getElementById('open-cart');
            let catalogScroll = 0;
            openCart.addEventListener('click', () => {
                catalogScroll = window.scrollY;
                pos.dataset.mobilePanel = 'cart';
                openCart.setAttribute('aria-expanded', 'true');
                document.getElementById('cart-title').focus();
                document.getElementById('pos-cart-panel').scrollIntoView({block: 'start'});
            });
            document.getElementById('back-to-catalog').addEventListener('click', () => {
                pos.dataset.mobilePanel = 'catalog';
                openCart.setAttribute('aria-expanded', 'false');
                openCart.focus({preventScroll: true});
                window.scrollTo(0, catalogScroll);
            });

            document.getElementById('payment-form').addEventListener('submit', () => {
                paymentButton.disabled = true;
                paymentButton.querySelector('span').textContent = 'Memproses Pembayaran…';
            });

            renderCart();
        });
    </script>
@endpush
