<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'market_id',
        'order_id',
        'type',
        'side',
        'quantity',
        'price',
        'filled_quantity',
        'remaining_quantity',
        'average_price',
        'total_value',
        'fee_amount',
        'fee_currency',
        'status',
        'stop_price',
        'trigger_price',
        'time_in_force',
        'is_admin_created',
        'executed_at',
        'cancelled_at',
        'cancel_reason',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'price' => 'decimal:8',
        'filled_quantity' => 'decimal:8',
        'remaining_quantity' => 'decimal:8',
        'average_price' => 'decimal:8',
        'total_value' => 'decimal:8',
        'fee_amount' => 'decimal:8',
        'stop_price' => 'decimal:8',
        'trigger_price' => 'decimal:8',
        'is_admin_created' => 'boolean',
        'executed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_id)) {
                $order->order_id = Str::uuid();
            }
            $order->remaining_quantity = $order->quantity;
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

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['pending', 'partially_filled']);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['filled', 'cancelled']);
    }

    // Methods
    public function isOpen(): bool
    {
        return in_array($this->status, ['pending', 'partially_filled']);
    }

    public function isFilled(): bool
    {
        return $this->status === 'filled';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);
    }

    public function fillOrder(float $fillQuantity, float $fillPrice): void
    {
        $newFilledQuantity = $this->filled_quantity + $fillQuantity;
        $newRemainingQuantity = $this->quantity - $newFilledQuantity;
        
        // Calculate average price
        $totalValue = ($this->filled_quantity * $this->average_price) + ($fillQuantity * $fillPrice);
        $newAveragePrice = $newFilledQuantity > 0 ? $totalValue / $newFilledQuantity : 0;

        $this->update([
            'filled_quantity' => $newFilledQuantity,
            'remaining_quantity' => $newRemainingQuantity,
            'average_price' => $newAveragePrice,
            'total_value' => $totalValue,
            'status' => $newRemainingQuantity <= 0 ? 'filled' : 'partially_filled',
            'executed_at' => $this->executed_at ?? now(),
        ]);
    }

    public function getFillPercentage(): float
    {
        return $this->quantity > 0 ? ($this->filled_quantity / $this->quantity) * 100 : 0;
    }
}
