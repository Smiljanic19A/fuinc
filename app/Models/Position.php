<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'market_id',
        'position_id',
        'side',
        'entry_price',
        'current_price',
        'quantity',
        'remaining_quantity',
        'margin_used',
        'leverage',
        'unrealized_pnl',
        'realized_pnl',
        'total_fees',
        'stop_loss_price',
        'take_profit_price',
        'liquidation_price',
        'status',
        'is_admin_created',
        'opened_at',
        'closed_at',
        'close_reason',
        'metadata',
    ];

    protected $casts = [
        'entry_price' => 'decimal:8',
        'current_price' => 'decimal:8',
        'quantity' => 'decimal:8',
        'remaining_quantity' => 'decimal:8',
        'margin_used' => 'decimal:8',
        'leverage' => 'decimal:2',
        'unrealized_pnl' => 'decimal:8',
        'realized_pnl' => 'decimal:8',
        'total_fees' => 'decimal:8',
        'stop_loss_price' => 'decimal:8',
        'take_profit_price' => 'decimal:8',
        'liquidation_price' => 'decimal:8',
        'is_admin_created' => 'boolean',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($position) {
            if (empty($position->position_id)) {
                $position->position_id = Str::uuid();
            }
            if (empty($position->opened_at)) {
                $position->opened_at = now();
            }
            $position->remaining_quantity = $position->quantity;
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByMarket($query, $marketId)
    {
        return $query->where('market_id', $marketId);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeLong($query)
    {
        return $query->where('side', 'long');
    }

    public function scopeShort($query)
    {
        return $query->where('side', 'short');
    }

    // Methods
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isLiquidated(): bool
    {
        return $this->status === 'liquidated';
    }

    public function calculateUnrealizedPnL(float $currentPrice = null): float
    {
        $price = $currentPrice ?? $this->current_price;
        
        if ($this->side === 'long') {
            $pnl = ($price - $this->entry_price) * $this->remaining_quantity;
        } else {
            $pnl = ($this->entry_price - $price) * $this->remaining_quantity;
        }
        
        return $pnl * $this->leverage;
    }

    public function updateCurrentPrice(float $price): void
    {
        $unrealizedPnl = $this->calculateUnrealizedPnL($price);
        
        $this->update([
            'current_price' => $price,
            'unrealized_pnl' => $unrealizedPnl,
        ]);

        // Check for liquidation
        if ($this->shouldLiquidate($price)) {
            $this->liquidate();
        }
    }

    public function closePosition(float $closePrice, string $reason = null): void
    {
        $realizedPnl = $this->calculateUnrealizedPnL($closePrice);
        
        $this->update([
            'status' => 'closed',
            'current_price' => $closePrice,
            'realized_pnl' => $this->realized_pnl + $realizedPnl,
            'unrealized_pnl' => 0,
            'remaining_quantity' => 0,
            'closed_at' => now(),
            'close_reason' => $reason,
        ]);
    }

    public function liquidate(): void
    {
        $this->update([
            'status' => 'liquidated',
            'realized_pnl' => -$this->margin_used, // Total loss
            'unrealized_pnl' => 0,
            'remaining_quantity' => 0,
            'closed_at' => now(),
            'close_reason' => 'Liquidated due to insufficient margin',
        ]);
    }

    private function shouldLiquidate(float $currentPrice): bool
    {
        if (!$this->liquidation_price) {
            return false;
        }

        if ($this->side === 'long') {
            return $currentPrice <= $this->liquidation_price;
        } else {
            return $currentPrice >= $this->liquidation_price;
        }
    }

    public function getPnLPercentage(): float
    {
        $totalPnL = $this->realized_pnl + $this->unrealized_pnl;
        return $this->margin_used > 0 ? ($totalPnL / $this->margin_used) * 100 : 0;
    }
}
