<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CoinController extends Controller
{
    /**
     * Get all coins
     */
    public function index(Request $request): JsonResponse
    {
        $query = Coin::query();

        // Apply filters
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('hot_only')) {
            $query->where('is_hot', $request->boolean('hot_only'));
        }

        if ($request->has('new_only')) {
            $query->where('is_new', $request->boolean('new_only'));
        }

        if ($request->has('trending_only')) {
            $query->where('is_trending', $request->boolean('trending_only'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('blockchain')) {
            $query->where('blockchain', $request->blockchain);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('symbol', 'LIKE', "%{$search}%")
                  ->orWhere('full_name', 'LIKE', "%{$search}%");
            });
        }

        $limit = min($request->input('limit', 50), 100);
        $coins = $query->orderBy('market_cap_rank', 'asc')
                      ->take($limit)
                      ->get();

        return response()->json([
            'success' => true,
            'message' => 'Coins retrieved successfully',
            'data' => $coins->map(function ($coin) {
                return [
                    'id' => $coin->id,
                    'symbol' => $coin->symbol,
                    'name' => $coin->name,
                    'full_name' => $coin->full_name,
                    'description' => $coin->description,
                    'current_price' => $coin->current_price,
                    'market_cap' => $coin->market_cap,
                    'volume_24h' => $coin->volume_24h,
                    'price_change_24h' => $coin->price_change_24h,
                    'price_change_percentage_24h' => $coin->price_change_percentage_24h,
                    'market_cap_rank' => $coin->market_cap_rank,
                    'circulating_supply' => $coin->circulating_supply,
                    'total_supply' => $coin->total_supply,
                    'max_supply' => $coin->max_supply,
                    'is_hot' => $coin->is_hot,
                    'is_new' => $coin->is_new,
                    'is_trending' => $coin->is_trending,
                    'category' => $coin->category,
                    'blockchain' => $coin->blockchain,
                    'tags' => $coin->tags,
                    'icon_url' => $coin->icon_url,
                    'website_url' => $coin->website_url,
                    'launched_at' => $coin->launched_at,
                ];
            }),
            'count' => $coins->count(),
            'filters_applied' => [
                'active' => $request->boolean('active'),
                'hot_only' => $request->boolean('hot_only'),
                'new_only' => $request->boolean('new_only'),
                'trending_only' => $request->boolean('trending_only'),
                'category' => $request->category,
                'blockchain' => $request->blockchain,
            ],
        ]);
    }

    /**
     * Get hot coins (is_hot = 1)
     */
    public function hot(): JsonResponse
    {
        $hotCoins = Coin::active()
                       ->hot()
                       ->orderedByRank()
                       ->get();

        return response()->json([
            'success' => true,
            'message' => 'Hot coins retrieved successfully',
            'data' => $hotCoins->map(function ($coin) {
                return [
                    'id' => $coin->id,
                    'symbol' => $coin->symbol,
                    'name' => $coin->name,
                    'full_name' => $coin->full_name,
                    'current_price' => $coin->current_price,
                    'price_change_percentage_24h' => $coin->price_change_percentage_24h,
                    'market_cap' => $coin->market_cap,
                    'volume_24h' => $coin->volume_24h,
                    'market_cap_rank' => $coin->market_cap_rank,
                    'category' => $coin->category,
                    'icon_url' => $coin->icon_url,
                    'is_hot' => $coin->is_hot,
                    'tags' => $coin->tags,
                ];
            }),
            'count' => $hotCoins->count(),
        ]);
    }

    /**
     * Get new coins (is_new = 1)
     */
    public function new(): JsonResponse
    {
        $newCoins = Coin::active()
                       ->new()
                       ->orderBy('launched_at', 'desc')
                       ->take(20)
                       ->get();

        return response()->json([
            'success' => true,
            'message' => 'New coins retrieved successfully',
            'data' => $newCoins->map(function ($coin) {
                return [
                    'id' => $coin->id,
                    'symbol' => $coin->symbol,
                    'name' => $coin->name,
                    'full_name' => $coin->full_name,
                    'current_price' => $coin->current_price,
                    'price_change_percentage_24h' => $coin->price_change_percentage_24h,
                    'market_cap' => $coin->market_cap,
                    'volume_24h' => $coin->volume_24h,
                    'category' => $coin->category,
                    'icon_url' => $coin->icon_url,
                    'is_new' => $coin->is_new,
                    'launched_at' => $coin->launched_at,
                ];
            }),
            'count' => $newCoins->count(),
        ]);
    }

    /**
     * Get trending coins (is_trending = 1)
     */
    public function trending(): JsonResponse
    {
        $trendingCoins = Coin::active()
                            ->trending()
                            ->orderBy('volume_24h', 'desc')
                            ->take(20)
                            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Trending coins retrieved successfully',
            'data' => $trendingCoins->map(function ($coin) {
                return [
                    'id' => $coin->id,
                    'symbol' => $coin->symbol,
                    'name' => $coin->name,
                    'current_price' => $coin->current_price,
                    'price_change_percentage_24h' => $coin->price_change_percentage_24h,
                    'volume_24h' => $coin->volume_24h,
                    'category' => $coin->category,
                    'icon_url' => $coin->icon_url,
                    'is_trending' => $coin->is_trending,
                ];
            }),
            'count' => $trendingCoins->count(),
        ]);
    }

    /**
     * Get coin details by ID or symbol
     */
    public function show(string $identifier): JsonResponse
    {
        $coin = Coin::where('id', $identifier)
                   ->orWhere('symbol', strtoupper($identifier))
                   ->first();

        if (!$coin) {
            return response()->json([
                'success' => false,
                'message' => 'Coin not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coin details retrieved successfully',
            'data' => [
                'id' => $coin->id,
                'symbol' => $coin->symbol,
                'name' => $coin->name,
                'full_name' => $coin->full_name,
                'description' => $coin->description,
                'current_price' => $coin->current_price,
                'market_cap' => $coin->market_cap,
                'volume_24h' => $coin->volume_24h,
                'price_change_24h' => $coin->price_change_24h,
                'price_change_percentage_24h' => $coin->price_change_percentage_24h,
                'market_cap_rank' => $coin->market_cap_rank,
                'circulating_supply' => $coin->circulating_supply,
                'total_supply' => $coin->total_supply,
                'max_supply' => $coin->max_supply,
                'is_active' => $coin->is_active,
                'is_hot' => $coin->is_hot,
                'is_new' => $coin->is_new,
                'is_trending' => $coin->is_trending,
                'category' => $coin->category,
                'blockchain' => $coin->blockchain,
                'tags' => $coin->tags,
                'icon_url' => $coin->icon_url,
                'website_url' => $coin->website_url,
                'whitepaper_url' => $coin->whitepaper_url,
                'launched_at' => $coin->launched_at,
                'markets' => $coin->markets->map(function ($market) {
                    return [
                        'id' => $market->id,
                        'symbol' => $market->symbol,
                        'display_name' => $market->display_name,
                        'current_price' => $market->current_price,
                        'volume_24h' => $market->volume_24h,
                        'is_active' => $market->is_active,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get categories
     */
    public function categories(): JsonResponse
    {
        $categories = Coin::active()
                         ->whereNotNull('category')
                         ->groupBy('category')
                         ->selectRaw('category, COUNT(*) as coin_count')
                         ->orderBy('coin_count', 'desc')
                         ->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories->map(function ($category) {
                return [
                    'name' => $category->category,
                    'coin_count' => $category->coin_count,
                ];
            }),
        ]);
    }

    /**
     * SuperAdmin: Update hot coin status
     */
    public function updateHotStatus(Request $request): JsonResponse
    {
        $request->validate([
            'coin_id' => 'required|exists:coins,id',
            'is_hot' => 'required|boolean',
        ]);

        $coin = Coin::findOrFail($request->coin_id);
        
        if ($request->is_hot) {
            $coin->makeHot();
        } else {
            $coin->removeHot();
        }

        return response()->json([
            'success' => true,
            'message' => 'Hot coin status updated successfully',
            'data' => [
                'id' => $coin->id,
                'symbol' => $coin->symbol,
                'name' => $coin->name,
                'is_hot' => $coin->is_hot,
            ],
        ]);
    }

    /**
     * SuperAdmin: Bulk update hot coins
     */
    public function bulkUpdateHot(Request $request): JsonResponse
    {
        $request->validate([
            'coin_ids' => 'required|array',
            'coin_ids.*' => 'exists:coins,id',
            'is_hot' => 'required|boolean',
        ]);

        $coins = Coin::whereIn('id', $request->coin_ids)->get();
        
        foreach ($coins as $coin) {
            if ($request->is_hot) {
                $coin->makeHot();
            } else {
                $coin->removeHot();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Hot coin status updated for ' . $coins->count() . ' coins',
            'data' => $coins->map(function ($coin) {
                return [
                    'id' => $coin->id,
                    'symbol' => $coin->symbol,
                    'name' => $coin->name,
                    'is_hot' => $coin->is_hot,
                ];
            }),
        ]);
    }
}
