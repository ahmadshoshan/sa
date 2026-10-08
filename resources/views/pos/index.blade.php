<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>نقطة البيع</title>

    @if(file_exists(public_path('assets/css/bootstrap.rtl.min.css')))
        <link href="{{ asset('assets/css/bootstrap.rtl.min.css') }}" rel="stylesheet">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @endif

    @if(file_exists(public_path('assets/js/alpine.min.js')))
        <script src="{{ asset('assets/js/alpine.min.js') }}" defer></script>
    @else
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @endif

    <script>
        (function () {
            const theme = localStorage.getItem('pos_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <style>
        :root {
            --pos-bg: #f1f5f9;
            --pos-card: #ffffff;
            --pos-border: #e2e8f0;
            --pos-text: #0f172a;
            --pos-muted: #64748b;
            --pos-primary: #2563eb;
            --pos-success: #16a34a;
            --pos-danger: #dc2626;
            --pos-warning: #f59e0b;
        }

        [data-bs-theme="dark"] {
            --pos-bg: #0f172a;
            --pos-card: #1e293b;
            --pos-border: #334155;
            --pos-text: #f1f5f9;
            --pos-muted: #94a3b8;
        }

        body {
            background: var(--pos-bg);
            color: var(--pos-text);
            font-family: Tahoma, Arial, sans-serif;
            overflow: hidden;
        }

        .pos-topbar {
            background: var(--pos-card);
            border-bottom: 1px solid var(--pos-border);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pos-brand {
            font-weight: 800;
            font-size: 18px;
            color: var(--pos-primary);
            white-space: nowrap;
        }

        .pos-main {
            display: flex;
            height: calc(100vh - 62px);
        }

        .pos-products-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 12px;
            min-width: 0;
        }

        .pos-cart-area {
            width: 400px;
            background: var(--pos-card);
            border-inline-start: 1px solid var(--pos-border);
            display: flex;
            flex-direction: column;
        }

        .barcode-input {
            font-size: 18px;
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid var(--pos-primary);
            width: 100%;
            background: var(--pos-card);
            color: var(--pos-text);
        }

        .search-row {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--pos-border);
            background: var(--pos-card);
            color: var(--pos-text);
        }

        .filter-select {
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--pos-border);
            background: var(--pos-card);
            color: var(--pos-text);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            overflow-y: auto;
            padding: 12px 4px;
            flex: 1;
        }

        .product-card {
            background: var(--pos-card);
            border: 1px solid var(--pos-border);
            border-radius: 14px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            flex-direction: column;
            gap: 6px;
            user-select: none;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15);
            border-color: var(--pos-primary);
        }

        .product-card:active {
            transform: scale(0.97);
        }

        .product-img {
            width: 100%;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
            background: var(--pos-bg);
        }

        .product-emoji {
            width: 100%;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            background: var(--pos-bg);
            border-radius: 10px;
        }

        .product-name {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-size: 16px;
            font-weight: 800;
            color: var(--pos-primary);
        }

        .product-stock {
            font-size: 11px;
            font-weight: 600;
        }

        .stock-ok { color: var(--pos-success); }
        .stock-low { color: var(--pos-warning); }
        .stock-out { color: var(--pos-danger); }

        .cart-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--pos-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }

        .cart-item {
            background: var(--pos-bg);
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 8px;
        }

        .cart-item-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .cart-item-name {
            font-size: 13px;
            font-weight: 600;
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid var(--pos-border);
            background: var(--pos-card);
            color: var(--pos-text);
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-input {
            width: 60px;
            text-align: center;
            border: 1px solid var(--pos-border);
            border-radius: 8px;
            padding: 4px;
            background: var(--pos-card);
            color: var(--pos-text);
        }

        .cart-summary {
            border-top: 1px solid var(--pos-border);
            padding: 14px 16px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 14px;
        }

        .summary-total {
            font-size: 22px;
            font-weight: 800;
            color: var(--pos-primary);
            border-top: 2px solid var(--pos-border);
            padding-top: 10px;
            margin-top: 6px;
        }

        .checkout-btn {
            width: 100%;
            padding: 14px;
            font-size: 18px;
            font-weight: 800;
            border: none;
            border-radius: 12px;
            background: var(--pos-success);
            color: #fff;
            cursor: pointer;
            margin-top: 10px;
        }

        .checkout-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .payment-method-btn {
            flex: 1;
            padding: 14px 8px;
            border-radius: 12px;
            border: 2px solid var(--pos-border);
            background: var(--pos-card);
            color: var(--pos-text);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.15s;
        }

        .payment-method-btn.active {
            border-color: var(--pos-primary);
            background: rgba(37, 99, 235, 0.1);
            color: var(--pos-primary);
        }

        .quick-amount-btn {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--pos-border);
            background: var(--pos-card);
            color: var(--pos-text);
            cursor: pointer;
            font-weight: 700;
        }

        .empty-cart {
            text-align: center;
            color: var(--pos-muted);
            padding: 40px 20px;
        }

        .products-count {
            font-size: 12px;
            color: var(--pos-muted);
            padding: 4px 8px;
        }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--pos-border); border-radius: 10px; }
    </style>
