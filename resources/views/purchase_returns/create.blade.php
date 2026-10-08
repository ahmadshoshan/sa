@extends('layouts.master')

@section('title', 'إنشاء مرتجع شراء')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">إنشاء مرتجع شراء</h5>
        </div>

        <div class="card-body">

            @include('partials.errors')

            <form method="POST" action="{{ route('purchase-returns.store') }}" x-data="purchaseReturnInvoice()">
                @csrf

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label">المورد</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">اختر المورد</option>

                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">فاتورة الشراء الأصلية (اختياري)</label>
                        <select name="original_invoice_id" class="form-select">
                            <option value="">بدون ربط بفاتورة</option>
                            @foreach($originalInvoices as $originalInvoice)
                                <option value="{{ $originalInvoice->id }}" @selected(old('original_invoice_id') == $originalInvoice->id)>
                                    {{ $originalInvoice->invoice_no }} - {{ $originalInvoice->supplier->name }}
                                    (متبقي {{ number_format($originalInvoice->remaining_amount, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">المخزن</label>
                        <select name="warehouse_id"
                                class="form-select"
                                x-model="warehouse_id"
                                @change="updateAllStock()"
                                required>
                            <option value="">اختر المخزن</option>

                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">تاريخ المرتجع</label>
                        <input type="date"
                               name="invoice_date"
                               value="{{ old('invoice_date', date('Y-m-d')) }}"
                               class="form-control"
                               required>
                    </div>

                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">الأصناف المرتجعة</h6>

                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addRow()">
                        إضافة صنف
                    </button>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 35%">الصنف</th>
                            <th style="width: 10%">الكمية</th>
                            <th style="width: 12%">السعر</th>
                            <th style="width: 10%">الخصم</th>
                            <th style="width: 10%">المخزون</th>
                            <th style="width: 13%">الإجمالي</th>
                            <th style="width: 10%">حذف</th>
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td>
                                    <select :name="'items[' + index + '][product_id]'"
                                            class="form-select"
                                            x-model="row.product_id"
                                            @change="onProductChange(row)"
                                            required>
                                        <option value="">اختر الصنف</option>

                                        <template x-for="product in products" :key="product.id">
                                            <option :value="product.id" x-text="product.name"></option>
                                        </template>
                                    </select>
                                </td>

                                <td>
                                    <input type="number"
                                           step="1"
                                           min="0.001"
                                           :name="'items[' + index + '][quantity]'"
                                           class="form-control"
                                           x-model="row.quantity"
                                           @input="updateRow(row)"
                                           required>
                                </td>

                                <td>
                                    <input type="number"
                                           step="1"
                                           min="0"
                                           :name="'items[' + index + '][price]'"
                                           class="form-control"
                                           x-model="row.price"
                                           @input="updateRow(row)"
                                           required>
                                </td>

                                <td>
                                    <input type="number"
                                           step="1"
                                           min="0"
                                           :name="'items[' + index + '][discount]'"
                                           class="form-control"
                                           x-model="row.discount"
                                           @input="updateRow(row)">
                                </td>

                                <td x-text="row.stock"></td>

                                <td x-text="row.total.toFixed(2)"></td>

                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-danger"
                                            @click="removeRow(index)">
                                        حذف
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div class="row">

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">

                                <div class="d-flex justify-content-between">
                                    <span>الإجمالي قبل الخصم</span>
                                    <strong x-text="totals.subtotal.toFixed(2)"></strong>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>الخصم</span>
                                    <strong x-text="totals.discount.toFixed(2)"></strong>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>الضريبة</span>
                                    <strong x-text="totals.tax.toFixed(2)"></strong>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">
                                    <span>الإجمالي النهائي</span>
                                    <strong x-text="totals.total.toFixed(2)"></strong>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="row mt-3">

                    <div class="col-md-3">
                        <label class="form-label">طريقة استلام المبلغ</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash" @selected(old('payment_method', 'cash') == 'cash')>نقدي</option>
                            <option value="card" @selected(old('payment_method') == 'card')> انستا/كاش</option>
                            <option value="bank_transfer" @selected(old('payment_method') == 'bank_transfer')>تحويل بنكي</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">المبلغ المستلم من المورد</label>
                        <input type="number"
                               step="1"
                               min="0"
                               name="refund_amount"
                               value="{{ old('refund_amount', 0) }}"
                               class="form-control">
                    </div>

                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">حفظ المرتجع</button>
                    <a href="{{ route('purchase-returns.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>

            </form>

        </div>
    </div>

    <script>
        function purchaseReturnInvoice() {
            return {
                warehouse_id: '{{ old('warehouse_id') }}',
                products: @json($products),

                rows: [
                    {
                        product_id: '',
                        quantity: 1,
                        price: 0,
                        discount: 0,
                        tax_rate: 0,
                        stock: 0,
                        subtotal: 0,
                        tax: 0,
                        total: 0
                    }
                ],

                addRow() {
                    this.rows.push({
                        product_id: '',
                        quantity: 1,
                        price: 0,
                        discount: 0,
                        tax_rate: 0,
                        stock: 0,
                        subtotal: 0,
                        tax: 0,
                        total: 0
                    });
                },

                removeRow(index) {
                    this.rows.splice(index, 1);
                },

                onProductChange(row) {
                    const product = this.products.find(p => String(p.id) === String(row.product_id));

                    if (product) {
                        row.price = product.cost_price;
                        row.tax_rate = product.tax_rate;
                    } else {
                        row.price = 0;
                        row.tax_rate = 0;
                    }

                    this.updateRow(row);
                    this.updateStock(row);
                },

                updateRow(row) {
                    const quantity = parseFloat(row.quantity) || 0;
                    const price = parseFloat(row.price) || 0;
                    const discount = parseFloat(row.discount) || 0;
                    const taxRate = parseFloat(row.tax_rate) || 0;

                    row.subtotal = quantity * price;

                    const net = Math.max(row.subtotal - discount, 0);

                    row.tax = net * taxRate / 100;
                    row.total = net + row.tax;
                },

                updateStock(row) {
                    const product = this.products.find(p => String(p.id) === String(row.product_id));

                    if (product && this.warehouse_id) {
                        row.stock = product.stocks[this.warehouse_id] ?? 0;
                    } else {
                        row.stock = 0;
                    }
                },

                updateAllStock() {
                    this.rows.forEach(row => this.updateStock(row));
                },

                get totals() {
                    return this.rows.reduce((acc, row) => {
                        const quantity = parseFloat(row.quantity) || 0;
                        const price = parseFloat(row.price) || 0;
                        const discount = parseFloat(row.discount) || 0;
                        const taxRate = parseFloat(row.tax_rate) || 0;

                        const subtotal = quantity * price;
                        const net = Math.max(subtotal - discount, 0);
                        const tax = net * taxRate / 100;
                        const total = net + tax;

                        acc.subtotal += subtotal;
                        acc.discount += discount;
                        acc.tax += tax;
                        acc.total += total;

                        return acc;
                    }, {
                        subtotal: 0,
                        discount: 0,
                        tax: 0,
                        total: 0
                    });
                }
            };
        }
    </script>

@endsection