<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function transfer(TransferRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $fromUser = User::find($validated['from_user_id']);
            $toUser = User::find($validated['to_user_id']);

            if (!$fromUser->wallet || !$toUser->wallet) {
                return response()->json(['message' => 'Wallet not found'], 404);
            }

            if ($fromUser->wallet->balance < $validated['amount']) {
                return response()->json(['message' => 'Insufficient funds'], 400);
            }

            DB::transaction(function () use ($fromUser, $toUser, $validated) {
                $fromUser->wallet->decrement('balance', $validated['amount']);
                $toUser->wallet->increment('balance', $validated['amount']);

                Transaction::create([
                    'wallet_id' => $fromUser->wallet->id,
                    'type' => 'transfer',
                    'amount' => $validated['amount'],
                    'description' => "Transfer to user {$toUser->id}",
                ]);

                Transaction::create([
                    'wallet_id' => $toUser->wallet->id,
                    'type' => 'transfer',
                    'amount' => $validated['amount'],
                    'description' => "Transfer from user {$fromUser->id}",
                ]);
            });

            return response()->json(['message' => 'Transfer successful'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing the transfer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index($userId): JsonResponse
    {
        try {
            $user = User::find($userId);
            if (!$user || !$user->wallet) {
                return response()->json(['message' => 'User or wallet not found'], 404);
            }

            $transactions = $user->wallet->transactions()
                ->latest()
                ->paginate(10);

            return response()->json($transactions, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving transactions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}