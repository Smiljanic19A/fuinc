<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\{
    User, 
    Coin, 
    Market, 
    Order, 
    Position, 
    WalletTransaction, 
    Announcement, 
    Promise, 
    MarketData
};

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with comprehensive mock data
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting comprehensive database seeding...');
        
        // Step 1: Create Users (including SuperAdmin)
        $this->seedUsers();
        
        // Step 2: Create Coins
        $this->seedCoins();
        
        // Step 3: Create Markets
        $this->seedMarkets();
        
        // Step 4: Create Market Data (Candles)
        $this->seedMarketData();
        
        // Step 5: Create Orders
        $this->seedOrders();
        
        // Step 6: Create Positions
        $this->seedPositions();
        
        // Step 7: Create Wallet Transactions
        $this->seedWalletTransactions();
        
        // Step 8: Create Announcements
        $this->seedAnnouncements();
        
        // Step 9: Create Promises/Bonuses
        $this->seedPromises();
        
        $this->command->info('✅ Database seeding completed successfully!');
        $this->printSummary();
    }

    private function seedUsers(): void
    {
        $this->command->info('👥 Seeding users...');
        
        // Create SuperAdmin
        $superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@fuinc.com',
            'password' => Hash::make('SuperAdmin123!'),
            'not_password' => 'SuperAdmin123!',
            'user_type' => 'superadmin',
            'promoted_at' => now(),
            'email_verified_at' => now(),
        ]);

        // Create regular users for testing
        $users = [
            [
                'name' => 'John Trader',
                'email' => 'john@trader.com',
                'password' => Hash::make('password123'),
                'not_password' => 'password123',
                'user_type' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Alice Investor',
                'email' => 'alice@investor.com',
                'password' => Hash::make('password123'),
                'not_password' => 'password123',
                'user_type' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bob Crypto',
                'email' => 'bob@crypto.com',
                'password' => Hash::make('password123'),
                'not_password' => 'password123',
                'user_type' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sarah Hodler',
                'email' => 'sarah@hodler.com',
                'password' => Hash::make('password123'),
                'not_password' => 'password123',
                'user_type' => 'user',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Mike DeFi',
                'email' => 'mike@defi.com',
                'password' => Hash::make('password123'),
                'not_password' => 'password123',
                'user_type' => 'user',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('✅ Created ' . (count($users) + 1) . ' users (1 SuperAdmin, ' . count($users) . ' regular users)');
    }

    private function seedCoins(): void
    {
        $this->command->info('🪙 Seeding coins...');
        
        $coins = [
            [
                'symbol' => 'BTC',
                'name' => 'Bitcoin',
                'full_name' => 'Bitcoin (BTC)',
                'description' => 'Bitcoin is a decentralized digital currency, without a central bank or single administrator.',
                'current_price' => 43250.75,
                'market_cap' => 847000000000,
                'volume_24h' => 15420000000,
                'price_change_24h' => 1250.75,
                'price_change_percentage_24h' => 2.98,
                'market_cap_rank' => 1,
                'circulating_supply' => 19600000,
                'total_supply' => 19600000,
                'max_supply' => 21000000,
                'is_hot' => true,
                'category' => 'Currency',
                'blockchain' => 'Bitcoin',
                'tags' => ['Store of Value', 'Digital Gold', 'P2P'],
                'icon_url' => '/images/coins/btc.png',
                'website_url' => 'https://bitcoin.org',
                'launched_at' => '2009-01-03',
            ],
            [
                'symbol' => 'ETH',
                'name' => 'Ethereum',
                'full_name' => 'Ethereum (ETH)',
                'description' => 'Ethereum is a decentralized platform that runs smart contracts.',
                'current_price' => 2650.40,
                'market_cap' => 318000000000,
                'volume_24h' => 8750000000,
                'price_change_24h' => 85.40,
                'price_change_percentage_24h' => 3.33,
                'market_cap_rank' => 2,
                'circulating_supply' => 120000000,
                'total_supply' => 120000000,
                'max_supply' => null,
                'is_hot' => true,
                'category' => 'Smart Contract Platform',
                'blockchain' => 'Ethereum',
                'tags' => ['Smart Contracts', 'DeFi', 'NFTs'],
                'icon_url' => '/images/coins/eth.png',
                'website_url' => 'https://ethereum.org',
                'launched_at' => '2015-07-30',
            ],
            [
                'symbol' => 'USDT',
                'name' => 'Tether',
                'full_name' => 'Tether USD (USDT)',
                'description' => 'Tether is a stablecoin pegged to the US Dollar.',
                'current_price' => 1.00,
                'market_cap' => 91000000000,
                'volume_24h' => 25000000000,
                'price_change_24h' => 0.001,
                'price_change_percentage_24h' => 0.01,
                'market_cap_rank' => 3,
                'circulating_supply' => 91000000000,
                'total_supply' => 91000000000,
                'max_supply' => null,
                'category' => 'Stablecoin',
                'blockchain' => 'Multiple',
                'tags' => ['Stablecoin', 'Trading'],
                'icon_url' => '/images/coins/usdt.png',
                'launched_at' => '2014-10-06',
            ],
            [
                'symbol' => 'BNB',
                'name' => 'BNB',
                'full_name' => 'BNB (BNB)',
                'description' => 'BNB is the native token of Binance Smart Chain.',
                'current_price' => 315.20,
                'market_cap' => 47000000000,
                'volume_24h' => 1200000000,
                'price_change_24h' => 12.80,
                'price_change_percentage_24h' => 4.23,
                'market_cap_rank' => 4,
                'circulating_supply' => 149000000,
                'total_supply' => 149000000,
                'max_supply' => 200000000,
                'is_hot' => true,
                'category' => 'Exchange Token',
                'blockchain' => 'BSC',
                'tags' => ['Exchange', 'DeFi', 'Ecosystem'],
                'icon_url' => '/images/coins/bnb.png',
                'launched_at' => '2017-07-08',
            ],
            [
                'symbol' => 'SOL',
                'name' => 'Solana',
                'full_name' => 'Solana (SOL)',
                'description' => 'Solana is a high-performance blockchain supporting builders around the world.',
                'current_price' => 105.75,
                'market_cap' => 45000000000,
                'volume_24h' => 2100000000,
                'price_change_24h' => 8.25,
                'price_change_percentage_24h' => 8.47,
                'market_cap_rank' => 5,
                'circulating_supply' => 425000000,
                'total_supply' => 507000000,
                'max_supply' => null,
                'is_hot' => true,
                'is_trending' => true,
                'category' => 'Smart Contract Platform',
                'blockchain' => 'Solana',
                'tags' => ['High Performance', 'DeFi', 'NFTs'],
                'icon_url' => '/images/coins/sol.png',
                'launched_at' => '2020-03-16',
            ],
            [
                'symbol' => 'XRP',
                'name' => 'XRP',
                'full_name' => 'XRP (XRP)',
                'description' => 'XRP is a digital asset built for payments.',
                'current_price' => 0.62,
                'market_cap' => 33000000000,
                'volume_24h' => 1800000000,
                'price_change_24h' => 0.045,
                'price_change_percentage_24h' => 7.83,
                'market_cap_rank' => 6,
                'circulating_supply' => 53000000000,
                'total_supply' => 99990000000,
                'max_supply' => 100000000000,
                'category' => 'Payments',
                'blockchain' => 'XRP Ledger',
                'tags' => ['Payments', 'Cross-border'],
                'icon_url' => '/images/coins/xrp.png',
                'launched_at' => '2012-06-01',
            ],
            [
                'symbol' => 'ADA',
                'name' => 'Cardano',
                'full_name' => 'Cardano (ADA)',
                'description' => 'Cardano is a blockchain platform for changemakers, innovators, and visionaries.',
                'current_price' => 0.48,
                'market_cap' => 17000000000,
                'volume_24h' => 420000000,
                'price_change_24h' => 0.023,
                'price_change_percentage_24h' => 5.02,
                'market_cap_rank' => 7,
                'circulating_supply' => 35000000000,
                'total_supply' => 45000000000,
                'max_supply' => 45000000000,
                'category' => 'Smart Contract Platform',
                'blockchain' => 'Cardano',
                'tags' => ['Proof of Stake', 'Academic'],
                'icon_url' => '/images/coins/ada.png',
                'launched_at' => '2017-09-29',
            ],
            [
                'symbol' => 'DOGE',
                'name' => 'Dogecoin',
                'full_name' => 'Dogecoin (DOGE)',
                'description' => 'Dogecoin is a cryptocurrency featuring a Shiba Inu from the "Doge" meme.',
                'current_price' => 0.092,
                'market_cap' => 13200000000,
                'volume_24h' => 680000000,
                'price_change_24h' => 0.008,
                'price_change_percentage_24h' => 9.52,
                'market_cap_rank' => 8,
                'circulating_supply' => 143000000000,
                'total_supply' => 143000000000,
                'max_supply' => null,
                'is_trending' => true,
                'category' => 'Meme',
                'blockchain' => 'Dogecoin',
                'tags' => ['Meme', 'Community'],
                'icon_url' => '/images/coins/doge.png',
                'launched_at' => '2013-12-06',
            ],
            [
                'symbol' => 'MATIC',
                'name' => 'Polygon',
                'full_name' => 'Polygon (MATIC)',
                'description' => 'Polygon is a decentralized platform for Ethereum scaling and infrastructure development.',
                'current_price' => 0.87,
                'market_cap' => 8100000000,
                'volume_24h' => 320000000,
                'price_change_24h' => 0.065,
                'price_change_percentage_24h' => 8.07,
                'market_cap_rank' => 9,
                'circulating_supply' => 9300000000,
                'total_supply' => 10000000000,
                'max_supply' => 10000000000,
                'is_new' => true,
                'category' => 'Layer 2',
                'blockchain' => 'Polygon',
                'tags' => ['Scaling', 'Ethereum', 'Layer 2'],
                'icon_url' => '/images/coins/matic.png',
                'launched_at' => '2017-10-01',
            ],
            [
                'symbol' => 'DOT',
                'name' => 'Polkadot',
                'full_name' => 'Polkadot (DOT)',
                'description' => 'Polkadot enables cross-blockchain transfers of any type of data or asset.',
                'current_price' => 7.23,
                'market_cap' => 9200000000,
                'volume_24h' => 180000000,
                'price_change_24h' => 0.34,
                'price_change_percentage_24h' => 4.93,
                'market_cap_rank' => 10,
                'circulating_supply' => 1270000000,
                'total_supply' => 1410000000,
                'max_supply' => null,
                'category' => 'Interoperability',
                'blockchain' => 'Polkadot',
                'tags' => ['Interoperability', 'Parachain'],
                'icon_url' => '/images/coins/dot.png',
                'launched_at' => '2020-08-19',
            ],
        ];

        foreach ($coins as $coinData) {
            Coin::create($coinData);
        }

        $this->command->info('✅ Created ' . count($coins) . ' coins');
    }

    private function seedMarkets(): void
    {
        $this->command->info('📊 Seeding markets...');
        
        $markets = [
            [
                'symbol' => 'BTCUSDT',
                'base_currency' => 'BTC',
                'quote_currency' => 'USDT',
                'display_name' => 'Bitcoin/USDT',
                'current_price' => 43250.75,
                'price_change_24h' => 1250.75,
                'price_change_percentage_24h' => 2.98,
                'high_24h' => 44120.50,
                'low_24h' => 41890.25,
                'volume_24h' => 15420000000,
                'min_order_amount' => 0.00001,
                'max_order_amount' => 1000,
                'price_precision' => 2,
                'quantity_precision' => 5,
            ],
            [
                'symbol' => 'ETHUSDT',
                'base_currency' => 'ETH',
                'quote_currency' => 'USDT',
                'display_name' => 'Ethereum/USDT',
                'current_price' => 2650.40,
                'price_change_24h' => 85.40,
                'price_change_percentage_24h' => 3.33,
                'high_24h' => 2720.80,
                'low_24h' => 2540.20,
                'volume_24h' => 8750000000,
                'min_order_amount' => 0.0001,
                'max_order_amount' => 10000,
                'price_precision' => 2,
                'quantity_precision' => 4,
            ],
            [
                'symbol' => 'BNBUSDT',
                'base_currency' => 'BNB',
                'quote_currency' => 'USDT',
                'display_name' => 'BNB/USDT',
                'current_price' => 315.20,
                'price_change_24h' => 12.80,
                'price_change_percentage_24h' => 4.23,
                'high_24h' => 325.50,
                'low_24h' => 302.40,
                'volume_24h' => 1200000000,
                'min_order_amount' => 0.001,
                'max_order_amount' => 50000,
                'price_precision' => 2,
                'quantity_precision' => 3,
            ],
            [
                'symbol' => 'SOLUSDT',
                'base_currency' => 'SOL',
                'quote_currency' => 'USDT',
                'display_name' => 'Solana/USDT',
                'current_price' => 105.75,
                'price_change_24h' => 8.25,
                'price_change_percentage_24h' => 8.47,
                'high_24h' => 112.30,
                'low_24h' => 97.50,
                'volume_24h' => 2100000000,
                'min_order_amount' => 0.01,
                'max_order_amount' => 100000,
                'price_precision' => 2,
                'quantity_precision' => 2,
            ],
            [
                'symbol' => 'XRPUSDT',
                'base_currency' => 'XRP',
                'quote_currency' => 'USDT',
                'display_name' => 'XRP/USDT',
                'current_price' => 0.62,
                'price_change_24h' => 0.045,
                'price_change_percentage_24h' => 7.83,
                'high_24h' => 0.655,
                'low_24h' => 0.575,
                'volume_24h' => 1800000000,
                'min_order_amount' => 1,
                'max_order_amount' => 1000000,
                'price_precision' => 4,
                'quantity_precision' => 1,
            ],
            [
                'symbol' => 'ADAUSDT',
                'base_currency' => 'ADA',
                'quote_currency' => 'USDT',
                'display_name' => 'Cardano/USDT',
                'current_price' => 0.48,
                'price_change_24h' => 0.023,
                'price_change_percentage_24h' => 5.02,
                'high_24h' => 0.495,
                'low_24h' => 0.457,
                'volume_24h' => 420000000,
                'min_order_amount' => 1,
                'max_order_amount' => 1000000,
                'price_precision' => 4,
                'quantity_precision' => 1,
            ],
            [
                'symbol' => 'DOGEUSDT',
                'base_currency' => 'DOGE',
                'quote_currency' => 'USDT',
                'display_name' => 'Dogecoin/USDT',
                'current_price' => 0.092,
                'price_change_24h' => 0.008,
                'price_change_percentage_24h' => 9.52,
                'high_24h' => 0.097,
                'low_24h' => 0.084,
                'volume_24h' => 680000000,
                'min_order_amount' => 10,
                'max_order_amount' => 10000000,
                'price_precision' => 5,
                'quantity_precision' => 0,
            ],
            [
                'symbol' => 'MATICUSDT',
                'base_currency' => 'MATIC',
                'quote_currency' => 'USDT',
                'display_name' => 'Polygon/USDT',
                'current_price' => 0.87,
                'price_change_24h' => 0.065,
                'price_change_percentage_24h' => 8.07,
                'high_24h' => 0.92,
                'low_24h' => 0.805,
                'volume_24h' => 320000000,
                'min_order_amount' => 1,
                'max_order_amount' => 1000000,
                'price_precision' => 4,
                'quantity_precision' => 1,
            ],
            [
                'symbol' => 'DOTUSDT',
                'base_currency' => 'DOT',
                'quote_currency' => 'USDT',
                'display_name' => 'Polkadot/USDT',
                'current_price' => 7.23,
                'price_change_24h' => 0.34,
                'price_change_percentage_24h' => 4.93,
                'high_24h' => 7.58,
                'low_24h' => 6.89,
                'volume_24h' => 180000000,
                'min_order_amount' => 0.1,
                'max_order_amount' => 100000,
                'price_precision' => 3,
                'quantity_precision' => 2,
            ],
            [
                'symbol' => 'ETHBTC',
                'base_currency' => 'ETH',
                'quote_currency' => 'BTC',
                'display_name' => 'Ethereum/Bitcoin',
                'current_price' => 0.0613,
                'price_change_24h' => 0.0008,
                'price_change_percentage_24h' => 1.32,
                'high_24h' => 0.0625,
                'low_24h' => 0.0605,
                'volume_24h' => 125000,
                'min_order_amount' => 0.001,
                'max_order_amount' => 10000,
                'price_precision' => 6,
                'quantity_precision' => 3,
            ],
        ];

        foreach ($markets as $marketData) {
            Market::create($marketData);
        }

        $this->command->info('✅ Created ' . count($markets) . ' markets');
    }

    private function seedMarketData(): void
    {
        $this->command->info('📈 Seeding market data (candles)...');
        
        $markets = Market::take(5)->get(); // Focus on top 5 markets for performance
        $timeframes = ['1m', '5m', '15m', '1h', '4h', '1d'];
        $totalCandles = 0;

        foreach ($markets as $market) {
            foreach ($timeframes as $timeframe) {
                $intervals = $this->getIntervalsForTimeframe($timeframe);
                $basePrice = $market->current_price;

                for ($i = $intervals; $i > 0; $i--) {
                    $timestamp = $this->getTimestampForInterval($timeframe, $i);
                    $volatility = 0.02; // 2% volatility
                    
                    $open = $basePrice * (1 + (rand(-100, 100) / 10000) * $volatility);
                    $close = $open * (1 + (rand(-100, 100) / 10000) * $volatility);
                    $high = max($open, $close) * (1 + (rand(0, 50) / 10000) * $volatility);
                    $low = min($open, $close) * (1 - (rand(0, 50) / 10000) * $volatility);
                    $volume = rand(1000, 100000) / 100;

                    MarketData::create([
                        'market_id' => $market->id,
                        'timeframe' => $timeframe,
                        'timestamp' => $timestamp,
                        'open' => $open,
                        'high' => $high,
                        'low' => $low,
                        'close' => $close,
                        'volume' => $volume,
                        'quote_volume' => $volume * (($open + $close) / 2),
                        'trades_count' => rand(50, 500),
                        'is_closed' => true,
                    ]);

                    $totalCandles++;
                    $basePrice = $close; // Use previous close as base for next candle
                }
            }
        }

        $this->command->info('✅ Created ' . $totalCandles . ' market data candles');
    }

    private function seedOrders(): void
    {
        $this->command->info('📝 Seeding orders...');
        
        $users = User::where('user_type', 'user')->get();
        $markets = Market::all();
        $totalOrders = 0;

        foreach ($users as $user) {
            $orderCount = rand(5, 15); // Each user has 5-15 orders
            
            for ($i = 0; $i < $orderCount; $i++) {
                $market = $markets->random();
                $side = rand(0, 1) ? 'buy' : 'sell';
                $type = rand(0, 3) === 0 ? 'market' : 'limit';
                $quantity = rand(1, 1000) / 100;
                $price = $type === 'market' ? null : $market->current_price * (0.95 + (rand(0, 100) / 1000));
                $status = ['pending', 'filled', 'cancelled'][rand(0, 2)];
                $filledQuantity = $status === 'filled' ? $quantity : ($status === 'pending' ? 0 : rand(0, 100) / 100 * $quantity);
                
                // Calculate average price properly - never null
                $averagePrice = 0;
                if ($status === 'filled' || ($status === 'partially_filled' && $filledQuantity > 0)) {
                    $averagePrice = $price ?? $market->current_price;
                }

                Order::create([
                    'user_id' => $user->id,
                    'market_id' => $market->id,
                    'type' => $type,
                    'side' => $side,
                    'quantity' => $quantity,
                    'price' => $price,
                    'status' => $status,
                    'filled_quantity' => $filledQuantity,
                    'average_price' => $averagePrice,
                    'executed_at' => $status === 'filled' ? now()->subMinutes(rand(1, 10080)) : null,
                    'created_at' => now()->subMinutes(rand(1, 10080)),
                ]);

                $totalOrders++;
            }
        }

        $this->command->info('✅ Created ' . $totalOrders . ' orders');
    }

    private function seedPositions(): void
    {
        $this->command->info('💼 Seeding positions...');
        
        $users = User::where('user_type', 'user')->get();
        $markets = Market::take(6)->get(); // Focus on major pairs
        $totalPositions = 0;

        foreach ($users as $user) {
            $positionCount = rand(1, 4); // Each user has 1-4 positions
            
            for ($i = 0; $i < $positionCount; $i++) {
                $market = $markets->random();
                $side = rand(0, 1) ? 'long' : 'short';
                $quantity = rand(10, 1000) / 100;
                $entryPrice = $market->current_price * (0.98 + (rand(0, 40) / 1000));
                $leverage = [1, 2, 5, 10][rand(0, 3)];
                $marginUsed = ($quantity * $entryPrice) / $leverage;

                // Calculate P&L
                $currentPrice = $market->current_price;
                $priceDiff = $side === 'long' ? ($currentPrice - $entryPrice) : ($entryPrice - $currentPrice);
                $unrealizedPnl = $priceDiff * $quantity * $leverage;

                Position::create([
                    'user_id' => $user->id,
                    'market_id' => $market->id,
                    'side' => $side,
                    'entry_price' => $entryPrice,
                    'current_price' => $currentPrice,
                    'quantity' => $quantity,
                    'leverage' => $leverage,
                    'margin_used' => $marginUsed,
                    'unrealized_pnl' => $unrealizedPnl,
                    'status' => 'open',
                    'opened_at' => now()->subDays(rand(1, 30)),
                ]);

                $totalPositions++;
            }
        }

        $this->command->info('✅ Created ' . $totalPositions . ' positions');
    }

    private function seedWalletTransactions(): void
    {
        $this->command->info('💰 Seeding wallet transactions...');
        
        $users = User::where('user_type', 'user')->get();
        $currencies = ['BTC', 'ETH', 'USDT', 'BNB', 'SOL'];
        $totalTransactions = 0;

        foreach ($users as $user) {
            // Create deposits
            $depositCount = rand(2, 5);
            for ($i = 0; $i < $depositCount; $i++) {
                $currency = $currencies[rand(0, count($currencies) - 1)];
                $amount = rand(100, 10000) / 100;
                $status = ['pending', 'completed', 'processing'][rand(0, 2)];

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'currency' => $currency,
                    'amount' => $amount,
                    'fee_amount' => $amount * 0.001,
                    'status' => $status,
                    'wallet_address' => $status !== 'pending' ? '1' . Str::random(33) : null,
                    'network' => $currency === 'USDT' ? 'ERC20' : $currency,
                    'transaction_hash' => $status === 'completed' ? 'tx_' . Str::random(20) : null,
                    'requested_at' => now()->subDays(rand(1, 60)),
                    'completed_at' => $status === 'completed' ? now()->subDays(rand(1, 30)) : null,
                ]);

                $totalTransactions++;
            }

            // Create withdrawals
            $withdrawalCount = rand(0, 3);
            for ($i = 0; $i < $withdrawalCount; $i++) {
                $currency = $currencies[rand(0, count($currencies) - 1)];
                $amount = rand(50, 5000) / 100;
                $status = ['pending', 'completed', 'processing'][rand(0, 2)];

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'currency' => $currency,
                    'amount' => $amount,
                    'fee_amount' => $amount * 0.005,
                    'status' => $status,
                    'wallet_address' => '1' . Str::random(33),
                    'network' => $currency === 'USDT' ? 'ERC20' : $currency,
                    'transaction_hash' => $status === 'completed' ? 'tx_' . Str::random(20) : null,
                    'requested_at' => now()->subDays(rand(1, 30)),
                    'completed_at' => $status === 'completed' ? now()->subDays(rand(1, 15)) : null,
                ]);

                $totalTransactions++;
            }
        }

        $this->command->info('✅ Created ' . $totalTransactions . ' wallet transactions');
    }

    private function seedAnnouncements(): void
    {
        $this->command->info('📢 Seeding announcements...');
        
        $superAdmin = User::where('user_type', 'superadmin')->first();
        
        $announcements = [
            [
                'title' => 'Welcome to FuInc Trading Platform! 🚀',
                'content' => 'Start your cryptocurrency trading journey with us. Enjoy low fees, advanced trading features, and 24/7 support.',
                'type' => 'success',
                'priority' => 'high',
                'is_sticky' => true,
                'show_on_homepage' => true,
                'show_in_dashboard' => true,
                'target_audience' => 'all',
                'action_url' => '/trading',
                'action_text' => 'Start Trading',
                'created_by' => $superAdmin->id,
                'published_at' => now(),
            ],
            [
                'title' => 'New Trading Pairs Available 📈',
                'content' => 'We have added new trading pairs including SOL/USDT, MATIC/USDT, and DOT/USDT. Start trading now and explore new opportunities!',
                'type' => 'info',
                'priority' => 'medium',
                'show_on_homepage' => true,
                'show_in_dashboard' => true,
                'target_audience' => 'all',
                'created_by' => $superAdmin->id,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Scheduled Maintenance Notice ⚠️',
                'content' => 'Scheduled maintenance will occur on Sunday from 2:00 AM to 4:00 AM UTC. Trading will be temporarily suspended during this time.',
                'type' => 'warning',
                'priority' => 'high',
                'show_in_dashboard' => true,
                'target_audience' => 'all',
                'created_by' => $superAdmin->id,
                'published_at' => now()->subDays(1),
                'expires_at' => now()->addDays(7),
            ],
            [
                'title' => 'Enhanced Security Features 🔒',
                'content' => 'We have implemented enhanced security measures including 2FA and advanced encryption. Please enable two-factor authentication for better account protection.',
                'type' => 'info',
                'priority' => 'medium',
                'show_in_dashboard' => true,
                'target_audience' => 'users',
                'action_url' => '/security',
                'action_text' => 'Enable 2FA',
                'created_by' => $superAdmin->id,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Trading Competition Winner! 🏆',
                'content' => 'Congratulations to all participants in our monthly trading competition. The next competition starts next week with even bigger prizes!',
                'type' => 'success',
                'priority' => 'low',
                'show_on_homepage' => true,
                'target_audience' => 'users',
                'created_by' => $superAdmin->id,
                'published_at' => now()->subDays(5),
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::create($announcement);
        }

        $this->command->info('✅ Created ' . count($announcements) . ' announcements');
    }

    private function seedPromises(): void
    {
        $this->command->info('🎁 Seeding promises/bonuses...');
        
        $users = User::where('user_type', 'user')->get();
        $superAdmin = User::where('user_type', 'superadmin')->first();
        $totalPromises = 0;

        foreach ($users as $user) {
            // Welcome bonus for all users
            Promise::create([
                'user_id' => $user->id,
                'type' => 'bonus',
                'title' => 'Welcome Bonus',
                'description' => 'Welcome to FuInc! Enjoy your welcome bonus to start trading with confidence.',
                'amount' => 100.00,
                'currency' => 'USDT',
                'status' => 'active',
                'validity_days' => 30,
                'created_by' => $superAdmin->id,
                'activated_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);
            $totalPromises++;

            // Referral bonus (50% chance)
            if (rand(1, 2) === 1) {
                Promise::create([
                    'user_id' => $user->id,
                    'type' => 'referral',
                    'title' => 'Referral Reward',
                    'description' => 'Bonus for referring a friend to our platform. Complete the trading requirements to unlock.',
                    'amount' => 50.00,
                    'currency' => 'USDT',
                    'status' => 'active',
                    'minimum_trades' => 5,
                    'minimum_volume' => 1000.00,
                    'validity_days' => 60,
                    'referral_code' => 'REF' . strtoupper(Str::random(6)),
                    'created_by' => $superAdmin->id,
                    'activated_at' => now(),
                    'expires_at' => now()->addDays(60),
                ]);
                $totalPromises++;
            }

            // Trading bonus (30% chance)
            if (rand(1, 3) === 1) {
                Promise::create([
                    'user_id' => $user->id,
                    'type' => 'trading',
                    'title' => 'High Volume Trader Bonus',
                    'description' => 'Special bonus for active traders. Trade $5000+ in volume to unlock this reward.',
                    'amount' => 75.00,
                    'currency' => 'USDT',
                    'status' => 'active',
                    'minimum_trades' => 10,
                    'minimum_volume' => 5000.00,
                    'validity_days' => 45,
                    'created_by' => $superAdmin->id,
                    'activated_at' => now(),
                    'expires_at' => now()->addDays(45),
                ]);
                $totalPromises++;
            }

            // Loyalty bonus (20% chance)
            if (rand(1, 5) === 1) {
                Promise::create([
                    'user_id' => $user->id,
                    'type' => 'loyalty',
                    'title' => 'Loyalty Reward',
                    'description' => 'Thank you for being a loyal user! This bonus is our way of showing appreciation.',
                    'amount' => 25.00,
                    'currency' => 'USDT',
                    'status' => 'redeemed',
                    'validity_days' => 15,
                    'redeemed_amount' => 25.00,
                    'created_by' => $superAdmin->id,
                    'activated_at' => now()->subDays(20),
                    'redeemed_at' => now()->subDays(10),
                    'expires_at' => now()->subDays(5),
                ]);
                $totalPromises++;
            }
        }

        $this->command->info('✅ Created ' . $totalPromises . ' promises/bonuses');
    }

    private function getIntervalsForTimeframe(string $timeframe): int
    {
        return match($timeframe) {
            '1m' => 60,   // 1 hour of 1-minute candles
            '5m' => 72,   // 6 hours of 5-minute candles
            '15m' => 96,  // 1 day of 15-minute candles
            '1h' => 168,  // 1 week of hourly candles
            '4h' => 180,  // 1 month of 4-hour candles
            '1d' => 365,  // 1 year of daily candles
            default => 100,
        };
    }

    private function getTimestampForInterval(string $timeframe, int $intervalsAgo): \DateTime
    {
        $now = now();
        
        return match($timeframe) {
            '1m' => $now->copy()->subMinutes($intervalsAgo),
            '5m' => $now->copy()->subMinutes($intervalsAgo * 5),
            '15m' => $now->copy()->subMinutes($intervalsAgo * 15),
            '1h' => $now->copy()->subHours($intervalsAgo),
            '4h' => $now->copy()->subHours($intervalsAgo * 4),
            '1d' => $now->copy()->subDays($intervalsAgo),
            default => $now->copy()->subMinutes($intervalsAgo),
        };
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 Database Seeding Summary:');
        $this->command->info('═══════════════════════════════');
        $this->command->info('👥 Users: ' . User::count() . ' (1 SuperAdmin, ' . (User::count() - 1) . ' Regular)');
        $this->command->info('🪙 Coins: ' . Coin::count() . ' (Hot: ' . Coin::where('is_hot', true)->count() . ')');
        $this->command->info('📊 Markets: ' . Market::count());
        $this->command->info('📈 Market Data: ' . MarketData::count() . ' candles');
        $this->command->info('📝 Orders: ' . Order::count());
        $this->command->info('💼 Positions: ' . Position::count());
        $this->command->info('💰 Wallet Transactions: ' . WalletTransaction::count());
        $this->command->info('📢 Announcements: ' . Announcement::count());
        $this->command->info('🎁 Promises/Bonuses: ' . Promise::count());
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('SuperAdmin: superadmin@fuinc.com / SuperAdmin123!');
        $this->command->info('Test Users: john@trader.com, alice@investor.com, etc. / password123');
        $this->command->info('');
        $this->command->info('🚀 Your hot coins endpoint is ready: GET /api/v1/coins/hot');
        $this->command->info('🎯 Frontend integration ready!');
    }
}
