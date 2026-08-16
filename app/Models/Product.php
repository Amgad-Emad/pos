<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'supplier_id',
    'category_id',
    'name',
    'code',
    'purchase_price',
    'selling_price',
    'wholesale_price',
    'quantity',
])]
class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(\Spatie\Image\Enums\Fit::Crop, 80, 80)
            ->nonQueued();
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= config('pos.low_stock_threshold');
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('quantity', '<=', config('pos.low_stock_threshold'));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(
            fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
        ));
    }
}
