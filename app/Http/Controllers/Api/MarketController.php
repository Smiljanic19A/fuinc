<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MarketController extends Controller
{
    /**
     * Get all markets
     */
    public function index(Request $request): JsonResponse
    {
        $query = Market::with(['baseCoin', 'quoteCoin']);

        // Apply filters
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('trading_enabled')) {
            $query->where('is_trading_enabled', $request->boolean('trading_enabled'));
        }

        if ($request->filled('symbol')) {
            $query->where('symbol', 'LIKE', '%' . $request->symbol . '%');
        }

        if ($request->filled('base_currency')) {
            $query->where('base_currency', $request->base_currency);
        }

        if ($request->filled('quote_currency')) {
            $query->where('quote_currency', $request->quote_currency);
        }

        $markets = $query->orderBy('volume_24h', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Markets retrieved successfully',
            'data' => $markets->map(function ($market) {
                return [
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
                    'is_active' => $market->is_active,
                    'is_trading_enabled' => $market->is_trading_enabled,
                    'icon_url' => $market->icon_url,
                    'base_coin' => $market->baseCoin ? [
                        'symbol' => $market->baseCoin->symbol,
                        'name' => $market->baseCoin->name,
                        'is_hot' => $market->baseCoin->is_hot,
                        'is_new' => $market->baseCoin->is_new,
                        'is_trending' => $market->baseCoin->is_trending,
                    ] : null,
                ];
            }),
            'count' => $markets->count(),
        ]);
    }

    /**
     * Get specific market by symbol
     */
    public function show(string $symbol): JsonResponse
    {
        $market = Market::with(['baseCoin', 'quoteCoin'])
            ->where('symbol', strtoupper($symbol))
            ->first();

        if (!$market) {
            return response()->json([
                'success' => false,
                'message' => 'Market not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Market details retrieved successfully',
            'data' => [
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
                'base_coin' => $market->baseCoin ? [
                    'id' => $market->baseCoin->id,
                    'symbol' => $market->baseCoin->symbol,
                    'name' => $market->baseCoin->name,
                    'full_name' => $market->baseCoin->full_name,
                    'current_price' => $market->baseCoin->current_price,
                    'market_cap' => $market->baseCoin->market_cap,
                    'is_hot' => $market->baseCoin->is_hot,
                    'is_new' => $market->baseCoin->is_new,
                    'is_trending' => $market->baseCoin->is_trending,
                    'category' => $market->baseCoin->category,
                    'icon_url' => $market->baseCoin->icon_url,
                ] : null,
                'quote_coin' => $market->quoteCoin ? [
                    'id' => $market->quoteCoin->id,
                    'symbol' => $market->quoteCoin->symbol,
                    'name' => $market->quoteCoin->name,
                    'full_name' => $market->quoteCoin->full_name,
                    'is_hot' => $market->quoteCoin->is_hot,
                ] : null,
            ],
        ]);
    }

    /**
     * Get market statistics
     */
    public function stats(): JsonResponse
    {
        $totalMarkets = Market::count();
        $activeMarkets = Market::where('is_active', true)->count();
        $totalVolume24h = Market::sum('volume_24h');
        $topGainers = Market::where('price_change_percentage_24h', '>', 0)
            ->orderBy('price_change_percentage_24h', 'desc')
            ->take(5)
            ->get(['symbol', 'display_name', 'price_change_percentage_24h']);
        $topLosers = Market::where('price_change_percentage_24h', '<', 0)
            ->orderBy('price_change_percentage_24h', 'asc')
            ->take(5)
            ->get(['symbol', 'display_name', 'price_change_percentage_24h']);

        return response()->json([
            'success' => true,
            'message' => 'Market statistics retrieved successfully',
            'data' => [
                'total_markets' => $totalMarkets,
                'active_markets' => $activeMarkets,
                'total_volume_24h' => $totalVolume24h,
                'top_gainers' => $topGainers,
                'top_losers' => $topLosers,
            ],
        ]);
    }

    /**
     * Get trending markets
     */
    public function trending(): JsonResponse
    {
        $trending = Market::with(['baseCoin'])
            ->where('is_active', true)
            ->whereHas('baseCoin', function ($query) {
                $query->where('is_trending', true);
            })
            ->orWhere('volume_24h', '>', 1000000000) // High volume markets
            ->orderBy('volume_24h', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Trending markets retrieved successfully',
            'data' => $trending->map(function ($market) {
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
                        'is_trending' => $market->baseCoin->is_trending,
                        'category' => $market->baseCoin->category,
                    ] : null,
                ];
            }),
        ]);
    }
}
