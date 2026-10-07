@extends('layouts.master')

@section('title', 'إنشاء فاتورة بيع')

@section('content')

<div class="card">

    <div class="card-header">
        <h5 class="mb-0">إنشاء فاتورة بيع</h5>
    </div>

    <div class="card-body">

        @include('partials.errors')

        <form method="POST"
              action="{{ route('sales.store') }}"
              x-data="saleInvoice()">

            @csrf

            {{-- ========================================= --}}
            {{-- بيانات الفاتورة --}}
            {{-- ========================================= --}}

            <div class="row">

                {{-- العميل --}}
                <div class="col-md-4 mb-3">

                    <label class="form-label">العميل</label>

                    <select name="customer_id"
                            class="form-select"
                            required>

                        <option value="">اختر العميل</option>

                        @foreach($customers as $customer)

                            <option value="{{ $customer->id }}"
                                @selected(old('customer_id') == $customer->id)>

                                {{ $customer->name }}

                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- المخزن --}}
                <div class="col-md-4 mb-3">

                    <label class="form-label">المخزن</label>

                    @if($warehouses->isEmpty())

                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            لا توجد مخازن مسجلة.
                        </div>

                    @else

                        <select name="warehouse_id"
                                class="form-select"
                                x-model="warehouse_id"
                                @change="updateAllStock()"
                                required>

                            <option value="">اختر المخزن</option>

                            @foreach($warehouses as $warehouse)

                                <option value="{{ $warehouse->id }}"
                                    @selected(
                                        old(
                                            'warehouse_id',
                                            $warehouses->first()->id
                                        ) == $warehouse->id
                                    )>

                                    {{ $warehouse->name }}

                                </option>

                            @endforeach

                        </select>

                    @endif

                </div>


                {{-- تاريخ الفاتورة --}}
                <div class="col-md-2 mb-3">

                    <label class="form-label">
                        تاريخ الفاتورة
                    </label>

                    <input type="date"
                           name="invoice_date"
                           value="{{ old('invoice_date', date('Y-m-d')) }}"
                           class="form-control"
                           required>

                </div>


                {{-- تاريخ الاستحقاق --}}
                <div class="col-md-2 mb-3">

                    <label class="form-label">
                        تاريخ الاستحقاق
                    </label>

                    <input type="date"
                           name="due_date"
                           value="{{ old('due_date') }}"
                           class="form-control">

                </div>

            </div>


            <hr>


            {{-- ========================================= --}}
            {{-- الأصناف --}}
            {{-- ========================================= --}}

            <div class="d-flex justify-content-between align-items-center mb-2">

                <h6 class="mb-0">
                    الأصناف
                </h6>

                <button type="button"
                        class="btn btn-sm btn-outline-primary"
                        @click="addRow()">

                    إضافة صنف

                </button>

            </div>


            <table class="table table-bordered">

                <thead>

                    <tr>

                        <th style="width: 35%">
                            الصنف
                        </th>

                        <th style="width: 10%">
                            الكمية
                        </th>

                        <th style="width: 12%">
                            السعر
                        </th>

                        <th style="width: 10%">
                            الخصم
                        </th>

                        <th style="width: 10%">
                            المخزون
                        </th>

                        <th style="width: 13%">
                            الإجمالي
                        </th>

                        <th style="width: 10%">
                            حذف
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <template x-for="(row, index) in rows"
                              :key="index">

                        <tr>

                            {{-- الصنف --}}
                            <td>

                                <select :name="'items[' + index + '][product_id]'"
                                        class="form-select"
                                        x-model="row.product_id"
                                        @change="onProductChange(row)"
                                        required>

                                    <option value="">
                                        اختر الصنف
                                    </option>

                                    <template x-for="product in products"
                                              :key="product.id">

                                        <option :value="product.id"
                                                x-text="product.name">
                                        </option>

                                    </template>

                                </select>

                            </td>


                            {{-- الكمية --}}
                            <td>

                                <input type="number"
                                       step="1"
                                       min="1"
                                       :name="'items[' + index + '][quantity]'"
                                       class="form-control"
                                       x-model="row.quantity"
                                       @input="updateRow(row)"
                                       required>

                            </td>


                            {{-- السعر --}}
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


                            {{-- الخصم --}}
                            <td>

                                <input type="number"
                                       step="1"
                                       min="0"
                                       :name="'items[' + index + '][discount]'"
                                       class="form-control"
                                       x-model="row.discount"
                                       @input="updateRow(row)">

                            </td>


                            {{-- المخزون --}}
                            <td>

                                <span x-text="row.stock"></span>

                            </td>


                            {{-- إجمالي الصنف --}}
                            <td>

                                <span
                                    x-text="Number(row.total || 0).toFixed(2)">
                                </span>

                            </td>


                            {{-- حذف --}}
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


            {{-- ========================================= --}}
            {{-- الملاحظات والإجماليات --}}
            {{-- ========================================= --}}

            <div class="row">

                {{-- الملاحظات --}}
                <div class="col-md-6">

                    <div class="mb-3">

                        <label class="form-label">
                            ملاحظات
                        </label>

                        <textarea name="notes"
                                  class="form-control">{{ old('notes') }}</textarea>

                    </div>

                </div>


                {{-- الإجماليات --}}
                <div class="col-md-6">

                    <div class="card bg-light">

                        <div class="card-body">

                            {{-- الإجمالي قبل الخصم --}}
                            <div class="d-flex justify-content-between">

                                <span>
                                    الإجمالي قبل الخصم
                                </span>

                                <strong
                                    x-text="totals.subtotal.toFixed(2)">
                                </strong>

                            </div>


                            {{-- الخصم --}}
                            <div class="d-flex justify-content-between">

                                <span>
                                    الخصم
                                </span>

                                <strong
                                    x-text="totals.discount.toFixed(2)">
                                </strong>

                            </div>


                            {{-- الضريبة --}}
                            <div class="d-flex justify-content-between">

                                <span>
                                    الضريبة
                                </span>

                                <strong
                                    x-text="totals.tax.toFixed(2)">
                                </strong>

                            </div>


                            <hr>


                            {{-- الإجمالي النهائي --}}
                            <div class="d-flex justify-content-between">

                                <span>
                                    <strong>
                                        الإجمالي النهائي
                                    </strong>
                                </span>

                                <strong
                                    x-text="totals.total.toFixed(2)">
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================= --}}
            {{-- طريقة الدفع والمبلغ المدفوع --}}
            {{-- ========================================= --}}

            <div class="row mt-3">

                {{-- طريقة الدفع --}}
                <div class="col-md-3">

                    <label class="form-label">
                        طريقة الدفع
                    </label>

                    <select name="payment_method"
                            class="form-select">

                        <option value="cash"
                            @selected(old('payment_method', 'cash') == 'cash')>

                            نقدي

                        </option>

                        <option value="card"
                            @selected(old('payment_method') == 'card')>

                            بطاقة

                        </option>

                        <option value="bank_transfer"
                            @selected(old('payment_method') == 'bank_transfer')>

                            تحويل بنكي

                        </option>

                        <option value="cheque"
                            @selected(old('payment_method') == 'cheque')>

                            شيك

                        </option>

                        <option value="credit"
                            @selected(old('payment_method') == 'credit')>

                            آجل

                        </option>

                    </select>

                </div>


                {{-- المبلغ المدفوع --}}
                <div class="col-md-3">

                    <label class="form-label">
                        المبلغ المدفوع
                    </label>

                    {{-- القيمة الظاهرة للمستخدم --}}
                    <input type="text"
                           x-model="paidAmountFormatted"
                           @input="paidAmountManuallyEdited = true"
                           @blur="formatPaidAmount($event)"
                           class="form-control"
                           inputmode="decimal"
                           autocomplete="off"
                           placeholder="0">

                    {{-- القيمة التي يتم إرسالها إلى Laravel --}}
                    <input type="hidden"
                           name="paid_amount"
                           :value="paidAmount">

                </div>

            </div>


            {{-- ========================================= --}}
            {{-- الأزرار --}}
            {{-- ========================================= --}}

            <div class="mt-4">

                <button type="submit"
                        class="btn btn-success">

                    حفظ الفاتورة

                </button>

                <a href="{{ route('sales.index') }}"
                   class="btn btn-secondary">

                    إلغاء

                </a>

            </div>

        </form>

    </div>

