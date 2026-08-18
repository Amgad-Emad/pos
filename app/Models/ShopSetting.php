<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ShopSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'phone', 'address'];

    /**
     * السجل الوحيد لإعدادات المتجر (يُنشأ عند أول طلب).
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 600, 600)
            ->quality(95)
            ->nonQueued();
    }

    /**
     * القيم الفعلية مع الرجوع إلى الإعدادات الافتراضية في config/pos.php.
     */
    public function displayName(): string
    {
        return $this->name ?: config('pos.shop.name');
    }

    public function displayPhone(): string
    {
        return $this->phone ?: config('pos.shop.phone');
    }

    public function displayAddress(): string
    {
        return $this->address ?: config('pos.shop.address');
    }
}
