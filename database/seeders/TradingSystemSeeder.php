<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Announcement, Promise, Order, Position, WalletTransaction, MarketData, User, Market, Coin};
use Illuminate\Support\Str;

class TradingSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First run the individual seeders
        $this->call([
            CoinSeeder::class,
            MarketSeeder::class,
        ]);

        // Create sample announcements
        $this->createAnnouncements();
        
        // Create sample promises/bonuses
        $this->createPromises();
        
        // Create sample orders and positions
        $this->createSampleTradingData();
        
        // Create sample wallet transactions
        $this->createWalletTransactions();
        
        // Create sample market data (candles)
        $this->createMarketData();
    }

    private function createAnnouncements(): void
    {
        $superAdmin = User::where('user_type', 'superadmin')->first();
        
        $announcements = [
            [
                'title' => 'Welcome to FuInc Trading Platform!',
                'content' => 'Start your cryptocurrency trading journey with us. Enjoy low fees and advanced trading features.',
                'type' => 'success',
                'priority' => 'high',
                'is_sticky' => true,
                'show_on_homepage' => true,
                'show_in_dashboard' => true,
                'created_by' => $superAdmin->id,
                'published_at' => now(),
            ],
            [
                'title' => 'New Trading Pairs Available',
                'content' => 'We have added new trading pairs including SOL/USDT and MATIC/USDT. Start trading now!',
                'type' => 'info',
                'priority' => 'medium',
                'show_on_homepage' => true,
                'show_in_dashboard' => true,
                'created_by' => $superAdmin->id,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Maintenance Schedule',
                'content' => 'Scheduled maintenance will occur on Sunday from 2:00 AM to 4:00 AM UTC. Trading will be temporarily suspended.',
                'type' => 'warning',
                'priority' => 'high',
                'show_in_dashboard' => true,
                'created_by' => $superAdmin->id,
                'published_at' => now()->subDays(1),
                'expires_at' => now()->addDays(7),
            ],
            [
                'title' => 'Security Update',
                'content' => 'We have implemented enhanced security measures. Please enable 2FA for better account protection.',
                'type' => 'info',
                'priority' => 'medium',
                'show_in_dashboard' => true,
                'target_audience' => 'users',
                'created_by' => $superAdmin->id,
                'published_at' => now()->subDays(3),
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::create($announcement);
        }
    }

    private function createPromises(): void
    {
        $users = User::where('user_type', 'user')->take(5)->get();
        $superAdmin = User::where('user_type', 'superadmin')->first();

        foreach ($users as $user) {
            // Welcome bonus
            Promise::create([
                'user_id' => $user->id,
                'type' => 'bonus',
                'title' => 'Welcome Bonus',
                'description' => 'Welcome to FuInc! Enjoy your welcome bonus to start trading.',
                'amount' => 100.00,
                'currency' => 'USDT',
                'status' => 'active',
                'validity_days' => 30,
                'created_by' => $superAdmin->id,
                'activated_at' => now(),
            ]);

            // Referral bonus (random)
            if (rand(1, 3) === 1) {
                Promise::create([
                    'user_id' => $user->id,
                    'type' => 'referral',
                    'title' => 'Referral Reward',
                    'description' => 'Bonus for referring a friend to our platform.',
                    'amount' => 50.00,
                    'currency' => 'USDT',
                    'status' => 'active',
                    'minimum_trades' => 5,
                    'minimum_volume' => 1000.00,
                    'validity_days' => 60,
                    'referral_code' => 'REF' . strtoupper(Str::random(6)),
                    'created_by' => $superAdmin->id,
                    'activated_at' => now(),
                ]);
            }
        }
    }

    private function createSampleTradingData(): void
    {
        $users = User::where('user_type', 'user')->take(3)->get();
        $markets = Market::take(5)->get();

        foreach ($users as $user) {
            foreach ($markets->take(3) as $market) {
                // Create some orders
                for ($i = 0; $i < rand(2, 4); $i++) {
                    $side = rand(0, 1) ? 'buy' : 'sell';
                    $type = rand(0, 3) === 0 ? 'market' : 'limit';
                    $quantity = rand(1, 100) / 10;
                    $price = $type === 'market' ? null : $market->current_price * (0.95 + (rand(0, 100) / 1000));

                    Order::create([
                        'user_id' => $user->id,
                        'market_id' => $market->id,
                        'type' => $type,
                        'side' => $side,
                        'quantity' => $quantity,
                        'price' => $price,
                        'status' => rand(0, 2) === 0 ? 'filled' : 'pending',
                        'executed_at' => rand(0, 1) ? now()->subMinutes(rand(1, 1440)) : null,
                    ]);
                }

                // Create some positions
                if (rand(0, 2) === 0) {
                    $side = rand(0, 1) ? 'long' : 'short';
                    $quantity = rand(10, 1000) / 100;
                    $entryPrice = $market->current_price * (0.98 + (rand(0, 40) / 1000));
                    $leverage = [1, 2, 5, 10][rand(0, 3)];

                    Position::create([
                        'user_id' => $user->id,
                        'market_id' => $market->id,
                        'side' => $side,
                        'entry_price' => $entryPrice,
                        'current_price' => $market->current_price,
                        'quantity' => $quantity,
                        'leverage' => $leverage,
                        'margin_used' => ($quantity * $entryPrice) / $leverage,
                        'status' => 'open',
                    ]);
                }
            }
        }
    }

    private function createWalletTransactions(): void
    {
        $users = User::where('user_type', 'user')->take(5)->get();
        $currencies = ['BTC', 'ETH', 'USDT', 'BNB'];

        foreach ($users as $user) {
            // Create some deposits
            for ($i = 0; $i < rand(1, 3); $i++) {
                $currency = $currencies[rand(0, count($currencies) - 1)];
                $amount = rand(100, 10000) / 100;

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'currency' => $currency,
                    'amount' => $amount,
                    'fee_amount' => $amount * 0.001,
                    'status' => ['pending', 'completed', 'processing'][rand(0, 2)],
                    'wallet_address' => '1' . Str::random(33),
                    'network' => $currency === 'USDT' ? 'ERC20' : $currency,
                    'requested_at' => now()->subDays(rand(1, 30)),
                ]);
            }

            // Create some withdrawals
            for ($i = 0; $i < rand(0, 2); $i++) {
                $currency = $currencies[rand(0, count($currencies) - 1)];
                $amount = rand(50, 5000) / 100;

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'currency' => $currency,
                    'amount' => $amount,
                    'fee_amount' => $amount * 0.005,
                    'status' => ['pending', 'completed', 'processing'][rand(0, 2)],
                    'wallet_address' => '1' . Str::random(33),
                    'network' => $currency === 'USDT' ? 'ERC20' : $currency,
                    'requested_at' => now()->subDays(rand(1, 15)),
                ]);
            }
        }
    }

    private function createMarketData(): void
    {
        $markets = Market::take(3)->get();
        $timeframes = ['1m', '5m', '15m', '1h', '4h', '1d'];

        foreach ($markets as $market) {
            foreach ($timeframes as $timeframe) {
                $intervals = $this->getIntervalsForTimeframe($timeframe);
                $basePrice = $market->current_price;

                for ($i = $intervals; $i > 0; $i--) {
                    $timestamp = $this->getTimestampForInterval($timeframe, $i);
                    $open = $basePrice * (0.95 + (rand(0, 100) / 1000));
                    $close = $open * (0.98 + (rand(0, 40) / 1000));
                    $high = max($open, $close) * (1 + (rand(0, 20) / 1000));
                    $low = min($open, $close) * (1 - (rand(0, 20) / 1000));
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

                    $basePrice = $close; // Use previous close as base for next candle
                }
            }
        }
    }

    private function getIntervalsForTimeframe(string $timeframe): int
    {
        return match($timeframe) {
            '1m' => 100,
            '5m' => 100,
            '15m' => 96,
            '1h' => 168,
            '4h' => 180,
            '1d' => 365,
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
}
