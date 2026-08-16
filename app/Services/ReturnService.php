<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    /**
     * إنشاء إذن إرجاع على فاتورة بيع مع إعادة الكميات إلى المخزون.
     *
     * قيمة الاسترداد للوحدة هي سعرها بعد توزيع خصم الفاتورة نسبيًا
     * (price_after_sale)، فلا يسترد العميل أكثر مما دفعه فعليًا للصنف.
     *
     * @param  array{sale_id: int, date: string, notes: ?string, items: array<int, array{sale_item_id: int, qty: int}>}  $data
     */
    public function create(array $data, User $user): SaleReturn
    {
        return DB::transaction(function () use ($data, $user): SaleReturn {
            $sale = Sale::query()
                ->with('items.product')
                ->lockForUpdate()
                ->findOrFail($data['sale_id']);

            $returnedByItem = $this->returnedQuantities($sale);

            $saleReturn = new SaleReturn([
                'return_number' => SaleReturn::nextReturnNumber(),
                'sale_id' => $sale->id,
                'date' => $data['date'],
                'notes' => $data['notes'] ?? null,
                'user_id' => $user->id,
                'total_refund' => 0,
            ]);
            $saleReturn->save();

            $totalRefund = 0.0;

            foreach ($data['items'] as $row) {
                $saleItem = $sale->items->firstWhere('id', (int) $row['sale_item_id']);
                $qty = (int) $row['qty'];

                if (! $saleItem) {
                    throw ValidationException::withMessages([
                        'items' => __('messages.errors.return_item_not_in_sale'),
                    ]);
                }

                if ($qty < 1) {
                    continue;
                }

                $returnable = $saleItem->qty - ($returnedByItem[$saleItem->id] ?? 0);

                if ($qty > $returnable) {
                    throw ValidationException::withMessages([
                        'items' => __('messages.errors.return_qty_exceeds', [
                            'product' => $saleItem->product?->name,
                            'available' => $returnable,
                        ]),
                    ]);
                }

                $unitRefund = (float) $saleItem->price_after_sale;
                $lineRefund = round($unitRefund * $qty, 2);

                $saleReturn->items()->create([
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'qty' => $qty,
                    'price' => (float) $saleItem->price,
                    'unit_refund' => $unitRefund,
                    'total_refund' => $lineRefund,
                ]);

                $saleItem->product?->increment('quantity', $qty);
                $totalRefund += $lineRefund;
            }

            if ($saleReturn->items()->count() === 0) {
                throw ValidationException::withMessages([
                    'items' => __('messages.errors.items_required'),
                ]);
            }

            $saleReturn->update(['total_refund' => round($totalRefund, 2)]);

            return $saleReturn;
        });
    }

    /**
     * حذف إذن إرجاع: خصم الكميات المرتجعة من المخزون مرة أخرى.
     */
    public function delete(SaleReturn $saleReturn): void
    {
        DB::transaction(function () use ($saleReturn): void {
            foreach ($saleReturn->items()->with('product')->lockForUpdate()->get() as $item) {
                if ($item->qty > $item->product->quantity) {
                    throw ValidationException::withMessages([
                        'return' => __('messages.errors.cannot_reverse_return', [
                            'product' => $item->product->name,
                        ]),
                    ]);
                }
            }

            foreach ($saleReturn->items as $item) {
                $item->product->decrement('quantity', $item->qty);
            }

            $saleReturn->items()->delete();
            $saleReturn->delete();
        });
    }

    /**
     * الكميات المرتجعة سابقًا لكل صنف من أصناف الفاتورة.
     *
     * @return array<int, int> [sale_item_id => الكمية المرتجعة]
     */
    public function returnedQuantities(Sale $sale): array
    {
        return DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->where('sale_returns.sale_id', $sale->id)
            ->groupBy('sale_return_items.sale_item_id')
            ->selectRaw('sale_return_items.sale_item_id, SUM(sale_return_items.qty) as returned')
            ->pluck('returned', 'sale_item_id')
            ->map(fn ($qty) => (int) $qty)
            ->all();
    }
}
