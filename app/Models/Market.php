<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'base_currency',
        'quote_currency',
        'display_name',
        'current_price',
        'price_change_24h',
        'price_change_percentage_24h',
        'high_24h',
        'low_24h',
        'volume_24h',
        'market_cap',
        'rank',
        'min_order_amount',
        'max_order_amount',
        'price_precision',
        'quantity_precision',
        'is_active',
        'is_trading_enabled',
        'icon_url',
        'description',
    ];

    protected $casts = [
        'current_price' => 'decimal:8',
        'price_change_24h' => 'decimal:8',
        'price_change_percentage_24h' => 'decimal:4',
        'high_24h' => 'decimal:8',
        'low_24h' => 'decimal:8',
        'volume_24h' => 'decimal:8',
        'market_cap' => 'decimal:2',
        'min_order_amount' => 'decimal:8',
        'max_order_amount' => 'decimal:8',
        'is_active' => 'boolean',
        'is_trading_enabled' => 'boolean',
    ];

    // Relationships
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function marketData(): HasMany
    {
        return $this->hasMany(MarketData::class);
    }

    public function baseCoin(): BelongsTo
    {
        return $this->belongsTo(Coin::class, 'base_currency', 'symbol');
    }

    public function quoteCoin(): BelongsTo
    {
        return $this->belongsTo(Coin::class, 'quote_currency', 'symbol');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTradingEnabled($query)
    {
        return $query->where('is_trading_enabled', true);
    }

    public function scopeBySymbol($query, $symbol)
    {
        return $query->where('symbol', strtoupper($symbol));
    }

    // Methods
    public function isActive(): bool
    {
        return $this->is_active && $this->is_trading_enabled;
    }

    public function getTickerData(): array
    {
        return [
            'symbol' => $this->symbol,
            'price' => $this->current_price,
            'change' => $this->price_change_24h,
            'changePercent' => $this->price_change_percentage_24h,
            'high' => $this->high_24h,
            'low' => $this->low_24h,
            'volume' => $this->volume_24h,
        ];
    }

    public function updatePrice(float $price): void
    {
        $oldPrice = $this->current_price;
        $change = $price - $oldPrice;
        $changePercent = $oldPrice > 0 ? ($change / $oldPrice) * 100 : 0;

        $this->update([
            'current_price' => $price,
            'price_change_24h' => $change,
            'price_change_percentage_24h' => $changePercent,
        ]);
    }
}
