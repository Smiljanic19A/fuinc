<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * Get all orders for a specific user
     */
    public function getUserOrders($userId): JsonResponse
    {
        try {
            // Verify user exists
            $user = User::findOrFail($userId);

            $orders = Order::where('user_id', $userId)
                ->with([
                    'user:id,name,email',
                    'market:id,symbol,display_name,current_price'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => "Orders for user {$userId} retrieved successfully",
                'data' => $orders
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject an order
     */
    public function rejectOrder($id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);

            // Check if order can be rejected
            if (!$order->isOpen()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be rejected. Current status: ' . $order->status
                ], 400);
            }

            $order->cancel('Rejected by admin');

            return response()->json([
                'success' => true,
                'message' => 'Order rejected successfully',
                'data' => $order->fresh()->load([
                    'user:id,name,email',
                    'market:id,symbol,display_name,current_price'
                ])
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark order as filled
     */
    public function markAsFilled(Request $request, $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);

            // Check if order can be filled
            if (!$order->isOpen()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be filled. Current status: ' . $order->status
                ], 400);
            }

            // Validate fill parameters
            $validated = $request->validate([
                'fill_price' => 'nullable|numeric|min:0',
                'fill_quantity' => 'nullable|numeric|min:0'
            ]);

            // Use provided values or defaults
            $fillPrice = $validated['fill_price'] ?? $order->price;
            $fillQuantity = $validated['fill_quantity'] ?? $order->remaining_quantity;

            // Fill the order
            $order->fillOrder($fillQuantity, $fillPrice);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as filled successfully',
                'data' => $order->fresh()->load([
                    'user:id,name,email',
                    'market:id,symbol,display_name,current_price'
                ])
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
                'message' => 'Order not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark order as filled',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all orders for all users (paginated)
     */
    public function getAllOrders(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status'); // 'pending', 'filled', 'cancelled', etc.
            $userId = $request->get('user_id');
            $marketId = $request->get('market_id');

            $query = Order::query()
                ->with([
                    'user:id,name,email',
                    'market:id,symbol,display_name,current_price'
                ])
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($status) {
                if ($status === 'open') {
                    $query->open();
                } else {
                    $query->byStatus($status);
                }
            }

            // Filter by user
            if ($userId) {
                $query->byUser($userId);
            }

            // Filter by market
            if ($marketId) {
                $query->byMarket($marketId);
            }

            $orders = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
