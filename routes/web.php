<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleInvoiceController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PriceCompareController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\ProductCardController;
use App\Http\Controllers\PayablesController;
use App\Http\Controllers\CollectionsController;
use App\Http\Controllers\CommandCenterController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\InvoiceReturnController;
use App\Http\Controllers\CustomerPayablesController;
use App\Http\Controllers\SupplierCollectionsController;

// ============================================
// الصفحة الرئيسية
// ============================================
Route::redirect('/', '/dashboard');

// ============================================
// Auth Routes
// ============================================
if (file_exists(__DIR__ . '/auth.php')) {
    require __DIR__ . '/auth.php';
}

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ============================================
// المسارات الثابتة (قبل Route::resource)
// ============================================

// مقارنة الأسعار
Route::middleware('auth')->group(function () {
    Route::get('/products/price-compare', [PriceCompareController::class, 'index'])
        ->name('products.price-compare');
});

// ============================================
// Master Data Resources
// ============================================
Route::middleware(['auth'])->group(function () {

    // Categories, Units, Warehouses
    Route::middleware('permission:master-data.manage')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('units', UnitController::class);
        Route::resource('warehouses', WarehouseController::class);
    });

    // Customers
    Route::middleware('permission:master-data.manage')->group(function () {
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    Route::middleware('permission:master-data.manage|sales.view|sales.create')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    });

    // Suppliers
    Route::middleware('permission:master-data.manage')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    Route::middleware('permission:master-data.manage|purchases.view|purchases.create')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    });

    // Products
    Route::middleware('permission:master-data.manage')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    Route::middleware('permission:master-data.manage|sales.view|sales.create|purchases.view|purchases.create|returns.view|returns.create')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    });

    // Sales Invoices
    Route::middleware('permission:sales.create')->group(function () {
        Route::get('/sales/create', [SaleInvoiceController::class, 'create'])->name('sales.create');
        Route::post('/sales', [SaleInvoiceController::class, 'store'])->name('sales.store');
    });

    Route::middleware('permission:sales.view')->group(function () {
        Route::get('/sales', [SaleInvoiceController::class, 'index'])->name('sales.index');
        Route::get('/sales/{invoice}', [SaleInvoiceController::class, 'show'])->name('sales.show');
    });

    // Purchase Invoices
    Route::middleware('permission:purchases.create')->group(function () {
        Route::get('/purchases/create', [PurchaseInvoiceController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseInvoiceController::class, 'store'])->name('purchases.store');
    });

    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('/purchases', [PurchaseInvoiceController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{invoice}', [PurchaseInvoiceController::class, 'show'])->name('purchases.show');
    });

    // ============================================
    // Sale Returns (Legacy - من SaleReturnController)
    // ============================================
    Route::middleware('permission:returns.create')->group(function () {
        Route::get('/sales-returns/create', [SaleReturnController::class, 'create'])->name('sales-returns.create');
        Route::post('/sales-returns', [SaleReturnController::class, 'store'])->name('sales-returns.store');
    });

    Route::middleware('permission:returns.view')->group(function () {
        Route::get('/sales-returns', [SaleReturnController::class, 'index'])->name('sales-returns.index');
        Route::get('/sales-returns/{invoice}', [SaleReturnController::class, 'show'])->name('sales-returns.show');
    });

    // ============================================
    // Purchase Returns (Legacy - من PurchaseReturnController)
    // ============================================
    Route::middleware('permission:returns.create')->group(function () {
        Route::get('/purchase-returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
        Route::post('/purchase-returns', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
    });

    Route::middleware('permission:returns.view')->group(function () {
        Route::get('/purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::get('/purchase-returns/{invoice}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
    });

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
        Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
        Route::get('/reports/suppliers', [ReportController::class, 'suppliers'])->name('reports.suppliers');
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/reports/stock-movements', [ReportController::class, 'stockMovements'])->name('reports.stock-movements');
        Route::get('/reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
        Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    });

    // Users management
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['show']);
    });
});
    // ============================================
    // Expenses (المصروفات)
    // ============================================
Route::middleware('auth')->group(function () {        Route::get('/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [\App\Http\Controllers\ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'show'])->name('expenses.show');
        Route::get('/expenses/{expense}/edit', [\App\Http\Controllers\ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });
// ============================================
// Fund Management (الأموال والتحويلات)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/fund', [FundController::class, 'index'])->name('fund.index');
    Route::get('/fund/transfer', [FundController::class, 'transferForm'])->name('fund.transfer-form');
    Route::post('/fund/transfer', [FundController::class, 'storeTransfer'])->name('fund.store-transfer');
    Route::get('/fund/accounts/{id}', [FundController::class, 'accountDetails'])->name('fund.account-details');
});

// ============================================
// Statements (كشوف الحسابات)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/statements', [StatementController::class, 'index'])->name('statements.index');
    Route::get('/statements/customer/{customer}', [StatementController::class, 'customerStatement'])->name('statements.customer');
    Route::get('/statements/supplier/{supplier}', [StatementController::class, 'supplierStatement'])->name('statements.supplier');
});

// ============================================
// Product Card (كارتة الصنف)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/products/{product}/card', [ProductCardController::class, 'show'])->name('product.card');
});

// ============================================
// Payables (دفع المستحقات - مصروفات + فواتير + مرتبات)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/payables', [PayablesController::class, 'index'])->name('payables.index');
    Route::get('/payables/pay-expense/{expense}', [PayablesController::class, 'payExpenseForm'])->name('payables.pay-expense-form');
    Route::post('/payables/pay-expense/{expense}', [PayablesController::class, 'payExpense'])->name('payables.pay-expense');
    Route::get('/payables/pay-invoice/{invoice}', [PayablesController::class, 'payInvoiceForm'])->name('payables.pay-invoice-form');
    Route::post('/payables/pay-invoice/{invoice}', [PayablesController::class, 'payInvoice'])->name('payables.pay-invoice');
    Route::get('/payables/pay-salary', [PayablesController::class, 'paySalaryForm'])->name('payables.salary-form');
    Route::post('/payables/pay-salary', [PayablesController::class, 'paySalary'])->name('payables.pay-salary');
});

