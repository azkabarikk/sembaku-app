@extends('layouts.app')

@section('title', 'Penjualan')
@section('heading', 'Penjualan')
@section('description', 'Pilih barang dan selesaikan pembayaran pelanggan.')

@section('content')
    @if (session('success'))
        <div class="alert alert-success" role="status">
            <strong>{{ session('success') }}</strong>
            @if (session()->has('bukti_pembayaran.kembalian'))
                @php($bukti = session('bukti_pembayaran'))
                <dl class="row mb-0 mt-2">
                    <dt class="col-sm-3">Total pembayaran</dt>
                    <dd class="col-sm-9">Rp {{ number_format($bukti['total'], 0, ',', '.') }}</dd>
                    <dt class="col-sm-3">Uang diterima</dt>
                    <dd class="col-sm-9">Rp {{ number_format($bukti['uang_diterima'], 0, ',', '.') }}</dd>
                    <dt class="col-sm-3">Kembalian</dt>
                    <dd class="col-sm-9 mb-0">Rp {{ number_format($bukti['kembalian'], 0, ',', '.') }}</dd>
                </dl>
            @endif
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Pembayaran belum dapat diproses</strong>
            <ul class="mb-0">
                @foreach (array_unique($errors->all()) as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @unless ($margin)
        <div class="alert alert-warning" role="alert">Penjualan memerlukan tepat satu margin aktif.</div>
    @endunless

    <div class="row g-3">
        <section class="col-12 col-lg-7" aria-labelledby="catalog-title">
            <div class="card">
                <h2 class="card-header h5" id="catalog-title">Pilih barang</h2>
                <div class="card-body">
                    <label class="form-label" for="product-search">Cari nama atau ID barang</label>
                    <input class="form-control mb-3" id="product-search" type="search" autocomplete="off">
                    <div class="pos-product-grid">
                        @forelse ($daftarBarang as $barang)
                            <button class="btn btn-light border rounded text-start d-flex flex-column justify-content-center align-items-start p-2 text-break" type="button"
                                data-product-card data-id="{{ $barang->idbarang }}" data-name="{{ $barang->nama }}"
                                data-unit="{{ $barang->nama_satuan }}" data-price="{{ $barang->harga_jual }}"
                                data-stock="{{ $barang->stok }}" @disabled($barang->stok < 1)>
                                <small class="text-muted">#{{ $barang->idbarang }}</small>
                                <strong>{{ $barang->nama }}</strong>
                                <small>Stok {{ $barang->stok }} {{ $barang->nama_satuan }}</small>
                                <span class="fw-semibold">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</span>
                            </button>
                        @empty
                            <p class="text-muted mb-0">Belum ada barang yang dapat dijual.</p>
                        @endforelse
                    </div>
                    <p class="text-muted mb-0 d-none" id="search-empty" role="status">Barang tidak ditemukan.</p>
                </div>
            </div>
        </section>
        <aside class="col-12 col-lg-5" aria-labelledby="cart-title">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0" id="cart-title">Keranjang</h2>
                    <span class="badge text-bg-secondary" id="cart-count">0 item</span>
                </div>
                <form class="card-body" method="POST" action="{{ route('penjualan.store') }}" id="payment-form">
                    @csrf
                    <input type="hidden" name="token_pembayaran" value="{{ $tokenPembayaran }}">
                    <div id="cart-items"></div>
                    <p class="text-muted" id="empty-cart">Keranjang masih kosong.</p>
                    <dl class="row mb-3">
                        <dt class="col-6 fw-normal">Subtotal</dt><dd class="col-6 text-end" id="subtotal-value">Rp 0</dd>
                        <dt class="col-6 fw-normal">PPN {{ $persenPpn }}%</dt><dd class="col-6 text-end" id="tax-value">Rp 0</dd>
                        <dt class="col-6">Total</dt><dd class="col-6 text-end fw-bold" id="total-value">Rp 0</dd>
                    </dl>
                    <label class="form-label" for="cash">Uang diterima</label>
                    <div class="input-group mb-2">
                        <span class="input-group-text">Rp</span>
                        <input class="form-control @error('uang_diterima') is-invalid @enderror" id="cash" name="uang_diterima"
                            type="number" inputmode="numeric" min="0" step="1" required
                            value="{{ is_scalar(old('uang_diterima')) ? old('uang_diterima') : '' }}">
                    </div>
                    <p class="d-flex justify-content-between" aria-live="polite">
                        <span id="change-label">Kembalian</span><strong id="change-value">—</strong>
                    </p>
                    <button class="btn btn-primary w-100" id="payment-button" type="submit" disabled>
                        <span>Proses Pembayaran</span>
                    </button>
                </form>
            </div>
        </aside>
    </div>
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

                    const row = document.createElement('div');
                    row.className = 'border-bottom pb-2 mb-3';

                    const identity = document.createElement('div');
                    identity.className = 'd-flex flex-wrap justify-content-between gap-2 mb-2';
                    const name = document.createElement('strong');
                    name.textContent = product.name;
                    const price = document.createElement('span');
                    price.textContent = `${formatCurrency(product.price)} / ${product.unit}`;
                    identity.append(name, price);

                    const controls = document.createElement('div');
                    controls.className = 'input-group input-group-sm mb-2';
                    const decrease = document.createElement('button');
                    decrease.type = 'button';
                    decrease.className = 'btn btn-outline-secondary';
                    decrease.setAttribute('aria-label', `Kurangi ${product.name}`);
                    decrease.textContent = '−';
                    decrease.addEventListener('click', () => changeQuantity(id, quantity - 1));

                    const quantityInput = document.createElement('input');
                    quantityInput.className = 'form-control text-center';
                    quantityInput.type = 'number';
                    quantityInput.min = '1';
                    quantityInput.max = String(product.stock);
                    quantityInput.value = String(quantity);
                    quantityInput.setAttribute('aria-label', `Jumlah ${product.name}`);
                    quantityInput.addEventListener('change', () => changeQuantity(id, quantityInput.value));

                    const increase = document.createElement('button');
                    increase.type = 'button';
                    increase.className = 'btn btn-outline-secondary';
                    increase.setAttribute('aria-label', `Tambah ${product.name}`);
                    increase.textContent = '+';
                    increase.disabled = quantity >= product.stock;
                    increase.addEventListener('click', () => changeQuantity(id, quantity + 1));
                    controls.append(decrease, quantityInput, increase);

                    const actions = document.createElement('div');
                    actions.className = 'd-flex align-items-center justify-content-between';
                    const total = document.createElement('strong');
                    total.textContent = formatCurrency(lineTotal);
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'btn btn-sm btn-outline-danger';
                    remove.textContent = 'Hapus';
                    remove.addEventListener('click', () => {
                        cart.delete(id);
                        renderCart();
                    });
                    actions.append(total, remove);

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = `items[${index}][idbarang]`;
                    idInput.value = String(id);
                    const serverQuantity = document.createElement('input');
                    serverQuantity.type = 'hidden';
                    serverQuantity.name = `items[${index}][jumlah]`;
                    serverQuantity.value = String(quantity);

                    row.append(identity, controls, actions, idInput, serverQuantity);
                    cartItems.append(row);
                });

                const tax = Math.floor((subtotal * taxPercent + 50) / 100);
                total = subtotal + tax;
                document.getElementById('subtotal-value').textContent = formatCurrency(subtotal);
                document.getElementById('tax-value').textContent = formatCurrency(tax);
                document.getElementById('total-value').textContent = formatCurrency(subtotal + tax);
                cartCount.textContent = `${itemCount} item`;
                emptyCart.classList.toggle('d-none', cart.size > 0);
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

            document.getElementById('product-search').addEventListener('input', (event) => {
                const query = event.target.value.trim().toLocaleLowerCase('id-ID');
                let visible = 0;

                cards.forEach((card) => {
                    const matches = card.dataset.id === query || card.dataset.name.toLocaleLowerCase('id-ID').includes(query);
                    card.classList.toggle('d-none', ! matches);
                    visible += matches ? 1 : 0;
                });

                document.getElementById('search-empty').classList.toggle('d-none', visible > 0 || cards.length === 0);
            });

            document.getElementById('payment-form').addEventListener('submit', () => {
                paymentButton.disabled = true;
                paymentButton.querySelector('span').textContent = 'Memproses Pembayaran…';
            });

            renderCart();
        });
    </script>
@endpush
