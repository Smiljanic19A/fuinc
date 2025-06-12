<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MarketData extends Model
{
    use HasFactory;

    protected $fillable = [
        'market_id',
        'timeframe',
        'timestamp',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'quote_volume',
        'trades_count',
        'is_fake',
        'is_closed',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'open' => 'decimal:8',
        'high' => 'decimal:8',
        'low' => 'decimal:8',
        'close' => 'decimal:8',
        'volume' => 'decimal:8',
        'quote_volume' => 'decimal:8',
        'is_fake' => 'boolean',
        'is_closed' => 'boolean',
    ];

    // Relationships
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    // Scopes
    public function scopeByMarket($query, $marketId)
    {
        return $query->where('market_id', $marketId);
    }

    public function scopeByTimeframe($query, $timeframe)
    {
        return $query->where('timeframe', $timeframe);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('timestamp', [$startDate, $endDate]);
    }

    public function scopeLatest($query, $limit = 100)
    {
        return $query->orderBy('timestamp', 'desc')->limit($limit);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    // Methods
    public function isBullish(): bool
    {
        return $this->close > $this->open;
    }

    public function isBearish(): bool
    {
        return $this->close < $this->open;
    }

    public function isDoji(): bool
    {
        return $this->close == $this->open;
    }

    public function getBodySize(): float
    {
        return abs($this->close - $this->open);
    }

    public function getWickSizes(): array
    {
        $upperWick = $this->high - max($this->open, $this->close);
        $lowerWick = min($this->open, $this->close) - $this->low;
        
        return [
            'upper' => $upperWick,
            'lower' => $lowerWick,
        ];
    }

    public function getOHLCVArray(): array
    {
        return [
            'timestamp' => $this->timestamp->timestamp,
            'open' => (float) $this->open,
            'high' => (float) $this->high,
            'low' => (float) $this->low,
            'close' => (float) $this->close,
            'volume' => (float) $this->volume,
        ];
    }

    public function getPriceChange(): float
    {
        return $this->close - $this->open;
    }

    public function getPriceChangePercentage(): float
    {
        return $this->open > 0 ? (($this->close - $this->open) / $this->open) * 100 : 0;
    }
}
