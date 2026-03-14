<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Enums\TxnType;
use App\Enums\TxnStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * List all transactions for the authenticated user
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $limit = (int) $request->get('limit', 15);
        $type  = $request->get('type'); // e.g. deposit, withdraw, subtract …
        $from  = $request->get('from');  // YYYY-MM-DD
        $to    = $request->get('to');    // YYYY-MM-DD

        $query = Transaction::where('user_id', $user->id)
            ->when($type && $type !== 'all', function ($q) use ($type) {
                if ($type === TxnType::Deposit->value) {
                    $q->whereIn('type', [TxnType::Deposit->value, TxnType::ManualDeposit->value]);
                    return;
                }

                if ($type === TxnType::Withdraw->value) {
                    $q->whereIn('type', [TxnType::Withdraw->value, TxnType::WithdrawAuto->value]);
                    return;
                }

                $q->where('type', $type);
            })
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', Carbon::parse($from)->format('Y-m-d')))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', Carbon::parse($to)->format('Y-m-d')))
            ->when($request->get('search'), function ($q) use ($request) {
                $s = $request->get('search');
                $q->where(fn($q2) => $q2->where('tnx', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%"));
            })
            ->latest();

        $transactions = $query->paginate($limit);

        return response()->json([
            'status' => true,
            'data'   => $transactions->map(fn($t) => $this->formatTransaction($t)),
            'meta'   => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'per_page'     => $transactions->perPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }

    /**
     * Show a single transaction
     */
    public function show(Request $request, $tnx)
    {
        $transaction = Transaction::where('user_id', $request->user()->id)
            ->where('tnx', $tnx)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $this->formatTransaction($transaction),
        ]);
    }

    /**
     * Deposit history only
     */
    public function deposits(Request $request)
    {
        $user  = $request->user();
        $limit = (int) $request->get('limit', 15);

        $deposits = Transaction::where('user_id', $user->id)
            ->whereIn('type', [TxnType::Deposit, TxnType::ManualDeposit])
            ->latest()
            ->paginate($limit);

        return response()->json([
            'status' => true,
            'data'   => $deposits->map(fn($t) => $this->formatTransaction($t)),
            'meta'   => [
                'current_page' => $deposits->currentPage(),
                'last_page'    => $deposits->lastPage(),
                'per_page'     => $deposits->perPage(),
                'total'        => $deposits->total(),
            ],
        ]);
    }

    /**
     * Withdraw history only
     */
    public function withdrawals(Request $request)
    {
        $user  = $request->user();
        $limit = (int) $request->get('limit', 15);

        $withdrawals = Transaction::where('user_id', $user->id)
            ->whereIn('type', [TxnType::Withdraw, TxnType::WithdrawAuto])
            ->latest()
            ->paginate($limit);

        return response()->json([
            'status' => true,
            'data'   => $withdrawals->map(fn($t) => $this->formatTransaction($t)),
            'meta'   => [
                'current_page' => $withdrawals->currentPage(),
                'last_page'    => $withdrawals->lastPage(),
                'per_page'     => $withdrawals->perPage(),
                'total'        => $withdrawals->total(),
            ],
        ]);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    private function formatTransaction(Transaction $t): array
    {
        return [
            'id'           => $t->id,
            'tnx'          => $t->tnx,
            'type'         => $t->type instanceof \BackedEnum ? $t->type->value : $t->type,
            'description'  => $t->description,
            'method'       => $t->method,
            'amount'       => (float) $t->amount,
            'charge'       => (float) $t->charge,
            'final_amount' => (float) $t->final_amount,
            'pay_currency' => $t->pay_currency,
            'pay_amount'   => (float) $t->pay_amount,
            'status'       => $t->status instanceof \BackedEnum ? $t->status->value : $t->status,
            'created_at'   => $t->attributes['created_at'] ?? $t->created_at,
        ];
    }
}

