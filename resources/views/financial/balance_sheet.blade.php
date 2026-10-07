@extends('layouts.master')

@section('title', 'المركز المالي (الميزانية العمومية)')

@section('content')

    <h4 class="mb-4">المركز المالي (الميزانية العمومية)</h4>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('financial.balance-sheet') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">حتى تاريخ</label>
                        <input type="date" name="to" value="{{ $toDate }}" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-primary">عرض الميزانية</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- الأصول -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">الأصول</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            @forelse($assets['rows'] as $row)
                                <tr>
                                    <td>{{ $row['account']->name }}</td>
                                    <td class="text-end">{{ number_format((float) $row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td class="text-center text-muted">لا توجد أصول</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td>إجمالي الأصول</td>
                                <td class="text-end">{{ number_format((float) $totalAssets, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- الخصوم وحقوق الملكية -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">الخصوم وحقوق الملكية</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-muted mt-2">الخصوم</h6>
                    <table class="table table-sm">
                        <tbody>
                            @forelse($liabilities['rows'] as $row)
                                <tr>
                                    <td>{{ $row['account']->name }}</td>
                                    <td class="text-end">{{ number_format((float) $row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td class="text-center text-muted">لا توجد خصوم</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>إجمالي الخصوم</td>
                                <td class="text-end">{{ number_format((float) $liabilities['total'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <hr>

                    <h6 class="text-muted mt-3">حقوق الملكية</h6>
                    <table class="table table-sm">
                        <tbody>
                            @foreach($equityAccounts['rows'] as $row)
                                <tr>
                                    <td>{{ $row['account']->name }}</td>
                                    <td class="text-end">{{ number_format((float) $row['balance'], 2) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>صافي {{ $netIncome >= 0 ? 'ربح' : 'خسارة' }} الفترة</td>
                                <td class="text-end {{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format((float) $netIncome, 2) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td>إجمالي حقوق الملكية</td>
                                <td class="text-end">{{ number_format((float) $totalEquity, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <hr>
                    <table class="table table-sm">
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td>إجمالي الخصوم وحقوق الملكية</td>
                                <td class="text-end">{{ number_format((float) $totalLiabEquity, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- حالة التوازن -->
    <div class="card">
        <div class="card-body text-center">
            @if($isBalanced)
                <h4 class="text-success mb-0">✅ الميزانية متوازنة (الأصول = الخصوم + حقوق الملكية)</h4>
            @else
                <h4 class="text-danger mb-0">⚠️ الميزانية غير متوازنة (يوجد فرق بقيمة {{ number_format(abs($totalAssets - $totalLiabEquity), 2) }})</h4>
                <small class="text-muted">قد يكون السبب وجود قيود يدوية غير متوازنة أو أخطاء في ترحيل الحسابات.</small>
            @endif
        </div>
    </div>

@endsection