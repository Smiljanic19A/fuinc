<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'network',
        'address',
        'filled',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'filled' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeFilled($query)
    {
        return $query->where('filled', true);
    }

    public function scopePending($query)
    {
        return $query->where('filled', false);
    }

    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeByNetwork($query, $network)
    {
        return $query->where('network', $network);
    }

    // Methods
    public function markAsFilled(): void
    {
        $this->update(['filled' => true]);
    }

    public function isPending(): bool
    {
        return !$this->filled;
    }

    public function isFilled(): bool
    {
        return $this->filled;
    }

    public function hasAddress(): bool
    {
        return !is_null($this->address);
    }
}
