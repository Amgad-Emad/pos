<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_number',
    'client_name',
    'client_phone',
    'date',
    'total_amount',
    'sale_amount',
    'total_after_sale',
    'paid_amount',
    'remaining_amount',
    'payment_method',
    'user_id',
])]
class Sale extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_amount' => 'decimal:2',
            'sale_amount' => 'decimal:2',
            'total_after_sale' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * توليد رقم فاتورة تسلسلي بصيغة INV-000001.
     */
    public static function nextInvoiceNumber(): string
    {
        $last = static::query()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'INV-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
