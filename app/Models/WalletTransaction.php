<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'type',
        'currency',
        'amount',
        'fee_amount',
        'status',
        'wallet_address',
        'transaction_hash',
        'network',
        'confirmations',
        'required_confirmations',
        'admin_notes',
        'is_fake',
        'requested_at',
        'approved_at',
        'completed_at',
        'cancelled_at',
        'approved_by',
        'cancel_reason',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'fee_amount' => 'decimal:8',
        'is_fake' => 'boolean',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = Str::uuid();
            }
            if (empty($transaction->requested_at)) {
                $transaction->requested_at = now();
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDeposits($query)
    {
        return $query->where('type', 'deposit');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Methods
    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    public function isWithdrawal(): bool
    {
        return $this->type === 'withdrawal';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function approve(int $adminId): void
    {
        $this->update([
            'status' => 'processing',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);
    }

    public function getNetAmount(): float
    {
        return $this->amount - $this->fee_amount;
    }
}