</div>


<script>

function saleInvoice() {

    return {

        // =========================================
        // المخزن
        // =========================================

        warehouse_id: @json(
            old('warehouse_id', $warehouses->first()->id ?? '')
        ),


        // =========================================
        // المنتجات
        // =========================================

        products: @json($products),


        // =========================================
        // الأصناف
        // =========================================

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


        // =========================================
        // المبلغ المدفوع
        // =========================================

        paidAmount: @json(old('paid_amount', '')),

        paidAmountFormatted: @json(old('paid_amount', '')),

        paidAmountManuallyEdited: false,


        // =========================================
        // تشغيل عند تحميل الصفحة
        // =========================================

        init() {

            /*
             * إذا كانت الصفحة رجعت بسبب Validation Error
             * وهناك مبلغ قديم، نحافظ عليه.
             */

            if (
                this.paidAmount !== null &&
                this.paidAmount !== ''
            ) {

                this.paidAmount =
                    Number(this.paidAmount);

                this.paidAmountFormatted =
                    this.formatNumber(this.paidAmount);

                this.paidAmountManuallyEdited = true;

            } else {

                /*
                 * أول فتح للفواتير:
                 * المبلغ المدفوع = الإجمالي النهائي
                 */

                this.syncPaidAmountWithTotal();

            }


            /*
             * تحديث المخزون حسب المخزن المختار
             */

            this.updateAllStock();

        },


        // =========================================
        // إضافة صنف
        // =========================================

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


            this.syncPaidAmountWithTotal();

        },


        // =========================================
        // حذف صنف
        // =========================================

        removeRow(index) {

            this.rows.splice(index, 1);

            this.syncPaidAmountWithTotal();

        },


        // =========================================
        // عند اختيار الصنف
        // =========================================

        onProductChange(row) {

            const product =
                this.products.find(
                    p =>
                        String(p.id) ===
                        String(row.product_id)
                );


            if (product) {

                /*
                 * في المبيعات نستخدم sale_price
                 */

                row.price =
                    product.sale_price ?? 0;


                row.tax_rate =
                    product.tax_rate ?? 0;

            } else {

                row.price = 0;

                row.tax_rate = 0;

            }


            this.updateRow(row);

            this.updateStock(row);

        },


        // =========================================
        // حساب الصف
        // =========================================

        updateRow(row) {

            const quantity =
                parseFloat(row.quantity) || 0;


            const price =
                parseFloat(row.price) || 0;


            const discount =
                parseFloat(row.discount) || 0;


            const taxRate =
                parseFloat(row.tax_rate) || 0;


            // الإجمالي قبل الخصم
            row.subtotal =
                quantity * price;


            // بعد الخصم
            const net =
                Math.max(
                    row.subtotal - discount,
                    0
                );


            // الضريبة
            row.tax =
                net * taxRate / 100;


            // إجمالي الصف
            row.total =
                net + row.tax;


            /*
             * تحديث المبلغ المدفوع تلقائيًا
             * طالما المستخدم لم يعدله يدويًا
             */

            this.syncPaidAmountWithTotal();

        },


        // =========================================
        // تحديث مخزون الصنف
        // =========================================

        updateStock(row) {

            const product =
                this.products.find(
                    p =>
                        String(p.id) ===
                        String(row.product_id)
                );


            if (
                product &&
                this.warehouse_id
            ) {

                row.stock =
                    product.stocks?.[this.warehouse_id] ?? 0;

            } else {

                row.stock = 0;

            }

        },


        // =========================================
        // تحديث مخزون كل الأصناف
        // =========================================

        updateAllStock() {

            this.rows.forEach(row => {

                this.updateStock(row);

            });

        },


        // =========================================
        // حساب الإجماليات
        // =========================================

        calculateTotals() {

            return this.rows.reduce(

                (acc, row) => {

                    const quantity =
                        parseFloat(row.quantity) || 0;


                    const price =
                        parseFloat(row.price) || 0;


                    const discount =
                        parseFloat(row.discount) || 0;


                    const taxRate =
                        parseFloat(row.tax_rate) || 0;


                    // قبل الخصم
                    const subtotal =
                        quantity * price;


                    // بعد الخصم
                    const net =
                        Math.max(
                            subtotal - discount,
                            0
                        );


                    // الضريبة
                    const tax =
                        net * taxRate / 100;


                    // الإجمالي النهائي
                    const total =
                        net + tax;


                    acc.subtotal += subtotal;

                    acc.discount += discount;

                    acc.tax += tax;

                    acc.total += total;


                    return acc;

                },

                {

                    subtotal: 0,

                    discount: 0,

                    tax: 0,

                    total: 0

                }

            );

        },


        // =========================================
        // الإجماليات
        // =========================================

        get totals() {

            return this.calculateTotals();

        },


        // =========================================
        // مزامنة المبلغ المدفوع مع الإجمالي
        // =========================================

        syncPaidAmountWithTotal() {

            /*
             * إذا عدّل المستخدم المبلغ بنفسه
             * لا نغيره.
             */

            if (
                this.paidAmountManuallyEdited
            ) {

                return;

            }


            const total =
                this.calculateTotals().total;


            this.paidAmount =
                Math.round(total * 100) / 100;


            this.paidAmountFormatted =
                this.formatNumber(
                    this.paidAmount
                );

        },


        // =========================================
        // تنسيق الرقم
        // =========================================

        formatNumber(value) {

            if (
                value === null ||
                value === undefined ||
                value === ''
            ) {

                return '';

            }


            const number =
                Number(value);


            if (isNaN(number)) {

                return '';

            }


            return number.toLocaleString(
                'en-US',
                {
                    maximumFractionDigits: 2
                }
            );

        },


        // =========================================
        // تعديل المبلغ المدفوع
        // =========================================

        formatPaidAmount(event) {

            let value =
                event.target.value;


            /*
             * إزالة الفواصل
             */

            value =
                value.replace(/,/g, '');


            /*
             * السماح بالأرقام والنقطة العشرية
             */

            value =
                value.replace(/[^\d.]/g, '');


            /*
             * منع وجود أكثر من نقطة
             */

            const parts =
                value.split('.');


            if (parts.length > 2) {

                value =
                    parts[0] +
                    '.' +
                    parts.slice(1).join('');

            }


            /*
             * القيمة الحقيقية
             * التي سترسل إلى Laravel
             */

            this.paidAmount =
                value === ''
                    ? ''
                    : Number(value);


            /*
             * القيمة الظاهرة
             */

            this.paidAmountFormatted =
                value === ''
                    ? ''
                    : this.formatNumber(value);

        }

    };

}

</script>

@endsection