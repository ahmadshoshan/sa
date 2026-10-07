<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Account;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Exception;
use Throwable;

/**
 * ReturnController الموحد
 * يعرض جميع المرتجعات (بيع + شراء) في صفحة واحدة
 */
class ReturnController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * عرض جميع المرتجعات (بيع + شراء)
     */
    public function index(Request $request)
    {
        $type = $request->input('type'); // sale_return أو purchase_return أو null للكل
        
        $query = Invoice::with(['customer', 'supplier', 'warehouse', 'items.product', 'user'])
            ->whereIn('type', ['sale_return', 'purchase_return'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');
        
        if ($type && in_array($type, ['sale_return', 'purchase_return'])) {
            $query->where('type', $type);
        }
        
        $from = $request->input('from');
        $to = $request->input('to');
        
        if ($from) {
            $query->whereDate('invoice_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('invoice_date', '<=', $to);
        }
        
        $search = $request->input('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }
        
        $returns = $query->paginate(20)->withQueryString();
        
        // إحصائيات
        $stats = [
            'total_count' => Invoice::whereIn('type', ['sale_return', 'purchase_return'])->count(),
            'sale_returns_count' => Invoice::where('type', 'sale_return')->count(),
            'purchase_returns_count' => Invoice::where('type', 'purchase_return')->count(),
            'sale_returns_total' => (float) Invoice::where('type', 'sale_return')->sum('total'),
            'purchase_returns_total' => (float) Invoice::where('type', 'purchase_return')->sum('total'),
        ];
        
        return view('returns.unified_index', compact('returns', 'stats', 'type', 'from', 'to', 'search'));
    }

    /**
     * عرض تفاصيل المرتجع
     */
    public function show(Invoice $return)
    {
        if (!in_array($return->type, ['sale_return', 'purchase_return'])) {
            abort(404, 'هذه ليست فاتورة مرتجع');
        }
        
        $return->load(['customer', 'supplier', 'warehouse', 'items.product', 'payments', 'user']);
        
        return view('returns.unified_show', compact('return'));
    }

    /**
     * عرض نموذج إنشاء مرتجع بيع
     */
    public function createSaleReturn(Request $request)
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();
        
        // إذا تم تمرير فاتورة أصلية
        $originalInvoice = null;
        if ($request->has('from_invoice')) {
            $originalInvoice = Invoice::with('items.product')
                ->findOrFail($request->input('from_invoice'));
        }
        
        return view('returns.create_sale', compact('customers', 'products', 'warehouses', 'originalInvoice'));
    }

    /**
     * عرض نموذج إنشاء مرتجع شراء
     */
    public function createPurchaseReturn(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();
        
        $originalInvoice = null;
        if ($request->has('from_invoice')) {
            $originalInvoice = Invoice::with('items.product')
                ->findOrFail($request->input('from_invoice'));
        }
        
        return view('returns.create_purchase', compact('suppliers', 'products', 'warehouses', 'originalInvoice'));
    }

    /**
     * حفظ مرتجع بيع
     */
    public function storeSaleReturn(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'original_invoice_id' => 'nullable|exists:invoices,id',
        ]);
        
        try {
            $return = $this->invoiceService->createSaleReturn($validated);
            
            return redirect()->route('returns.show', $return)
                ->with('success', '✅ تم إنشاء مرتجع البيع بنجاح');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * حفظ مرتجع شراء
     */
    public function storePurchaseReturn(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'original_invoice_id' => 'nullable|exists:invoices,id',
        ]);
        
        try {
            $return = $this->invoiceService->createPurchaseReturn($validated);
            
            return redirect()->route('returns.show', $return)
                ->with('success', '✅ تم إنشاء مرتجع الشراء بنجاح');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }
}