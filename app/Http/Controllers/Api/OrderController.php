<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\UserFundAllocation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * Create a new order with fund allocation
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'market_id' => 'required|exists:markets,id',
                'type' => 'required|in:limit,market,stop,stop_limit',
                'side' => 'required|in:buy,sell',
                'quantity' => 'required|numeric|min:0.00000001',
                'price' => 'required_if:type,limit,stop_limit|numeric|min:0',
                'stop_price' => 'required_if:type,stop,stop_limit|numeric|min:0',
                'time_in_force' => 'sometimes|in:GTC,IOC,FOK'
            ]);

            // Get market information to determine currency allocation
            $market = \App\Models\Market::findOrFail($validated['market_id']);
            
            // Calculate total order value and determine currency to allocate
            $orderValue = $validated['quantity'] * ($validated['price'] ?? $market->current_price);
            
            if ($validated['side'] === 'buy') {
                // For buy orders, allocate quote currency (e.g., USDT)
                $allocationCurrency = $market->quote_currency;
                $allocationAmount = $orderValue;
            } else {
                // For sell orders, allocate base currency (e.g., BTC)
                $allocationCurrency = $market->base_currency;
                $allocationAmount = $validated['quantity'];
            }

            // Check if user has sufficient funds
            $userBalance = \App\Models\UserFund::getUserBalance($validated['user_id'], $allocationCurrency);
            $currentAllocated = \App\Models\UserFundAllocation::getTotalAllocated($validated['user_id'], $allocationCurrency);
            $availableBalance = $userBalance - $currentAllocated;

            if ($availableBalance < $allocationAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient funds',
                    'details' => [
                        'required' => $allocationAmount,
                        'available' => $availableBalance,
                        'currency' => $allocationCurrency
                    ]
                ], 400);
            }

            // Create the order
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'market_id' => $validated['market_id'],
                'type' => $validated['type'],
                'side' => $validated['side'],
                'quantity' => $validated['quantity'],
                'price' => $validated['price'] ?? $market->current_price,
                'stop_price' => $validated['stop_price'] ?? null,
                'time_in_force' => $validated['time_in_force'] ?? 'GTC',
                'total_value' => $orderValue,
                'status' => 'pending'
            ]);

            // Allocate funds for the order
            UserFundAllocation::allocateForOrder($order, $allocationCurrency, $allocationAmount);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully and funds allocated',
                'data' => $order->load([
                    'user:id,name,email',
                    'market:id,symbol,display_name,current_price'
                ])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
     * Edit an existing order
     */
    public function editOrder(Request $request, $id): JsonResponse
    {
        try {
            $order = Order::findOrFail($id);

            // Check if order can be edited
            if (!$order->isOpen()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be edited. Current status: ' . $order->status
                ], 400);
            }

            $validated = $request->validate([
                'quantity' => 'sometimes|numeric|min:0.00000001',
                'price' => 'sometimes|numeric|min:0',
                'stop_price' => 'sometimes|nullable|numeric|min:0',
                'time_in_force' => 'sometimes|in:GTC,IOC,FOK'
            ]);

            // Get market information
            $market = $order->market;
            
            // Calculate current and new allocations
            $currentQuantity = $order->quantity;
            $currentPrice = $order->price;
            $newQuantity = $validated['quantity'] ?? $currentQuantity;
            $newPrice = $validated['price'] ?? $currentPrice;
            
            // Calculate new order value and allocation
            $newOrderValue = $newQuantity * $newPrice;
            
            if ($order->side === 'buy') {
                $allocationCurrency = $market->quote_currency;
                $currentAllocation = $currentQuantity * $currentPrice;
                $newAllocation = $newOrderValue;
            } else {
                $allocationCurrency = $market->base_currency;
                $currentAllocation = $currentQuantity;
                $newAllocation = $newQuantity;
            }

            $allocationDifference = $newAllocation - $currentAllocation;

            // If we need more funds, check if user has sufficient balance
            if ($allocationDifference > 0) {
                $userBalance = \App\Models\UserFund::getUserBalance($order->user_id, $allocationCurrency);
                $currentAllocated = UserFundAllocation::getTotalAllocated($order->user_id, $allocationCurrency);
                $availableBalance = $userBalance - $currentAllocated;

                if ($availableBalance < $allocationDifference) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient funds for order modification',
                        'details' => [
                            'additional_required' => $allocationDifference,
                            'available' => $availableBalance,
                            'currency' => $allocationCurrency
                        ]
                    ], 400);
                }
            }

            // Update the order
            $order->update([
                'quantity' => $newQuantity,
                'price' => $newPrice,
                'remaining_quantity' => $newQuantity - $order->filled_quantity,
                'total_value' => $newOrderValue,
                'stop_price' => $validated['stop_price'] ?? $order->stop_price,
                'time_in_force' => $validated['time_in_force'] ?? $order->time_in_force,
            ]);

            // Update fund allocation
            if ($allocationDifference != 0) {
                // Get current allocation record
                $currentAllocationRecord = UserFundAllocation::where('order_id', $order->id)
                    ->where('is_active', true)
                    ->first();

                if ($currentAllocationRecord) {
                    // Update the allocation amount
                    $currentAllocationRecord->update([
                        'amount' => $newAllocation
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully and funds reallocated',
                'data' => [
                    'order' => $order->fresh()->load([
                        'user:id,name,email',
                        'market:id,symbol,display_name,current_price'
                    ]),
                    'fund_changes' => [
                        'currency' => $allocationCurrency,
                        'previous_allocation' => $currentAllocation,
                        'new_allocation' => $newAllocation,
                        'difference' => $allocationDifference
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
                'message' => 'Order not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to edit order',
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

            // Release allocated funds when order is rejected
            UserFundAllocation::releaseOrderAllocations($order);

            return response()->json([
                'success' => true,
                'message' => 'Order rejected and funds released successfully',
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

            // Handle fund transitions when order is filled
            if ($order->status === 'filled') {
                // Release all allocated funds for this order
                UserFundAllocation::releaseOrderAllocations($order);
                
                // For buy orders: deduct quote currency, add base currency
                // For sell orders: deduct base currency, add quote currency
                $market = $order->market;
                $filledValue = $fillQuantity * $fillPrice;
                
                if ($order->side === 'buy') {
                    // Deduct quote currency (what was paid)
                    \App\Models\UserFund::addFundsToBalance($order->user_id, $market->quote_currency, -$filledValue);
                    // Add base currency (what was bought)
                    \App\Models\UserFund::addFundsToBalance($order->user_id, $market->base_currency, $fillQuantity);
                } else {
                    // Deduct base currency (what was sold)
                    \App\Models\UserFund::addFundsToBalance($order->user_id, $market->base_currency, -$fillQuantity);
                    // Add quote currency (what was received)
                    \App\Models\UserFund::addFundsToBalance($order->user_id, $market->quote_currency, $filledValue);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Order filled and funds updated successfully',
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
