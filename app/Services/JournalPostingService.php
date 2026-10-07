<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Exception;
use Illuminate\Support\Facades\Auth;

class JournalPostingService
{
    private array $accountIds = [];

    private array $knownAccounts = [
        '1001' => ['name' => 'الصندوق', 'type' => 'asset'],
        '1002' => ['name' => 'البنك', 'type' => 'asset'],
        '1101' => ['name' => 'العملاء', 'type' => 'asset'],
        '1201' => ['name' => 'المخزون', 'type' => 'asset'],
        '1301' => ['name' => 'ضريبة المشتريات', 'type' => 'asset'],
        '1501' => ['name' => 'الأصول الثابتة', 'type' => 'asset'],
        '2101' => ['name' => 'الموردون', 'type' => 'liability'],
        '2201' => ['name' => 'ضريبة المبيعات', 'type' => 'liability'],
        '2301' => ['name' => 'مصروفات مستحقة', 'type' => 'liability'],
        '3101' => ['name' => 'رأس مال الشركاء', 'type' => 'equity'],
        '3102' => ['name' => 'مسحوبات الشركاء', 'type' => 'equity'],
        '3103' => ['name' => 'أرباح مرحّلة', 'type' => 'equity'],
        '3104' => ['name' => 'احتياطي قانوني', 'type' => 'equity'],
        '3201' => ['name' => 'أرباح مستحقة للتوزيع', 'type' => 'liability'],
        '4101' => ['name' => 'المبيعات', 'type' => 'revenue'],
        '4201' => ['name' => 'إيرادات أخرى', 'type' => 'revenue'],
        '5101' => ['name' => 'تكلفة البضاعة المباعة', 'type' => 'expense'],
        '5201' => ['name' => 'مصروفات عامة', 'type' => 'expense'],
        '5301' => ['name' => 'المرتبات والأجور', 'type' => 'expense'],
        '5302' => ['name' => 'العمولات', 'type' => 'expense'],
        '5303' => ['name' => 'النقل والمواصلات', 'type' => 'expense'],
        '5304' => ['name' => 'الإيجار', 'type' => 'expense'],
        '5305' => ['name' => 'الكهرباء والمياه', 'type' => 'expense'],
        '5306' => ['name' => 'الاتصالات', 'type' => 'expense'],
        '5307' => ['name' => 'الصيانة', 'type' => 'expense'],
        '5308' => ['name' => 'التسويق والإعلان', 'type' => 'expense'],
        '5309' => ['name' => 'الرسوم الحكومية', 'type' => 'expense'],
        '5310' => ['name' => 'الاستهلاك والإطفاء', 'type' => 'expense'],
        '5311' => ['name' => 'التمويل والفوائد', 'type' => 'expense'],
        '5312' => ['name' => 'مصروفات أخرى', 'type' => 'expense'],
    ];

    public function accountIdByCode(string $code): int
    {
        if (isset($this->accountIds[$code])) {
            return $this->accountIds[$code];
        }

        $account = Account::where('code', $code)->first();

        if (!$account) {
            if (!isset($this->knownAccounts[$code])) {
                throw new Exception("الحساب المحاسبي غير موجود: {$code}");
            }

            $account = Account::create([
                'code' => $code,
                'name' => $this->knownAccounts[$code]['name'],
                'type' => $this->knownAccounts[$code]['type'],
                'parent_id' => null,
                'is_active' => true,
            ]);
        }

        $this->accountIds[$code] = $account->id;

        return $account->id;
    }

    public function createJournal($date, string $description, string $refType, int $refId, array $lines): void
    {
        if (JournalEntry::where('ref_type', $refType)->where('ref_id', $refId)->exists()) {
            return;
        }

        $lines = array_values(array_filter($lines, function ($line) {
            return round((float) ($line['debit'] ?? 0), 2) != 0
                || round((float) ($line['credit'] ?? 0), 2) != 0;
        }));

        if (!count($lines)) {
            return;
        }

        foreach ($lines as $index => $line) {
            $lines[$index]['debit'] = round((float) ($line['debit'] ?? 0), 2);
            $lines[$index]['credit'] = round((float) ($line['credit'] ?? 0), 2);
        }

        $totalDebit = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));
        $diff = round($totalDebit - $totalCredit, 2);

        if (abs($diff) >= 0.01) {
            if ($diff > 0) {
                foreach ($lines as $index => $line) {
                    if ($line['credit'] > 0) {
                        $lines[$index]['credit'] = round($line['credit'] + $diff, 2);
                        break;
                    }
                }
            } else {
                foreach ($lines as $index => $line) {
                    if ($line['debit'] > 0) {
                        $lines[$index]['debit'] = round($line['debit'] + abs($diff), 2);
                        break;
                    }
                }
            }
        }

        $entry = JournalEntry::create([
            'entry_no' => $this->generateEntryNumber(),
            'entry_date' => $date,
            'description' => $description,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'is_posted' => true,
            'user_id' =>  Auth::id(),
        ]);

        foreach ($lines as $line) {
            if ($line['debit'] == 0 && $line['credit'] == 0) {
                continue;
            }

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $this->accountIdByCode($line['account_code']),
                'debit' => $line['debit'],
                'credit' => $line['credit'],
                'customer_id' => $line['customer_id'] ?? null,
                'supplier_id' => $line['supplier_id'] ?? null,
                'product_id' => $line['product_id'] ?? null,
                'invoice_id' => $line['invoice_id'] ?? null,
            ]);
        }
    }

    private function generateEntryNumber(): string
    {
        $lastId = JournalEntry::max('id') + 1;

        return 'JE-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }
}