@extends('layouts.master')

@section('title', 'طباعة باركود الأصناف')

@section('content')

    <h4 class="mb-4">طباعة باركود الأصناف</h4>

    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('barcode.index') }}">
                <div class="row">

                    <div class="col-md-5">
                        <label class="form-label">الصنف</label>
                        <select name="product_id" class="form-select">
                            <option value="">اختر الصنف</option>

                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                                    {{ $product->name }} - {{ $product->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">عدد الملصقات</label>
                        <input type="number" name="copies" value="{{ $copies }}" min="1" max="100" class="form-control">
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">عرض الملصقات</button>
                        <button type="button" class="btn btn-success no-print" onclick="window.print()">🖨️ طباعة</button>
                    </div>

                </div>
            </form>

        </div>
    </div>

    @if($selectedProduct)
        @php
            $barcodeValue = $selectedProduct->barcode ?: $selectedProduct->code;
        @endphp

        <div class="labels-grid">
            @for($i = 0; $i < $copies; $i++)
                <div class="barcode-label">
                    <div class="label-company">
                        {{ \App\Models\Setting::where('key', 'company_name')->value('value') ?? 'نظام المبيعات' }}
                    </div>

                    <div class="label-name">
                        {{ $selectedProduct->name }}
                    </div>

                    <div class="label-price">
                        {{ number_format((float) $selectedProduct->sale_price, 2) }}
                        {{ \App\Models\Setting::where('key', 'currency')->value('value') ?? '' }}
                    </div>

                    <svg class="barcode-svg" data-value="{{ $barcodeValue }}"></svg>
                </div>
            @endfor
        </div>
    @else
        <div class="alert alert-info">
            اختر صنفًا لعرض وطباعة ملصقات الباركود.
        </div>
    @endif

    <style>
        .labels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(62mm, 1fr));
            gap: 6px;
        }

        .barcode-label {
            border: 1px dashed #000;
            padding: 6px;
            text-align: center;
            width: 62mm;
            break-inside: avoid;
        }

        .label-company {
            font-size: 10px;
            color: #333;
        }

        .label-name {
            font-size: 12px;
            font-weight: bold;
            margin: 3px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label-price {
            font-size: 12px;
            margin-bottom: 3px;
        }

        @media print {
            .no-print,
            nav,
            form,
            h4,
            .alert {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>

    <script src="{{ asset('assets/js/jsbarcode.min.js') }}"></script>

    <script>
        window.addEventListener('load', function () {
            document.querySelectorAll('.barcode-svg').forEach(function (element) {
                JsBarcode(element, element.getAttribute('data-value'), {
                    format: 'CODE128',
                    displayValue: true,
                    fontSize: 12,
                    margin: 5,
                    height: 40
                });
            });
        });
    </script>

@endsection