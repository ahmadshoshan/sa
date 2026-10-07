<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AssistantMemory;
use App\Models\Category;
use App\Models\Customer;
use App\Models\FundTransfer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AssistantService
{
    protected array $synonyms = [
        'modify' => ['عدل', 'غير', 'بدل', 'حدث', 'ظبط', 'عدللي', 'غيرلي'],
        'increase' => ['زود', 'زيد', 'ارفع', 'غلي', 'علي السعر', 'زود السعر', 'ارفع السعر', 'زوده'],
        'decrease' => ['نقص', 'قلل', 'وطي', 'نزل', 'رخص', 'قلللي', 'نزله', 'رخصه'],
        'multiply' => ['ضاعف', 'اضرب', 'ضرب', 'ضاعفه'],
        'set' => ['خلي', 'حط', 'اجعل', 'حدد', 'ثبت', 'خلي السعر', 'حط السعر'],
        'price' => ['سعر', 'تمن', 'ثمن', 'بكام', 'قيمته', 'السعر'],
        'sale_price' => ['سعر البيع', 'سعر بيع', 'بيعه', 'البيع'],
        'cost_price' => ['سعر التكلفة', 'تكلفته', 'التكلفة', 'تكلفه', 'سعر الشراء', 'شراه'],
        'wholesale_price' => ['سعر الجملة', 'جملته', 'الجملة', 'جمله'],
        'product' => ['صنف', 'منتج', 'حاجة', 'غرض', 'بضاعة'],
        'customer' => ['عميل', 'زبون', 'مشتري'],
        'supplier' => ['مورد', 'تاجر'],
        'name' => ['اسم', 'التسمية', 'اسمه'],
        'stock' => ['مخزون', 'كمية', 'الكمية', 'الرصيد', 'كميته', 'الموجود', 'المتوفر'],
        'delete' => ['احذف', 'امسح', 'شيل', 'حذف', 'مسح'],
        'add' => ['اضف', 'أضف', 'سجل', 'اعمل', 'انشئ', 'أنشئ', 'زود'],
        'show' => ['اعرض', 'وريني', 'شوف', 'اظهر', 'اعرضلي'],
        'sales' => ['مبيعات', 'بيع', 'بيوعات', 'مبيوعات'],
        'purchases' => ['مشتريات', 'شراء', 'شراءات'],
        'profit' => ['ربح', 'ارباح', 'الأرباح', 'الارباح', 'مكسب', 'صافي الربح'],
        'balance' => ['رصيد', 'الباقي', 'مديونية'],
        'low' => ['منخفض', 'ناقص', 'قليل', 'خلص', 'مفيش', 'تحت الحد', 'منخفضة', 'منخفضه'],
        'best' => ['أفضل', 'افضل', 'الأكثر', 'الاكثر', 'الأحسن', 'الاحسن'],
        'worst' => ['أسوأ', 'اسوا', 'الأقل', 'الاقل', 'الوحش'],
        'today' => ['النهاردة', 'النهارده', 'اليوم'],
        'month' => ['الشهر', 'شهر', 'هذا الشهر'],
        'year' => ['السنة', 'سنه', 'السنه', 'السنة'],
        'yesterday' => ['امبارح', 'البارحة', 'البارحه'],
        'open' => ['افتح', 'روح', 'وديني', 'اذهب', 'افتحلي', 'روحلي'],
        'suggest' => ['اقترح', 'اقتراح', 'نصيحة', 'نصيحه', 'ساعدني'],
        'help' => ['مساعدة', 'مساعده', 'ساعدني', 'المساعدة'],
        'thanks' => ['شكرا', 'شكراً', 'تسلم', 'جزاك الله', 'برافو', 'ممتاز'],
        'greeting' => ['مرحبا', 'مرحباً', 'اهلا', 'أهلا', 'السلام عليكم', 'هاي', 'ازيك', 'كيف حالك', 'صباح الخير', 'مساء الخير'],
        'identity' => ['اسمك', 'مين انت', 'ايه اسمك', 'اسمك ايه', 'تعريف'],
        'transfer' => ['حول', 'تحويل', 'انقل', 'انقل فلوس', 'حول فلوس', 'نقل', 'حوّل', 'حوّل فلوس'],
        'cash' => ['كاش', 'نقدي', 'صندوق', 'الكاش', 'النقدي'],
        'bank' => ['بنك', 'البنك'],
        'insta' => ['انستا', 'إنستا', 'الإنستا', 'visa', 'فيزا', 'كارت'],
        'total_cash' => ['إجمالي النقدية', 'اجمالي النقدية', 'مجموع النقدية', 'كم فلوس', 'كام فلوس', 'كم عندنا', 'إجمالي الفلوس'],
        'yes' => ['نعم', 'ايوه', 'أيوة', 'ايوة', 'اكيد', 'أكيد', 'تمام', 'حاضر', 'صح'],
        'no' => ['لا', 'لأ', 'لاء', 'الغي', 'إلغاء', 'بلاش', 'مفيش'],
    ];

    protected array $pages = [
        'pos' => ['keywords' => ['نقطة البيع', 'نقطه البيع', 'الكاشير', 'البيع السريع', 'pos'], 'route' => 'pos.index', 'label' => '🖥️ نقطة البيع'],
        'sales' => ['keywords' => ['فواتير البيع', 'البيعات', 'المبيعات', 'فواتير'], 'route' => 'sales.index', 'label' => '🧾 فواتير البيع'],
        'purchases' => ['keywords' => ['فواتير الشراء', 'المشتريات', 'الشراء'], 'route' => 'purchases.index', 'label' => '🛒 فواتير الشراء'],
        'customers' => ['keywords' => ['العملاء', 'الزباين', 'عملاء'], 'route' => 'customers.index', 'label' => '👥 العملاء'],
        'suppliers' => ['keywords' => ['الموردين', 'موردين', 'التجار'], 'route' => 'suppliers.index', 'label' => '🏭 الموردون'],
        'products' => ['keywords' => ['الأصناف', 'المنتجات', 'البضاعة', 'اصناف'], 'route' => 'products.index', 'label' => '📦 الأصناف'],
        'reports' => ['keywords' => ['التقارير', 'تقارير', 'الاحصائيات'], 'route' => 'reports.index', 'label' => '📊 التقارير'],
        'dashboard' => ['keywords' => ['لوحة التحكم', 'الرئيسية', 'الداشبورد'], 'route' => 'dashboard.index', 'label' => '🏠 لوحة التحكم'],
        'settings' => ['keywords' => ['الإعدادات', 'الاعدادات', 'الضبط'], 'route' => 'settings.index', 'label' => '⚙️ الإعدادات'],
        'payments' => ['keywords' => ['السندات', 'المدفوعات', 'سندات', 'القبض والصرف'], 'route' => 'payments.index', 'label' => '💰 السندات'],
        'expenses' => ['keywords' => ['المصروفات', 'مصروفات', 'المصاريف'], 'route' => 'expenses.index', 'label' => '💸 المصروفات'],
        'partners' => ['keywords' => ['الشركاء', 'شركاء', 'الشريك'], 'route' => 'partners.index', 'label' => '👥 الشركاء'],
        'fund' => ['keywords' => ['إدارة الأموال', 'الأموال', 'اداره الاموال', 'النقدية', 'فلوس'], 'route' => 'fund.index', 'label' => '💰 إدارة الأموال'],
        'accounting' => ['keywords' => ['الحسابات', 'القيود', 'المحاسبة', 'ميزان المراجعة'], 'route' => 'journal.index', 'label' => '📒 الحسابات'],
        'backups' => ['keywords' => ['النسخ الاحتياطي', 'نسخ احتياطي', 'الباك اب'], 'route' => 'backups.index', 'label' => '💾 النسخ الاحتياطي'],
    ];

    // ============================================
    // الأسئلة الديناميكية التي لا تُحفظ في الذاكرة
    // ============================================
    protected array $dynamicKeywords = [
        'مبيعات', 'مشتريات', 'ربح', 'ارباح', 'رصيد', 'نقدية', 'كاش', 'بنك',
        'منخفض', 'ناقص', 'مخزون', 'اقترح', 'صافي', 'إيرادات', 'مصروفات',
        'اليوم', 'الشهر', 'السنة', 'امبارح', 'النهارده', 'فلوس', 'إجمالي', 'اجمالي'
    ];

    public function __construct() {}

    public function answer(string $question, array $context = []): array
    {
        try {
            $q = trim($question);

            if (!empty($context['awaiting'])) {
                return $this->handleAwaiting($q, $context);
            }

            // التحقق من أن السؤال ليس ديناميكياً قبل استخدام الذاكرة
            if (!$this->isDynamicQuestion($q)) {
                $mem = $this->recallMemory($q);
                if ($mem && $mem->times_asked >= 2) {
                    return [
                        'answer' => $mem->answer,
                        'context' => null,
                        'source' => 'memory',
                        'suggestions' => $this->getSuggestions()
                    ];
                }
            }

            $ai = $this->tryAI($q);
            if ($ai !== null) {
                if (!$this->isDynamicQuestion($q)) {
                    $this->remember($q, $ai, 'ai');
                }
                return [
                    'answer' => $ai,
                    'context' => null,
                    'source' => 'ai',
                    'suggestions' => $this->getSuggestions()
                ];
            }

            $result = $this->processQuestion($q);

            if (!empty($result['answer']) && empty($result['context']) && !$this->isDynamicQuestion($q)) {
                $this->remember($q, $result['answer'], $result['intent'] ?? 'general');
            }

            if (!isset($result['suggestions'])) {
                $result['suggestions'] = $this->getSuggestions();
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Assistant Error: ' . $e->getMessage());
            return [
                'answer' => "⚠️ حدث خطأ تقني. حاول مرة أخرى.\n\nجرّب: 'مساعدة' لعرض الأوامر المتاحة.",
                'context' => null,
                'intent' => 'error',
                'suggestions' => $this->getSuggestions()
            ];
        }
    }

    /**
     * التحقق من أن السؤال ديناميكي (يجب حسابه من قاعدة البيانات)
     */
    protected function isDynamicQuestion(string $q): bool
    {
        $normalized = $this->normalize($q);
        foreach ($this->dynamicKeywords as $kw) {
            if (mb_strpos($normalized, $this->normalize($kw)) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function handleAwaiting(string $q, array $context): array
    {
        $awaiting = $context['awaiting'];
        $data = $context['data'] ?? [];

        if ($this->matchesAny($q, 'no')) {
            return ['answer' => '✅ تم الإلغاء.', 'context' => null];
        }

        if ($awaiting === 'customer_name') {
            $name = trim($q);
            if (empty($name)) {
                return ['answer' => 'اكتب اسم العميل من فضلك.', 'context' => $context];
            }
            try {
                $customer = $this->createCustomer($name);
                return [
                    'answer' => "✅ تم إضافة العميل!\n\n👤 الاسم: {$customer->name}\n🔖 الكود: {$customer->code}\n💳 الرصيد: 0.00",
                    'context' => null
                ];
            } catch (\Exception $e) {
                return ['answer' => "❌ خطأ: " . $e->getMessage(), 'context' => null];
            }
        }

        if ($awaiting === 'supplier_name') {
            $name = trim($q);
            if (empty($name)) {
                return ['answer' => 'اكتب اسم المورد من فضلك.', 'context' => $context];
            }
            try {
                $supplier = $this->createSupplier($name);
                return [
                    'answer' => "✅ تم إضافة المورد!\n\n🏭 الاسم: {$supplier->name}\n🔖 الكود: {$supplier->code}",
                    'context' => null
                ];
            } catch (\Exception $e) {
                return ['answer' => "❌ خطأ: " . $e->getMessage(), 'context' => null];
            }
        }

        if ($awaiting === 'product_name') {
            $name = trim($q);
            if (empty($name)) {
                return ['answer' => 'اكتب اسم الصنف من فضلك.', 'context' => $context];
            }
            $data['name'] = $name;
            return [
                'answer' => "ممتاز! ما سعر بيع '{$name}'؟\nاكتب الرقم فقط مثل: 100",
                'context' => ['awaiting' => 'product_price', 'data' => $data]
            ];
        }

        if ($awaiting === 'product_price') {
            $price = $this->extractNumber($q);
            if ($price === null || $price < 0) {
                return ['answer' => 'اكتب سعر صحيح (رقم فقط).', 'context' => $context];
            }
            $data['price'] = $price;
            try {
                $product = $this->createProduct($data['name'], $price);
                return [
                    'answer' => "✅ تم إضافة الصنف!\n\n📦 الاسم: {$product->name}\n🔖 الكود: {$product->code}\n💰 السعر: " . number_format($price, 2),
                    'context' => null
                ];
            } catch (\Exception $e) {
                return ['answer' => "❌ خطأ: " . $e->getMessage(), 'context' => null];
            }
        }

        if ($awaiting === 'edit_value') {
            $product = Product::find($data['product_id'] ?? 0);
            if (!$product) {
                return ['answer' => 'الصنف غير موجود.', 'context' => null];
            }
            $field = $data['field'];
            $operation = $data['operation'] ?? 'set';
            $currentValue = (float) $product->$field;

            if ($field === 'name') {
                $newValue = trim($q);
                if (empty($newValue)) {
                    return ['answer' => 'اكتب الاسم الجديد.', 'context' => $context];
                }
            } else {
                $newValue = $this->calculateNewValue($q, $currentValue, $operation);
                if ($newValue === null) {
                    return ['answer' => 'اكتب رقم صحيح مثل: 100 أو "زود 50"', 'context' => $context];
                }
            }

            try {
                $oldValue = $product->$field;
                $product->$field = $newValue;
                $product->save();
                $fieldLabel = $this->getFieldLabel($field);

                if ($field === 'name') {
                    return ['answer' => "✅ تم تغيير الاسم!\n\nمن: {$oldValue}\nإلى: {$newValue}", 'context' => null];
                } else {
                    return [
                        'answer' => "✅ تم تعديل {$fieldLabel}!\n\n📦 الصنف: {$product->name}\nمن: " . number_format($currentValue, 2) . "\nإلى: " . number_format($newValue, 2),
                        'context' => null
                    ];
                }
            } catch (\Exception $e) {
                return ['answer' => "❌ خطأ: " . $e->getMessage(), 'context' => null];
            }
        }

        if ($awaiting === 'edit_field_select') {
            $product = Product::find($data['product_id'] ?? 0);
            if (!$product) {
                return ['answer' => 'الصنف غير موجود.', 'context' => null];
            }
            $field = $this->detectField($this->normalize($q));
            if (!$field) {
                return [
                    'answer' => "اختر الحقل:\n• سعر البيع\n• سعر التكلفة\n• سعر الجملة\n• الاسم",
                    'context' => $context
                ];
            }
            if ($field === 'name') {
                return [
                    'answer' => "اكتب الاسم الجديد للصنف '{$product->name}':",
                    'context' => ['awaiting' => 'edit_value', 'data' => ['product_id' => $product->id, 'field' => 'name']]
                ];
            }
            return [
                'answer' => "القيمة الحالية: " . number_format((float) $product->$field, 2) . "\n\nاكتب القيمة الجديدة، أو:\n• 'زود 100' للزيادة\n• 'نقص 50' للنقصان",
                'context' => ['awaiting' => 'edit_value', 'data' => ['product_id' => $product->id, 'field' => $field, 'operation' => 'set']]
            ];
        }

        if ($awaiting === 'confirm_delete') {
            if ($this->matchesAny($q, 'yes')) {
                return $this->executeDelete($data);
            }
            return ['answer' => '✅ تم إلغاء الحذف.', 'context' => null];
        }

        return ['answer' => 'عذراً، لم أفهم المطلوب.', 'context' => null];
    }

    protected function processQuestion(string $q): array
    {
        $normalized = $this->normalize($q);

        // تحية
        if ($this->matchesAny($normalized, 'greeting')) {
            $hour = now()->hour;
            $time = $hour < 12 ? 'صباح الخير ☀️' : ($hour < 17 ? 'مساء الخير 🌙' : 'مساء النور 🌙');
            $userName = auth()->user()?->name ?? '';
            return [
                'answer' => "{$time} {$userName}! 👋\n\nأنا المساعد الذكي المحاسبي 🤖\nكيف أقدر أساعدك؟",
                'intent' => 'greeting'
            ];
        }

        if ($this->matchesAny($normalized, 'identity')) {
            return [
                'answer' => "🤖 أنا المساعد الذكي لنظام المبيعات والمحاسبة\n\nأستطيع مساعدتك في:\n• 📊 الاستعلامات المالية\n• 💰 التحويلات النقدية\n• ✏️ تعديل الأصناف\n• ➕ إضافة العملاء والموردين\n• 🔗 التنقل بين الصفحات\n\nجرّب: 'مساعدة' لعرض كل الأوامر",
                'intent' => 'identity'
            ];
        }

        if ($this->matchesAny($normalized, 'thanks')) {
            $responses = ['العفو! 😊', 'تسلم! 🌹', 'ربنا يبارك فيك! 🤲', 'في خدمتك دائماً! 💙'];
            return ['answer' => $responses[array_rand($responses)], 'intent' => 'thanks'];
        }

        if ($this->matchesAny($normalized, 'help')) {
            return ['answer' => $this->getHelpText(), 'intent' => 'help'];
        }

        if ($this->matchesAny($normalized, 'suggest')) {
            return ['answer' => $this->buildSmartAdvice(), 'intent' => 'suggest'];
        }

        // ============================================
        // الأولوية الأولى: الاستعلامات الحساسة للترتيب
        // ============================================
        
        // إجمالي النقدية (قبل أي فحص آخر)
        if ($this->matchesAny($normalized, 'total_cash')) {
            return $this->handleTotalCashQuery();
        }

        // الأصناف المنخفضة (قبل البحث عن منتج)
        if ($this->matchesAny($normalized, 'low') && !$this->findProduct($q)) {
            return $this->handleLowStockQuery();
        }

        // التحويلات النقدية
        if ($this->matchesAny($normalized, 'transfer')) {
            return $this->handleTransfer($q, $normalized);
        }

        // التنقل
        if ($this->matchesAny($normalized, 'open')) {
            return $this->handleNavigation($q, $normalized);
        }

        // ============================================
        // استعلامات الرصيد النقدية
        // ============================================
        if ($this->matchesAny($normalized, 'cash') && !$this->matchesAny($normalized, 'total_cash')) {
            if ($this->matchesAny($normalized, ['الصندوق', 'النقدي', 'صندوق'])) {
                return $this->handleCashBalanceQuery();
            }
            if ($this->matchesAny($normalized, 'bank') || $this->matchesAny($normalized, ['البنك'])) {
                return $this->handleBankBalanceQuery();
            }
            if ($this->matchesAny($normalized, 'insta') || $this->matchesAny($normalized, ['الإنستا', 'انستا', 'فيزا'])) {
                return $this->handleInstaBalanceQuery();
            }
        }

        if ($this->matchesAny($normalized, 'bank') && !$this->matchesAny($normalized, 'total_cash')) {
            return $this->handleBankBalanceQuery();
        }

        if ($this->matchesAny($normalized, 'insta') && !$this->matchesAny($normalized, 'total_cash')) {
            return $this->handleInstaBalanceQuery();
        }

        // تعديل
        if ($this->matchesAny($normalized, 'modify') || $this->matchesAny($normalized, 'increase') || $this->matchesAny($normalized, 'decrease')) {
            return $this->handleModify($q, $normalized);
        }

        // حذف
        if ($this->matchesAny($normalized, 'delete')) {
            return $this->handleDelete($q, $normalized);
        }

        // إضافة
        if ($this->matchesAny($normalized, 'add')) {
            return $this->handleAdd($q, $normalized);
        }

        // استعلامات مالية (مبيعات/مشتريات/أرباح)
        if ($this->matchesAny($normalized, 'sales') || $this->matchesAny($normalized, 'purchases') || $this->matchesAny($normalized, 'profit')) {
            return $this->handleFinancialQuery($q, $normalized);
        }

        // مخزون (بعد فحص low)
        if ($this->matchesAny($normalized, 'stock')) {
            $product = $this->findProduct($q);
            if ($product) {
                return $this->getProductStock($product);
            }
            return $this->handleLowStockQuery();
        }

        // محاسبة
        if ($this->matchesAny($normalized, ['حساب', 'قيد', 'قيود', 'ميزان', 'قائمة الدخل', 'المركز المالي'])) {
            return $this->handleAccountingQuery($q, $normalized);
        }

        // أفضل/أسوأ
        if ($this->matchesAny($normalized, 'best') || $this->matchesAny($normalized, 'worst')) {
            return $this->handleBestWorst($q, $normalized);
        }

        // رصيد عميل/مورد (بعد كل الفحوصات)
        if ($this->matchesAny($normalized, 'balance')) {
            return $this->handleBalanceQuery($q, $normalized);
        }

        // محاولة البحث عن كيان
        $product = $this->findProduct($q);
        if ($product) {
            return $this->getProductFullDetails($product);
        }

        $customer = $this->findCustomer($q);
        if ($customer) {
            return $this->getCustomerStatement($customer);
        }

        $supplier = $this->findSupplier($q);
        if ($supplier) {
            return $this->getSupplierStatement($supplier);
        }

        return [
            'answer' => "لم أفهم طلبك 🤔\n\nجرّب:\n• 'ما مبيعات اليوم؟'\n• 'ما إجمالي النقدية؟'\n• 'ما الأصناف المنخفضة؟'\n• 'حول 5000 من الصندوق للبنك'\n• 'مساعدة'",
            'intent' => 'unknown'
        ];
    }

    // ============================================
    // الأصناف المنخفضة (منفصلة)
    // ============================================
    protected function handleLowStockQuery(): array
    {
        $money = fn($v) => number_format((float) $v, 2);
        
        $low = Product::withSum('stocks', 'quantity')
            ->where('is_active', true)
            ->get()
            ->filter(fn($p) => (float) $p->min_stock > 0 && ((float) ($p->stocks_sum_quantity ?? 0)) <= (float) $p->min_stock)
            ->take(10);

        if ($low->isEmpty()) {
            return [
                'answer' => "✅ لا توجد أصناف منخفضة المخزون.\n\nجميع الأصناف فوق الحد الأدنى.",
                'intent' => 'query_low_stock'
            ];
        }

        $lines = $low->map(fn($p) => 
            "• {$p->name}: " . $money($p->stocks_sum_quantity ?? 0) . " / الحد: " . $money($p->min_stock)
        )->implode("\n");

        return [
            'answer' => "⚠️ الأصناف المنخفضة ({$low->count()}):\n\n{$lines}\n\n💡 يُنصح بإعادة الطلب قريباً.",
            'intent' => 'query_low_stock'
        ];
    }

    // ============================================
    // التحويلات النقدية
    // ============================================
    protected function handleTransfer(string $q, string $normalized): array
    {
        $amount = $this->extractNumber($q);

        if ($amount === null || $amount <= 0) {
            return [
                'answer' => "💰 كم المبلغ الذي تريد تحويله؟\n\nمثال: 'حول 5000 من الصندوق للبنك'",
                'intent' => 'transfer'
            ];
        }

        $fromAccount = $this->detectAccountFromText($q, 'from');
        $toAccount = $this->detectAccountFromText($q, 'to');

        if (!$fromAccount || !$toAccount) {
            return [
                'answer' => "💰 من أي حساب إلى أي حساب؟\n\nأمثلة:\n• 'حول 5000 من الصندوق للبنك'\n• 'حول 10000 من البنك للإنستا'\n\nالحسابات المتاحة:\n💵 الصندوق | 🏦 البنك | 💳 الكاش/الإنستا",
                'intent' => 'transfer'
            ];
        }

        if ($fromAccount->id === $toAccount->id) {
            return ['answer' => "⚠️ لا يمكن التحويل لنفس الحساب!", 'intent' => 'transfer'];
        }

        try {
            if (!class_exists(\App\Services\FundService::class)) {
                return [
                    'answer' => "⚠️ نظام التحويلات غير مفعّل.\n\nافتح صفحة إدارة الأموال للتنفيذ يدوياً.",
                    'intent' => 'transfer'
                ];
            }

            $fundService = app(\App\Services\FundService::class);
            $transfer = $fundService->transfer(
                $fromAccount->id,
                $toAccount->id,
                $amount,
                now()->format('Y-m-d'),
                "تحويل نقدي عبر المساعد الذكي"
            );

            return [
                'answer' => "✅ تم التحويل بنجاح!\n\n🔄 من: {$fromAccount->name}\n➡️ إلى: {$toAccount->name}\n💰 المبلغ: " . number_format($amount, 2) . "\n📝 رقم العملية: {$transfer->transfer_no}",
                'intent' => 'transfer'
            ];
        } catch (\Exception $e) {
            return ['answer' => "❌ فشل التحويل: " . $e->getMessage(), 'intent' => 'transfer'];
        }
    }

    protected function detectAccountFromText(string $text, string $type): ?Account
    {
        $normalized = $this->normalize($text);

        $accountMap = [
            'الصندوق' => '1001', 'صندوق' => '1001', 'النقدي' => '1001', 'نقدي' => '1001',
            'البنك' => '1002', 'بنك' => '1002',
            'الكاش' => '1003', 'كاش' => '1003', 'الإنستا' => '1003', 'انستا' => '1003', 'إنستا' => '1003', 'فيزا' => '1003', 'visa' => '1003',
        ];

        $patterns = [
            '/من\s+([^\sإلىلل]+)\s+(?:إلى|ل|لل|الى)\s+([^\s]+)/u',
            '/([^\s]+)\s+(?:إلى|ل|لل|الى)\s+([^\s]+)/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $normalized, $matches)) {
                $keyword = $type === 'from' ? $matches[1] : $matches[2];
                foreach ($accountMap as $name => $code) {
                    if (mb_strpos($this->normalize($name), $this->normalize($keyword)) !== false) {
                        return Account::where('code', $code)->first();
                    }
                }
            }
        }

        return null;
    }

    // ============================================
    // استعلامات الرصيد النقدية
    // ============================================
    protected function handleCashBalanceQuery(): array
    {
        $account = Account::where('code', '1001')->first();
        $balance = $account ? $this->getAccountBalance($account->id) : 0;

        return [
            'answer' => "💵 رصيد الصندوق النقدي: " . number_format($balance, 2) . " ج.م\n\n🔖 حساب: 1001",
            'intent' => 'query_balance'
        ];
    }

    protected function handleBankBalanceQuery(): array
    {
        $account = Account::where('code', '1002')->first();
        $balance = $account ? $this->getAccountBalance($account->id) : 0;

        return [
            'answer' => "🏦 رصيد البنك: " . number_format($balance, 2) . " ج.م\n\n🔖 حساب: 1002",
            'intent' => 'query_balance'
        ];
    }

    protected function handleInstaBalanceQuery(): array
    {
        $account = Account::where('code', '1003')->first();
        $balance = $account ? $this->getAccountBalance($account->id) : 0;

        return [
            'answer' => "💳 رصيد الكاش/الإنستا: " . number_format($balance, 2) . " ج.م\n\n🔖 حساب: 1003",
            'intent' => 'query_balance'
        ];
    }

    protected function handleTotalCashQuery(): array
    {
        $cashAcc = Account::where('code', '1001')->first();
        $bankAcc = Account::where('code', '1002')->first();
        $instaAcc = Account::where('code', '1003')->first();

        $cash = $cashAcc ? $this->getAccountBalance($cashAcc->id) : 0;
        $bank = $bankAcc ? $this->getAccountBalance($bankAcc->id) : 0;
        $insta = $instaAcc ? $this->getAccountBalance($instaAcc->id) : 0;
        $total = $cash + $bank + $insta;

        $money = fn($v) => number_format($v, 2);

        return [
            'answer' => "💰 إجمالي النقدية المتاحة:\n\n💵 الصندوق: {$money($cash)} ج.م\n🏦 البنك: {$money($bank)} ج.م\n💳 الكاش/الإنستا: {$money($insta)} ج.م\n━━━━━━━━━━━━━━━\n📊 الإجمالي: {$money($total)} ج.م",
            'intent' => 'query_total_cash'
        ];
    }

    protected function getAccountBalance(int $accountId): float
    {
        $account = Account::find($accountId);
        if (!$account) return 0;

        $debit = (float) JournalLine::where('account_id', $accountId)->sum('debit');
        $credit = (float) JournalLine::where('account_id', $accountId)->sum('credit');

        return in_array($account->type, ['asset', 'expense']) 
            ? ($debit - $credit) 
            : ($credit - $debit);
    }

    // ============================================
    // التنقل
    // ============================================
    protected function handleNavigation(string $q, string $normalized): array
    {
        foreach ($this->pages as $page) {
            foreach ($page['keywords'] as $keyword) {
                if (mb_strpos($normalized, $this->normalize($keyword)) !== false) {
                    try {
                        $url = route($page['route']);
                    } catch (\Exception $e) {
                        continue;
                    }
                    return [
                        'answer' => "🔗 تفضل:\n\n{$page['label']}\n{$url}\n\nأو انسخ الرابط وافتحه في المتصفح",
                        'intent' => 'navigation',
                        'link' => $url,
                        'link_label' => $page['label'],
                    ];
                }
            }
        }
        return ['answer' => 'لم أجد الصفحة المطلوبة. جرّب: "افتح التقارير"', 'intent' => 'navigation'];
    }

    // ============================================
    // تعديل الأصناف
    // ============================================
    protected function handleModify(string $q, string $normalized): array
    {
        $product = $this->findProduct($q);
        if (!$product) {
            return ['answer' => 'لم أجد الصنف. تأكد من الاسم.', 'intent' => 'modify'];
        }

        $field = $this->detectField($normalized);
        $operation = $this->detectOperation($normalized);
        $number = $this->extractNumber($q);

        if (!$field) {
            return [
                'answer' => "وجدت الصنف '{$product->name}'\n\nماذا تريد تعديله؟\n• سعر البيع\n• سعر التكلفة\n• سعر الجملة\n• الاسم",
                'intent' => 'modify',
                'context' => ['awaiting' => 'edit_field_select', 'data' => ['product_id' => $product->id]]
            ];
        }

        if ($field === 'name') {
            $newName = $this->extractAfterKeyword($q, ['إلى', 'الى', 'ل', 'باسم']);
            if ($newName) {
                try {
                    $oldName = $product->name;
                    $product->name = $newName;
                    $product->save();
                    return ['answer' => "✅ تم تغيير الاسم!\n\nمن: {$oldName}\nإلى: {$newName}", 'intent' => 'modify'];
                } catch (\Exception $e) {
                    return ['answer' => "❌ خطأ: " . $e->getMessage(), 'intent' => 'modify'];
                }
            }
            return [
                'answer' => "اكتب الاسم الجديد للصنف '{$product->name}':",
                'intent' => 'modify',
                'context' => ['awaiting' => 'edit_value', 'data' => ['product_id' => $product->id, 'field' => 'name']]
            ];
        }

        $currentValue = (float) $product->$field;
        if ($number === null) {
            return [
                'answer' => "القيمة الحالية: " . number_format($currentValue, 2) . "\n\nاكتب القيمة الجديدة أو:\n• 'زود 100' للزيادة\n• 'نقص 50' للنقصان",
                'intent' => 'modify',
                'context' => ['awaiting' => 'edit_value', 'data' => ['product_id' => $product->id, 'field' => $field, 'operation' => $operation]]
            ];
        }

        $newValue = $this->applyOperation($currentValue, $number, $operation);
        try {
            $product->$field = $newValue;
            $product->save();
            $fieldLabel = $this->getFieldLabel($field);
            $opText = match ($operation) {
                'increase' => 'زيادة', 'decrease' => 'نقص',
                'multiply' => 'مضاعفة', 'divide' => 'قسمة',
                default => 'تعديل'
            };
            return [
                'answer' => "✅ تم {$opText} {$fieldLabel}!\n\n📦 الصنف: {$product->name}\nمن: " . number_format($currentValue, 2) . "\nإلى: " . number_format($newValue, 2),
                'intent' => 'modify'
            ];
        } catch (\Exception $e) {
            return ['answer' => "❌ خطأ: " . $e->getMessage(), 'intent' => 'modify'];
        }
    }

    protected function detectField(string $q): ?string
    {
        if ($this->matchesAny($q, 'sale_price')) return 'sale_price';
        if ($this->matchesAny($q, 'cost_price')) return 'cost_price';
        if ($this->matchesAny($q, 'wholesale_price')) return 'wholesale_price';
        if ($this->matchesAny($q, 'name') && !$this->matchesAny($q, 'price')) return 'name';
        if ($this->matchesAny($q, 'price')) return 'sale_price';
        return null;
    }

    protected function detectOperation(string $q): string
    {
        if ($this->matchesAny($q, 'increase')) return 'increase';
        if ($this->matchesAny($q, 'decrease')) return 'decrease';
        if ($this->matchesAny($q, 'multiply')) return 'multiply';
        if ($this->matchesAny($q, 'divide')) return 'divide';
        return 'set';
    }

    protected function applyOperation(float $current, float $number, string $operation): float
    {
        return match ($operation) {
            'increase' => $current + $number,
            'decrease' => max(0, $current - $number),
            'multiply' => $current * $number,
            'divide' => $number != 0 ? $current / $number : $current,
            default => $number,
        };
    }

    protected function calculateNewValue(string $q, float $current, string $operation): ?float
    {
        $number = $this->extractNumber($q);
        if ($number === null) return null;
        return $this->applyOperation($current, $number, $operation);
    }

    protected function getFieldLabel(string $field): string
    {
        return match ($field) {
            'sale_price' => 'سعر البيع', 'cost_price' => 'سعر التكلفة',
            'wholesale_price' => 'سعر الجملة', 'name' => 'الاسم',
            'min_stock' => 'الحد الأدنى', default => $field,
        };
    }

    // ============================================
    // الحذف
    // ============================================
    protected function handleDelete(string $q, string $normalized): array
    {
        $product = $this->findProduct($q);
        if ($product) {
            $hasInvoices = InvoiceItem::where('product_id', $product->id)->exists();
            if ($hasInvoices) {
                return ['answer' => "⚠️ لا يمكن حذف '{$product->name}'\nلأنه مرتبط بفواتير سابقة", 'intent' => 'delete'];
            }
            return [
                'answer' => "⚠️ هل أنت متأكد من حذف '{$product->name}'؟\nاكتب 'نعم' للتأكيد أو 'لا' للإلغاء",
                'intent' => 'delete',
                'context' => ['awaiting' => 'confirm_delete', 'data' => ['type' => 'product', 'id' => $product->id, 'name' => $product->name]]
            ];
        }

        $customer = $this->findCustomer($q);
        if ($customer) {
            $hasInvoices = Invoice::where('customer_id', $customer->id)->exists();
            if ($hasInvoices) {
                return ['answer' => "⚠️ لا يمكن حذف '{$customer->name}'\nلأنه مرتبط بفواتير", 'intent' => 'delete'];
            }
            return [
                'answer' => "⚠️ هل أنت متأكد من حذف '{$customer->name}'؟\nاكتب 'نعم' للتأكيد",
                'intent' => 'delete',
                'context' => ['awaiting' => 'confirm_delete', 'data' => ['type' => 'customer', 'id' => $customer->id, 'name' => $customer->name]]
            ];
        }

        return ['answer' => 'لم أجد العنصر المراد حذفه.', 'intent' => 'delete'];
    }

    protected function executeDelete(array $data): array
    {
        try {
            $typeLabel = match ($data['type']) {
                'product' => 'الصنف', 'customer' => 'العميل',
                'supplier' => 'المورد', default => 'العنصر',
            };
            match ($data['type']) {
                'product' => Product::destroy($data['id']),
                'customer' => Customer::destroy($data['id']),
                'supplier' => Supplier::destroy($data['id']),
                default => null,
            };
            return ['answer' => "✅ تم حذف {$typeLabel} '{$data['name']}' بنجاح.", 'context' => null];
        } catch (\Exception $e) {
            return ['answer' => "❌ خطأ: " . $e->getMessage(), 'context' => null];
        }
    }

    // ============================================
    // الإضافة
    // ============================================
    protected function handleAdd(string $q, string $normalized): array
    {
        if ($this->matchesAny($normalized, 'customer')) {
            $name = $this->extractAfterKeyword($q, ['عميل', 'زبون']);
            if ($name) {
                try {
                    $customer = $this->createCustomer($name);
                    return [
                        'answer' => "✅ تم إضافة العميل!\n\n👤 الاسم: {$customer->name}\n🔖 الكود: {$customer->code}",
                        'intent' => 'add_customer'
                    ];
                } catch (\Exception $e) {
                    return ['answer' => "❌ خطأ: " . $e->getMessage(), 'intent' => 'add_customer'];
                }
            }
            return ['answer' => 'ما اسم العميل الجديد؟', 'intent' => 'add_customer', 'context' => ['awaiting' => 'customer_name']];
        }

        if ($this->matchesAny($normalized, 'supplier')) {
            $name = $this->extractAfterKeyword($q, ['مورد']);
            if ($name) {
                try {
                    $supplier = $this->createSupplier($name);
                    return [
                        'answer' => "✅ تم إضافة المورد!\n\n🏭 الاسم: {$supplier->name}\n🔖 الكود: {$supplier->code}",
                        'intent' => 'add_supplier'
                    ];
                } catch (\Exception $e) {
                    return ['answer' => "❌ خطأ: " . $e->getMessage(), 'intent' => 'add_supplier'];
                }
            }
            return ['answer' => 'ما اسم المورد الجديد؟', 'intent' => 'add_supplier', 'context' => ['awaiting' => 'supplier_name']];
        }

        if ($this->matchesAny($normalized, 'product')) {
            $name = $this->extractAfterKeyword($q, ['صنف', 'منتج']);
            $price = $this->extractNumber($q);

            if ($name && $price !== null) {
                try {
                    $product = $this->createProduct($name, $price);
                    return [
                        'answer' => "✅ تم إضافة الصنف!\n\n📦 الاسم: {$product->name}\n🔖 الكود: {$product->code}\n💰 السعر: " . number_format($price, 2),
                        'intent' => 'add_product'
                    ];
                } catch (\Exception $e) {
                    return ['answer' => "❌ خطأ: " . $e->getMessage(), 'intent' => 'add_product'];
                }
            }

            if ($name) {
                return [
                    'answer' => "ما سعر بيع '{$name}'؟",
                    'intent' => 'add_product',
                    'context' => ['awaiting' => 'product_price', 'data' => ['name' => $name]]
                ];
            }
            return ['answer' => 'ما اسم الصنف الجديد؟', 'intent' => 'add_product', 'context' => ['awaiting' => 'product_name']];
        }

        return [
            'answer' => 'ماذا تريد إضافته؟\n• عميل جديد\n• مورد جديد\n• صنف جديد',
            'intent' => 'add'
        ];
    }

    // ============================================
    // الاستعلامات المالية
    // ============================================
    protected function handleFinancialQuery(string $q, string $normalized): array
    {
        $money = fn($v) => number_format((float) $v, 2);
        $period = $this->detectPeriod($normalized);
        $periodLabel = $this->getPeriodLabel($period);

        if ($this->matchesAny($normalized, 'sales')) {
            $result = $this->getSalesForPeriod($period);
            $count = $this->getSalesCountForPeriod($period);

            $product = $this->findProduct($q);
            if ($product) {
                $query = InvoiceItem::where('product_id', $product->id)
                    ->whereHas('invoice', fn($inv) => $inv->where('type', 'sale'));

                if ($period === 'today') $query->whereHas('invoice', fn($i) => $i->whereDate('invoice_date', today()));
                elseif ($period === 'month') $query->whereHas('invoice', fn($i) => $i->whereDate('invoice_date', '>=', now()->startOfMonth()));
                elseif ($period === 'year') $query->whereHas('invoice', fn($i) => $i->whereDate('invoice_date', '>=', now()->startOfYear()));

                $productSales = (float) $query->sum('total');
                $productQty = (float) $query->sum('quantity');

                return [
                    'answer' => "📊 مبيعات '{$product->name}' {$periodLabel}:\n\n💰 الإجمالي: {$money($productSales)} ج.م\n📦 الكمية: {$money($productQty)} وحدة",
                    'intent' => 'query_sales'
                ];
            }

            if ($result == 0) {
                // إذا كان اليوم صفراً، جرب الشهر
                if ($period === 'today') {
                    $monthSales = $this->getSalesForPeriod('month');
                    $monthCount = $this->getSalesCountForPeriod('month');
                    $lastInvoice = Invoice::where('type', 'sale')->latest()->first();

                    $answer = "📊 لا توجد مبيعات اليوم\n\n";
                    $answer .= "💰 مبيعات الشهر: {$money($monthSales)} ج.م ({$monthCount} فاتورة)\n";

                    if ($lastInvoice) {
                        $answer .= "\n🧾 آخر فاتورة:\n{$lastInvoice->invoice_no}\nالمبلغ: {$money($lastInvoice->total)} ج.م";
                    }
                    return ['answer' => $answer, 'intent' => 'query_sales'];
                }
            }

            return [
                'answer' => "📊 المبيعات {$periodLabel}:\n\n💰 الإجمالي: {$money($result)} ج.م\n📄 عدد الفواتير: {$count}",
                'intent' => 'query_sales'
            ];
        }

        if ($this->matchesAny($normalized, 'purchases')) {
            $result = $this->getPurchasesForPeriod($period);
            $count = $this->getPurchasesCountForPeriod($period);
            return [
                'answer' => "🛒 المشتريات {$periodLabel}:\n\n💰 الإجمالي: {$money($result)} ج.م\n📄 عدد الفواتير: {$count}",
                'intent' => 'query_purchases'
            ];
        }

        if ($this->matchesAny($normalized, 'profit')) {
            $from = now()->startOfMonth()->toDateString();
            $to = today()->toDateString();

            $revenueIds = Account::where('type', 'revenue')->pluck('id');
            $expenseIds = Account::where('type', 'expense')->pluck('id');

            $revenue = (float) JournalLine::whereIn('account_id', $revenueIds)
                ->whereHas('entry', fn($e) => $e->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to))
                ->selectRaw('COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) as bal')
                ->value('bal') ?? 0;

            $expense = (float) JournalLine::whereIn('account_id', $expenseIds)
                ->whereHas('entry', fn($e) => $e->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to))
                ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as bal')
                ->value('bal') ?? 0;

            // إضافة المبيعات من الفواتير مباشرة كإيرادات (إذا لم توجد قيود)
            if ($revenue == 0) {
                $revenue = (float) Invoice::where('type', 'sale')
                    ->whereDate('invoice_date', '>=', $from)
                    ->whereDate('invoice_date', '<=', $to)
                    ->sum('total');
            }

            $net = $revenue - $expense;
            $trend = $net >= 0 ? '📈' : '📉';

            return [
                'answer' => "💰 صافي ربح هذا الشهر:\n\n📈 الإيرادات: {$money($revenue)} ج.م\n📉 المصروفات: {$money($expense)} ج.م\n━━━━━━━━━━━━━━━\n{$trend} الصافي: {$money($net)} ج.م",
                'intent' => 'query_profit'
            ];
        }

        return ['answer' => 'معلومة مالية عن ماذا؟', 'intent' => 'query_financial'];
    }

    protected function detectPeriod(string $q): string
    {
        if ($this->matchesAny($q, 'today')) return 'today';
        if ($this->matchesAny($q, 'yesterday')) return 'yesterday';
        if ($this->matchesAny($q, 'month')) return 'month';
        if ($this->matchesAny($q, 'year')) return 'year';
        return 'month';
    }

    protected function getPeriodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'اليوم', 'yesterday' => 'أمس',
            'month' => 'هذا الشهر', 'year' => 'هذه السنة',
            default => 'هذه الفترة',
        };
    }

    protected function getSalesForPeriod(string $period): float
    {
        $query = Invoice::where('type', 'sale');
        match ($period) {
            'today' => $query->whereDate('invoice_date', today()),
            'yesterday' => $query->whereDate('invoice_date', today()->subDay()),
            'month' => $query->whereDate('invoice_date', '>=', now()->startOfMonth()),
            'year' => $query->whereDate('invoice_date', '>=', now()->startOfYear()),
            default => null,
        };
        return (float) $query->sum('total');
    }

    protected function getSalesCountForPeriod(string $period): int
    {
        $query = Invoice::where('type', 'sale');
        match ($period) {
            'today' => $query->whereDate('invoice_date', today()),
            'yesterday' => $query->whereDate('invoice_date', today()->subDay()),
            'month' => $query->whereDate('invoice_date', '>=', now()->startOfMonth()),
            'year' => $query->whereDate('invoice_date', '>=', now()->startOfYear()),
            default => null,
        };
        return (int) $query->count();
    }

    protected function getPurchasesForPeriod(string $period): float
    {
        $query = Invoice::where('type', 'purchase');
        match ($period) {
            'today' => $query->whereDate('invoice_date', today()),
            'month' => $query->whereDate('invoice_date', '>=', now()->startOfMonth()),
            'year' => $query->whereDate('invoice_date', '>=', now()->startOfYear()),
            default => null,
        };
        return (float) $query->sum('total');
    }

    protected function getPurchasesCountForPeriod(string $period): int
    {
        $query = Invoice::where('type', 'purchase');
        match ($period) {
            'today' => $query->whereDate('invoice_date', today()),
            'month' => $query->whereDate('invoice_date', '>=', now()->startOfMonth()),
            'year' => $query->whereDate('invoice_date', '>=', now()->startOfYear()),
            default => null,
        };
        return (int) $query->count();
    }

    // ============================================
    // استعلامات الأرصدة
    // ============================================
    protected function handleBalanceQuery(string $q, string $normalized): array
    {
        $money = fn($v) => number_format((float) $v, 2);

        if ($this->matchesAny($normalized, 'customer')) {
            $customer = $this->findCustomer($q);
            if ($customer) {
                $status = (float) $customer->current_balance > 0 ? '🔴 عليه مستحقات' : '🟢 لا مستحقات';
                return [
                    'answer' => "💳 رصيد العميل '{$customer->name}': {$money($customer->current_balance)} ج.م\n{$status}",
                    'intent' => 'query_balance'
                ];
            }
            return ['answer' => 'لم أجد العميل.', 'intent' => 'query_balance'];
        }

        if ($this->matchesAny($normalized, 'supplier')) {
            $supplier = $this->findSupplier($q);
            if ($supplier) {
                return [
                    'answer' => "💳 رصيد المورد '{$supplier->name}': {$money($supplier->current_balance)} ج.م",
                    'intent' => 'query_balance'
                ];
            }
            return ['answer' => 'لم أجد المورد.', 'intent' => 'query_balance'];
        }

        // محاولة البحث عن عميل/مورد بالاسم مباشرة
        $customer = $this->findCustomer($q);
        if ($customer) {
            $status = (float) $customer->current_balance > 0 ? '🔴 عليه مستحقات' : '🟢 لا مستحقات';
            return [
                'answer' => "💳 رصيد العميل '{$customer->name}': {$money($customer->current_balance)} ج.م\n{$status}",
                'intent' => 'query_balance'
            ];
        }

        $supplier = $this->findSupplier($q);
        if ($supplier) {
            return [
                'answer' => "💳 رصيد المورد '{$supplier->name}': {$money($supplier->current_balance)} ج.م",
                'intent' => 'query_balance'
            ];
        }

        $receivables = (float) Customer::sum('current_balance');
        $payables = (float) Supplier::sum('current_balance');

        return [
            'answer' => "💰 إجمالي أرصدة العملاء: {$money($receivables)} ج.م\n💸 إجمالي أرصدة الموردين: {$money($payables)} ج.م",
            'intent' => 'query_balance'
        ];
    }

    // ============================================
    // استعلامات المخزون
    // ============================================
    protected function handleStockQuery(string $q, string $normalized): array
    {
        $product = $this->findProduct($q);
        if ($product) {
            return $this->getProductStock($product);
        }
        return $this->handleLowStockQuery();
    }

    // ============================================
    // الاستعلامات المحاسبية
    // ============================================
    protected function handleAccountingQuery(string $q, string $normalized): array
    {
        if ($this->matchesAny($normalized, ['قائمة الدخل', 'الدخل'])) return $this->getIncomeStatement();
        if ($this->matchesAny($normalized, ['ميزان المراجعة', 'الميزان'])) return $this->getTrialBalance();
        if ($this->matchesAny($normalized, ['قيود', 'قيد'])) return $this->getJournalEntries();
        return ['answer' => 'معلومة محاسبية عن ماذا؟\n• قائمة الدخل\n• ميزان المراجعة\n• القيود المحاسبية', 'intent' => 'query_accounting'];
    }

    protected function getIncomeStatement(): array
    {
        $money = fn($v) => number_format((float) $v, 2);
        $from = now()->startOfMonth()->toDateString();
        $to = today()->toDateString();

        $revenueIds = Account::where('type', 'revenue')->pluck('id');
        $expenseIds = Account::where('type', 'expense')->pluck('id');

        $revenue = (float) JournalLine::whereIn('account_id', $revenueIds)
            ->whereHas('entry', fn($e) => $e->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to))
            ->selectRaw('COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) as bal')->value('bal') ?? 0;

        $expense = (float) JournalLine::whereIn('account_id', $expenseIds)
            ->whereHas('entry', fn($e) => $e->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to))
            ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as bal')->value('bal') ?? 0;

        // Fallback: من الفواتير
        if ($revenue == 0) {
            $revenue = (float) Invoice::where('type', 'sale')
                ->whereDate('invoice_date', '>=', $from)->whereDate('invoice_date', '<=', $to)->sum('total');
        }

        $net = $revenue - $expense;

        return [
            'answer' => "🧮 قائمة الدخل (هذا الشهر)\n━━━━━━━━━━━━━━━\n📈 الإيرادات: {$money($revenue)}\n📉 المصروفات: {$money($expense)}\n━━━━━━━━━━━━━━━\n💰 صافي الربح: {$money($net)} " . ($net >= 0 ? '📈' : '📉'),
            'intent' => 'query_income'
        ];
    }

    protected function getTrialBalance(): array
    {
        $accounts = Account::get();
        $lines = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $debit = (float) JournalLine::where('account_id', $account->id)->sum('debit');
            $credit = (float) JournalLine::where('account_id', $account->id)->sum('credit');
            if ($debit == 0 && $credit == 0) continue;
            $totalDebit += $debit;
            $totalCredit += $credit;
            $lines[] = "• {$account->name}: م " . number_format($debit, 2) . " / د " . number_format($credit, 2);
        }

        $balanced = abs($totalDebit - $totalCredit) < 0.01;

        return [
            'answer' => "⚖️ ميزان المراجعة:\n" . implode("\n", array_slice($lines, 0, 10)) . "\n\nإجمالي المدين: " . number_format($totalDebit, 2) . "\nإجمالي الدائن: " . number_format($totalCredit, 2) . "\n" . ($balanced ? '✅ متوازن' : '⚠️ غير متوازن'),
            'intent' => 'query_trial_balance'
        ];
    }

    protected function getJournalEntries(): array
    {
        $entries = JournalEntry::with('lines')->latest()->limit(5)->get();
        if ($entries->isEmpty()) return ['answer' => 'لا توجد قيود بعد.', 'intent' => 'query_journal'];

        $lines = $entries->map(function ($entry) {
            $total = (float) $entry->lines->sum('debit');
            return "• {$entry->entry_no} - " . mb_substr($entry->description ?? '-', 0, 40) . " - " . number_format($total, 2);
        })->implode("\n");

        return ['answer' => "📒 آخر القيود:\n{$lines}", 'intent' => 'query_journal'];
    }

    // ============================================
    // أفضل/أسوأ
    // ============================================
    protected function handleBestWorst(string $q, string $normalized): array
    {
        $isBest = $this->matchesAny($normalized, 'best');

        $top = InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->where('invoices.type', 'sale')
            ->selectRaw('products.name as name, SUM(invoice_items.total) as total, SUM(invoice_items.quantity) as qty')
            ->groupBy('products.id', 'products.name')
            ->orderBy('total', $isBest ? 'desc' : 'asc')
            ->limit(5)
            ->get();

        if ($top->isEmpty()) return ['answer' => 'لا توجد مبيعات بعد.', 'intent' => 'query_top'];

        $lines = $top->map(function ($item, $i) {
            return ($i + 1) . ". {$item->name}: " . number_format((float) $item->qty, 2) . " وحدة = " . number_format((float) $item->total, 2);
        })->implode("\n");

        $title = $isBest ? '🏆 أفضل الأصناف مبيعاً' : '📉 أقل الأصناف مبيعاً';
        return ['answer' => "{$title}:\n{$lines}", 'intent' => 'query_top'];
    }

    // ============================================
    // تفاصيل الكيانات
    // ============================================
    protected function getProductFullDetails(Product $product): array
    {
        $money = fn($v) => number_format((float) $v, 2);
        $totalStock = (float) $product->stocks()->sum('quantity');

        $salesQty = (float) InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', fn($i) => $i->where('type', 'sale'))->sum('quantity');

        $salesRevenue = (float) InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', fn($i) => $i->where('type', 'sale'))->sum('total');

        return [
            'answer' => "📦 {$product->name}\n━━━━━━━━━━━━━━━\n🔖 الكود: {$product->code}\n" .
                ($product->barcode ? "📊 الباركود: {$product->barcode}\n" : '') .
                "\n💰 الأسعار:\n• التكلفة: {$money($product->cost_price)}\n• البيع: {$money($product->sale_price)}\n• الجملة: {$money($product->wholesale_price)}\n\n📦 المخزون: {$money($totalStock)}\n📈 المبيعات: {$money($salesQty)} وحدة = {$money($salesRevenue)}",
            'intent' => 'query_product_details'
        ];
    }

    protected function getProductStock(Product $product): array
    {
        $money = fn($v) => number_format((float) $v, 2);
        $total = (float) $product->stocks()->sum('quantity');
        $status = $total <= 0 ? '🔴 نفد' : ((float) $product->min_stock > 0 && $total <= (float) $product->min_stock ? '🟡 منخفض' : '🟢 جيد');

        return [
            'answer' => "📦 مخزون '{$product->name}'\n\nالإجمالي: {$money($total)}\nالحالة: {$status}\nالحد الأدنى: {$money($product->min_stock)}",
            'intent' => 'query_stock'
        ];
    }

    protected function getCustomerStatement(Customer $customer): array
    {
        $money = fn($v) => number_format((float) $v, 2);
        $invoices = Invoice::where('type', 'sale')->where('customer_id', $customer->id)->count();
        $totalSales = (float) Invoice::where('type', 'sale')->where('customer_id', $customer->id)->sum('total');

        return [
            'answer' => "👤 {$customer->name}\n━━━━━━━━━━━━━━━\n🔖 الكود: {$customer->code}\n" . ($customer->phone ? "📞 الهاتف: {$customer->phone}\n" : '') . "\n📊 عدد الفواتير: {$invoices}\n💰 إجمالي المبيعات: {$money($totalSales)}\n💳 الرصيد: {$money($customer->current_balance)}",
            'intent' => 'query_customer'
        ];
    }

    protected function getSupplierStatement(Supplier $supplier): array
    {
        $money = fn($v) => number_format((float) $v, 2);
        $invoices = Invoice::where('type', 'purchase')->where('supplier_id', $supplier->id)->count();
        $totalPurchases = (float) Invoice::where('type', 'purchase')->where('supplier_id', $supplier->id)->sum('total');

        return [
            'answer' => "🏭 {$supplier->name}\n━━━━━━━━━━━━━━━\n🔖 الكود: {$supplier->code}\n" . ($supplier->phone ? "📞 الهاتف: {$supplier->phone}\n" : '') . "\n📊 عدد الفواتير: {$invoices}\n🛒 إجمالي المشتريات: {$money($totalPurchases)}\n💳 الرصيد: {$money($supplier->current_balance)}",
            'intent' => 'query_supplier'
        ];
    }

    // ============================================
    // الأدوات المساعدة
    // ============================================
    protected function matchesAny(string $q, $keywords): bool
    {
        if (is_string($keywords)) {
            $keywords = $this->synonyms[$keywords] ?? [$keywords];
        }
        foreach ($keywords as $kw) {
            if (mb_strpos($q, $this->normalize($kw)) !== false) return true;
        }
        return false;
    }

    protected function normalize(string $str): string
    {
        return mb_strtolower(trim(
            preg_replace('/[ًٌٍَُِّْـ]/u', '',
                str_replace(['أ', 'إ', 'آ'], 'ا',
                    str_replace('ة', 'ه', $str)
                )
            )
        ));
    }

    protected function extractNumber(string $q): ?float
    {
        if (preg_match('/(\d+(?:\.\d+)?)/', str_replace(',', '', $q), $m)) {
            return (float) $m[1];
        }
        return null;
    }

    protected function extractAfterKeyword(string $q, array $keywords): ?string
    {
        $normalized = $this->normalize($q);
        foreach ($keywords as $kw) {
            $kw = $this->normalize($kw);
            if (mb_strpos($normalized, $kw) !== false) {
                $parts = explode($kw, $normalized, 2);
                if (isset($parts[1])) {
                    $rest = trim($parts[1]);
                    $rest = preg_replace('/^(اسمه|اسمها|باسم|اسم|هو|هي|جديد|إلى|الى|ل)\s*/u', '', $rest);
                    if (!empty($rest)) return $rest;
                }
            }
        }
        return null;
    }

    protected function findProduct(string $q): ?Product
    {
        $normalized = $this->normalize($q);
        
        // استبعاد الكلمات العامة من البحث
        $excludeWords = ['الصنف', 'صنف', 'المنتج', 'منتج', 'مبيعات', 'مشتريات', 'منخفض', 'منخفضة', 'مخزون', 'ما', 'كم', 'هل'];
        $searchTerm = $normalized;
        foreach ($excludeWords as $word) {
            $searchTerm = trim(str_replace($this->normalize($word), '', $searchTerm));
        }
        
        if (empty($searchTerm)) return null;

        $product = Product::where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('code', 'LIKE', "%{$searchTerm}%")
            ->orWhere('barcode', $searchTerm)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return Product::where('is_active', true)->get()->first(function ($p) use ($searchTerm) {
                return mb_strpos($this->normalize($p->name), $searchTerm) !== false;
            });
        }

        return $product;
    }

    protected function findCustomer(string $q): ?Customer
    {
        $normalized = $this->normalize($q);
        $excludeWords = ['العميل', 'عميل', 'زبون', 'رصيد', 'ما'];
        $searchTerm = $normalized;
        foreach ($excludeWords as $word) {
            $searchTerm = trim(str_replace($this->normalize($word), '', $searchTerm));
        }
        if (empty($searchTerm)) return null;
        
        return Customer::where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('code', 'LIKE', "%{$searchTerm}%")
            ->where('is_active', true)
            ->first();
    }

    protected function findSupplier(string $q): ?Supplier
    {
        $normalized = $this->normalize($q);
        $excludeWords = ['المورد', 'مورد', 'تاجر', 'رصيد', 'ما'];
        $searchTerm = $normalized;
        foreach ($excludeWords as $word) {
            $searchTerm = trim(str_replace($this->normalize($word), '', $searchTerm));
        }
        if (empty($searchTerm)) return null;
        
        return Supplier::where('name', 'LIKE', "%{$searchTerm}%")
            ->orWhere('code', 'LIKE', "%{$searchTerm}%")
            ->where('is_active', true)
            ->first();
    }

    protected function createCustomer(string $name, ?string $phone = null): Customer
    {
        $code = 'C' . str_pad(((int) Customer::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
        return Customer::create([
            'code' => $code, 'name' => $name, 'phone' => $phone,
            'opening_balance' => 0, 'current_balance' => 0,
            'price_level' => 'retail', 'is_active' => true,
        ]);
    }

    protected function createSupplier(string $name, ?string $phone = null): Supplier
    {
        $code = 'S' . str_pad(((int) Supplier::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        return Supplier::create([
            'code' => $code, 'name' => $name, 'phone' => $phone,
            'opening_balance' => 0, 'current_balance' => 0, 'is_active' => true,
        ]);
    }

    protected function createProduct(string $name, float $price): Product
    {
        $code = 'P' . str_pad(((int) Product::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
        $category = Category::first() ?? Category::create(['code' => 'GEN', 'name' => 'عام', 'is_active' => true]);
        $unit = Unit::first() ?? Unit::create(['code' => 'PCS', 'name' => 'قطعة', 'is_active' => true]);

        $product = Product::create([
            'code' => $code, 'name' => $name,
            'category_id' => $category->id, 'unit_id' => $unit->id,
            'cost_price' => $price * 0.8, 'sale_price' => $price, 'wholesale_price' => $price * 0.9,
            'factory_price' => $price * 0.75,
            'tax_rate' => 14, 'min_stock' => 10, 'is_active' => true,
        ]);

        $warehouse = Warehouse::first();
        if ($warehouse) {
            Stock::create(['product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'quantity' => 0]);
        }

        return $product;
    }

    // ============================================
    // الذاكرة
    // ============================================
    protected function recallMemory(string $q): ?AssistantMemory
    {
        $normalized = $this->normalize($q);
        $memories = AssistantMemory::orderByDesc('times_asked')->limit(100)->get();

        foreach ($memories as $memory) {
            if ($memory->question === $normalized) {
                $memory->increment('times_asked');
                $memory->update(['last_asked' => now()]);
                return $memory;
            }

            similar_text($memory->question, $normalized, $percent);
            if ($percent >= 90) {
                $memory->increment('times_asked');
                $memory->update(['last_asked' => now()]);
                return $memory;
            }
        }
        return null;
    }

    protected function remember(string $q, string $answer, string $intent): void
    {
        try {
            if ($this->isDynamicQuestion($q)) return;
            
            $normalized = $this->normalize($q);
            $existing = AssistantMemory::where('question', $normalized)->first();

            if ($existing) {
                $existing->increment('times_asked');
                $existing->update(['answer' => $answer, 'intent' => $intent, 'last_asked' => now()]);
            } else {
                AssistantMemory::create([
                    'question' => $normalized, 'answer' => $answer,
                    'intent' => $intent, 'times_asked' => 1, 'last_asked' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Memory save failed: ' . $e->getMessage());
        }
    }

    // ============================================
    // الاقتراحات الذكية
    // ============================================
    protected function getSuggestions(): array
    {
        $suggestions = [];
        try {
            $low = Product::withSum('stocks', 'quantity')->where('is_active', true)->get()
                ->filter(fn($p) => (float) $p->min_stock > 0 && ((float) ($p->stocks_sum_quantity ?? 0)) <= (float) $p->min_stock)->count();

            if ($low > 0) {
                $suggestions[] = ['text' => "⚠️ {$low} صنف منخفض", 'action' => 'ما الأصناف المنخفضة؟'];
            }

            $todaySales = Invoice::where('type', 'sale')->whereDate('invoice_date', today())->sum('total');
            $monthSales = Invoice::where('type', 'sale')->whereDate('invoice_date', '>=', now()->startOfMonth())->sum('total');

            if ($todaySales > 0) {
                $suggestions[] = ['text' => '📊 مبيعات اليوم: ' . number_format((float) $todaySales, 2), 'action' => 'ما مبيعات اليوم؟'];
            } else {
                $suggestions[] = ['text' => '📊 مبيعات الشهر: ' . number_format((float) $monthSales, 2), 'action' => 'ما مبيعات الشهر؟'];
            }

            $suggestions[] = ['text' => '💰 إجمالي النقدية', 'action' => 'ما إجمالي النقدية؟'];
            $suggestions[] = ['text' => '💡 اقترح عليّ', 'action' => 'اقترح عليّ'];
        } catch (\Exception $e) {}

        return array_slice($suggestions, 0, 5);
    }

    protected function getHelpText(): string
    {
        return "🤖 المساعد الذكي\n━━━━━━━━━━━━━━━\n\n📊 الاستعلامات:\n• 'ما مبيعات اليوم/الشهر؟'\n• 'ما رصيد [اسم العميل]؟'\n• 'ما مخزون [اسم الصنف]؟'\n• 'ما الأصناف المنخفضة؟'\n\n💰 التحويلات:\n• 'حول 5000 من الصندوق للبنك'\n• 'ما رصيد الكاش؟'\n• 'ما إجمالي النقدية؟'\n\n✏️ التعديل:\n• 'عدل سعر [الصنف] إلى [القيمة]'\n• 'زود/نقص سعر [الصنف] [المقدار]'\n\n➕ الإضافة:\n• 'أضف عميل/مورد/صنف جديد'\n\n🗑️ الحذف:\n• 'احذف [اسم العنصر]'\n\n🔗 التنقل:\n• 'افتح [اسم الصفحة]'\n\n💡 التحليلات:\n• 'اقترح عليّ'\n• 'ما المركز المالي؟'\n\nجرب أي أمر!";
    }

    protected function buildSmartAdvice(): string
    {
        $advice = [];
        try {
            $low = Product::withSum('stocks', 'quantity')->where('is_active', true)->get()
                ->filter(fn($p) => (float) $p->min_stock > 0 && ((float) ($p->stocks_sum_quantity ?? 0)) <= (float) $p->min_stock)->count();

            if ($low > 0) {
                $advice[] = "⚠️ لديك {$low} صنف منخفض المخزون. راجع تقرير الأصناف المنخفضة.";
            }

            $receivables = (float) Customer::sum('current_balance');
            if ($receivables > 0) {
                $advice[] = '💰 لديك ذمم بقيمة ' . number_format($receivables, 2) . '. تابع التحصيل.';
            }

            $todaySales = (float) Invoice::where('type', 'sale')->whereDate('invoice_date', today())->sum('total');
            if ($todaySales == 0) {
                $advice[] = '📊 لم تسجل مبيعات اليوم. تحقق من نقطة البيع.';
            }

            $payables = (float) Supplier::sum('current_balance');
            if ($payables > 0) {
                $advice[] = '💸 لديك مستحقات للموردين بقيمة ' . number_format($payables, 2) . '. جدولة السداد.';
            }

            if (empty($advice)) {
                $advice[] = '✅ كل شيء جيد! استمر في المتابعة.';
            }
        } catch (\Exception $e) {
            $advice[] = '✅ كل شيء يبدو جيداً!';
        }

        return "💡 اقتراحاتي:\n" . implode("\n\n", $advice);
    }

    protected function tryAI(string $question): ?string
    {
        try {
            $provider = Setting::where('key', 'ai_provider')->value('value');
            $apiKey = Setting::where('key', 'ai_api_key')->value('value');

            if (!$provider || $provider === 'none' || !$apiKey) return null;

            $company = Setting::where('key', 'company_name')->value('value') ?? 'النظام';
            $todaySales = Invoice::where('type', 'sale')->whereDate('invoice_date', today())->sum('total');

            $context = "أنت مساعد ذكي محاسبي في نظام «{$company}». أجب بالعربية بإيجاز. مبيعات اليوم = " . number_format((float) $todaySales, 2) . ".";

            if ($provider === 'gemini') {
                $response = Http::timeout(15)->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey,
                    ['contents' => [['parts' => [['text' => $context . "\n\nالسؤال: " . $question]]]]]
                );
                return $response->json('candidates.0.content.parts.0.text');
            }

            if ($provider === 'groq') {
                $response = Http::timeout(15)
                    ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => 'llama-3.3-70b-versatile',
                        'messages' => [
                            ['role' => 'system', 'content' => $context],
                            ['role' => 'user', 'content' => $question],
                        ],
                        'max_tokens' => 500,
                    ]);
                return $response->json('choices.0.message.content');
            }

            if ($provider === 'openai') {
                $response = Http::timeout(15)
                    ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => $context],
                            ['role' => 'user', 'content' => $question],
                        ],
                        'max_tokens' => 500,
                    ]);
                return $response->json('choices.0.message.content');
            }
        } catch (\Exception $e) {
            Log::warning('AI Error: ' . $e->getMessage());
            return null;
        }
        return null;
    }
}