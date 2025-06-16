<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFundAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'currency',
        'amount',
        'type',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function release(): void
    {
        $this->update(['is_active' => false]);
    }

    // Static methods
    public static function allocateForOrder(Order $order, string $currency, float $amount): self
    {
        return static::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'currency' => $currency,
            'amount' => $amount,
            'type' => 'order_reserve',
            'is_active' => true,
        ]);
    }

    public static function getTotalAllocated($userId, $currency): float
    {
        return static::where('user_id', $userId)
            ->where('currency', $currency)
            ->where('is_active', true)
            ->sum('amount');
    }

    public static function releaseOrderAllocations(Order $order): void
    {
        static::where('order_id', $order->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}
