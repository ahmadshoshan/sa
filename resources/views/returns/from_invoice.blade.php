@extends('layouts.master')
@section('title', 'إرجاع فاتورة ' . $invoice->invoice_no)
@section('content')
<style>
    .return-item-row { transition: all 0.2s; }
    .return-item-row.disabled { opacity: 0.5; }
    .settlement-option { border: 2px solid #e9ecef; border-radius: 10px; padding: 15px; cursor: pointer; transition: all 0.2s; }
    .settlement-option:hover { border-color: #0d6efd; background: #f0f7ff; }
    .settlement-option.selected { border-color: #198754; background: #d4edda; }
    .settlement-option input[type="radio"] { margin-right: 8px; }
    .invoice-summary { background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); color: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .invoice-summary .label { opacity: 0.85; font-size: 13px; }
    .invoice-summary .value { font-size: 20px; font-weight: bold; }
    
    /* تنبيه الأصناف */
    .items-alert {
        background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
        border: 2px solid #ffc107;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .items-alert.success {
        background: linear-gradient(135deg, #d4edda 0%, #b8e6c0 100%);
        border-color: #198754;
        color: #155724;
    }
    .items-alert.warning {
        color: #856404;
    }
    .items-alert-icon {
        font-size: 28px;
    }
    
    /* الزر المعطل */
    .btn-submit:disabled {
        background: #6c757d !important;
        cursor: not-allowed;
        opacity: 0.6;
    }
    .btn-submit:disabled:hover {
        transform: none !important;
        box-shadow: none !important;
    }
    
    /* تأثير الاختيار */
    .return-item-row.selected {
        background: #d4edda !important;
        border-right: 4px solid #198754;
    }
    
    /* حقل الكمية المحدد */
    .qty-input:focus {
        border-color: #198754;
        box-shadow: 0 0 0 3px rgba(25,135,84,0.15);
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            🔄 إرجاع فاتورة {{ $invoice->type === 'purchase' ? 'شراء' : 'بيع' }}: {{ $invoice->invoice_no }}
        </h2>
        <a href="{{ $invoice->type === 'purchase' ? route('purchases.index') : route('sales.index') }}" class="btn btn-outline-secondary">↩️ رجوع</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- تنبيه حالة الأصناف المختارة --}}
    <div class="items-alert warning" id="itemsAlert">
        <div class="items-alert-icon">⚠️</div>
        <div>
            <strong id="alertTitle">لم يتم اختيار أي صنف للإرجاع</strong>
            <div id="alertDetails" style="font-size: 13px; margin-top: 3px;">
                يجب تحديد صنف واحد على الأقل لإتمام عملية الإرجاع
            </div>
        </div>
    </div>

    {{-- ملخص الفاتورة الأصلية --}}
    <div class="invoice-summary">
        <div class="row">
            <div class="col-md-3">
                <div class="label">رقم الفاتورة</div>
                <div class="value">{{ $invoice->invoice_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="label">{{ $invoice->type === 'purchase' ? 'المورد' : 'العميل' }}</div>
                <div class="value">{{ $invoice->type === 'purchase' ? $invoice->supplier?->name : $invoice->customer?->name }}</div>
            </div>
            <div class="col-md-2">
                <div class="label">الإجمالي</div>
                <div class="value">{{ number_format($invoice->total, 2) }}</div>
            </div>
            <div class="col-md-2">
                <div class="label">المدفوع</div>
                <div class="value text-success">{{ number_format($invoice->paid_amount, 2) }}</div>
            </div>
            <div class="col-md-2">
                <div class="label">المتبقي</div>
                <div class="value text-warning">{{ number_format($invoice->remaining_amount, 2) }}</div>
            </div>
        </div>
    </div>

    <form action="{{ route('returns.process-from-invoice', $invoice->id) }}" method="POST" id="returnForm" novalidate>
        @csrf
        
        {{-- اختيار الأصناف المرتجعة --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    📦 الأصناف المرتجعة 
                    <span class="badge bg-secondary ms-2" id="selectedCount">0</span>
                </h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllItems()">
                        ✅ تحديد الكل
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllItems()">
                        ❌ إلغاء الكل
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px" class="text-center">اختيار</th>
                                <th>الصنف</th>
                                <th class="text-end">الكمية الأصلية</th>
                                <th class="text-end">الكمية المرتجعة سابقاً</th>
                                <th class="text-end">المتاح للإرجاع</th>
                                <th class="text-end">السعر</th>
                                <th class="text-end">الإجمالي المرتجع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $index => $item)
                                @php
                                    $alreadyReturned = $item->already_returned ?? 0;
                                    $maxReturnable = $item->returnable_qty ?? ((float) $item->quantity - $alreadyReturned);
                                @endphp
                                <tr class="return-item-row" id="row-{{ $index }}">
                                    <td class="text-center align-middle">
                                        <input type="checkbox" class="item-check form-check-input" 
                                               data-index="{{ $index }}" 
                                               data-max="{{ $maxReturnable }}" 
                                               data-price="{{ $item->price }}"
                                               onchange="toggleItem(this)">
                                        <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">
                                    </td>
                                    <td class="align-middle" onclick="document.querySelector('[data-index={{ $index }}]').click()" style="cursor:pointer;">
                                        <strong>{{ $item->product?->name ?? 'غير محدد' }}</strong>
                                        <br><small class="text-muted">{{ $item->product?->code ?? '' }}</small>
                                    </td>
                                    <td class="text-end align-middle">{{ number_format($item->quantity, 3) }}</td>
                                    <td class="text-end text-warning align-middle">{{ number_format($alreadyReturned, 3) }}</td>
                                    <td class="text-end text-success align-middle fw-bold">{{ number_format($maxReturnable, 3) }}</td>
                                    <td class="text-end align-middle">{{ number_format($item->price, 2) }}</td>
                                    <td class="text-end fw-bold align-middle" id="item-total-{{ $index }}">0.00</td>
                                </tr>
                                <tr class="return-item-row" style="display: none;" id="qty-row-{{ $index }}">
                                    <td></td>
                                    <td colspan="5">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="form-label mb-0 fw-bold">الكمية المرتجعة:</label>
                                            <input type="number" name="items[{{ $index }}][quantity]" 
                                                   id="item-qty-{{ $index }}"
                                                   step="0.001" min="0.001" max="{{ $maxReturnable }}" 
                                                   value="{{ $maxReturnable }}" 
                                                   class="form-control qty-input" style="max-width: 180px;"
                                                   oninput="updateItemTotal({{ $index }}, {{ $item->price }})">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    onclick="document.getElementById('item-qty-{{ $index }}').value = {{ $maxReturnable }}; updateItemTotal({{ $index }}, {{ $item->price }});">
                                                الكل
                                            </button>
                                        </div>
                                    </td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="6" class="text-end fw-bold fs-5">إجمالي المرتجع:</td>
                                <td class="text-end fw-bold fs-4 text-danger" id="grand-total">0.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- طريقة معالجة المبلغ المدفوع --}}
        @if($invoice->paid_amount > 0)
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">💰 طريقة معالجة المبلغ المدفوع ({{ number_format($invoice->paid_amount, 2) }})</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="settlement-option" onclick="selectSettlement('cash_refund', this)">
                            <input type="radio" name="settlement_method" value="cash_refund" id="sm_cash">
                            <label for="sm_cash" class="w-100 mb-0">
                                <strong>💵 تحصيل/رد نقدي</strong>
                                <br><small class="text-muted">
                                    {{ $invoice->type === 'purchase' ? 'استلام المبلغ من المورد مع سند قبض' : 'رد المبلغ للعميل مع سند صرف' }}
                                </small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="settlement-option selected" onclick="selectSettlement('carry_forward', this)">
                            <input type="radio" name="settlement_method" value="carry_forward" id="sm_carry" checked>
                            <label for="sm_carry" class="w-100 mb-0">
                                <strong>📝 ترحيل لرصيد {{ $invoice->type === 'purchase' ? 'المورد' : 'العميل' }}</strong>
                                <br><small class="text-muted">
                                    {{ $invoice->type === 'purchase' ? 'يُصبح دين لنا على المورد يمكن تحصيله أو خصمه لاحقاً' : 'يُصبح دين علينا للعميل يمكن خصمه من فاتورة أخرى' }}
                                </small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="settlement-option" onclick="selectSettlement('offset_invoice', this)">
                            <input type="radio" name="settlement_method" value="offset_invoice" id="sm_offset">
                            <label for="sm_offset" class="w-100 mb-0">
                                <strong>🔄 خصم من فاتورة أخرى</strong>
                                <br><small class="text-muted">يُخصم المبلغ من فاتورة أخرى</small>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- اختيار الحساب النقدي --}}
                <div id="account-selection" style="display: none; margin-top: 20px;">
                    <label class="form-label fw-bold">اختر الحساب النقدي:</label>
                    <div class="row g-2">
                        @foreach($accounts ?? [] as $account)
                            <div class="col-md-4">
                                <div class="settlement-option" onclick="selectAccount({{ $account->id }}, this)">
                                    <input type="radio" name="account_id" value="{{ $account->id }}" id="acc_{{ $account->id }}">
                                    <label for="acc_{{ $account->id }}" class="w-100 mb-0">
                                        <strong>
                                            @if($account->code === '1001') 💵 @elseif($account->code === '1002') 🏦 @else 💳 @endif
                                            {{ $account->name }}
                                        </strong>
                                        <br><small class="text-muted">{{ $account->code }}</small>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @else
            <input type="hidden" name="settlement_method" value="carry_forward">
        @endif

        {{-- الملاحظات --}}
        <div class="card mb-4">
            <div class="card-body">
                <label class="form-label fw-bold">📝 ملاحظات:</label>
                <textarea name="notes" rows="2" class="form-control" placeholder="سبب الإرجاع أو أي ملاحظات...">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- زر التنفيذ --}}
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-danger btn-lg btn-submit" id="submitBtn" disabled>
                🔄 تأكيد الإرجاع (0 صنف)
            </button>
        </div>
    </form>
