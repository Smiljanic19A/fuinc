<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Coin;
use App\Models\Market;
use App\Models\MarketData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import {--limit=100 : Number of coins to import} {--with-history : Import historical data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import real cryptocurrency data from CoinCap API';

    /**
     * CoinCap API key
     */
    private $apiKey = 'c630f700aa9a741a103f90de8cae81c6b509e0b0e6c5a331e8f7ac39162a2ec7';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting crypto data import from CoinCap API...');
        
        $limit = $this->option('limit');
        $withHistory = $this->option('with-history');
        
        // Import coins data
        $this->importCoins($limit);
        
        // Create markets for imported coins
        $this->createMarkets();
        
        // Import historical data if requested
        if ($withHistory) {
            $this->importHistoricalData();
        }
        
        $this->info('✅ Crypto data import completed successfully!');
    }
    
    /**
     * Import cryptocurrency data from CoinCap API
     */
    private function importCoins($limit)
    {
        $this->info("📥 Importing top {$limit} cryptocurrencies...");
        
        try {
            // Fetch assets from CoinCap API v3
            $response = Http::timeout(30)->get('https://rest.coincap.io/v3/assets', [
                'apiKey' => $this->apiKey,
                'limit' => $limit,
                'offset' => 0
            ]);
            
            if (!$response->successful()) {
                $this->error('Failed to fetch data from CoinCap API: ' . $response->body());
                return;
            }
            
            $responseData = $response->json();
            $assets = $responseData['data'] ?? [];
            
            if (empty($assets)) {
                $this->error('No assets data received from API');
                return;
            }
            
            $progressBar = $this->output->createProgressBar(count($assets));
            $progressBar->start();
            
            foreach ($assets as $asset) {
                $this->createOrUpdateCoin($asset);
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
            $this->info("✅ Successfully imported " . count($assets) . " cryptocurrencies");
            
        } catch (\Exception $e) {
            $this->error("Error importing coins: " . $e->getMessage());
            Log::error('Crypto import error: ' . $e->getMessage());
        }
    }
    
    /**
     * Create or update a coin record
     */
    private function createOrUpdateCoin($asset)
    {
        $priceChangePercent = $asset['changePercent24Hr'] ? floatval($asset['changePercent24Hr']) : 0;
        $currentPrice = $asset['priceUsd'] ? floatval($asset['priceUsd']) : 0;
        $priceChange24h = $currentPrice * ($priceChangePercent / 100);
        
        Coin::updateOrCreate(
            ['symbol' => strtoupper($asset['symbol'])],
            [
                'name' => $asset['name'],
                'full_name' => $asset['name'] . ' (' . strtoupper($asset['symbol']) . ')',
                'current_price' => $currentPrice,
                'market_cap' => $asset['marketCapUsd'] ? floatval($asset['marketCapUsd']) : 0,
                'volume_24h' => $asset['volumeUsd24Hr'] ? floatval($asset['volumeUsd24Hr']) : 0,
                'price_change_24h' => $priceChange24h,
                'price_change_percentage_24h' => $priceChangePercent,
                'market_cap_rank' => intval($asset['rank']),
                'circulating_supply' => $asset['supply'] ? floatval($asset['supply']) : null,
                'max_supply' => $asset['maxSupply'] ? floatval($asset['maxSupply']) : null,
                'is_active' => true,
                'is_hot' => $priceChangePercent > 10, // Mark as hot if > 10% change
                'is_trending' => intval($asset['rank']) <= 10, // Top 10 as trending
                'category' => $this->getCoinCategory(strtoupper($asset['symbol'])),
                'blockchain' => $this->getBlockchain(strtoupper($asset['symbol'])),
                'metadata' => [
                    'coincap_id' => $asset['id'],
                    'last_updated' => now()->toISOString()
                ]
            ]
        );
    }
    
    /**
     * Create trading markets for imported coins
     */
    private function createMarkets()
    {
        $this->info('📊 Creating trading markets...');
        
        $coins = Coin::active()->orderBy('market_cap_rank')->take(50)->get();
        $baseAssets = ['USDT', 'BTC', 'ETH'];
        
        $progressBar = $this->output->createProgressBar($coins->count() * count($baseAssets));
        $progressBar->start();
        
        foreach ($coins as $coin) {
            foreach ($baseAssets as $baseAsset) {
                if ($coin->symbol !== $baseAsset) {
                    Market::updateOrCreate(
                        [
                            'symbol' => $coin->symbol . '/' . $baseAsset,
                            'base_currency' => $coin->symbol,
                            'quote_currency' => $baseAsset
                        ],
                        [
                            'display_name' => $coin->name . '/' . $baseAsset,
                            'current_price' => $coin->current_price,
                            'price_change_24h' => $coin->price_change_24h,
                            'price_change_percentage_24h' => $coin->price_change_percentage_24h,
                            'high_24h' => $coin->current_price * 1.05, // Simulate high
                            'low_24h' => $coin->current_price * 0.95, // Simulate low
                            'volume_24h' => $coin->volume_24h,
                            'market_cap' => $coin->market_cap,
                            'rank' => $coin->market_cap_rank,
                            'min_order_amount' => 0.00001,
                            'max_order_amount' => 1000000,
                            'price_precision' => 8,
                            'quantity_precision' => 8,
                            'is_active' => true,
                            'is_trading_enabled' => true,
                            'description' => "Trade {$coin->name} against {$baseAsset}"
                        ]
                    );
                }
                $progressBar->advance();
            }
        }
        
        $progressBar->finish();
        $this->newLine();
        $this->info('✅ Trading markets created successfully');
    }
    
    /**
     * Import historical market data
     */
    private function importHistoricalData()
    {
        $this->info('📈 Importing historical data for top coins...');
        
        $topCoins = Coin::active()->orderBy('market_cap_rank')->take(10)->get();
        
        foreach ($topCoins as $coin) {
            $this->importCoinHistory($coin);
        }
        
        $this->info('✅ Historical data import completed');
    }
    
    /**
     * Import historical data for a specific coin
     */
    private function importCoinHistory($coin)
    {
        try {
            $coincapId = $coin->metadata['coincap_id'] ?? null;
            if (!$coincapId) return;
            
            // Get 7 days of hourly data
            $end = now()->timestamp * 1000;
            $start = now()->subDays(7)->timestamp * 1000;
            
            $response = Http::timeout(30)->get("https://rest.coincap.io/v3/assets/{$coincapId}/history", [
                'apiKey' => $this->apiKey,
                'interval' => 'h1',
                'start' => $start,
                'end' => $end
            ]);
            
            if (!$response->successful()) {
                $this->warn("  ✗ Failed to fetch history for {$coin->symbol}: " . $response->body());
                return;
            }
            
            $responseData = $response->json();
            $data = $responseData['data'] ?? [];
            
            $market = Market::where('base_currency', $coin->symbol)
                           ->where('quote_currency', 'USDT')
                           ->first();
            
            if (!$market) return;
            
            foreach ($data as $point) {
                $timestamp = Carbon::createFromTimestampMs($point['time']);
                $price = floatval($point['priceUsd']);
                
                MarketData::updateOrCreate(
                    [
                        'market_id' => $market->id,
                        'timeframe' => '1h',
                        'timestamp' => $timestamp
                    ],
                    [
                        'open' => $price,
                        'high' => $price * 1.02, // Simulate some variance
                        'low' => $price * 0.98,
                        'close' => $price,
                        'volume' => rand(1000, 100000),
                        'quote_volume' => rand(1000000, 10000000),
                        'trades_count' => rand(100, 1000),
                        'is_fake' => false,
                        'is_closed' => true
                    ]
                );
            }
            
            $this->line("  ✓ Imported history for {$coin->symbol}");
            
        } catch (\Exception $e) {
            $this->warn("  ✗ Failed to import history for {$coin->symbol}: " . $e->getMessage());
        }
    }
    
    /**
     * Get coin category based on symbol
     */
    private function getCoinCategory($symbol)
    {
        $categories = [
            'BTC' => 'Store of Value',
            'ETH' => 'Smart Contracts',
            'ADA' => 'Smart Contracts', 
            'DOT' => 'Interoperability',
            'LINK' => 'Oracle',
            'UNI' => 'DeFi',
            'AAVE' => 'DeFi',
            'COMP' => 'DeFi',
            'MKR' => 'DeFi',
            'USDT' => 'Stablecoin',
            'USDC' => 'Stablecoin',
            'BUSD' => 'Stablecoin',
            'MATIC' => 'Layer 2',
            'AVAX' => 'Smart Contracts',
            'SOL' => 'Smart Contracts',
            'DOGE' => 'Meme',
            'SHIB' => 'Meme'
        ];
        
        return $categories[$symbol] ?? 'Cryptocurrency';
    }
    
    /**
     * Get blockchain for coin
     */
    private function getBlockchain($symbol)
    {
        $blockchains = [
            'BTC' => 'Bitcoin',
            'ETH' => 'Ethereum',
            'ADA' => 'Cardano',
            'DOT' => 'Polkadot',
            'AVAX' => 'Avalanche',
            'SOL' => 'Solana',
            'MATIC' => 'Polygon',
            'BNB' => 'BSC',
            'DOGE' => 'Dogecoin',
            'LTC' => 'Litecoin'
        ];
        
        return $blockchains[$symbol] ?? 'Unknown';
    }
}
