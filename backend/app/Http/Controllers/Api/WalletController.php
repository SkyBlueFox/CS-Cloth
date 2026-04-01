<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Support\ApiData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    private const TOP_UP_PROVIDERS = [
        'scb_easy',
        'k_plus',
        'krungthai_next',
        'krungsri_app',
        'bualuang_mbanking',
        'line_pay',
        'apple_pay',
        'truemoney',
        'google_pay',
        'shopee_pay',
        'credit_debit_card',
    ];

    public function index(Request $request)
    {
        $transactions = WalletTransaction::query()
            ->where('user_id', $request->user()->id)
            ->latest('completed_at')
            ->latest()
            ->paginate(12);

        return response()->json([
            'balance' => (float) $request->user()->balance,
            'transactions' => ApiData::pagination(
                $transactions,
                fn (WalletTransaction $transaction) => ApiData::walletTransaction($transaction)
            ),
        ]);
    }

    public function topUp(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:50', 'max:50000'],
            'provider' => ['required', 'string', 'in:' . implode(',', self::TOP_UP_PROVIDERS)],
            'cardholder_name' => ['nullable', 'string', 'max:255'],
            'card_number' => ['nullable', 'string', 'regex:/^[0-9 ]+$/', 'min:12', 'max:23'],
            'expiry_date' => ['nullable', 'string', 'regex:/^(0[1-9]|1[0-2])\/[0-9]{2}$/'],
            'cvv' => ['nullable', 'string', 'regex:/^[0-9]{3,4}$/'],
        ]);

        if ($validated['provider'] === 'credit_debit_card') {
            $cardDigits = preg_replace('/\D+/', '', (string) ($validated['card_number'] ?? ''));
            abort_unless(filled($validated['cardholder_name'] ?? null), 422, 'Please enter the cardholder name.');
            abort_unless(strlen($cardDigits) >= 12 && strlen($cardDigits) <= 19, 422, 'Please enter a valid card number.');
            abort_unless(filled($validated['expiry_date'] ?? null), 422, 'Please enter the card expiry date.');
            abort_unless(filled($validated['cvv'] ?? null), 422, 'Please enter the card security code.');
        }

        $result = DB::transaction(function () use ($request, $validated) {
            /** @var User $user */
            $user = User::query()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();

            $amount = round((float) $validated['amount'], 2);
            $before = (float) $user->balance;
            $after = $before + $amount;

            $transaction = WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'top_up',
                'provider' => $validated['provider'],
                'status' => 'completed',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference' => $this->generateReference(),
                'note' => $validated['provider'] === 'credit_debit_card'
                    ? 'Card ending in ' . substr(preg_replace('/\D+/', '', (string) ($validated['card_number'] ?? '')), -4)
                    : null,
                'completed_at' => now(),
            ]);

            $user->update([
                'balance' => $after,
            ]);

            return [$user->fresh(), $transaction->fresh()];
        });

        [$user, $transaction] = $result;

        return response()->json([
            'message' => 'Wallet topped up successfully.',
            'user' => ApiData::user($user),
            'transaction' => ApiData::walletTransaction($transaction),
        ], 201);
    }

    private function generateReference(): string
    {
        do {
            $reference = 'TOPUP-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (WalletTransaction::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
