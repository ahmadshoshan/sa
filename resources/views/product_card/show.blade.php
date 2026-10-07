@extends('layouts.master')
@section('title', 'كارتة - ' . $product->name)
@section('content')
<style>
    .kpi-card { border-left: 4px solid; padding: 15px; border-radius: 8px; }
    .kpi-stock { border-color: #0d6efd; }
    .kpi-purchase { border-color: #6c757d; }
    .kpi-sale { border-color: #198754; }
    .kpi-profit { border-color: #ffc107; }
    .status-ok { color: #198754; }
    .status-low { color: #ffc107; }
    .status-out { color: #dc3545; }
    .movement-in { color: #198754; }
    .movement-out { color: #dc3545; }
    .nav-tabs .nav-link { color: #495057; }
    .nav-tabs .nav-link.active { background: var(--accent, #198754); color: white; border-color: var(--accent, #198754); }
    .product-header { background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white; padding: 25px; border-radius: 12px; margin-bottom: 20px; }
    .product-image { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 3px solid white; }
    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd; }
        .product-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

<div class="container-fluid py-4">
    {{-- ========== Header ========== --}}
    <div class="product-header d-flex justify-content-between align-items-center no-print">
        <div class="d-flex align-items-center gap-3">
            @if($product->image)
                <img src="{{ asset($product->image) }}" class="product-image" alt="">
            @else
                <div class="product-image bg-white d-flex align-items-center justify-content-center" style="font-size: 40px; color: #198754;">📦</div>
            @endif
            <div>
                <h2 class="mb-1">{{ $product->name }}</h2>
                <div class="opacity-75">
                    <span class="me-3">🔖 {{ $product->code }}</span>
                    @if($product->barcode)<span class="me-3">📊 {{ $product->barcode }}</span>@endif
                    <span class="me-3">📁 {{ $product->category?->name ?? '-' }}</span>
                    <span>⚖️ {{ $product->unit?->name ?? '-' }}</span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-light">🖨️ طباعة</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-light">↩️ رجوع</a>
        </div>
    </div>

    {{-- ========== Filter ========== --}}
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">من تاريخ</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">إلى تاريخ</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary w-100">🔍 تصفية</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('product.card', $product->id) }}" class="btn btn-outline-secondary w-100">♻️ الكل</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== KPI Cards ========== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card kpi-card kpi-stock h-100">
                <h6 class="text-muted mb-1">📦 الرصيد الحالي</h6>
                <h3 class="mb-0">{{ $money($totalStock) }}</h3>
                <small class="{{ $stockStatus == 'ok' ? 'status-ok' : ($stockStatus == 'low' ? 'status-low' : 'status-out') }}">
                    @if($stockStatus == 'ok') ✅ متوفر
                    @elseif($stockStatus == 'low') ⚠️ منخفض (الحد: {{ $money($product->min_stock) }})
                    @else 🔴 نفد المخزون @endif
                </small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card kpi-purchase h-100">
                <h6 class="text-muted mb-1">🛒 إجمالي المشتريات</h6>
                <h3 class="mb-0">{{ $money($purchaseTotal) }}</h3>
                <small class="text-muted">{{ $money($purchaseQty) }} وحدة × {{ $money($purchaseAvgCost) }} متوسط</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card kpi-sale h-100">
                <h6 class="text-muted mb-1">💰 إجمالي المبيعات</h6>
                <h3 class="mb-0">{{ $money($salesTotal) }}</h3>
                <small class="text-muted">{{ $money($salesQty) }} وحدة × {{ $money($salesAvgPrice) }} متوسط</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card kpi-profit h-100">
                <h6 class="text-muted mb-1">💹 إجمالي الربح</h6>
                <h3 class="mb-0 {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ $money($grossProfit) }}</h3>
                <small class="text-muted">هامش الربح: {{ number_format($profitMargin, 1) }}%</small>
            </div>
        </div>
    </div>

    {{-- ========== Prices ========== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted">سعر التكلفة</h6>
                    <h4 class="mb-0">{{ $money($product->cost_price) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted">سعر البيع</h6>
                    <h4 class="mb-0 text-success">{{ $money($product->sale_price) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted">سعر الجملة</h6>
                    <h4 class="mb-0">{{ $money($product->wholesale_price) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== Tabs ========== --}}
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-stock">📦 المخزون بالمخازن</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-movements">🔄 حركة المخزون</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-sales">💰 المبيعات</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-purchases">🛒 المشتريات</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-profit">💹 الأرباح</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-top">🏆 الأعلى تعاملاً</a></li>
    </ul>

    <div class="tab-content">
        {{-- Tab 1: المخزون بالمخازن --}}
        <div class="tab-pane fade show active" id="tab-stock">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>المخزن</th>
                                    <th class="text-end">الرصيد</th>
                                    <th class="text-center">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stocks as $s)
                                    <tr>
                                        <td><strong>{{ $s->warehouse?->name ?? '-' }}</strong></td>
                                        <td class="text-end fw-bold">{{ $money($s->quantity) }}</td>
                                        <td class="text-center">
                                            @if((float)$s->quantity <= 0)
                                                <span class="badge bg-danger">نفد</span>
                                            @elseif((float)$product->min_stock > 0 && (float)$s->quantity <= (float)$product->min_stock)
                                                <span class="badge bg-warning text-dark">منخفض</span>
                                            @else
                                                <span class="badge bg-success">متوفر</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">لا توجد أرصدة</td></tr>
                                @endforelse
                                <tr class="table-dark fw-bold">
                                    <td>الإجمالي</td>
                                    <td class="text-end">{{ $money($totalStock) }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 2: حركة المخزون --}}
        <div class="tab-pane fade" id="tab-movements">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>النوع</th>
                                    <th>المرجع</th>
                                    <th>المخزن</th>
                                    <th class="text-end movement-in">وارد (+)</th>
                                    <th class="text-end movement-out">صادر (-)</th>
                                    <th class="text-end">الرصيد بعد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movementsWithRunning as $m)
                                    <tr>
                                        <td>{{ $m->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @switch($m->movement_type)
                                                @case('purchase') <span class="badge bg-secondary">شراء</span> @break
                                                @case('sale') <span class="badge bg-success">بيع</span> @break
                                                @case('sale_return') <span class="badge bg-info">مرتجع بيع</span> @break
                                                @case('purchase_return') <span class="badge bg-warning text-dark">مرتجع شراء</span> @break
                                                @default <span class="badge bg-light text-dark">{{ $m->movement_type }}</span>
                                            @endswitch
                                        </td>
                                        <td><code>{{ $m->ref_type }} #{{ $m->ref_id }}</code></td>
                                        <td>{{ $m->warehouse?->name ?? '-' }}</td>
                                        <td class="text-end movement-in fw-bold">{{ $m->in > 0 ? '+ ' . $money($m->in) : '-' }}</td>
                                        <td class="text-end movement-out fw-bold">{{ $m->out > 0 ? '- ' . $money($m->out) : '-' }}</td>
                                        <td class="text-end fw-bold">{{ $money($m->running_after) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-3">لا توجد حركات</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 3: المبيعات --}}
        <div class="tab-pane fade" id="tab-sales">
            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">إجمالي المبيعات</small>
                    <h4 class="mb-0 text-success">{{ $money($salesTotal) }}</h4>
                </div></div>
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">الكمية المباعة</small>
                    <h4 class="mb-0">{{ $money($salesQty) }}</h4>
                </div></div>
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">مرتجعات البيع</small>
                    <h4 class="mb-0 text-warning">{{ $money($salesReturnTotal) }}</h4>
                </div></div>
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">صافي المبيعات</small>
                    <h4 class="mb-0 text-primary">{{ $money($salesTotal - $salesReturnTotal) }}</h4>
                </div></div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">📜 تفاصيل المبيعات</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>رقم الفاتورة</th>
                                    <th>العميل</th>
                                    <th class="text-end">الكمية</th>
                                    <th class="text-end">السعر</th>
                                    <th class="text-end">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $item)
                                    <tr>
                                        <td>{{ $item->invoice?->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                                        <td><code>{{ $item->invoice?->invoice_no ?? '-' }}</code></td>
                                        <td>{{ $item->invoice?->customer?->name ?? '-' }}</td>
                                        <td class="text-end">{{ $money($item->quantity) }}</td>
                                        <td class="text-end">{{ $money($item->price) }}</td>
                                        <td class="text-end fw-bold text-success">{{ $money($item->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">لا توجد مبيعات</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 4: المشتريات --}}
        <div class="tab-pane fade" id="tab-purchases">
            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">إجمالي المشتريات</small>
                    <h4 class="mb-0">{{ $money($purchaseTotal) }}</h4>
                </div></div>
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">الكمية المشتراة</small>
                    <h4 class="mb-0">{{ $money($purchaseQty) }}</h4>
                </div></div>
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">متوسط التكلفة</small>
                    <h4 class="mb-0">{{ $money($purchaseAvgCost) }}</h4>
                </div></div>
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">مرتجعات الشراء</small>
                    <h4 class="mb-0 text-warning">{{ $money($purchaseReturnTotal) }}</h4>
                </div></div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">📜 تفاصيل المشتريات</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>التاريخ</th>
                                    <th>رقم الفاتورة</th>
                                    <th>المورد</th>
                                    <th class="text-end">الكمية</th>
                                    <th class="text-end">السعر</th>
                                    <th class="text-end">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $item)
                                    <tr>
                                        <td>{{ $item->invoice?->invoice_date?->format('Y-m-d') ?? '-' }}</td>
                                        <td><code>{{ $item->invoice?->invoice_no ?? '-' }}</code></td>
                                        <td>{{ $item->invoice?->supplier?->name ?? '-' }}</td>
                                        <td class="text-end">{{ $money($item->quantity) }}</td>
                                        <td class="text-end">{{ $money($item->price) }}</td>
                                        <td class="text-end fw-bold">{{ $money($item->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">لا توجد مشتريات</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 5: الأرباح --}}
        <div class="tab-pane fade" id="tab-profit">
            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">إيرادات المبيعات</small>
                    <h4 class="mb-0 text-success">{{ $money($salesTotal) }}</h4>
                </div></div>
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">تكلفة المبيعات</small>
                    <h4 class="mb-0 text-danger">{{ $money($costOfGoodsSold) }}</h4>
                </div></div>
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">مجمل الربح</small>
                    <h4 class="mb-0 {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ $money($grossProfit) }}</h4>
                </div></div>
                <div class="col-md-3"><div class="card bg-light p-3 text-center">
                    <small class="text-muted">هامش الربح</small>
                    <h4 class="mb-0 text-primary">{{ number_format($profitMargin, 1) }}%</h4>
                </div></div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">📈 المبيعات الشهرية (آخر 12 شهر)</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    @foreach($monthlySales as $m)
                                        <th class="text-center">{{ $m['label'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach($monthlySales as $m)
                                        <td class="text-center fw-bold {{ $m['total'] > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ $money($m['total']) }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 6: الأعلى تعاملاً --}}
        <div class="tab-pane fade" id="tab-top">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-success text-white"><h6 class="mb-0">🏆 أفضل العملاء شراءً</h6></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>#</th><th>العميل</th><th class="text-end">الكمية</th><th class="text-end">الإجمالي</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topCustomers as $i => $c)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td><strong>{{ $c->name }}</strong><br><small class="text-muted">{{ $c->code }}</small></td>
                                                <td class="text-end">{{ $money($c->qty) }}</td>
                                                <td class="text-end fw-bold text-success">{{ $money($c->total) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">لا يوجد</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-secondary text-white"><h6 class="mb-0">🏭 أفضل الموردين توريداً</h6></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>#</th><th>المورد</th><th class="text-end">الكمية</th><th class="text-end">الإجمالي</th></tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topSuppliers as $i => $s)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td><strong>{{ $s->name }}</strong><br><small class="text-muted">{{ $s->code }}</small></td>
                                                <td class="text-end">{{ $money($s->qty) }}</td>
                                                <td class="text-end fw-bold">{{ $money($s->total) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">لا يوجد</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection