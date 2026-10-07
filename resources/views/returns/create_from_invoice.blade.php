@extends('layouts.master')

@section('title', 'إرجاع فاتورة ' . $invoice->invoice_no)

@section('content')

<style>
    .return-item-row {
        transition: all 0.2s;
    }

    .return-item-row.selected {
        background-color: #f0fff4;
    }

    .settlement-option {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.2s;
        height: 100%;
    }

    .settlement-option:hover {
        border-color: #0d6efd;
        background: #f0f7ff;
    }

    .settlement-option.selected {
        border-color: #198754;
        background: #d4edda;
    }

    .settlement-option input[type="radio"] {
        margin-right: 8px;
    }

    .invoice-summary {
        background: linear-gradient(135deg, #198754 0%, #0d6efd 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .invoice-summary .label {
        opacity: 0.85;
        font-size: 13px;
    }

    .invoice-summary .value {
        font-size: 20px;
        font-weight: bold;
    }

    .items-alert {
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s;
    }

    .items-alert.warning {
        background: #fff3cd;
        border: 1px solid #ffecb5;
        color: #664d03;
    }

    .items-alert.success {
        background: #d1e7dd;
        border: 1px solid #badbcc;
        color: #0f5132;
    }

    .items-alert.danger {
        background: #f8d7da;
        border: 1px solid #f5c2c7;
        color: #842029;
    }

    .items-alert-icon {
        font-size: 25px;
        line-height: 1;
    }

    .qty-input {
        max-width: 200px;
    }

    #grand-total {
        font-size: 20px;
    }

    .btn-submit {
        min-height: 55px;
        font-size: 18px;
    }
</style>


<div class="container-fluid py-4">

    {{-- العنوان --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            🔄 إرجاع فاتورة
            {{ $invoice->type === 'purchase' ? 'شراء' : 'بيع' }}:
            {{ $invoice->invoice_no }}
        </h2>

        <a href="{{ $invoice->type === 'purchase'
                ? route('purchases.index')
                : route('sales.index') }}"
           class="btn btn-outline-secondary">
            ↩️ رجوع
        </a>

    </div>


    {{-- رسائل Laravel --}}
    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert">
            </button>

        </div>

    @endif


    {{-- ملخص الفاتورة --}}
    <div class="invoice-summary">

        <div class="row">

            <div class="col-md-3">

                <div class="label">
                    رقم الفاتورة
                </div>

                <div class="value">
                    {{ $invoice->invoice_no }}
                </div>

            </div>


            <div class="col-md-3">

                <div class="label">
                    {{ $invoice->type === 'purchase' ? 'المورد' : 'العميل' }}
                </div>

                <div class="value">

                    {{ $invoice->type === 'purchase'
                        ? $invoice->supplier?->name
                        : $invoice->customer?->name }}

                </div>

            </div>


            <div class="col-md-2">

                <div class="label">
                    الإجمالي
                </div>

                <div class="value">
                    {{ number_format($invoice->total, 2) }}
                </div>

            </div>


            <div class="col-md-2">

                <div class="label">
                    المدفوع
                </div>

                <div class="value">
                    {{ number_format($invoice->paid_amount, 2) }}
                </div>

            </div>


            <div class="col-md-2">

                <div class="label">
                    المتبقي
                </div>

                <div class="value">
                    {{ number_format($invoice->remaining_amount, 2) }}
                </div>

            </div>

        </div>

    </div>


    {{-- تنبيه حالة الأصناف --}}
    <div class="items-alert warning" id="itemsAlert">

        <div class="items-alert-icon" id="alertIcon">
            ⚠️
        </div>

        <div>

            <strong id="alertTitle">
                لم يتم اختيار أي صنف للإرجاع
            </strong>

            <div id="alertDetails"
                 style="font-size: 13px; margin-top: 3px;">

                يجب تحديد صنف واحد على الأقل لإتمام عملية الإرجاع

            </div>

        </div>

    </div>


    {{-- نموذج الإرجاع --}}
    <form action="{{ route('returns.store-from-invoice', $invoice->id) }}"
          method="POST"
          id="returnForm">

        @csrf


        {{-- اختيار الأصناف --}}
        <div class="card mb-4">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <h5 class="mb-0">
                    📦 الأصناف المرتجعة
                </h5>

                <div class="d-flex gap-2">

                    <button type="button"
                            class="btn btn-sm btn-outline-primary"
                            onclick="selectAllItems()">
                        ☑️ تحديد الكل
                    </button>

                    <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            onclick="deselectAllItems()">
                        ⬜ إلغاء الكل
                    </button>

                </div>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th style="width: 60px">
                                    إرجاع
                                </th>

                                <th>
                                    الصنف
                                </th>

                                <th class="text-end">
                                    الكمية الأصلية
                                </th>

                                <th class="text-end">
                                    الكمية المرتجعة سابقاً
                                </th>

                                <th class="text-end">
                                    المتاح للإرجاع
                                </th>

                                <th class="text-end">
                                    السعر
                                </th>

                                <th class="text-end">
                                    الإجمالي المرتجع
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($invoice->items as $index => $item)

                                @php

                                    /*
                                     * سيتم لاحقاً استبدال هذا الحساب
                                     * بالحساب الحقيقي للمرتجعات السابقة
                                     */
                                    $alreadyReturned = 0;

                                    $maxReturnable =
                                        max(
                                            (float) $item->quantity -
                                            $alreadyReturned,
                                            0
                                        );

                                @endphp


                                {{-- صف الصنف --}}
                                <tr class="return-item-row"
                                    id="row-{{ $index }}">

                                    <td>

                                        <input type="checkbox"
                                               class="item-check form-check-input"
                                               data-index="{{ $index }}"
                                               data-max="{{ $maxReturnable }}"
                                               data-price="{{ $item->price }}"
                                               onchange="toggleItem(this)">

                                        <input type="hidden"
                                               name="items[{{ $index }}][item_id]"
                                               value="{{ $item->id }}">

                                    </td>


                                    <td>

                                        <strong>
                                            {{ $item->product?->name ?? 'غير محدد' }}
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            {{ $item->product?->code ?? '' }}
                                        </small>

                                    </td>


                                    <td class="text-end">

                                        {{ number_format($item->quantity, 3) }}

                                    </td>


                                    <td class="text-end text-warning">

                                        {{ number_format($alreadyReturned, 3) }}

                                    </td>


                                    <td class="text-end text-success">

                                        {{ number_format($maxReturnable, 3) }}

                                    </td>


                                    <td class="text-end">

                                        {{ number_format($item->price, 2) }}

                                    </td>


                                    <td class="text-end fw-bold"
                                        id="item-total-{{ $index }}">

                                        0.00

                                    </td>

                                </tr>


                                {{-- صف الكمية --}}
                                <tr class="return-item-row"
                                    style="display: none;"
                                    id="qty-row-{{ $index }}">

                                    <td></td>

                                    <td colspan="6">

                                        <label class="form-label fw-bold">

                                            الكمية المرتجعة:

                                        </label>


                                        <input type="number"
                                               name="items[{{ $index }}][quantity]"
                                               id="item-qty-{{ $index }}"
                                               step="0.001"
                                               min="0.001"
                                               max="{{ $maxReturnable }}"
                                               value="0"
                                               class="form-control qty-input"
                                               oninput="updateItemTotal(
                                                   {{ $index }},
                                                   {{ $item->price }}
                                               )">

                                        <small class="text-muted">

                                            الحد الأقصى:
                                            {{ number_format($maxReturnable, 3) }}

                                        </small>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>


                        {{-- الإجمالي --}}
                        <tfoot class="table-light">

                            <tr>

                                <td colspan="6"
                                    class="text-end fw-bold">

                                    إجمالي المرتجع:

                                </td>

                                <td class="text-end fw-bold text-danger"
                                    id="grand-total">

                                    0.00

                                </td>

                            </tr>

                        </tfoot>

                    </table>

                </div>

            </div>

        </div>


        {{-- طريقة معالجة المبلغ --}}
        <div class="card mb-4">

            <div class="card-header bg-white">

                <h5 class="mb-0">

                    💰 طريقة معالجة المبلغ المدفوع

                    <span class="text-danger">
                        ({{ number_format($invoice->paid_amount, 2) }})
                    </span>

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">


                    {{-- نقدي --}}
                    <div class="col-md-4">

                        <div class="settlement-option"
                             onclick="selectSettlement('cash_refund', this)">

                            <input type="radio"
                                   name="settlement_method"
                                   value="cash_refund"
                                   id="sm_cash">

                            <label for="sm_cash"
                                   class="w-100">

                                <strong>
                                    💵 تحصيل/رد نقدي
                                </strong>

                                <br>

                                <small class="text-muted">

                                    {{ $invoice->type === 'purchase'
                                        ? 'استلام المبلغ من المورد مع سند قبض'
                                        : 'رد المبلغ للعميل مع سند صرف' }}

                                </small>

                            </label>

                        </div>

                    </div>


                    {{-- ترحيل --}}
                    <div class="col-md-4">

                        <div class="settlement-option selected"
                             onclick="selectSettlement('carry_forward', this)">

                            <input type="radio"
                                   name="settlement_method"
                                   value="carry_forward"
                                   id="sm_carry"
                                   checked>

                            <label for="sm_carry"
                                   class="w-100">

                                <strong>

                                    📝 ترحيل لرصيد
                                    {{ $invoice->type === 'purchase'
                                        ? 'المورد'
                                        : 'العميل' }}

                                </strong>

                                <br>

                                <small class="text-muted">

                                    {{ $invoice->type === 'purchase'
                                        ? 'يُصبح دين لنا على المورد يمكن تحصيله أو خصمه لاحقاً'
                                        : 'يُصبح دين علينا للعميل يمكن خصمه من فاتورة أخرى' }}

                                </small>

                            </label>

                        </div>

                    </div>


                    {{-- خصم من فاتورة --}}
                    <div class="col-md-4">

                        <div class="settlement-option"
                             onclick="selectSettlement('offset_invoice', this)">

                            <input type="radio"
                                   name="settlement_method"
                                   value="offset_invoice"
                                   id="sm_offset">

                            <label for="sm_offset"
                                   class="w-100">

                                <strong>
                                    🔄 خصم من فاتورة أخرى
                                </strong>

                                <br>

                                <small class="text-muted">

                                    يُخصم المبلغ من فاتورة أخرى

                                </small>

                            </label>

                        </div>

                    </div>

                </div>


                {{-- اختيار الحساب --}}
                <div id="account-selection"
                     style="display: none; margin-top: 20px;">

                    <label class="form-label fw-bold">

                        اختر الحساب النقدي:

                    </label>


                    <div class="row g-2">

                        @foreach($accounts as $account)

                            <div class="col-md-4">

                                <div class="settlement-option"
                                     onclick="selectAccount(
                                         {{ $account->id }},
                                         this
                                     )">

                                    <input type="radio"
                                           name="account_id"
                                           value="{{ $account->id }}"
                                           id="acc_{{ $account->id }}">

                                    <label for="acc_{{ $account->id }}"
                                           class="w-100">

                                        <strong>

                                            @if($account->code === '1001')

                                                💵

                                            @elseif($account->code === '1002')

                                                🏦

                                            @else

                                                💳

                                            @endif

                                            {{ $account->name }}

                                        </strong>

                                        <br>

                                        <small class="text-muted">

                                            {{ $account->code }}

                                        </small>

                                    </label>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>


        {{-- الملاحظات --}}
        <div class="card mb-4">

            <div class="card-body">

                <label class="form-label fw-bold">

                    📝 ملاحظات:

                </label>

                <textarea name="notes"
                          rows="2"
                          class="form-control"
                          placeholder="سبب الإرجاع أو أي ملاحظات...">{{ old('notes') }}</textarea>

            </div>

        </div>


        {{-- زر التنفيذ --}}
        <div class="d-grid gap-2">

            <button type="submit"
                    class="btn btn-danger btn-lg btn-submit"
                    id="submitBtn">

                🔄 تأكيد الإرجاع

            </button>

        </div>

    </form>

</div>


<script>

    /*
    |--------------------------------------------------------------------------
    | المتغيرات العامة
    |--------------------------------------------------------------------------
    */

    let grandTotal = 0;


    /*
    |--------------------------------------------------------------------------
    | عند اختيار / إلغاء اختيار الصنف
    |--------------------------------------------------------------------------
    */

    function toggleItem(checkbox) {

        const index = checkbox.dataset.index;

        const qtyRow =
            document.getElementById('qty-row-' + index);

        const mainRow =
            document.getElementById('row-' + index);

        const qtyInput =
            document.getElementById('item-qty-' + index);

        const price =
            parseFloat(checkbox.dataset.price) || 0;

        const max =
            parseFloat(checkbox.dataset.max) || 0;


        if (checkbox.checked) {

            /*
             * إظهار حقل الكمية
             */
            qtyRow.style.display = 'table-row';

            mainRow.classList.add('selected');


            /*
             * وضع أقصى كمية تلقائياً
             */
            qtyInput.value = max;


            /*
             * حساب إجمالي الصنف
             */
            updateItemTotal(index, price);

        } else {

            /*
             * إخفاء حقل الكمية
             */
            qtyRow.style.display = 'none';

            mainRow.classList.remove('selected');


            /*
             * تصفير الكمية
             */
            qtyInput.value = 0;


            /*
             * تصفير إجمالي الصنف
             */
            document.getElementById(
                'item-total-' + index
            ).textContent = '0.00';


            calculateGrandTotal();

        }

        updateItemsAlert();

    }


    /*
    |--------------------------------------------------------------------------
    | تحديث إجمالي الصنف
    |--------------------------------------------------------------------------
    */

    function updateItemTotal(index, price) {

        const checkbox =
            document.querySelector(
                '.item-check[data-index="' + index + '"]'
            );

        const qtyInput =
            document.getElementById(
                'item-qty-' + index
            );


        if (!qtyInput) {
            return;
        }


        let qty =
            parseFloat(qtyInput.value) || 0;

        const max =
            parseFloat(checkbox?.dataset.max) || 0;


        /*
         * منع تجاوز الكمية المتاحة
         */
        if (qty > max) {

            qty = max;

            qtyInput.value = max;

        }


        /*
         * منع القيمة السالبة
         */
        if (qty < 0) {

            qty = 0;

            qtyInput.value = 0;

        }


        const total =
            qty * price;


        document.getElementById(
            'item-total-' + index
        ).textContent =
            total.toFixed(2);


        calculateGrandTotal();

        updateItemsAlert();

    }


    /*
    |--------------------------------------------------------------------------
    | حساب إجمالي المرتجع
    |--------------------------------------------------------------------------
    */

    function calculateGrandTotal() {

        grandTotal = 0;


        document.querySelectorAll(
            '.item-check:checked'
        ).forEach(function(checkbox) {

            const index =
                checkbox.dataset.index;

            const qtyInput =
                document.getElementById(
                    'item-qty-' + index
                );

            if (!qtyInput) {
                return;
            }


            const qty =
                parseFloat(qtyInput.value) || 0;

            const price =
                parseFloat(checkbox.dataset.price) || 0;


            if (qty > 0) {

                grandTotal +=
                    qty * price;

            }

        });


        document.getElementById(
            'grand-total'
        ).textContent =
            grandTotal.toFixed(2);

    }


    /*
    |--------------------------------------------------------------------------
    | تحديث التنبيه أعلى الصفحة
    |--------------------------------------------------------------------------
    */

    function updateItemsAlert() {

        const alertBox =
            document.getElementById('itemsAlert');

        const alertIcon =
            document.getElementById('alertIcon');

        const alertTitle =
            document.getElementById('alertTitle');

        const alertDetails =
            document.getElementById('alertDetails');


        let selectedCount = 0;

        let validCount = 0;


        document.querySelectorAll(
            '.item-check:checked'
        ).forEach(function(checkbox) {

            selectedCount++;


            const index =
                checkbox.dataset.index;

            const qtyInput =
                document.getElementById(
                    'item-qty-' + index
                );


            const qty =
                parseFloat(qtyInput?.value) || 0;


            if (qty > 0) {

                validCount++;

            }

        });


        /*
         * لا يوجد صنف
         */
        if (validCount === 0) {

            alertBox.className =
                'items-alert warning';

            alertIcon.textContent =
                '⚠️';

            alertTitle.textContent =
                'لم يتم اختيار أي صنف للإرجاع';

            alertDetails.textContent =
                'يجب تحديد صنف واحد على الأقل بكمية أكبر من صفر لإتمام عملية الإرجاع';

            return;

        }


        /*
         * يوجد أصناف
         */
        alertBox.className =
            'items-alert success';

        alertIcon.textContent =
            '✅';

        alertTitle.textContent =
            'تم اختيار ' +
            validCount +
            ' صنف للإرجاع';

        alertDetails.textContent =
            'إجمالي المبلغ المرتجع: ' +
            grandTotal.toFixed(2);

    }


    /*
    |--------------------------------------------------------------------------
    | تحديد كل الأصناف
    |--------------------------------------------------------------------------
    */

    function selectAllItems() {

        document.querySelectorAll(
            '.item-check'
        ).forEach(function(checkbox) {

            if (!checkbox.checked) {

                checkbox.checked = true;

                toggleItem(checkbox);

            }

        });

        updateItemsAlert();

    }


    /*
    |--------------------------------------------------------------------------
    | إلغاء تحديد كل الأصناف
    |--------------------------------------------------------------------------
    */

    function deselectAllItems() {

        document.querySelectorAll(
            '.item-check'
        ).forEach(function(checkbox) {

            if (checkbox.checked) {

                checkbox.checked = false;

                toggleItem(checkbox);

            }

        });


        updateItemsAlert();

    }


    /*
    |--------------------------------------------------------------------------
    | اختيار طريقة التسوية
    |--------------------------------------------------------------------------
    */

    function selectSettlement(method, element) {

        /*
         * إزالة التحديد السابق
         */
        document.querySelectorAll(
            '.settlement-option'
        ).forEach(function(opt) {

            opt.classList.remove('selected');

        });


        /*
         * تحديد الخيار الحالي
         */
        element.classList.add('selected');


        /*
         * تحديد Radio
         */
        const radio =
            element.querySelector(
                'input[type="radio"]'
            );


        if (radio) {

            radio.checked = true;

        }


        /*
         * إظهار / إخفاء الحساب النقدي
         */
        const accountSelection =
            document.getElementById(
                'account-selection'
            );


        if (method === 'cash_refund') {

            accountSelection.style.display =
                'block';

        } else {

            accountSelection.style.display =
                'none';


            /*
             * إزالة اختيار الحساب
             */
            document.querySelectorAll(
                'input[name="account_id"]'
            ).forEach(function(input) {

                input.checked = false;

            });


            document.querySelectorAll(
                '#account-selection .settlement-option'
            ).forEach(function(opt) {

                opt.classList.remove('selected');

            });

        }

    }


    /*
    |--------------------------------------------------------------------------
    | اختيار الحساب النقدي
    |--------------------------------------------------------------------------
    */

    function selectAccount(accountId, element) {

        document.querySelectorAll(
            '#account-selection .settlement-option'
        ).forEach(function(opt) {

            opt.classList.remove('selected');

        });


        element.classList.add('selected');


        const radio =
            element.querySelector(
                'input[type="radio"]'
            );


        if (radio) {

            radio.checked = true;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | التحقق قبل إرسال النموذج
    |--------------------------------------------------------------------------
    */

    document.getElementById(
        'returnForm'
    ).addEventListener(
        'submit',
        function(e) {

            let validItems = 0;

            let total = 0;

            let invalidQuantity = false;


            /*
             * فحص الأصناف
             */
            document.querySelectorAll(
                '.item-check:checked'
            ).forEach(function(checkbox) {

                const index =
                    checkbox.dataset.index;


                const qtyInput =
                    document.getElementById(
                        'item-qty-' + index
                    );


                if (!qtyInput) {
                    return;
                }


                const qty =
                    parseFloat(qtyInput.value) || 0;


                const price =
                    parseFloat(
                        checkbox.dataset.price
                    ) || 0;


                const max =
                    parseFloat(
                        checkbox.dataset.max
                    ) || 0;


                /*
                 * الكمية غير صحيحة
                 */
                if (
                    qty <= 0 ||
                    qty > max
                ) {

                    invalidQuantity = true;

                    return;

                }


                validItems++;

                total +=
                    qty * price;

            });


            /*
             * لا يوجد أصناف
             *
             * مهم جداً:
             * الزر غير disabled لذلك submit
             * سيصل إلى هنا ويظهر alert.
             */
            if (validItems === 0) {

                e.preventDefault();

                alert(
                    '⚠️ يجب اختيار صنف واحد على الأقل بكمية أكبر من صفر لإتمام عملية الإرجاع'
                );

                updateItemsAlert();

                return false;

            }


            /*
             * كمية غير صحيحة
             */
            if (invalidQuantity) {

                e.preventDefault();

                alert(
                    '⚠️ توجد كمية مرتجعة غير صحيحة. يجب أن تكون أكبر من صفر وألا تتجاوز الكمية المتاحة للإرجاع.'
                );

                return false;

            }


            /*
             * معرفة طريقة التسوية
             */
            const settlementMethod =
                document.querySelector(
                    'input[name="settlement_method"]:checked'
                )?.value;


            /*
             * في حالة الرد النقدي
             * يجب اختيار الحساب
             */
            if (
                settlementMethod ===
                'cash_refund'
            ) {

                const accountSelected =
                    document.querySelector(
                        'input[name="account_id"]:checked'
                    );


                if (!accountSelected) {

                    e.preventDefault();

                    alert(
                        '⚠️ يجب اختيار الحساب النقدي عند اختيار التحصيل/الرد النقدي'
                    );

                    return false;

                }

            }


            /*
             * إذا لم توجد طريقة تسوية
             */
            if (!settlementMethod) {

                e.preventDefault();

                alert(
                    '⚠️ يجب اختيار طريقة معالجة المبلغ'
                );

                return false;

            }


            /*
             * تأكيد نهائي
             */
            const confirmed =
                confirm(
                    '⚠️ هل أنت متأكد من تنفيذ الإرجاع؟\n\n' +

                    'عدد الأصناف: ' +
                    validItems +

                    '\nإجمالي المرتجع: ' +
                    total.toFixed(2)
                );


            if (!confirmed) {

                e.preventDefault();

                return false;

            }


            /*
             * السماح بالإرسال
             */
            return true;

        }
    );


    /*
    |--------------------------------------------------------------------------
    | تهيئة الصفحة
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function() {

            /*
             * تحديد طريقة الترحيل الافتراضية
             */
            const defaultSettlement =
                document.getElementById(
                    'sm_carry'
                );


            if (defaultSettlement) {

                const parent =
                    defaultSettlement.closest(
                        '.settlement-option'
                    );


                if (parent) {

                    parent.classList.add(
                        'selected'
                    );

                }

            }


            /*
             * تحديث التنبيه
             */
            updateItemsAlert();


            /*
             * تحديث الإجمالي
             */
            calculateGrandTotal();

        }
    );

</script>

@endsection