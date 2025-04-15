<?php

namespace App\Http\Controllers;

use App\Http\Requests\WalletOperationRequest;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function deposit(WalletOperationRequest $request, $userId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = User::find($userId);

            if (!$user || !$user->wallet) {
                return response()->json(['message' => 'User or wallet not found'], 404);
            }

            DB::transaction(function () use ($user, $validated) {
                $user->wallet->increment('balance', $validated['amount']);
                Transaction::create([
                    'wallet_id' => $user->wallet->id,
                    'type' => 'deposit',
                    'amount' => $validated['amount'],
                    'description' => 'Deposit to wallet',
                ]);
            });

            return response()->json([
                'message' => 'Deposit successful',
                'balance' => $user->wallet->fresh()->balance,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing the deposit',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function withdraw(WalletOperationRequest $request, $userId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = User::find($userId);

            if (!$user || !$user->wallet) {
                return response()->json(['message' => 'User or wallet not found'], 404);
            }

            $wallet = $user->wallet;
            if ($wallet->balance < $validated['amount']) {
                return response()->json(['message' => 'Insufficient funds'], 400);
            }

            DB::transaction(function () use ($wallet, $validated) {
                $wallet->decrement('balance', $validated['amount']);
                Transaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'withdraw',
                    'amount' => $validated['amount'],
                    'description' => 'Withdrawal from wallet',
                ]);
            });

            return response()->json([
                'message' => 'Withdrawal successful',
                'balance' => $wallet->fresh()->balance,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing the withdrawal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}