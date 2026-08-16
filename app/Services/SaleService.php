<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * إنشاء فاتورة بيع جديدة مع خصم الكميات من المخزون.
     *
     * @param  array{client_name: ?string, client_phone: ?string, date: string, sale_amount: ?float, paid_amount: float, payment_method: string, items: array<int, array{product_id: int, qty: int}>}  $data
     */
    public function create(array $data, User $seller): Sale
    {
        return DB::transaction(function () use ($data, $seller): Sale {
            $sale = new Sale([
                'invoice_number' => Sale::nextInvoiceNumber(),
                'client_name' => $data['client_name'] ?? null,
                'client_phone' => $data['client_phone'] ?? null,
                'date' => $data['date'],
                'payment_method' => $data['payment_method'],
                'user_id' => $seller->id,
                'total_amount' => 0,
                'sale_amount' => 0,
                'total_after_sale' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
            ]);
            $sale->save();

            $this->applyItems($sale, $data);

            return $sale;
        });
    }

    /**
     * تعديل فاتورة بيع: إرجاع الكميات القديمة ثم تطبيق الجديدة.
     */
    public function update(Sale $sale, array $data): Sale
    {
        $this->ensureHasNoReturns($sale);

        return DB::transaction(function () use ($sale, $data): Sale {
            $this->restoreStock($sale);
            $sale->items()->delete();

            $sale->fill([
                'client_name' => $data['client_name'] ?? null,
                'client_phone' => $data['client_phone'] ?? null,
                'date' => $data['date'],
                'payment_method' => $data['payment_method'],
            ]);

            $this->applyItems($sale, $data);

            return $sale;
        });
    }

    /**
     * حذف فاتورة بيع مع إرجاع الكميات إلى المخزون.
     */
    public function delete(Sale $sale): void
    {
        $this->ensureHasNoReturns($sale);

        DB::transaction(function () use ($sale): void {
            $this->restoreStock($sale);
            $sale->items()->delete();
            $sale->delete();
        });
    }

    /**
     * تسجيل الأصناف وخصم المخزون وحساب الإجماليات (داخل معاملة قائمة).
     */
    protected function applyItems(Sale $sale, array $data): void
    {
        $totalAmount = 0.0;
        $saleAmount = round((float) ($data['sale_amount'] ?? 0), 2);
        $rows = [];

        foreach ($data['items'] as $item) {
            $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
            $qty = (int) $item['qty'];

            if ($qty > $product->quantity) {
                throw ValidationException::withMessages([
                    'items' => __('messages.errors.insufficient_stock', [
                        'product' => $product->name,
                        'available' => $product->quantity,
                    ]),
                ]);
            }

            $product->decrement('quantity', $qty);

            $rows[] = [
                'product_id' => $product->id,
                'qty' => $qty,
                'price' => (float) $product->selling_price,
                'total' => round($qty * (float) $product->selling_price, 2),
            ];
            $totalAmount += $qty * (float) $product->selling_price;
        }

        $totalAmount = round($totalAmount, 2);

        if ($saleAmount > $totalAmount) {
            throw ValidationException::withMessages([
                'sale_amount' => __('messages.errors.discount_exceeds_total'),
            ]);
        }

        $totalAfterSale = round($totalAmount - $saleAmount, 2);
        $paidAmount = round((float) $data['paid_amount'], 2);

        // توزيع الخصم على الأصناف بنسبة قيمتها لحساب سعر الصنف بعد الخصم.
        $discountRatio = $totalAmount > 0 ? $totalAfterSale / $totalAmount : 1;

        foreach ($rows as $row) {
            $sale->items()->create([
                'product_id' => $row['product_id'],
                'qty' => $row['qty'],
                'price' => $row['price'],
                'price_after_sale' => round($row['price'] * $discountRatio, 2),
                'total' => $row['total'],
            ]);
        }

        $sale->fill([
            'total_amount' => $totalAmount,
            'sale_amount' => $saleAmount,
            'total_after_sale' => $totalAfterSale,
            'paid_amount' => $paidAmount,
            'remaining_amount' => round($totalAfterSale - $paidAmount, 2),
        ])->save();
    }

    /**
     * منع تعديل أو حذف فاتورة لها مرتجعات مسجلة.
     */
    protected function ensureHasNoReturns(Sale $sale): void
    {
        if ($sale->returns()->exists()) {
            throw ValidationException::withMessages([
                'sale' => __('messages.errors.sale_has_returns'),
            ]);
        }
    }

    /**
     * إرجاع كميات أصناف الفاتورة إلى المخزون.
     */
    protected function restoreStock(Sale $sale): void
    {
        foreach ($sale->items()->with('product')->lockForUpdate()->get() as $item) {
            $item->product->increment('quantity', $item->qty);
        }
    }
}
