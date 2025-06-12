<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Promise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'promise_id',
        'type',
        'title',
        'description',
        'amount',
        'currency',
        'status',
        'redeemed_amount',
        'remaining_amount',
        'redemption_conditions',
        'minimum_trades',
        'minimum_volume',
        'validity_days',
        'is_transferable',
        'auto_apply',
        'referral_code',
        'activated_at',
        'expires_at',
        'redeemed_at',
        'created_by',
        'admin_notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'redeemed_amount' => 'decimal:8',
        'remaining_amount' => 'decimal:8',
        'minimum_volume' => 'decimal:8',
        'is_transferable' => 'boolean',
        'auto_apply' => 'boolean',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'redemption_conditions' => 'array',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($promise) {
            if (empty($promise->promise_id)) {
                $promise->promise_id = Str::uuid();
            }
            $promise->remaining_amount = $promise->amount;
            
            // Set expiration if validity_days is provided
            if ($promise->validity_days && !$promise->expires_at) {
                $promise->expires_at = now()->addDays($promise->validity_days);
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRedeemable($query)
    {
        return $query->where('status', 'active')
                    ->where('remaining_amount', '>', 0)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->expires_at === null || $this->expires_at > now()) &&
               $this->remaining_amount > 0;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at <= now();
    }

    public function isFullyRedeemed(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);
    }

    public function redeem(float $amount): bool
    {
        if ($amount > $this->remaining_amount) {
            return false;
        }

        $newRedeemed = $this->redeemed_amount + $amount;
        $newRemaining = $this->remaining_amount - $amount;

        $this->update([
            'redeemed_amount' => $newRedeemed,
            'remaining_amount' => $newRemaining,
            'status' => $newRemaining <= 0 ? 'redeemed' : 'active',
            'redeemed_at' => $this->redeemed_at ?? now(),
        ]);

        return true;
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'admin_notes' => $reason,
        ]);
    }

    public function checkConditions(array $userStats): bool
    {
        if ($this->minimum_trades && $userStats['total_trades'] < $this->minimum_trades) {
            return false;
        }

        if ($this->minimum_volume && $userStats['total_volume'] < $this->minimum_volume) {
            return false;
        }

        return true;
    }

    public function getRedemptionPercentage(): float
    {
        return $this->amount > 0 ? ($this->redeemed_amount / $this->amount) * 100 : 0;
    }
}
