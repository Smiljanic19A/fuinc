<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Market;
use App\Models\MarketData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DataController extends Controller
{
    /**
     * Get top 10 performers by price change percentage
     */
    public function topPerformers(Request $request): JsonResponse
    {
        $timeframe = $request->input('timeframe', '24h'); // 24h, 7d, 30d
        $limit = min($request->input('limit', 10), 50);
        
        $performers = Coin::active()
            ->where('price_change_percentage_24h', '>', 0)
            ->orderBy('price_change_percentage_24h', 'desc')
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Top {$limit} performers retrieved successfully",
            'data' => [
                'timeframe' => $timeframe,
                'performers' => $performers->map(function ($coin) {
                    return [
                        'id' => $coin->id,
                        'symbol' => $coin->symbol,
                        'name' => $coin->name,
                        'full_name' => $coin->full_name,
                        'current_price' => $coin->current_price,
                        'price_change_24h' => $coin->price_change_24h,
                        'price_change_percentage_24h' => $coin->price_change_percentage_24h,
                        'volume_24h' => $coin->volume_24h,
                        'market_cap' => $coin->market_cap,
                        'market_cap_rank' => $coin->market_cap_rank,
                        'category' => $coin->category,
                        'blockchain' => $coin->blockchain,
                        'icon_url' => $coin->icon_url,
                        'is_hot' => $coin->is_hot,
                        'is_trending' => $coin->is_trending,
                        'tags' => $coin->tags,
                    ];
                }),
                'count' => $performers->count()
            ]
        ]);
    }

    /**
     * Get hot coins with enhanced data
     */
    public function hotCoins(Request $request): JsonResponse
    {
        $limit = min($request->input('limit', 20), 100);
        
        $hotCoins = Coin::active()
            ->hot()
            ->orderBy('price_change_percentage_24h', 'desc')
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Hot coins retrieved successfully',
            'data' => [
                'coins' => $hotCoins->map(function ($coin) {
                    return [
                        'id' => $coin->id,
                        'symbol' => $coin->symbol,
                        'name' => $coin->name,
                        'full_name' => $coin->full_name,
                        'current_price' => $coin->current_price,
                        'price_change_24h' => $coin->price_change_24h,
                        'price_change_percentage_24h' => $coin->price_change_percentage_24h,
                        'volume_24h' => $coin->volume_24h,
                        'market_cap' => $coin->market_cap,
                        'market_cap_rank' => $coin->market_cap_rank,
                        'circulating_supply' => $coin->circulating_supply,
                        'category' => $coin->category,
                        'blockchain' => $coin->blockchain,
                        'icon_url' => $coin->icon_url,
                        'website_url' => $coin->website_url,
                        'is_hot' => $coin->is_hot,
                        'is_new' => $coin->is_new,
                        'is_trending' => $coin->is_trending,
                        'tags' => $coin->tags,
                        'launched_at' => $coin->launched_at,
                        'heat_score' => $this->calculateHeatScore($coin),
                    ];
                }),
                'count' => $hotCoins->count(),
                'stats' => [
                    'average_gain' => $hotCoins->avg('price_change_percentage_24h'),
                    'total_volume' => $hotCoins->sum('volume_24h'),
                    'categories' => $hotCoins->groupBy('category')->map->count(),
                ]
            ]
        ]);
    }

    /**
     * Get single market by ID with comprehensive data
     */
    public function market(Request $request, int $id): JsonResponse
    {
        $market = Market::with(['baseCoin', 'quoteCoin'])->find($id);

        if (!$market) {
            return response()->json([
                'success' => false,
                'message' => 'Market not found',
            ], 404);
        }

        // Get recent market data for additional insights
        $recentCandles = MarketData::where('market_id', $id)
            ->where('timeframe', '1h')
            ->orderBy('timestamp', 'desc')
            ->take(24)
            ->get();

        // Calculate additional metrics
        $volumeAvg = $recentCandles->avg('volume');
        $priceVolatility = $this->calculateVolatility($recentCandles);

        return response()->json([
            'success' => true,
            'message' => 'Market data retrieved successfully',
            'data' => [
                'market' => [
                    'id' => $market->id,
                    'symbol' => $market->symbol,
                    'base_currency' => $market->base_currency,
                    'quote_currency' => $market->quote_currency,
                    'display_name' => $market->display_name,
                    'current_price' => $market->current_price,
                    'price_change_24h' => $market->price_change_24h,
                    'price_change_percentage_24h' => $market->price_change_percentage_24h,
                    'high_24h' => $market->high_24h,
                    'low_24h' => $market->low_24h,
                    'volume_24h' => $market->volume_24h,
                    'market_cap' => $market->market_cap,
                    'rank' => $market->rank,
                    'min_order_amount' => $market->min_order_amount,
                    'max_order_amount' => $market->max_order_amount,
                    'price_precision' => $market->price_precision,
                    'quantity_precision' => $market->quantity_precision,
                    'is_active' => $market->is_active,
                    'is_trading_enabled' => $market->is_trading_enabled,
                    'icon_url' => $market->icon_url,
                    'description' => $market->description,
                ],
                'base_coin' => $market->baseCoin ? [
                    'id' => $market->baseCoin->id,
                    'symbol' => $market->baseCoin->symbol,
                    'name' => $market->baseCoin->name,
                    'full_name' => $market->baseCoin->full_name,
                    'description' => $market->baseCoin->description,
                    'current_price' => $market->baseCoin->current_price,
                    'market_cap' => $market->baseCoin->market_cap,
                    'volume_24h' => $market->baseCoin->volume_24h,
                    'price_change_percentage_24h' => $market->baseCoin->price_change_percentage_24h,
                    'market_cap_rank' => $market->baseCoin->market_cap_rank,
                    'circulating_supply' => $market->baseCoin->circulating_supply,
                    'total_supply' => $market->baseCoin->total_supply,
                    'max_supply' => $market->baseCoin->max_supply,
                    'is_hot' => $market->baseCoin->is_hot,
                    'is_new' => $market->baseCoin->is_new,
                    'is_trending' => $market->baseCoin->is_trending,
                    'category' => $market->baseCoin->category,
                    'blockchain' => $market->baseCoin->blockchain,
                    'tags' => $market->baseCoin->tags,
                    'icon_url' => $market->baseCoin->icon_url,
                    'website_url' => $market->baseCoin->website_url,
                    'launched_at' => $market->baseCoin->launched_at,
                ] : null,
                'quote_coin' => $market->quoteCoin ? [
                    'id' => $market->quoteCoin->id,
                    'symbol' => $market->quoteCoin->symbol,
                    'name' => $market->quoteCoin->name,
                    'full_name' => $market->quoteCoin->full_name,
                    'current_price' => $market->quoteCoin->current_price,
                    'is_hot' => $market->quoteCoin->is_hot,
                    'category' => $market->quoteCoin->category,
                ] : null,
                'analytics' => [
                    'volume_avg_24h' => $volumeAvg,
                    'price_volatility' => $priceVolatility,
                    'candles_count' => $recentCandles->count(),
                    'trend' => $this->determineTrend($recentCandles),
                    'support_resistance' => $this->getSupportResistance($recentCandles),
                ],
                'recent_candles' => $recentCandles->take(6)->map(function ($candle) {
                    return [
                        'timestamp' => $candle->timestamp,
                        'open' => $candle->open,
                        'high' => $candle->high,
                        'low' => $candle->low,
                        'close' => $candle->close,
                        'volume' => $candle->volume,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Get market overview with top performers, hot coins, and trending markets
     */
    public function overview(Request $request): JsonResponse
    {
        // Top 5 performers
        $topPerformers = Coin::active()
            ->where('price_change_percentage_24h', '>', 0)
            ->orderBy('price_change_percentage_24h', 'desc')
            ->take(5)
            ->get(['id', 'symbol', 'name', 'current_price', 'price_change_percentage_24h', 'icon_url']);

        // Top 5 losers
        $topLosers = Coin::active()
            ->where('price_change_percentage_24h', '<', 0)
            ->orderBy('price_change_percentage_24h', 'asc')
            ->take(5)
            ->get(['id', 'symbol', 'name', 'current_price', 'price_change_percentage_24h', 'icon_url']);

        // Hot coins (top 10)
        $hotCoins = Coin::active()
            ->hot()
            ->orderBy('price_change_percentage_24h', 'desc')
            ->take(10)
            ->get(['id', 'symbol', 'name', 'current_price', 'price_change_percentage_24h', 'volume_24h', 'icon_url']);

        // Trending markets
        $trendingMarkets = Market::with(['baseCoin'])
            ->where('is_active', true)
            ->orderBy('volume_24h', 'desc')
            ->take(10)
            ->get(['id', 'symbol', 'display_name', 'current_price', 'price_change_percentage_24h', 'volume_24h']);

        // Market stats
        $totalMarketCap = Coin::sum('market_cap');
        $totalVolume24h = Coin::sum('volume_24h');
        $totalCoins = Coin::active()->count();
        $totalMarkets = Market::where('is_active', true)->count();

        return response()->json([
            'success' => true,
            'message' => 'Market overview retrieved successfully',
            'data' => [
                'market_stats' => [
                    'total_market_cap' => $totalMarketCap,
                    'total_volume_24h' => $totalVolume24h,
                    'total_coins' => $totalCoins,
                    'total_markets' => $totalMarkets,
                    'last_updated' => now()->toISOString(),
                ],
                'top_performers' => $topPerformers,
                'top_losers' => $topLosers,
                'hot_coins' => $hotCoins,
                'trending_markets' => $trendingMarkets->map(function ($market) {
                    return [
                        'id' => $market->id,
                        'symbol' => $market->symbol,
                        'display_name' => $market->display_name,
                        'current_price' => $market->current_price,
                        'price_change_percentage_24h' => $market->price_change_percentage_24h,
                        'volume_24h' => $market->volume_24h,
                        'base_coin' => $market->baseCoin ? [
                            'symbol' => $market->baseCoin->symbol,
                            'name' => $market->baseCoin->name,
                            'icon_url' => $market->baseCoin->icon_url,
                        ] : null,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Calculate heat score for a coin based on various factors
     */
    private function calculateHeatScore($coin): float
    {
        $score = 0;
        
        // Price change contribution (40%)
        $priceScore = min(abs($coin->price_change_percentage_24h) / 20, 1) * 40;
        
        // Volume contribution (30%)
        $volumeScore = min($coin->volume_24h / 1000000000, 1) * 30; // Normalize to 1B
        
        // Market cap rank contribution (20%)
        $rankScore = $coin->market_cap_rank ? max(0, (100 - $coin->market_cap_rank) / 100) * 20 : 0;
        
        // Flags contribution (10%)
        $flagScore = ($coin->is_hot ? 3 : 0) + ($coin->is_trending ? 4 : 0) + ($coin->is_new ? 3 : 0);
        
        return round($priceScore + $volumeScore + $rankScore + $flagScore, 2);
    }

    /**
     * Calculate price volatility from recent candles
     */
    private function calculateVolatility($candles): float
    {
        if ($candles->count() < 2) return 0;
        
        $prices = $candles->pluck('close')->toArray();
        $returns = [];
        
        for ($i = 1; $i < count($prices); $i++) {
            $returns[] = ($prices[$i] - $prices[$i-1]) / $prices[$i-1];
        }
        
        $meanReturn = array_sum($returns) / count($returns);
        $variance = array_sum(array_map(function($x) use ($meanReturn) {
            return pow($x - $meanReturn, 2);
        }, $returns)) / count($returns);
        
        return round(sqrt($variance) * 100, 4); // Return as percentage
    }

    /**
     * Determine trend from recent candles
     */
    private function determineTrend($candles): string
    {
        if ($candles->count() < 3) return 'neutral';
        
        $recent = $candles->take(3);
        $prices = $recent->pluck('close')->toArray();
        
        if ($prices[0] > $prices[1] && $prices[1] > $prices[2]) {
            return 'bullish';
        } elseif ($prices[0] < $prices[1] && $prices[1] < $prices[2]) {
            return 'bearish';
        }
        
        return 'neutral';
    }

    /**
     * Get support and resistance levels
     */
    private function getSupportResistance($candles): array
    {
        if ($candles->count() < 5) {
            return ['support' => null, 'resistance' => null];
        }
        
        $highs = $candles->pluck('high')->toArray();
        $lows = $candles->pluck('low')->toArray();
        
        return [
            'support' => min($lows),
            'resistance' => max($highs),
        ];
    }
}
