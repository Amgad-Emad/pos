<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'address'])]
class Supplier extends Model
{
    use HasFactory;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(SupplierPurchase::class);
    }

    /**
     * إجمالي المتبقي للمورد من جميع مشترياته.
     */
    protected function totalRemaining(): Attribute
    {
        return Attribute::get(fn (): float => (float) $this->purchases()->sum('remaining_amount'));
    }
}
