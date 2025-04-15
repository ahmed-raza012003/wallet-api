<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'initial_balance' => 'nullable|numeric|min:0',
            ]);

            $initialBalance = $validated['initial_balance'] ?? 0;

            $user = DB::transaction(function () use ($validated, $initialBalance) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ]);

                $user->wallet()->create(['balance' => $initialBalance]);

                return $user;
            });

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user->load('wallet'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $user = User::with('wallet')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }
}