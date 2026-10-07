<?php
namespace App\Http\Controllers;
use App\Models\Account;
use App\Services\FundService;
use Illuminate\Http\Request;

class FundController extends Controller
{
    protected FundService $fundService;

    public function __construct(FundService $fundService)
    {
        $this->fundService = $fundService;
    }

    public function index()
    {
        $balances = $this->fundService->getMainAccountsBalances();
        $accounts = Account::where('is_active', true)->orderBy('code')->get();
        $transfers = $this->fundService->getTransfers(20);
        return view('fund.index', compact('balances', 'accounts', 'transfers'));
    }

    public function transferForm()
    {
        $accounts = Account::where('is_active', true)
            ->whereIn('code', ['1001', '1002', '1003', '1004'])
            ->orderBy('code')->get();
        return view('fund.transfer', compact('accounts'));
    }

    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => ['required', 'exists:accounts,id'],
            'to_account_id' => ['required', 'exists:accounts,id', 'different:from_account_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $transfer = $this->fundService->transfer(
                $validated['from_account_id'], $validated['to_account_id'],
                (float)$validated['amount'], $validated['transfer_date'], $validated['notes'] ?? null
            );
            return redirect()->route('fund.index')->with('success', "تم التحويل بنجاح! رقم العملية: {$transfer->transfer_no}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function accountDetails(int $id)
    {
        $account = Account::findOrFail($id);
        $transactions = $this->fundService->getAccountTransactions($id, 50);
        $balance = $this->fundService->getAccountBalance($id);
        return view('fund.account-details', compact('account', 'transactions', 'balance'));
    }
}