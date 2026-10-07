<div class="card">
    <div class="card-body">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>الحساب</th>
                    <th>مدين</th>
                    <th>دائن</th>
                    <th>القيمة</th>
                    <th>دفتر الأستاذ</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['account']->code }}</td>
                        <td>{{ $row['account']->name }}</td>
                        <td>{{ number_format((float) $row['debit'], 2) }}</td>
                        <td>{{ number_format((float) $row['credit'], 2) }}</td>

                        <td>
                            <span class="{{ $row['amount'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format((float) $row['amount'], 2) }}
                            </span>
                        </td>

                        <td>
                            <a href="{{ route('financial.ledger', array_filter([
                                'account_id' => $row['account']->id,
                                'from' => $from ?? null,
                                'to' => $to ?? null,
                            ])) }}" class="btn btn-sm btn-info">
                                عرض
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            لا توجد نتائج.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="4">الإجمالي</th>
                    <th>
                        <span class="{{ $total >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format((float) $total, 2) }}
                        </span>
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

    </div>
</div>