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
        'value_in_dollars',
        'currency',
    ];

    protected $casts = [
        'value_in_dollars' => 'decimal:8',
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
        return $query->where('value_in_dollars', '>', 0);
    }

    // Methods
    public function addFunds($amount): void
    {
        $this->increment('value_in_dollars', $amount);
    }

    public function subtractFunds($amount): void
    {
        $this->decrement('value_in_dollars', $amount);
    }

    public function hasBalance(): bool
    {
        return $this->value_in_dollars > 0;
    }

    public function getFormattedBalance(): string
    {
        return '$' . number_format($this->value_in_dollars, 2);
    }

    // Static methods
    public static function getUserBalance($userId, $currency)
    {
        return static::where('user_id', $userId)
                    ->where('currency', $currency)
                    ->value('value_in_dollars') ?? 0;
    }

    public static function createOrUpdateBalance($userId, $currency, $amount)
    {
        return static::updateOrCreate(
            ['user_id' => $userId, 'currency' => $currency],
            ['value_in_dollars' => $amount]
        );
    }
}
