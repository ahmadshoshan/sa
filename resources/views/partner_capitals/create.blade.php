@extends('layouts.master')

@section('title', 'إضافة رأس مال')

@section('content')

    <div class="card">
        <div class="card-header">
            إضافة رأس مال للشريك: {{ $partner->name }}
        </div>

        <div class="card-body">
            @include('partials.errors')

            <form method="POST"
                  action="{{ route('partners.capitals.store', $partner) }}"
                  x-data="capitalForm()">

                @csrf

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label">التاريخ</label>

                        <input type="date"
                               name="contribution_date"
                               value="{{ old('contribution_date', date('Y-m-d')) }}"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">نوع المساهمة</label>

                        <select name="contribution_type"
                                class="form-select"
                                required>

                            <option value="cash"
                                @selected(old('contribution_type', 'cash') == 'cash')}>
                                نقدي
                            </option>

                            <option value="inventory"
                                @selected(old('contribution_type') == 'inventory')}>
                                بضاعة
                            </option>

                            <option value="asset"
                                @selected(old('contribution_type') == 'asset')}>
                                أصل
                            </option>

                            <option value="other"
                                @selected(old('contribution_type') == 'other')}>
                                أخرى
                            </option>

                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">المبلغ</label>

                        {{-- القيمة التي يراها المستخدم --}}
                        <input type="text"
                               x-model="amountFormatted"
                               @input="formatAmount($event)"
                               class="form-control"
                               inputmode="numeric"
                               autocomplete="off"
                               placeholder="مثال: 100,000,000"
                               required>

                        {{-- القيمة الحقيقية التي يتم إرسالها إلى Laravel --}}
                        <input type="hidden"
                               name="amount"
                               :value="amount">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">طريقة الدفع</label>

                        <select name="payment_method"
                                class="form-select">

                            <option value="cash"
                                @selected(old('payment_method', 'cash') == 'cash')}>
                                نقدي
                            </option>

                            <option value="card"
                                @selected(old('payment_method') == 'card')}>
                                بطاقة
                            </option>

                            <option value="bank_transfer"
                                @selected(old('payment_method') == 'bank_transfer')}>
                                تحويل بنكي
                            </option>

                            <option value="cheque"
                                @selected(old('payment_method') == 'cheque')}>
                                شيك
                            </option>

                            <option value="credit"
                                @selected(old('payment_method') == 'credit')}>
                                آجل / غير مدفوع
                            </option>

                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">البيان</label>

                        <textarea name="description"
                                  class="form-control">{{ old('description') }}</textarea>
                    </div>

                </div>

                <button type="submit" class="btn btn-success">
                    حفظ
                </button>

                <a href="{{ route('partners.show', $partner) }}"
                   class="btn btn-secondary">
                    إلغاء
                </a>

            </form>
        </div>
    </div>


    <script>
        function capitalForm() {
            return {

                // القيمة الحقيقية التي سترسل للـ Laravel
                amount: '{{ old('amount') }}',

                // القيمة التي تظهر للمستخدم
                amountFormatted: '{{ old('amount') }}',

                formatAmount(event) {

                    // الحصول على القيمة المكتوبة
                    let value = event.target.value;

                    // إزالة الفواصل وأي حروف أو رموز
                    value = value
                        .replace(/,/g, '')
                        .replace(/\D/g, '');

                    // القيمة النظيفة التي سيتم إرسالها
                    this.amount = value;

                    // تنسيق القيمة للمستخدم
                    this.amountFormatted = value
                        ? Number(value).toLocaleString('en-US')
                        : '';
                }
            };
        }
    </script>

@endsection