<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class CommandCenterService
{
    protected AssistantService $assistant;

    public function __construct(AssistantService $assistant)
    {
        $this->assistant = $assistant;
    }

    /**
     * معالجة أي أمر/سؤال
     */
    public function process(string $query): array
    {
        $query = trim($query);
        if (empty($query)) {
            return $this->emptyResponse();
        }

        try {
            // 1. أوامر مباشرة (تبدأ بـ /)
            if (str_starts_with($query, '/')) {
                return $this->handleSlashCommand($query);
            }

            // 2. بحث عن كيانات (إذا كان النص قصير ومش واضح)
            if (strlen($query) < 50) {
                $searchResult = $this->searchEntities($query);
                if (!empty($searchResult['entities'])) {
                    return $searchResult;
                }
            }

            // 3. استعلام عن طريق المساعد الذكي
            $aiResult = $this->assistant->answer($query);
            
            return [
                'type' => 'answer',
                'answer' => $aiResult['answer'] ?? 'لم أفهم السؤال',
                'intent' => $aiResult['intent'] ?? 'unknown',
                'suggestions' => $aiResult['suggestions'] ?? [],
            ];
        } catch (\Throwable $e) {
            \Log::error('CommandCenter Error: ' . $e->getMessage());
            return [
                'type' => 'error',
                'message' => 'خطأ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * معالجة الأوامر المباشرة
     */
    protected function handleSlashCommand(string $query): array
    {
        $parts = explode(' ', substr($query, 1), 2);
        $command = strtolower($parts[0]);
        $args = $parts[1] ?? '';

        try {
            switch ($command) {
                // البحث
                case 'p':
                case 'prod':
                case 'product':
                case 'products':
                    return $this->searchProducts($args);
                
                case 'c':
                case 'cust':
                case 'customer':
                case 'customers':
                    return $this->searchCustomers($args);
                
                case 's':
                case 'sup':
                case 'supplier':
                case 'suppliers':
                    return $this->searchSuppliers($args);
                
                case 'inv':
                case 'invoice':
                case 'invoices':
                    return $this->searchInvoices($args);

                // الإنشاء
                case 'new-customer':
                case 'add-customer':
                    return $this->createCustomer($args);
                
                case 'new-supplier':
                case 'add-supplier':
                    return $this->createSupplier($args);
                
                case 'new-product':
                case 'add-product':
                    return $this->createProduct($args);

                // التقارير
                case 'sales':
                case 'sales-today':
                    return $this->getSalesReport($args);
                
                case 'purchases':
                    return $this->getPurchasesReport($args);
                
                case 'stock':
                case 'inventory':
                    return $this->getStockReport($args);
                
                case 'profit':
                    return $this->getProfitReport($args);

                // الحسابات
                case 'balance':
                    return $this->getBalance($args);
                
                case 'transfer':
                    return $this->handleTransfer($args);

                // التنقل
                case 'go':
                case 'open':
                    return $this->handleNavigation($args);

                default:
                    return [
                        'type' => 'error',
                        'message' => "أمر غير معروف: /{$command}",
                        'suggestions' => $this->getAvailableCommands(),
                    ];
            }
        } catch (\Throwable $e) {
            return [
                'type' => 'error',
                'message' => 'خطأ في تنفيذ الأمر: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * البحث عن منتجات
     */
    protected function searchProducts(string $query): array
    {
        if (empty($query)) {
            return [
                'type' => 'form',
                'title' => '🔍 البحث عن منتج',
                'fields' => [
                    ['name' => 'q', 'label' => 'اسم المنتج أو الكود', 'type' => 'text', 'required' => true],
                ],
                'action' => '/search-products',
            ];
        }

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            ->where('is_active', true)
            ->limit(10)
            ->get();

        $entities = $products->map(function ($p) {
            return [
                'type' => 'product',
                'icon' => '📦',
                'title' => $p->name,
                'subtitle' => "كود: {$p->code} • السعر: " . number_format($p->sale_price, 2),
                'id' => $p->id,
                'url' => route('products.show', $p->id),
                'actions' => [
                    ['label' => 'عرض', 'url' => route('products.show', $p->id)],
                    ['label' => 'كارتة', 'url' => route('product.card', $p->id)],
                    ['label' => 'تعديل', 'url' => route('products.edit', $p->id)],
                ],
            ];
        })->toArray();

        return [
            'type' => 'entities',
            'entities' => $entities,
        ];
    }

    /**
     * البحث عن عملاء
     */
    protected function searchCustomers(string $query): array
    {
        if (empty($query)) {
            return [
                'type' => 'form',
                'title' => '🔍 البحث عن عميل',
                'fields' => [
                    ['name' => 'q', 'label' => 'اسم العميل أو الكود', 'type' => 'text', 'required' => true],
                ],
                'action' => '/search-customers',
            ];
        }

        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->where('is_active', true)
            ->limit(10)
            ->get();

        $entities = $customers->map(function ($c) {
            return [
                'type' => 'customer',
                'icon' => '👤',
                'title' => $c->name,
                'subtitle' => "كود: {$c->code} • الرصيد: " . number_format($c->current_balance, 2),
                'id' => $c->id,
                'url' => route('customers.show', $c->id),
                'actions' => [
                    ['label' => 'عرض', 'url' => route('customers.show', $c->id)],
                    ['label' => 'كشف حساب', 'url' => route('statements.customer', $c->id)],
                    ['label' => 'تعديل', 'url' => route('customers.edit', $c->id)],
                ],
            ];
        })->toArray();

        return [
            'type' => 'entities',
            'entities' => $entities,
        ];
    }

    /**
     * البحث عن موردين
     */
    protected function searchSuppliers(string $query): array
    {
        if (empty($query)) {
            return [
                'type' => 'form',
                'title' => '🔍 البحث عن مورد',
                'fields' => [
                    ['name' => 'q', 'label' => 'اسم المورد أو الكود', 'type' => 'text', 'required' => true],
                ],
                'action' => '/search-suppliers',
            ];
        }

        $suppliers = Supplier::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->where('is_active', true)
            ->limit(10)
            ->get();

        $entities = $suppliers->map(function ($s) {
            return [
                'type' => 'supplier',
                'icon' => '🏭',
                'title' => $s->name,
                'subtitle' => "كود: {$s->code} • الرصيد: " . number_format($s->current_balance, 2),
                'id' => $s->id,
                'url' => route('suppliers.show', $s->id),
                'actions' => [
                    ['label' => 'عرض', 'url' => route('suppliers.show', $s->id)],
                    ['label' => 'كشف حساب', 'url' => route('statements.supplier', $s->id)],
                    ['label' => 'تعديل', 'url' => route('suppliers.edit', $s->id)],
                ],
            ];
        })->toArray();

        return [
            'type' => 'entities',
            'entities' => $entities,
        ];
    }

    /**
     * البحث عن فواتير
     */
    protected function searchInvoices(string $query): array
    {
        if (empty($query)) {
            return [
                'type' => 'form',
                'title' => '🔍 البحث عن فاتورة',
                'fields' => [
                    ['name' => 'q', 'label' => 'رقم الفاتورة', 'type' => 'text', 'required' => true],
                ],
                'action' => '/search-invoices',
            ];
        }

        $invoices = Invoice::where('invoice_no', 'like', "%{$query}%")
            ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$query}%"))
            ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$query}%"))
            ->limit(10)
            ->get();

        $entities = $invoices->map(function ($inv) {
            $typeLabel = match ($inv->type) {
                'sale' => '🧾 بيع',
                'purchase' => '🛒 شراء',
                'sale_return' => '↩️ مرتجع بيع',
                'purchase_return' => '↪️ مرتجع شراء',
                default => $inv->type,
            };
            return [
                'type' => 'invoice',
                'icon' => '📄',
                'title' => $inv->invoice_no,
                'subtitle' => "{$typeLabel} • " . number_format($inv->total, 2) . " • " . ($inv->customer?->name ?? $inv->supplier?->name ?? '-'),
                'id' => $inv->id,
                'url' => match ($inv->type) {
                    'sale' => route('sales.show', $inv->id),
                    'purchase' => route('purchases.show', $inv->id),
                    'sale_return' => route('sales-returns.show', $inv->id),
                    'purchase_return' => route('purchase-returns.show', $inv->id),
                    default => '#',
                },
                'actions' => [
                    ['label' => 'عرض', 'url' => '#'],
                ],
            ];
        })->toArray();

        return [
            'type' => 'entities',
            'entities' => $entities,
        ];
    }

    /**
     * البحث الشامل في كل الكيانات
     */
    public function searchEntities(string $query): array
    {
        $query = trim($query);
        if (empty($query)) return ['entities' => []];

        $results = [];

        // منتجات
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->where('is_active', true)
            ->limit(3)
            ->get();

        foreach ($products as $p) {
            $results[] = [
                'type' => 'product',
                'icon' => '📦',
                'title' => $p->name,
                'subtitle' => "كود: {$p->code} • السعر: " . number_format($p->sale_price, 2),
                'id' => $p->id,
                'url' => route('products.show', $p->id),
                'actions' => [
                    ['label' => 'عرض', 'url' => route('products.show', $p->id)],
                    ['label' => 'كارتة', 'url' => route('product.card', $p->id)],
                ],
            ];
        }

        // عملاء
        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->where('is_active', true)
            ->limit(3)
            ->get();

        foreach ($customers as $c) {
            $results[] = [
                'type' => 'customer',
                'icon' => '👤',
                'title' => $c->name,
                'subtitle' => "كود: {$c->code} • الرصيد: " . number_format($c->current_balance, 2),
                'id' => $c->id,
                'url' => route('customers.show', $c->id),
                'actions' => [
                    ['label' => 'عرض', 'url' => route('customers.show', $c->id)],
                    ['label' => 'كشف حساب', 'url' => route('statements.customer', $c->id)],
                ],
            ];
        }

        // موردين
        $suppliers = Supplier::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->where('is_active', true)
            ->limit(3)
            ->get();

        foreach ($suppliers as $s) {
            $results[] = [
                'type' => 'supplier',
                'icon' => '🏭',
                'title' => $s->name,
                'subtitle' => "كود: {$s->code} • الرصيد: " . number_format($s->current_balance, 2),
                'id' => $s->id,
                'url' => route('suppliers.show', $s->id),
                'actions' => [
                    ['label' => 'عرض', 'url' => route('suppliers.show', $s->id)],
                    ['label' => 'كشف حساب', 'url' => route('statements.supplier', $s->id)],
                ],
            ];
        }

        return ['entities' => $results];
    }

    /**
     * إنشاء عميل جديد سريعاً
     */
    protected function createCustomer(string $args): array
    {
        $parts = explode('|', $args);
        $name = trim($parts[0] ?? '');
        $phone = trim($parts[1] ?? '');

        if (empty($name)) {
            return [
                'type' => 'form',
                'title' => '➕ إضافة عميل جديد',
                'fields' => [
                    ['name' => 'name', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
                    ['name' => 'phone', 'label' => 'الهاتف', 'type' => 'text'],
                    ['name' => 'price_level', 'label' => 'مستوى السعر', 'type' => 'select', 'options' => [
                        'retail' => 'بيع عادي',
                        'wholesale' => 'جملة',
                        'factory' => 'مصنع',
                    ]],
                ],
                'action' => '/create-customer',
            ];
        }

        $code = 'C' . str_pad(((int) Customer::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
        $customer = Customer::create([
            'code' => $code,
            'name' => $name,
            'phone' => $phone ?: null,
            'price_level' => 'retail',
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
        ]);

        return [
            'type' => 'success',
            'message' => "✅ تم إضافة العميل: {$customer->name} ({$customer->code})",
            'data' => ['id' => $customer->id, 'url' => route('customers.show', $customer->id)],
        ];
    }

    /**
     * إنشاء مورد جديد سريعاً
     */
    protected function createSupplier(string $args): array
    {
        $parts = explode('|', $args);
        $name = trim($parts[0] ?? '');
        $phone = trim($parts[1] ?? '');

        if (empty($name)) {
            return [
                'type' => 'form',
                'title' => '➕ إضافة مورد جديد',
                'fields' => [
                    ['name' => 'name', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
                    ['name' => 'phone', 'label' => 'الهاتف', 'type' => 'text'],
                ],
                'action' => '/create-supplier',
            ];
        }

        $code = 'S' . str_pad(((int) Supplier::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $supplier = Supplier::create([
            'code' => $code,
            'name' => $name,
            'phone' => $phone ?: null,
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
        ]);

        return [
            'type' => 'success',
            'message' => "✅ تم إضافة المورد: {$supplier->name} ({$supplier->code})",
            'data' => ['id' => $supplier->id, 'url' => route('suppliers.show', $supplier->id)],
        ];
    }

    /**
     * إنشاء منتج جديد سريعاً
     */
    protected function createProduct(string $args): array
    {
        $parts = explode('|', $args);
        $name = trim($parts[0] ?? '');
        $salePrice = (float) ($parts[1] ?? 0);
        $costPrice = (float) ($parts[2] ?? $salePrice * 0.8);

        if (empty($name)) {
            return [
                'type' => 'form',
                'title' => '➕ إضافة منتج جديد',
                'fields' => [
                    ['name' => 'name', 'label' => 'الاسم', 'type' => 'text', 'required' => true],
                    ['name' => 'cost_price', 'label' => 'سعر التكلفة', 'type' => 'number', 'required' => true],
                    ['name' => 'sale_price', 'label' => 'سعر البيع', 'type' => 'number', 'required' => true],
                    ['name' => 'wholesale_price', 'label' => 'سعر الجملة', 'type' => 'number'],
                ],
                'action' => '/create-product',
            ];
        }

        $code = 'P' . str_pad(((int) Product::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
        $category = Category::first();
        $unit = Unit::first();

        $product = Product::create([
            'code' => $code,
            'name' => $name,
            'category_id' => $category?->id ?? 1,
            'unit_id' => $unit?->id ?? 1,
            'cost_price' => $costPrice,
            'sale_price' => $salePrice,
            'wholesale_price' => $salePrice * 0.9,
            'factory_price' => $salePrice * 0.75,
            'tax_rate' => 14,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        return [
            'type' => 'success',
            'message' => "✅ تم إضافة المنتج: {$product->name} ({$product->code})",
            'data' => ['id' => $product->id, 'url' => route('products.show', $product->id)],
        ];
    }

    /**
     * تقرير المبيعات
     */
    protected function getSalesReport(string $args): array
    {
        $period = strtolower(trim($args)) ?: 'today';
        
        $query = Invoice::where('type', 'sale');
        
        switch ($period) {
            case 'today':
                $query->whereDate('invoice_date', today());
                $periodLabel = 'اليوم';
                break;
            case 'yesterday':
                $query->whereDate('invoice_date', today()->subDay());
                $periodLabel = 'أمس';
                break;
            case 'week':
                $query->whereDate('invoice_date', '>=', now()->startOfWeek());
                $periodLabel = 'هذا الأسبوع';
                break;
            case 'month':
                $query->whereDate('invoice_date', '>=', now()->startOfMonth());
                $periodLabel = 'هذا الشهر';
                break;
            case 'year':
                $query->whereDate('invoice_date', '>=', now()->startOfYear());
                $periodLabel = 'هذه السنة';
                break;
            default:
                $query->whereDate('invoice_date', today());
                $periodLabel = 'اليوم';
        }

        $total = (float) $query->sum('total');
        $count = $query->count();

        return [
            'type' => 'report',
            'title' => "📊 تقرير المبيعات - {$periodLabel}",
            'data' => [
                'count' => $count,
                'total' => $total,
                'formatted_total' => number_format($total, 2),
            ],
        ];
    }

    /**
     * تقرير المشتريات
     */
    protected function getPurchasesReport(string $args): array
    {
        $period = strtolower(trim($args)) ?: 'today';
        $query = Invoice::where('type', 'purchase');
        
        switch ($period) {
            case 'today':
                $query->whereDate('invoice_date', today());
                break;
            case 'month':
                $query->whereDate('invoice_date', '>=', now()->startOfMonth());
                break;
            case 'year':
                $query->whereDate('invoice_date', '>=', now()->startOfYear());
                break;
        }

        $total = (float) $query->sum('total');
        $count = $query->count();

        return [
            'type' => 'report',
            'title' => '🛒 تقرير المشتريات',
            'data' => [
                'count' => $count,
                'total' => $total,
                'formatted_total' => number_format($total, 2),
            ],
        ];
    }

    /**
     * تقرير المخزون
     */
    protected function getStockReport(string $args): array
    {
        $lowStock = Product::withSum('stocks', 'quantity')
            ->where('is_active', true)
            ->get()
            ->filter(fn($p) => (float) $p->min_stock > 0 && ((float) ($p->stocks_sum_quantity ?? 0)) <= (float) $p->min_stock)
            ->take(10);

        $items = $lowStock->map(fn($p) => [
            'name' => $p->name,
            'current' => (float) ($p->stocks_sum_quantity ?? 0),
            'min' => (float) $p->min_stock,
        ])->values();

        return [
            'type' => 'report',
            'title' => '⚠️ الأصناف المنخفضة',
            'data' => ['items' => $items, 'count' => $items->count()],
        ];
    }

    /**
     * تقرير الأرباح
     */
    protected function getProfitReport(string $args): array
    {
        $from = now()->startOfMonth()->toDateString();
        $to = today()->toDateString();

        $revenueIds = Account::where('type', 'revenue')->pluck('id');
        $expenseIds = Account::where('type', 'expense')->pluck('id');

        $revenue = (float) JournalLine::whereIn('account_id', $revenueIds)
            ->whereHas('entry', fn($e) => $e->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to))
            ->selectRaw('COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) as bal')
            ->value('bal');

        $expense = (float) JournalLine::whereIn('account_id', $expenseIds)
            ->whereHas('entry', fn($e) => $e->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to))
            ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as bal')
            ->value('bal');

        return [
            'type' => 'report',
            'title' => '💹 تقرير الأرباح (هذا الشهر)',
            'data' => [
                'revenue' => $revenue,
                'expense' => $expense,
                'net' => $revenue - $expense,
                'formatted_revenue' => number_format($revenue, 2),
                'formatted_expense' => number_format($expense, 2),
                'formatted_net' => number_format($revenue - $expense, 2),
            ],
        ];
    }

    /**
     * الحصول على رصيد
     */
    protected function getBalance(string $args): array
    {
        $query = trim($args);
        if (empty($query)) {
            return [
                'type' => 'form',
                'title' => '💳 استعلام عن رصيد',
                'fields' => [
                    ['name' => 'entity', 'label' => 'العميل/المورد', 'type' => 'text', 'required' => true],
                ],
                'action' => '/balance',
            ];
        }

        $customer = Customer::where('name', 'like', "%{$query}%")->first();
        if ($customer) {
            return [
                'type' => 'answer',
                'answer' => "💳 رصيد العميل '{$customer->name}': " . number_format($customer->current_balance, 2) . " ج.م",
            ];
        }

        $supplier = Supplier::where('name', 'like', "%{$query}%")->first();
        if ($supplier) {
            return [
                'type' => 'answer',
                'answer' => "💳 رصيد المورد '{$supplier->name}': " . number_format($supplier->current_balance, 2) . " ج.م",
            ];
        }

        return ['type' => 'error', 'message' => 'لم أجد هذا العميل أو المورد'];
    }

    /**
     * التحويلات النقدية
     */
    protected function handleTransfer(string $args): array
    {
        return [
            'type' => 'form',
            'title' => '💱 تحويل نقدي',
            'fields' => [
                ['name' => 'amount', 'label' => 'المبلغ', 'type' => 'number', 'required' => true],
                ['name' => 'from_account', 'label' => 'من حساب', 'type' => 'select', 'options' => [
                    '1001' => '💵 الصندوق',
                    '1002' => '🏦 البنك',
                    '1003' => '💳 الكاش/الإنستا',
                ], 'required' => true],
                ['name' => 'to_account', 'label' => 'إلى حساب', 'type' => 'select', 'options' => [
                    '1001' => '💵 الصندوق',
                    '1002' => '🏦 البنك',
                    '1003' => '💳 الكاش/الإنستا',
                ], 'required' => true],
                ['name' => 'notes', 'label' => 'ملاحظات', 'type' => 'text'],
            ],
            'action' => '/transfer',
        ];
    }

    /**
     * التنقل بين الصفحات
     */
    protected function handleNavigation(string $args): array
    {
        $pages = [
            'dashboard' => ['الرئيسية', 'لوحة التحكم', 'dashboard'],
            'products' => ['الأصناف', 'المنتجات', 'products'],
            'customers' => ['العملاء', 'customers'],
            'suppliers' => ['الموردين', 'suppliers'],
            'sales' => ['المبيعات', 'فواتير البيع', 'sales'],
            'purchases' => ['المشتريات', 'فواتير الشراء', 'purchases'],
            'payments' => ['السندات', 'المدفوعات', 'payments'],
            'expenses' => ['المصروفات', 'expenses'],
            'reports' => ['التقارير', 'reports'],
            'settings' => ['الإعدادات', 'settings'],
        ];

        $query = strtolower(trim($args));
        foreach ($pages as $key => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($query, $keyword)) {
                    try {
                        $url = route("{$key}.index");
                        return [
                            'type' => 'redirect',
                            'url' => $url,
                            'message' => "🔗 جاري فتح صفحة: {$keyword}",
                        ];
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return ['type' => 'error', 'message' => 'لم أجد الصفحة المطلوبة'];
    }

    /**
     * تنفيذ إجراء من النموذج
     */
    public function executeForm(string $action, array $data): array
    {
        try {
            switch ($action) {
                case '/create-customer':
                    return $this->createCustomer(($data['name'] ?? '') . '|' . ($data['phone'] ?? ''));
                
                case '/create-supplier':
                    return $this->createSupplier(($data['name'] ?? '') . '|' . ($data['phone'] ?? ''));
                
                case '/create-product':
                    return $this->createProduct(($data['name'] ?? '') . '|' . ($data['sale_price'] ?? 0) . '|' . ($data['cost_price'] ?? 0));
                
                case '/balance':
                    return $this->getBalance($data['entity'] ?? '');
                
                case '/transfer':
                    return $this->executeTransfer($data);
                
                case '/search-products':
                    return $this->searchProducts($data['q'] ?? '');
                
                case '/search-customers':
                    return $this->searchCustomers($data['q'] ?? '');
                
                case '/search-suppliers':
                    return $this->searchSuppliers($data['q'] ?? '');
                
                case '/search-invoices':
                    return $this->searchInvoices($data['q'] ?? '');
                
                default:
                    return ['type' => 'error', 'message' => 'إجراء غير معروف'];
            }
        } catch (\Throwable $e) {
            return ['type' => 'error', 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    /**
     * تنفيذ التحويل النقدي
     */
    protected function executeTransfer(array $data): array
    {
        try {
            $fromAccount = Account::where('code', $data['from_account'])->first();
            $toAccount = Account::where('code', $data['to_account'])->first();

            if (!$fromAccount || !$toAccount) {
                return ['type' => 'error', 'message' => 'الحساب غير موجود'];
            }

            if ($fromAccount->id === $toAccount->id) {
                return ['type' => 'error', 'message' => 'لا يمكن التحويل لنفس الحساب'];
            }

            $fundService = app(\App\Services\FundService::class);
            $transfer = $fundService->transfer(
                $fromAccount->id,
                $toAccount->id,
                (float) $data['amount'],
                now()->format('Y-m-d'),
                $data['notes'] ?? null
            );

            return [
                'type' => 'success',
                'message' => "✅ تم التحويل بنجاح!\nمن: {$fromAccount->name}\nإلى: {$toAccount->name}\nالمبلغ: " . number_format($data['amount'], 2),
                'data' => ['transfer_no' => $transfer->transfer_no],
            ];
        } catch (\Exception $e) {
            return ['type' => 'error', 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    /**
     * الأوامر المتاحة
     */
    protected function getAvailableCommands(): array
    {
        return [
            ['command' => '/products [اسم]', 'description' => 'البحث عن منتجات'],
            ['command' => '/customers [اسم]', 'description' => 'البحث عن عملاء'],
            ['command' => '/suppliers [اسم]', 'description' => 'البحث عن موردين'],
            ['command' => '/new-customer', 'description' => 'إضافة عميل جديد'],
            ['command' => '/new-supplier', 'description' => 'إضافة مورد جديد'],
            ['command' => '/new-product', 'description' => 'إضافة منتج جديد'],
            ['command' => '/sales [today|month|year]', 'description' => 'تقرير المبيعات'],
            ['command' => '/purchases', 'description' => 'تقرير المشتريات'],
            ['command' => '/profit', 'description' => 'تقرير الأرباح'],
            ['command' => '/stock', 'description' => 'الأصناف المنخفضة'],
            ['command' => '/balance [اسم]', 'description' => 'رصيد عميل/مورد'],
            ['command' => '/transfer', 'description' => 'تحويل نقدي'],
            ['command' => '/go [صفحة]', 'description' => 'فتح صفحة'],
        ];
    }

    /**
     * الاستجابة الفارغة
     */
    protected function emptyResponse(): array
    {
        return [
            'type' => 'help',
            'title' => '💡 أوامر سريعة يمكنك استخدامها',
            'commands' => $this->getAvailableCommands(),
        ];
    }

    /**
     * الحصول على الاقتراحات التلقائية
     */
    public function getSuggestions(): array
    {
        return [
            ['text' => 'ما مبيعات اليوم؟', 'action' => 'ما مبيعات اليوم؟'],
            ['text' => 'إضافة عميل جديد', 'action' => '/new-customer'],
            ['text' => 'ما الأصناف المنخفضة؟', 'action' => '/stock'],
            ['text' => 'تحويل نقدي', 'action' => '/transfer'],
            ['text' => 'تقرير الأرباح', 'action' => '/profit'],
        ];
    }
}