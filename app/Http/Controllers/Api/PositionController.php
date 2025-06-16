<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PositionController extends Controller
{
    /**
     * Get all positions for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // This would typically get user_id from authentication, but for now using request parameter
            $userId = $request->get('user_id');
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            $positions = Position::where('user_id', $userId)
                ->with([
                    'user:id,name,email',
                    'market:id,symbol,display_name,current_price,base_currency,quote_currency'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Positions retrieved successfully',
                'data' => $positions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve positions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific position
     */
    public function show($id): JsonResponse
    {
        try {
            $position = Position::with([
                'user:id,name,email',
                'market:id,symbol,display_name,current_price,base_currency,quote_currency'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Position retrieved successfully',
                'data' => $position
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Position not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve position',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close a position
     */
    public function close(Request $request, $id): JsonResponse
    {
        try {
            $position = Position::findOrFail($id);

            // Check if position can be closed
            if ($position->status !== 'open') {
                return response()->json([
                    'success' => false,
                    'message' => 'Position cannot be closed. Current status: ' . $position->status
                ], 400);
            }

            $validated = $request->validate([
                'close_price' => 'sometimes|numeric|min:0',
                'close_quantity' => 'sometimes|numeric|min:0.00000001'
            ]);

            $closePrice = $validated['close_price'] ?? $position->market->current_price;
            $closeQuantity = $validated['close_quantity'] ?? $position->remaining_quantity;

            // Calculate P&L based on position side
            if ($position->side === 'long') {
                $pnl = ($closePrice - $position->entry_price) * $closeQuantity;
            } else {
                $pnl = ($position->entry_price - $closePrice) * $closeQuantity;
            }

            // Update position
            $position->update([
                'status' => 'closed',
                'close_price' => $closePrice,
                'close_quantity' => $closeQuantity,
                'remaining_quantity' => $position->remaining_quantity - $closeQuantity,
                'realized_pnl' => $position->realized_pnl + $pnl,
                'closed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Position closed successfully',
                'data' => [
                    'position' => $position->fresh()->load([
                        'user:id,name,email',
                        'market:id,symbol,display_name,current_price'
                    ]),
                    'pnl' => round($pnl, 2),
                    'close_details' => [
                        'close_price' => $closePrice,
                        'close_quantity' => $closeQuantity,
                        'realized_pnl' => round($pnl, 2)
                    ]
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Position not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close position',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
