<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DepositController extends Controller
{
    /**
     * Request a new deposit
     */
    public function requestDeposit(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0.00000001',
                'currency' => 'required|string|max:10|in:BTC,ETH,USDT,USDC,BNB,ADA,XRP,SOL,DOT,MATIC',
                'network' => 'required|string|max:50'
            ]);

            $deposit = Deposit::create([
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'currency' => strtoupper($validated['currency']),
                'network' => $validated['network'],
                'address' => null, // Will be provided later
                'filled' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit request created successfully',
                'data' => $deposit->load('user:id,name,email')
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create deposit request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Provide wallet address for a deposit
     */
    public function provideAddress(Request $request, $id): JsonResponse
    {
        try {
            $deposit = Deposit::findOrFail($id);

            $validated = $request->validate([
                'address' => 'required|string|max:255'
            ]);

            $deposit->update([
                'address' => $validated['address']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wallet address provided successfully',
                'data' => $deposit->fresh()->load('user:id,name,email')
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to provide wallet address',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark deposit as filled
     */
    public function markAsFilled($id): JsonResponse
    {
        try {
            $deposit = Deposit::findOrFail($id);

            // Check if already filled to prevent double crediting
            if ($deposit->filled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit is already marked as filled'
                ], 400);
            }

            // Mark deposit as filled
            $deposit->markAsFilled();

            // Credit the user's account with the deposit amount
            \App\Models\UserFund::addFundsToBalance(
                $deposit->user_id,
                $deposit->currency,
                $deposit->amount
            );

            return response()->json([
                'success' => true,
                'message' => 'Deposit marked as filled and funds credited to user account',
                'data' => $deposit->fresh()->load('user:id,name,email')
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark deposit as filled',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all deposits and their statuses
     */
    public function getAllDeposits(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status'); // 'filled', 'pending', or null for all
            $currency = $request->get('currency');

            $query = Deposit::query()
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($status === 'filled') {
                $query->filled();
            } elseif ($status === 'pending') {
                $query->pending();
            }

            // Filter by currency
            if ($currency) {
                $query->byCurrency(strtoupper($currency));
            }

            $deposits = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Deposits retrieved successfully',
                'data' => $deposits
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve deposits',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all deposits for a specific user
     */
    public function getUserDeposits($userId): JsonResponse
    {
        try {
            $deposits = Deposit::where('user_id', $userId)
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => "Deposits for user {$userId} retrieved successfully",
                'data' => $deposits
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user deposits',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