</div>

<script>
    let selectedItemsCount = 0;

    function toggleItem(checkbox) {
        const index = checkbox.dataset.index;
        const qtyRow = document.getElementById('qty-row-' + index);
        const mainRow = document.getElementById('row-' + index);
        
        if (checkbox.checked) {
            qtyRow.style.display = 'table-row';
            mainRow.classList.add('selected');
            const max = parseFloat(checkbox.dataset.max);
            const currentQty = parseFloat(document.getElementById('item-qty-' + index).value) || 0;
            if (currentQty <= 0) {
                document.getElementById('item-qty-' + index).value = max;
            }
            updateItemTotal(index, parseFloat(checkbox.dataset.price));
        } else {
            qtyRow.style.display = 'none';
            mainRow.classList.remove('selected');
            document.getElementById('item-qty-' + index).value = 0;
            updateItemTotal(index, parseFloat(checkbox.dataset.price));
        }
        
        updateSelectedCount();
        updateSubmitButton();
    }

    function updateItemTotal(index, price) {
        const qty = parseFloat(document.getElementById('item-qty-' + index).value) || 0;
        const total = qty * price;
        document.getElementById('item-total-' + index).textContent = total.toFixed(2);
        calculateGrandTotal();
        updateSubmitButton();
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-check:checked').forEach(checkbox => {
            const index = checkbox.dataset.index;
            const qty = parseFloat(document.getElementById('item-qty-' + index).value) || 0;
            const price = parseFloat(checkbox.dataset.price);
            grandTotal += qty * price;
        });
        document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
    }

    function updateSelectedCount() {
        selectedItemsCount = document.querySelectorAll('.item-check:checked').length;
        document.getElementById('selectedCount').textContent = selectedItemsCount;
    }

    function updateSubmitButton() {
        const btn = document.getElementById('submitBtn');
        const alert = document.getElementById('itemsAlert');
        const alertTitle = document.getElementById('alertTitle');
        const alertDetails = document.getElementById('alertDetails');
        
        // حساب إجمالي الأصناف المختارة ذات الكمية > 0
        let validItems = 0;
        let grandTotal = 0;
        
        document.querySelectorAll('.item-check:checked').forEach(checkbox => {
            const index = checkbox.dataset.index;
            const qty = parseFloat(document.getElementById('item-qty-' + index).value) || 0;
            if (qty > 0) {
                validItems++;
                grandTotal += qty * parseFloat(checkbox.dataset.price);
            }
        });
        
        if (validItems === 0) {
            btn.disabled = true;
            btn.innerHTML = '🔄 تأكيد الإرجاع (لم يتم اختيار أصناف)';
            alert.className = 'items-alert warning';
            alert.querySelector('.items-alert-icon').textContent = '⚠️';
            alertTitle.textContent = 'لم يتم اختيار أي صنف للإرجاع';
            alertDetails.textContent = 'يجب تحديد صنف واحد على الأقل بكمية أكبر من صفر لإتمام عملية الإرجاع';
        } else {
            btn.disabled = false;
            btn.innerHTML = `🔄 تأكيد الإرجاع (${validItems} صنف • ${grandTotal.toFixed(2)})`;
            alert.className = 'items-alert success';
            alert.querySelector('.items-alert-icon').textContent = '✅';
            alertTitle.textContent = `تم اختيار ${validItems} صنف للإرجاع`;
            alertDetails.textContent = `إجمالي المبلغ المرتجع: ${grandTotal.toFixed(2)}`;
        }
        
        // التحقق من الحساب النقدي إذا كان التحصيل نقدي
        const settlementMethod = document.querySelector('input[name="settlement_method"]:checked')?.value;
        if (settlementMethod === 'cash_refund' && validItems > 0) {
            const accountSelected = document.querySelector('input[name="account_id"]:checked');
            if (!accountSelected) {
                btn.disabled = true;
                btn.innerHTML = '⚠️ اختر الحساب النقدي أولاً';
            }
        }
    }

    function selectAllItems() {
        document.querySelectorAll('.item-check').forEach(checkbox => {
            if (!checkbox.checked) {
                checkbox.checked = true;
                toggleItem(checkbox);
            }
        });
    }

    function deselectAllItems() {
        document.querySelectorAll('.item-check').forEach(checkbox => {
            if (checkbox.checked) {
                checkbox.checked = false;
                toggleItem(checkbox);
            }
        });
    }

    function selectSettlement(method, element) {
        document.querySelectorAll('.card-body .settlement-option').forEach(opt => {
            if (opt.closest('#account-selection') === null) {
                opt.classList.remove('selected');
            }
        });
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;

        const accountSelection = document.getElementById('account-selection');
        if (method === 'cash_refund') {
            accountSelection.style.display = 'block';
        } else {
            accountSelection.style.display = 'none';
        }
        
        updateSubmitButton();
    }

    function selectAccount(accountId, element) {
        document.querySelectorAll('#account-selection .settlement-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;
        updateSubmitButton();
    }

    // التحقق قبل الإرسال
    document.getElementById('returnForm').addEventListener('submit', function(e) {
        let validItems = 0;
        
        document.querySelectorAll('.item-check:checked').forEach(checkbox => {
            const index = checkbox.dataset.index;
            const qty = parseFloat(document.getElementById('item-qty-' + index).value) || 0;
            if (qty > 0) validItems++;
        });
        
        if (validItems === 0) {
            e.preventDefault();
            alert('⚠️ يجب اختيار صنف واحد على الأقل بكمية أكبر من صفر لإتمام عملية الإرجاع');
            return false;
        }
        
        // التحقق من الحساب النقدي
        const settlementMethod = document.querySelector('input[name="settlement_method"]:checked')?.value;
        if (settlementMethod === 'cash_refund') {
            const accountSelected = document.querySelector('input[name="account_id"]:checked');
            if (!accountSelected) {
                e.preventDefault();
                alert('⚠️ يجب اختيار الحساب النقدي عند اختيار التحصيل/الرد النقدي');
                return false;
            }
        }
        
        // تأكيد نهائي
        const grandTotal = document.getElementById('grand-total').textContent;
        if (!confirm(`هل أنت متأكد من إرجاع ${validItems} صنف بإجمالي ${grandTotal}؟`)) {
            e.preventDefault();
            return false;
        }
    });

    // مراقبة تغيير طريقة التسوية
    document.querySelectorAll('input[name="settlement_method"]').forEach(radio => {
        radio.addEventListener('change', updateSubmitButton);
    });
    document.querySelectorAll('input[name="account_id"]').forEach(radio => {
        radio.addEventListener('change', updateSubmitButton);
    });

    // التهيئة الأولى
    updateSubmitButton();
</script>
@endsection