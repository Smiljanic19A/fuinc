<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coin extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'name',
        'full_name',
        'description',
        'icon_url',
        'website_url',
        'whitepaper_url',
        'current_price',
        'market_cap',
        'volume_24h',
        'price_change_24h',
        'price_change_percentage_24h',
        'market_cap_rank',
        'circulating_supply',
        'total_supply',
        'max_supply',
        'is_active',
        'is_hot',
        'is_new',
        'is_trending',
        'category',
        'blockchain',
        'tags',
        'launched_at',
        'metadata',
    ];

    protected $casts = [
        'current_price' => 'decimal:8',
        'market_cap' => 'decimal:2',
        'volume_24h' => 'decimal:8',
        'price_change_24h' => 'decimal:8',
        'price_change_percentage_24h' => 'decimal:4',
        'circulating_supply' => 'decimal:8',
        'total_supply' => 'decimal:8',
        'max_supply' => 'decimal:8',
        'is_active' => 'boolean',
        'is_hot' => 'boolean',
        'is_new' => 'boolean',
        'is_trending' => 'boolean',
        'tags' => 'array',
        'launched_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function markets(): HasMany
    {
        return $this->hasMany(Market::class, 'base_currency', 'symbol');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHot($query)
    {
        return $query->where('is_hot', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByBlockchain($query, $blockchain)
    {
        return $query->where('blockchain', $blockchain);
    }

    public function scopeOrderedByRank($query)
    {
        return $query->orderBy('market_cap_rank', 'asc');
    }

    // Methods
    public function isHot(): bool
    {
        return $this->is_hot;
    }

    public function isNew(): bool
    {
        return $this->is_new;
    }

    public function isTrending(): bool
    {
        return $this->is_trending;
    }

    public function makeHot(): void
    {
        $this->update(['is_hot' => true]);
    }

    public function removeHot(): void
    {
        $this->update(['is_hot' => false]);
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

    public function getPriceData(): array
    {
        return [
            'symbol' => $this->symbol,
            'name' => $this->name,
            'price' => $this->current_price,
            'change' => $this->price_change_24h,
            'changePercent' => $this->price_change_percentage_24h,
            'volume' => $this->volume_24h,
            'marketCap' => $this->market_cap,
            'rank' => $this->market_cap_rank,
        ];
    }
}
