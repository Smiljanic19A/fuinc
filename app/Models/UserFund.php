<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithPositiveBalance($query)
    {
        return $query->where('amount', '>', 0);
    }

    // Methods
    public function addFunds($amount): void
    {
        $this->increment('amount', $amount);
    }

    public function subtractFunds($amount): void
    {
        $this->decrement('amount', $amount);
    }

    public function hasBalance(): bool
    {
        return $this->amount > 0;
    }

    public function getFormattedBalance(): string
    {
        if ($this->currency === 'USDT' || $this->currency === 'USDC') {
            return '$' . number_format($this->amount, 2);
        }
        return number_format($this->amount, 8) . ' ' . $this->currency;
    }

    /**
     * Get the USD value of this fund using current market price
     */
    public function getUsdValue(): float
    {
        return static::calculateUsdValue($this->amount, $this->currency);
    }

    /**
     * Calculate USD value for any amount and currency
     */
    public static function calculateUsdValue($amount, $currency): float
    {
        // If it's already USD-based, return as is
        if (in_array($currency, ['USDT', 'USDC', 'USD'])) {
            return (float) $amount;
        }

        // Get current market price for the currency
        $market = \App\Models\Market::where('base_currency', $currency)
            ->where('quote_currency', 'USDT')
            ->first();

        if (!$market) {
            // Fallback: try to find any USD pair
            $market = \App\Models\Market::where('base_currency', $currency)
                ->whereIn('quote_currency', ['USDT', 'USDC'])
                ->first();
        }

        if ($market && $market->current_price) {
            return (float) ($amount * $market->current_price);
        }

        // If no market found, return 0 (or you could throw an exception)
        return 0;
    }

    // Static methods
    public static function getUserBalance($userId, $currency)
    {
        return static::where('user_id', $userId)
                    ->where('currency', $currency)
                    ->value('amount') ?? 0;
    }

    public static function getUserBalanceInUsd($userId, $currency)
    {
        $amount = static::getUserBalance($userId, $currency);
        return static::calculateUsdValue($amount, $currency);
    }

    public static function getUserTotalUsdValue($userId)
    {
        $userFunds = static::where('user_id', $userId)->get();
        $totalUsd = 0;

        foreach ($userFunds as $fund) {
            $totalUsd += $fund->getUsdValue();
        }

        return $totalUsd;
    }

    public static function createOrUpdateBalance($userId, $currency, $amount)
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'currency' => $currency],
            ['amount' => $amount]
        );
    }

    public static function addFundsToBalance($userId, $currency, $amount)
    {
        $userFund = static::firstOrCreate(
            ['user_id' => $userId, 'currency' => $currency],
            ['amount' => 0]
        );
        
        $userFund->addFunds($amount);
        return $userFund;
    }
}
