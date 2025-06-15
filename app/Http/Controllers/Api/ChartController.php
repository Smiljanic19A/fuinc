<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\MarketData;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ChartController extends Controller
{
    /**
     * Get candlestick/OHLCV data for charting
     * 
     * @param Request $request
     * @param string $symbol Market symbol (e.g., BTC/USDT, ETH/BTC)
     * @return JsonResponse
     */
    public function candles(Request $request, string $symbol): JsonResponse
    {
        $symbol = strtoupper(str_replace('-', '/', $symbol));
        
        $market = Market::bySymbol($symbol)->active()->first();
        
        if (!$market) {
            return response()->json([
                'success' => false,
                'message' => 'Market not found or not active',
            ], 404);
        }
        
        // Get request parameters
        $timeframe = $request->input('interval', '1h'); // 1m, 5m, 15m, 30m, 1h, 4h, 1d
        $limit = min($request->input('limit', 100), 1000);
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');
        
        // Normalize timeframe
        $timeframe = $this->normalizeTimeframe($timeframe);
        
        // Build query
        $query = MarketData::byMarket($market->id)
            ->byTimeframe($timeframe)
            ->closed()
            ->orderBy('timestamp', 'asc');
        
        // Apply date filters if provided
        if ($startTime) {
            $query->where('timestamp', '>=', Carbon::createFromTimestamp($startTime / 1000));
        }
        
        if ($endTime) {
            $query->where('timestamp', '<=', Carbon::createFromTimestamp($endTime / 1000));
        }
        
        $candles = $query->take($limit)->get();
        
        // Format candles for frontend consumption
        $formattedCandles = $candles->map(function ($candle) {
            return [
                $candle->timestamp->timestamp * 1000, // Timestamp in milliseconds
                (float) $candle->open,
                (float) $candle->high,
                (float) $candle->low,
                (float) $candle->close,
                (float) $candle->volume,
            ];
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Candle data retrieved successfully',
            'data' => [
                'symbol' => $symbol,
                'interval' => $timeframe,
                'candles' => $formattedCandles,
                'count' => $formattedCandles->count(),
                'market_info' => [
                    'id' => $market->id,
                    'base_currency' => $market->base_currency,
                    'quote_currency' => $market->quote_currency,
                    'current_price' => $market->current_price,
                    'price_change_24h' => $market->price_change_24h,
                    'price_change_percentage_24h' => $market->price_change_percentage_24h,
                ]
            ]
        ]);
    }
    
    /**
     * Get ticker data for a symbol
     * 
     * @param Request $request
     * @param string $symbol Market symbol
     * @return JsonResponse
     */
    public function ticker(Request $request, string $symbol): JsonResponse
    {
        $symbol = strtoupper(str_replace('-', '/', $symbol));
        
        $market = Market::bySymbol($symbol)->active()->first();
        
        if (!$market) {
            return response()->json([
                'success' => false,
                'message' => 'Market not found or not active',
            ], 404);
        }
        
        // Get latest candle for additional data
        $latestCandle = MarketData::byMarket($market->id)
            ->byTimeframe('1h')
            ->closed()
            ->orderBy('timestamp', 'desc')
            ->first();
        
        // Calculate additional statistics
        $stats = $this->calculateTickerStats($market);
        
        return response()->json([
            'success' => true,
            'message' => 'Ticker data retrieved successfully',
            'data' => [
                'symbol' => $symbol,
                'price' => (float) $market->current_price,
                'price_change' => (float) $market->price_change_24h,
                'price_change_percent' => (float) $market->price_change_percentage_24h,
                'high_24h' => (float) $market->high_24h,
                'low_24h' => (float) $market->low_24h,
                'volume_24h' => (float) $market->volume_24h,
                'market_cap' => (float) $market->market_cap,
                'last_updated' => $market->updated_at->toISOString(),
                'bid' => $stats['bid'],
                'ask' => $stats['ask'],
                'bid_qty' => $stats['bid_qty'],
                'ask_qty' => $stats['ask_qty'],
                'open_24h' => $latestCandle ? (float) $latestCandle->open : (float) $market->current_price,
                'prev_close' => $latestCandle ? (float) $latestCandle->close : (float) $market->current_price,
                'quote_volume_24h' => $stats['quote_volume_24h'],
                'count' => $stats['trades_count'],
                'is_active' => $market->is_active,
                'is_trading_enabled' => $market->is_trading_enabled,
            ]
        ]);
    }
    
    /**
     * Get order book depth data
     * 
     * @param Request $request
     * @param string $symbol Market symbol
     * @return JsonResponse
     */
    public function orderBook(Request $request, string $symbol): JsonResponse
    {
        $symbol = strtoupper(str_replace('-', '/', $symbol));
        
        $market = Market::bySymbol($symbol)->active()->first();
        
        if (!$market) {
            return response()->json([
                'success' => false,
                'message' => 'Market not found or not active',
            ], 404);
        }
        
        $limit = min($request->input('limit', 20), 100);
        
        // Generate realistic order book data based on current market price
        $orderBook = $this->generateOrderBookData($market, $limit);
        
        return response()->json([
            'success' => true,
            'message' => 'Order book data retrieved successfully',
            'data' => [
                'symbol' => $symbol,
                'last_update_id' => time(),
                'bids' => $orderBook['bids'],
                'asks' => $orderBook['asks'],
                'market_info' => [
                    'current_price' => $market->current_price,
                    'spread' => $orderBook['spread'],
                    'spread_percent' => $orderBook['spread_percent'],
                ]
            ]
        ]);
    }
    
    /**
     * Get recent trades data
     * 
     * @param Request $request
     * @param string $symbol Market symbol
     * @return JsonResponse
     */
    public function recentTrades(Request $request, string $symbol): JsonResponse
    {
        $symbol = strtoupper(str_replace('-', '/', $symbol));
        
        $market = Market::bySymbol($symbol)->active()->first();
        
        if (!$market) {
            return response()->json([
                'success' => false,
                'message' => 'Market not found or not active',
            ], 404);
        }
        
        $limit = min($request->input('limit', 50), 500);
        
        // Get recent trades from orders table or generate realistic data
        $trades = $this->getRecentTrades($market, $limit);
        
        return response()->json([
            'success' => true,
            'message' => 'Recent trades retrieved successfully',
            'data' => [
                'symbol' => $symbol,
                'trades' => $trades,
                'count' => count($trades),
            ]
        ]);
    }
    
    /**
     * Normalize timeframe to match database values
     */
    private function normalizeTimeframe(string $timeframe): string
    {
        $timeframeMap = [
            '1m' => '1m',
            '5m' => '5m',
            '15m' => '15m',
            '30m' => '30m',
            '1h' => '1h',
            '2h' => '2h',
            '4h' => '4h',
            '6h' => '6h',
            '8h' => '8h',
            '12h' => '12h',
            '1d' => '1d',
            '3d' => '3d',
            '1w' => '1w',
            '1M' => '1M',
        ];
        
        return $timeframeMap[$timeframe] ?? '1h';
    }
    
    /**
     * Calculate ticker statistics
     */
    private function calculateTickerStats(Market $market): array
    {
        // Get recent market data for volume calculations
        $recentCandles = MarketData::byMarket($market->id)
            ->byTimeframe('1h')
            ->where('timestamp', '>=', now()->subDay())
            ->get();
        
        $quoteVolume = $recentCandles->sum('quote_volume');
        $tradesCount = $recentCandles->sum('trades_count');
        
        // Calculate bid/ask based on current price with realistic spread
        $spread = $market->current_price * 0.001; // 0.1% spread
        $bid = $market->current_price - ($spread / 2);
        $ask = $market->current_price + ($spread / 2);
        
        return [
            'bid' => (float) $bid,
            'ask' => (float) $ask,
            'bid_qty' => rand(100, 10000) / 100, // Random bid quantity
            'ask_qty' => rand(100, 10000) / 100, // Random ask quantity
            'quote_volume_24h' => (float) $quoteVolume,
            'trades_count' => (int) $tradesCount,
        ];
    }
    
    /**
     * Generate realistic order book data
     */
    private function generateOrderBookData(Market $market, int $limit): array
    {
        $currentPrice = (float) $market->current_price;
        $spread = $currentPrice * 0.001; // 0.1% spread
        
        $bids = [];
        $asks = [];
        
        // Generate bids (buy orders) - prices below current price
        for ($i = 1; $i <= $limit; $i++) {
            $price = $currentPrice - ($spread / 2) - ($i * $spread * 0.1);
            $quantity = rand(100, 10000) / 100;
            $bids[] = [
                (string) number_format($price, $market->price_precision),
                (string) number_format($quantity, $market->quantity_precision)
            ];
        }
        
        // Generate asks (sell orders) - prices above current price
        for ($i = 1; $i <= $limit; $i++) {
            $price = $currentPrice + ($spread / 2) + ($i * $spread * 0.1);
            $quantity = rand(100, 10000) / 100;
            $asks[] = [
                (string) number_format($price, $market->price_precision),
                (string) number_format($quantity, $market->quantity_precision)
            ];
        }
        
        $spreadValue = $asks[0][0] - $bids[0][0];
        $spreadPercent = ($spreadValue / $currentPrice) * 100;
        
        return [
            'bids' => $bids,
            'asks' => $asks,
            'spread' => $spreadValue,
            'spread_percent' => $spreadPercent,
        ];
    }
    
    /**
     * Get recent trades for a market
     */
    private function getRecentTrades(Market $market, int $limit): array
    {
        // In a real implementation, you would get actual trades from orders table
        // For now, generate realistic trade data based on recent candles
        
        $recentCandles = MarketData::byMarket($market->id)
            ->byTimeframe('1h')
            ->orderBy('timestamp', 'desc')
            ->take(5)
            ->get();
        
        $trades = [];
        $tradeId = time();
        
        foreach ($recentCandles as $candle) {
            $tradesPerCandle = min(rand(5, 15), $limit - count($trades));
            
            for ($i = 0; $i < $tradesPerCandle; $i++) {
                // Generate random price within candle range
                $price = rand((int)($candle->low * 100), (int)($candle->high * 100)) / 100;
                $quantity = rand(10, 1000) / 100;
                $timestamp = $candle->timestamp->addMinutes(rand(0, 59));
                $isBuyer = rand(0, 1) === 1;
                
                $trades[] = [
                    'id' => $tradeId++,
                    'price' => (string) number_format($price, $market->price_precision),
                    'qty' => (string) number_format($quantity, $market->quantity_precision),
                    'quote_qty' => (string) number_format($price * $quantity, 2),
                    'time' => $timestamp->timestamp * 1000,
                    'is_buyer_maker' => $isBuyer,
                    'side' => $isBuyer ? 'BUY' : 'SELL',
                ];
                
                if (count($trades) >= $limit) break;
            }
            
            if (count($trades) >= $limit) break;
        }
        
        // Sort by time descending (most recent first)
        usort($trades, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });
        
        return array_slice($trades, 0, $limit);
    }
}
