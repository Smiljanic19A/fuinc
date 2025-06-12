<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type',
        'priority',
        'is_active',
        'is_sticky',
        'show_on_homepage',
        'show_in_dashboard',
        'send_notification',
        'target_audience',
        'published_at',
        'expires_at',
        'created_by',
        'image_url',
        'action_url',
        'action_text',
        'view_count',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sticky' => 'boolean',
        'show_on_homepage' => 'boolean',
        'show_in_dashboard' => 'boolean',
        'send_notification' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now());
    }

    public function scopeForHomepage($query)
    {
        return $query->where('show_on_homepage', true);
    }

    public function scopeForDashboard($query)
    {
        return $query->where('show_in_dashboard', true);
    }

    public function scopeByAudience($query, $audience)
    {
        return $query->where('target_audience', $audience)
                    ->orWhere('target_audience', 'all');
    }

    public function scopeSticky($query)
    {
        return $query->where('is_sticky', true);
    }

    // Methods
    public function isActive(): bool
    {
        return $this->is_active && 
               ($this->expires_at === null || $this->expires_at > now()) &&
               ($this->published_at === null || $this->published_at <= now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at <= now();
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function publish(): void
    {
        $this->update([
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update(['is_active' => false]);
    }
}
