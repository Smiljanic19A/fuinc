<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFund;
use App\Models\UserFundAllocation;
use App\Models\Position;
use App\Models\Order;
use App\Models\Market;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserFundsController extends Controller
{
    /**
     * Get comprehensive user funds and portfolio information
     */
    public function getUserFunds($userId): JsonResponse
    {
        try {
            // Verify user exists
            $user = User::findOrFail($userId);

            // Get all user funds (available cash)
            $userFunds = UserFund::where('user_id', $userId)->get();
            
            // Get allocated funds (reserved for orders)
            $allocatedFunds = UserFundAllocation::where('user_id', $userId)
                ->where('is_active', true)
                ->get();
            
            // Get open positions (active trades)
            $openPositions = Position::where('user_id', $userId)
                ->where('status', 'open')
                ->with('market:id,symbol,current_price,base_currency,quote_currency')
                ->get();
            
            // Get closed positions for P&L calculation
            $closedPositions = Position::where('user_id', $userId)
                ->where('status', 'closed')
                ->get();
            
            // Get open orders count
            $openOrdersCount = Order::where('user_id', $userId)
                ->whereIn('status', ['pending', 'partially_filled'])
                ->count();

            // Calculate total funds and allocations using real-time USD values
            $totalUserFunds = 0;
            foreach ($userFunds as $fund) {
                $totalUserFunds += $fund->getUsdValue();
            }
            
            $totalAllocatedFunds = $allocatedFunds->sum('amount');
            $totalAvailableFunds = $totalUserFunds - $totalAllocatedFunds;
            
            // Calculate total margin used in active trades
            $totalMarginUsed = $openPositions->sum('margin_used');
            
            // Calculate unrealized P&L from open positions
            $unrealizedPnL = $openPositions->sum('unrealized_pnl');
            
            // Calculate realized P&L from all positions (open + closed)
            $totalRealizedPnL = $openPositions->sum('realized_pnl') + $closedPositions->sum('realized_pnl');
            
            // Calculate all-time P&L
            $allTimePnL = $totalRealizedPnL + $unrealizedPnL;
            
            // Calculate total portfolio value
            $totalPortfolioValue = $totalUserFunds + $totalMarginUsed + $unrealizedPnL;
            
            // Build fund distribution by currency
            $fundsByCurrency = [];
            $allocatedByCurrency = [];
            
            foreach ($userFunds as $fund) {
                $allocatedForCurrency = $allocatedFunds
                    ->where('currency', $fund->currency)
                    ->sum('amount');
                
                $fundUsdValue = $fund->getUsdValue();
                $availableForCurrency = $fundUsdValue - $allocatedForCurrency;
                
                $fundsByCurrency[] = [
                    'currency' => $fund->currency,
                    'crypto_amount' => (float) $fund->amount,
                    'total_balance_usd' => round($fundUsdValue, 2),
                    'available_balance_usd' => round(max(0, $availableForCurrency), 2),
                    'allocated_balance_usd' => round($allocatedForCurrency, 2),
                    'current_price' => $fund->currency === 'USDT' || $fund->currency === 'USDC' ? 1 : 
                        round(UserFund::calculateUsdValue(1, $fund->currency), 2),
                    'status' => 'cash'
                ];
            }
            
            // Add allocated funds breakdown
            foreach ($allocatedFunds->groupBy('currency') as $currency => $allocations) {
                $allocatedByCurrency[] = [
                    'currency' => $currency,
                    'amount_usd' => $allocations->sum('amount'),
                    'order_count' => $allocations->where('type', 'order_reserve')->count(),
                    'status' => 'allocated_to_orders'
                ];
            }
            
            // Build active trades breakdown
            $activeTrades = [];
            foreach ($openPositions as $position) {
                $activeTrades[] = [
                    'position_id' => $position->position_id,
                    'market' => $position->market->symbol,
                    'side' => $position->side,
                    'entry_price' => $position->entry_price,
                    'current_price' => $position->current_price,
                    'quantity' => $position->remaining_quantity,
                    'margin_used' => $position->margin_used,
                    'unrealized_pnl' => $position->unrealized_pnl,
                    'leverage' => $position->leverage
                ];
            }
            
            // Get recent performance metrics
            $last30DaysPositions = Position::where('user_id', $userId)
                ->where('closed_at', '>=', now()->subDays(30))
                ->get();
            
            $last30DaysPnL = $last30DaysPositions->sum('realized_pnl');
            
            return response()->json([
                'success' => true,
                'message' => 'User funds retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ],
                    'portfolio_summary' => [
                        'total_portfolio_value' => round($totalPortfolioValue, 2),
                        'total_funds' => round($totalUserFunds, 2),
                        'available_funds' => round($totalAvailableFunds, 2),
                        'allocated_funds' => round($totalAllocatedFunds, 2),
                        'margin_used' => round($totalMarginUsed, 2),
                        'unrealized_pnl' => round($unrealizedPnL, 2),
                        'all_time_pnl' => round($allTimePnL, 2),
                        'realized_pnl' => round($totalRealizedPnL, 2),
                        'last_30_days_pnl' => round($last30DaysPnL, 2)
                    ],
                    'fund_distribution' => [
                        'cash_balances' => $fundsByCurrency,
                        'allocated_funds' => $allocatedByCurrency,
                        'total_cash_usd' => round($totalUserFunds, 2),
                        'total_available_usd' => round($totalAvailableFunds, 2),
                        'total_allocated_usd' => round($totalAllocatedFunds, 2)
                    ],
                    'active_trades' => [
                        'positions' => $activeTrades,
                        'total_positions' => count($activeTrades),
                        'total_margin_used' => round($totalMarginUsed, 2),
                        'total_unrealized_pnl' => round($unrealizedPnL, 2)
                    ],
                    'trading_activity' => [
                        'open_orders_count' => $openOrdersCount,
                        'open_positions_count' => count($activeTrades),
                        'total_closed_positions' => $closedPositions->count()
                    ],
                    'performance_metrics' => [
                        'portfolio_change_percentage' => $totalAvailableFunds > 0 ? 
                            round(($allTimePnL / $totalAvailableFunds) * 100, 2) : 0,
                        'win_rate' => $this->calculateWinRate($userId),
                        'avg_position_size' => $openPositions->count() > 0 ? 
                            round($totalMarginUsed / $openPositions->count(), 2) : 0
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user funds',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate win rate for the user
     */
    private function calculateWinRate($userId): float
    {
        $closedPositions = Position::where('user_id', $userId)
            ->where('status', 'closed')
            ->get();

        if ($closedPositions->count() === 0) {
            return 0;
        }

        $winningPositions = $closedPositions->where('realized_pnl', '>', 0)->count();
        return round(($winningPositions / $closedPositions->count()) * 100, 2);
    }
}