</head>
<body x-data="posApp()" x-init="init()" @keydown.window="handleKeys($event)">

    <!-- Topbar -->
    <div class="pos-topbar">
        <div class="pos-brand">🖥️ نقطة البيع</div>

        <select x-model="warehouse_id" class="filter-select">
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
            @endforeach
        </select>

        <div class="d-flex align-items-center gap-2">
            <select x-model="customer_id" class="filter-select" style="min-width: 180px;">
                <option value="">عميل نقدي</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>

            <button class="btn btn-outline-primary btn-sm" @click="showCustomerModal = true" title="إضافة عميل سريع">
                + عميل
            </button>
        </div>

        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" @click="toggleTheme()" x-text="theme === 'dark' ? '☀️ فاتح' : '🌙 مظلم'"></button>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">خروج</a>
        </div>
    </div>

    <!-- Main -->
    <div class="pos-main">

        <!-- Products -->
        <div class="pos-products-area">
            <input type="text"
                   class="barcode-input"
                   x-model="barcode"
                   @keydown.enter.prevent="scanBarcode()"
                   x-ref="barcodeInput"
                   placeholder="📷 امسح الباركود أو اكتب الكود ثم Enter"
                   autocomplete="off">

            <div class="search-row">
                <input type="text"
                       class="search-input"
                       x-model="search"
                       placeholder="🔍 ابحث بالاسم أو الكود أو الباركود..."
                       autocomplete="off">

                <select class="filter-select" x-model="selectedCategory">
                    <option value="">كل التصنيفات</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <select class="filter-select" x-model="sortBy">
                    <option value="name">الاسم</option>
                    <option value="price_asc">السعر: الأقل</option>
                    <option value="price_desc">السعر: الأعلى</option>
                </select>
            </div>

            <div class="products-count" x-text="filteredProducts.length + ' صنف'"></div>

            <div class="products-grid">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="product-card" @click="addProduct(product); playSound('add')">
                        <template x-if="product.image_url">
                            <img :src="product.image_url" class="product-img" loading="lazy">
                        </template>
                        <template x-if="!product.image">
                            <div class="product-emoji">📦</div>
                        </template>

                        <div class="product-name" x-text="product.name" :title="product.name"></div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="product-price" x-text="product.sale_price.toFixed(2)"></div>
                            <div class="product-stock"
                                 :class="stockClass(product)"
                                 x-text="getStock(product)"></div>
                        </div>
                    </div>
                </template>

                <div x-show="filteredProducts.length === 0" class="empty-cart" style="grid-column: 1/-1;">
                    🔍 لا توجد أصناف مطابقة للبحث
                </div>
            </div>
        </div>

        <!-- Cart -->
        <div class="pos-cart-area">
            <div class="cart-header">
                <strong>🛒 السلة (<span x-text="cart.length"></span>)</strong>
                <button class="btn btn-outline-danger btn-sm" @click="clearCart()" x-show="cart.length > 0">
                    تفريغ
                </button>
            </div>

            <div class="cart-items">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="cart-item">
                        <div class="cart-item-top">
                            <div class="cart-item-name" x-text="item.name"></div>
                            <button class="btn btn-sm btn-outline-danger py-0 px-1" @click="removeFromCart(index)">✕</button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="qty-controls">
                                <button class="qty-btn" @click="changeQty(item, -1)">−</button>
                                <input type="number" class="qty-input" x-model.number="item.quantity" min="1" step="1" @input="updateRow(item)">
                                <button class="qty-btn" @click="changeQty(item, 1)">+</button>
                            </div>

                            <div class="text-end">
                                <div class="small text-muted" x-text="item.price.toFixed(2) + ' × ' + item.quantity"></div>
                                <div class="fw-bold" x-text="itemTotal(item).toFixed(2)"></div>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="empty-cart">
                    🛒 السلة فارغة<br>
                    <small>اضغط على صنف أو امسح باركود</small>
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>ال subtotal</span>
                    <span x-text="subtotal.toFixed(2)"></span>
                </div>
                <div class="summary-row">
                    <span>الخصم</span>
                    <span x-text="totalDiscount.toFixed(2)"></span>
                </div>
                <div class="summary-row">
                    <span>الضريبة</span>
                    <span x-text="tax.toFixed(2)"></span>
                </div>
                <div class="summary-row summary-total">
                    <span>الإجمالي</span>
                    <span x-text="total.toFixed(2)"></span>
                </div>

                <button class="checkout-btn" :disabled="cart.length === 0" @click="openPayment()">
                    ✅ إتمام البيع (F2)
                </button>
            </div>
        </div>

    </div>

    <!-- Payment Modal -->
    <div x-show="showPaymentModal" x-cloak
         style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1080; display:flex; align-items:center; justify-content:center;"
         @keydown.escape.window="showPaymentModal = false">
        <div style="background:var(--pos-card); border-radius:16px; padding:24px; width:480px; max-width:95vw; max-height:90vh; overflow-y:auto;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">💳 إتمام الدفع</h5>
                <button class="btn btn-outline-secondary btn-sm" @click="showPaymentModal = false">✕</button>
            </div>

            <div class="text-center mb-3">
                <div class="small text-muted">الإجمالي المستحق</div>
                <div style="font-size:36px; font-weight:800; color:var(--pos-primary);" x-text="total.toFixed(2)"></div>
            </div>

            <div class="d-flex gap-2 mb-3">
                <button class="payment-method-btn" :class="{'active': payment.method==='cash'}" @click="payment.method='cash'">💵 نقدي</button>
                <button class="payment-method-btn" :class="{'active': payment.method==='card'}" @click="payment.method='card'">💳 انستا/كاش</button>
                <button class="payment-method-btn" :class="{'active': payment.method==='bank_transfer'}" @click="payment.method='bank_transfer'">🏦 تحويل بنكي</button>
            </div>
 <div class="d-flex align-items-center gap-2">
            <select x-model="customer_id" class="filter-select" style="min-width: 180px;">
                <option value="">عميل نقدي</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>

           
            <div class="mb-3">
                <label class="form-label">المبلغ المدفوع</label>
                <input type="number" class="form-control form-control-lg" x-model.number="payment.paid" min="0" step="1">
            </div>
        </div>

            <div class="d-grid gap-2 mb-3" style="grid-template-columns: repeat(4, 1fr);">
                <button class="quick-amount-btn" @click="payment.paid = total">بالضبط</button>
                <button class="quick-amount-btn" @click="payment.paid = 50">50</button>
                <button class="quick-amount-btn" @click="payment.paid = 100">100</button>
                <button class="quick-amount-btn" @click="payment.paid = 200">200</button>
            </div>

            <div x-show="change > 0" class="alert alert-success py-2">
                الباقي للعميل: <strong x-text="change.toFixed(2)"></strong>
            </div>

            <div x-show="remaining > 0" class="alert alert-warning py-2">
                المتبقي على العميل: <strong x-text="remaining.toFixed(2)"></strong>
            </div>

            <button class="checkout-btn" @click="submitSale()" :disabled="submitting">
                <span x-show="!submitting">تأكيد البيع</span>
                <span x-show="submitting">جارٍ الحفظ...</span>
            </button>
        </div>
    </div>

    <!-- Quick Customer Modal -->
    <div x-show="showCustomerModal" x-cloak
         style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1080; display:flex; align-items:center; justify-content:center;"
         @keydown.escape.window="showCustomerModal = false">
        <div style="background:var(--pos-card); border-radius:16px; padding:24px; width:420px; max-width:95vw;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">👤 إضافة عميل سريع</h5>
                <button class="btn btn-outline-secondary btn-sm" @click="showCustomerModal = false">✕</button>
            </div>

            <div class="mb-3">
                <label class="form-label">اسم العميل *</label>
                <input type="text" class="form-control" x-model="newCustomer.name">
            </div>

            <div class="mb-3">
                <label class="form-label">الهاتف</label>
                <input type="text" class="form-control" x-model="newCustomer.phone">
            </div>

            <div class="mb-3">
                <label class="form-label">الرصيد الافتتاحي</label>
                <input type="number" class="form-control" x-model.number="newCustomer.opening_balance" min="0" step="1">
            </div>

            <button class="checkout-btn" @click="saveQuickCustomer()" :disabled="!newCustomer.name.trim()">
                حفظ العميل
            </button>
        </div>
    </div>

    <!-- Success Modal -->
    <div x-show="showSuccessModal" x-cloak
         style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1090; display:flex; align-items:center; justify-content:center;">
        <div style="background:var(--pos-card); border-radius:16px; padding:30px; width:420px; max-width:95vw; text-align:center;">
            <div style="font-size:60px;">✅</div>
            <h5 class="mt-2">تم البيع بنجاح!</h5>
            <p class="text-muted">فاتورة رقم: <span x-text="lastInvoice.invoice_no"></span></p>
            <p style="font-size:24px; font-weight:800; color:var(--pos-success);" x-text="lastInvoice.total.toFixed(2)"></p>

            <div class="d-flex gap-2 justify-content-center mt-3">
                <a :href="lastInvoice.print_thermal" target="_blank" class="btn btn-outline-dark">🖨️ حرارية</a>
                <a :href="lastInvoice.print_a4" target="_blank" class="btn btn-outline-primary">📄 A4</a>
                <button class="btn btn-success" @click="newSale()">بيع جديد</button>
            </div>
        </div>
    </div>

    @if(file_exists(public_path('assets/js/bootstrap.bundle.min.js')))
        <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    @else
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @endif

    <script>
        let posAudioCtx;
        function playSound(type) {
            try {
                posAudioCtx = posAudioCtx || new (window.AudioContext || window.webkitAudioContext)();
                const o = posAudioCtx.createOscillator();
                const g = posAudioCtx.createGain();
                o.connect(g); g.connect(posAudioCtx.destination);
                let freq = 880, dur = 0.12;
                if (type === 'add') { freq = 880; dur = 0.1; }
                if (type === 'success') { freq = 1046; dur = 0.25; }
                if (type === 'error') { freq = 220; dur = 0.3; }
                if (type === 'remove') { freq = 440; dur = 0.1; }
                o.frequency.value = freq; o.type = 'sine';
                g.gain.setValueAtTime(0.0001, posAudioCtx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.2, posAudioCtx.currentTime + 0.01);
                g.gain.exponentialRampToValueAtTime(0.0001, posAudioCtx.currentTime + dur);
                o.start(); o.stop(posAudioCtx.currentTime + dur);
            } catch (e) {}
        }

        function posApp() {
            return {
                warehouse_id: '{{ $warehouses->first()?->id }}',
                customer_id: '',
                products: @json($products),
                cart: [],
                barcode: '',
                search: '',
                selectedCategory: '',
                sortBy: 'name',
                theme: localStorage.getItem('pos_theme') || 'light',
                showPaymentModal: false,
                showCustomerModal: false,
                showSuccessModal: false,
                submitting: false,
                payment: { method: 'cash', paid: 0 },
                newCustomer: { name: '', phone: '', opening_balance: 0 },
                lastInvoice: { invoice_no: '', total: 0, print_a4: '', print_thermal: '' },

                init() {
                    this.$refs.barcodeInput.focus();
                },

                toggleTheme() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-bs-theme', this.theme);
                    localStorage.setItem('pos_theme', this.theme);
                },

                handleKeys(e) {
                    if (e.key === 'F2') {
                        e.preventDefault();
                        if (this.cart.length > 0) this.openPayment();
                    }
                    if (e.key === 'F4') {
                        e.preventDefault();
                        this.clearCart();
                    }
                    if (e.key === 'Escape') {
                        this.showPaymentModal = false;
                        this.showCustomerModal = false;
                    }
                },

                normalize(str) {
                    if (!str) return '';
                    return String(str).toLowerCase()
                        .replace(/[أإآ]/g, 'ا')
                        .replace(/ة/g, 'ه')
                        .replace(/[ًٌٍَُِّْـ]/g, '')
                        .trim();
                },

                get filteredProducts() {
                    let list = this.products;

                    if (this.selectedCategory) {
                        list = list.filter(p => p.category_id == this.selectedCategory);
                    }

                    if (this.search) {
                        const s = this.normalize(this.search);
                        list = list.filter(p =>
                            this.normalize(p.name).includes(s) ||
                            (p.code && this.normalize(p.code).includes(s)) ||
                            (p.barcode && String(p.barcode).includes(s))
                        );
                    }

                    if (this.sortBy === 'price_asc') {
                        list = [...list].sort((a, b) => a.sale_price - b.sale_price);
                    } else if (this.sortBy === 'price_desc') {
                        list = [...list].sort((a, b) => b.sale_price - a.sale_price);
                    } else {
                        list = [...list].sort((a, b) => a.name.localeCompare(b.name, 'ar'));
                    }

                    return list;
                },

                getStock(product) {
                    return product.stocks[this.warehouse_id] || 0;
                },

                stockClass(product) {
                    const stock = this.getStock(product);
                    if (stock <= 0) return 'stock-out';
                    if (stock <= 5) return 'stock-low';
                    return 'stock-ok';
                },

                addProduct(product) {
                    const existing = this.cart.find(i => i.product_id === product.id);

                    if (existing) {
                        existing.quantity = (parseFloat(existing.quantity) || 0) + 1;
                    } else {
                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            quantity: 1,
                            price: product.sale_price,
                            discount: 0,
                            tax_rate: product.tax_rate,
                        });
                    }

                    this.$refs.barcodeInput.focus();
                },

                changeQty(item, delta) {
                    item.quantity = Math.max(0.001, (parseFloat(item.quantity) || 0) + delta);
                },

                removeFromCart(index) {
                    playSound('remove'); this.cart.splice(index, 1);
                },

                clearCart() {
                    if (this.cart.length === 0) return;
                    if (confirm('هل تريد تفريغ السلة؟')) {
                        this.cart = [];
                    }
                },

                itemTotal(item) {
                    const gross = (parseFloat(item.quantity) || 0) * (parseFloat(item.price) || 0);
                    const net = Math.max(gross - (parseFloat(item.discount) || 0), 0);
                    return net + (net * (parseFloat(item.tax_rate) || 0) / 100);
                },

                get subtotal() {
                    return this.cart.reduce((sum, i) => sum + ((parseFloat(i.quantity) || 0) * (parseFloat(i.price) || 0)), 0);
                },

                get totalDiscount() {
                    return this.cart.reduce((sum, i) => sum + (parseFloat(i.discount) || 0), 0);
                },

                get tax() {
                    return this.cart.reduce((sum, i) => {
                        const gross = (parseFloat(i.quantity) || 0) * (parseFloat(i.price) || 0);
                        const net = Math.max(gross - (parseFloat(i.discount) || 0), 0);
                        return sum + (net * (parseFloat(i.tax_rate) || 0) / 100);
                    }, 0);
                },

                get total() {
                    return this.subtotal - this.totalDiscount + this.tax;
                },

                get change() {
                    return Math.max((parseFloat(this.payment.paid) || 0) - this.total, 0);
                },

                get remaining() {
                    return Math.max(this.total - (parseFloat(this.payment.paid) || 0), 0);
                },

                scanBarcode() {
                    if (!this.barcode) return;

                    const product = this.products.find(p =>
                        p.barcode === this.barcode || p.code === this.barcode
                    );

                    if (product) {
                        this.addProduct(product);
                    } else {
                        playSound('error'); alert('لم يتم العثور على صنف بالباركود: ' + this.barcode);
                    }

                    this.barcode = '';
                    this.$refs.barcodeInput.focus();
                },

                openPayment() {
                    this.payment.paid = this.total;
                    this.showPaymentModal = true;
                },

                async submitSale() {
                    if (this.cart.length === 0) return;

                    this.submitting = true;

                    const payload = {
                        customer_id: this.customer_id || null,
                        warehouse_id: this.warehouse_id,
                        payment_method: this.payment.method,
                        paid_amount: this.payment.paid,
                        items: this.cart.map(i => ({
                            product_id: i.product_id,
                            quantity: i.quantity,
                            price: i.price,
                            discount: i.discount,
                        })),
                    };

                    try {
                        const response = await fetch('{{ route("pos.checkout") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload),
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.lastInvoice = {
                                invoice_no: data.invoice_no,
                                total: data.total,
                                print_a4: data.print_a4,
                                print_thermal: data.print_thermal,
                            };

                            this.showPaymentModal = false;
                            this.showSuccessModal = true; playSound('success');
                        } else {
                            alert(data.message || 'حدث خطأ أثناء الحفظ');
                        }
                    } catch (e) {
                        alert('تعذر الاتصال بالخادم');
                    } finally {
                        this.submitting = false;
                    }
                },

                newSale() {
                    this.cart = [];
                    this.payment = { method: 'cash', paid: 0 };
                    this.showSuccessModal = false;
                    this.$refs.barcodeInput.focus();
                },

                async saveQuickCustomer() {
                    if (!this.newCustomer.name.trim()) return;

                    try {
                        const response = await fetch('{{ route("pos.quick-customer") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.newCustomer),
                        });

                        const data = await response.json();

                        if (data.success) {
                            const select = document.querySelector('select[x-model="customer_id"]');
                            const option = document.createElement('option');
                            option.value = data.customer.id;
                            option.textContent = data.customer.name;
                            select.appendChild(option);

                            this.customer_id = data.customer.id;
                            this.showCustomerModal = false;
                            this.newCustomer = { name: '', phone: '', opening_balance: 0 };
                        } else {
                            alert(data.message || 'حدث خطأ');
                        }
                    } catch (e) {
                        alert('تعذر الاتصال بالخادم');
                    }
                },
            };
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>


<script>
// ربط طريقة الدفع بالحساب تلقائياً
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.querySelector('select[name="payment_method"]');
    const accountSelect = document.getElementById('pos_account_id');
    
    if (paymentMethodSelect && accountSelect) {
        paymentMethodSelect.addEventListener('change', function() {
            const method = this.value;
            let accountId = '1'; // الصندوق افتراضياً
            
            switch(method) {
                case 'card':
                case 'insta':
                case 'visa':
                    accountId = '3'; // الكاش/الإنستا
                    break;
                case 'bank_transfer':
                case 'cheque':
                    accountId = '2'; // البنك
                    break;
                case 'cash':
                default:
                    accountId = '1'; // الصندوق
                    break;
            }
            
            accountSelect.value = accountId;
        });
    }
});
</script>
</body>
</html>