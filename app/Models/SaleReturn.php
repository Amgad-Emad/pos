<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'return_number',
    'sale_id',
    'date',
    'total_refund',
    'notes',
    'user_id',
])]
class SaleReturn extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_refund' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    /**
     * توليد رقم إرجاع تسلسلي بصيغة RET-000001.
     */
    public static function nextReturnNumber(): string
    {
        $last = static::query()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('return_number');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'RET-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