// ============================================
// Collections (تحصيل المستحقات من العملاء)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/collections', [CollectionsController::class, 'index'])->name('collections.index');
    Route::get('/collections/collect/{invoice}', [CollectionsController::class, 'collectForm'])->name('collections.collect-form');
    Route::post('/collections/collect/{invoice}', [CollectionsController::class, 'collect'])->name('collections.collect');
});

// ============================================
// Command Center (مركز الأوامر الشامل)
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/command-center', [CommandCenterController::class, 'index'])->name('command-center.index');
    Route::post('/command-center/process', [CommandCenterController::class, 'process']);
    Route::post('/command-center/search', [CommandCenterController::class, 'search']);
    Route::post('/command-center/execute', [CommandCenterController::class, 'execute']);
    Route::get('/command-center/suggestions', [CommandCenterController::class, 'suggestions']);
});

// ============================================
// PDF Reports
// ============================================
Route::middleware('auth')->prefix('pdf')->group(function () {
    Route::get('/invoice/{invoice}', [PdfController::class, 'invoice'])->name('pdf.invoice');
    Route::get('/customer-statement/{customer}', [PdfController::class, 'customerStatement'])->name('pdf.customer-statement');
    Route::get('/supplier-statement/{supplier}', [PdfController::class, 'supplierStatement'])->name('pdf.supplier-statement');
    Route::get('/sales-report', [PdfController::class, 'salesReport'])->name('pdf.sales-report');
    Route::get('/purchases-report', [PdfController::class, 'purchasesReport'])->name('pdf.purchases-report');
    Route::get('/payment/{payment}', [PdfController::class, 'payment'])->name('pdf.payment');
    Route::get('/income-statement', [PdfController::class, 'incomeStatement'])->name('pdf.income-statement');
    Route::get('/trial-balance', [PdfController::class, 'trialBalance'])->name('pdf.trial-balance');
});

// ============================================
// POS Quick Actions
// ============================================
Route::middleware('auth')->prefix('pos')->group(function () {
    Route::get('/quick-search', [PosController::class, 'quickSearch'])->name('pos.quick-search');
    Route::post('/scan-barcode', [PosController::class, 'scanBarcode'])->name('pos.scan-barcode');
});

// ============================================
// Returns System (نظام المرتجعات الموحد الجديد)
// ⚠️ المسارات الثابتة قبل resource
// ============================================
Route::middleware('auth')->group(function () {
    
    // 1. إرجاع من فاتورة (InvoiceReturnController) - الثابتة أولاً
    Route::get('/returns/select-invoice', [InvoiceReturnController::class, 'selectInvoice'])
        ->name('returns.select-invoice');
    Route::get('/returns/from-invoice/{invoice}', [InvoiceReturnController::class, 'showInvoiceForReturn'])
        ->name('returns.from-invoice');
    Route::post('/returns/from-invoice/{invoice}', [InvoiceReturnController::class, 'processReturn'])
        ->name('returns.process-from-invoice');
    
    // 2. إنشاء مرتجعات جديدة (ReturnController)
    Route::get('/returns/create-sale', [ReturnController::class, 'createSaleReturn'])
        ->name('returns.create-sale');
    Route::post('/returns/store-sale', [ReturnController::class, 'storeSaleReturn'])
        ->name('returns.store-sale');
    Route::get('/returns/create-purchase', [ReturnController::class, 'createPurchaseReturn'])
        ->name('returns.create-purchase');
    Route::post('/returns/store-purchase', [ReturnController::class, 'storePurchaseReturn'])
        ->name('returns.store-purchase');
    
    // 3. إرجاع من فاتورة موجودة (InvoiceReturnController)
    Route::get('/invoices/{invoice}/return', [InvoiceReturnController::class, 'createFromInvoice'])
        ->name('returns.create-from-invoice');
    Route::post('/invoices/{invoice}/return', [InvoiceReturnController::class, 'storeFromInvoice'])
        ->name('returns.store-from-invoice');
    
    // 4. Resource routes للـ returns (في النهاية لأن /{return} يلتقط أي شيء)
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/{return}', [ReturnController::class, 'show'])->name('returns.show');
});

// ============================================
// Customer Payables (مستحقات العملاء - نحن مدينون لهم)
// ============================================
Route::middleware('auth')->prefix('payables')->group(function () {
    Route::get('/customers', [CustomerPayablesController::class, 'index'])->name('payables.customers');
    Route::get('/pay-customer/{customer}', [CustomerPayablesController::class, 'payForm'])->name('payables.pay-customer');
    Route::post('/pay-customer/{customer}', [CustomerPayablesController::class, 'pay'])->name('payables.pay-customer.store');
});

// ============================================
// Supplier Collections (تحصيل من الموردين - نحن دائنون لهم)
// ============================================
Route::middleware('auth')->prefix('collections')->group(function () {
    Route::get('/suppliers', [SupplierCollectionsController::class, 'index'])->name('collections.suppliers');
    Route::get('/collect-from-supplier/{supplier}', [SupplierCollectionsController::class, 'collectForm'])->name('collections.collect-from-supplier');
    Route::post('/collect-from-supplier/{supplier}', [SupplierCollectionsController::class, 'collect'])->name('collections.collect-from-supplier.store');
});