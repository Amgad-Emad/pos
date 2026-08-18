<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'supplier_id',
        'category_id',
        'name',
        'code',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'quantity',
    ];

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
        // مصغّرة عالية الدقة (تُعرض بحجم 40-64 بكسل، والحجم المضاعف يجعلها حادة على شاشات retina).
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 240, 240)
            ->quality(95)
            ->nonQueued();

        // نسخة كبيرة للعرض داخل نافذة التكبير دون تحميل الملف الأصلي كاملًا.
        $this->addMediaConversion('large')
            ->fit(Fit::Contain, 2000, 2000)
            ->quality(95)
            ->nonQueued();
    }

    /**
     * كود تلقائي للمنتجات التي تُضاف بدون كود (الحقل اختياري في النماذج).
     */
    public static function generateCode(string $prefix = 'PRD'): string
    {
        do {
            $code = $prefix.'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('code', $code)->exists());

        return $code;
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
