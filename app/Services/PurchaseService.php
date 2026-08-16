<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SupplierPurchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * إنشاء فاتورة شراء جديدة وتحديث المخزون.
     *
     * @param  array{date: string, supplier_id: int, amount_paid: float, sale_price: ?float, items: array<int, array<string, mixed>>}  $data
     */
    public function create(array $data, User $user): SupplierPurchase
    {
        return DB::transaction(function () use ($data, $user): SupplierPurchase {
            $totalAmount = $this->totalOf($data);

            $purchase = SupplierPurchase::create([
                'date' => $data['date'],
                'supplier_id' => $data['supplier_id'],
                'total_amount' => $totalAmount,
                'amount_paid' => round((float) $data['amount_paid'], 2),
                'remaining_amount' => round($totalAmount - (float) $data['amount_paid'], 2),
                'sale_price' => $data['sale_price'] ?? null,
                'user_id' => $user->id,
            ]);

            $this->applyItems($purchase, $data['items']);

            return $purchase;
        });
    }

    /**
     * تعديل فاتورة شراء: عكس أثر الأصناف القديمة على المخزون ثم تطبيق الجديدة.
     */
    public function update(SupplierPurchase $purchase, array $data): SupplierPurchase
    {
        return DB::transaction(function () use ($purchase, $data): SupplierPurchase {
            $this->reverseItems($purchase);
            $purchase->items()->delete();

            $totalAmount = $this->totalOf($data);

            $purchase->update([
                'date' => $data['date'],
                'supplier_id' => $data['supplier_id'],
                'total_amount' => $totalAmount,
                'amount_paid' => round((float) $data['amount_paid'], 2),
                'remaining_amount' => round($totalAmount - (float) $data['amount_paid'], 2),
                'sale_price' => $data['sale_price'] ?? null,
            ]);

            $this->applyItems($purchase, $data['items']);

            return $purchase;
        });
    }

    /**
     * حذف فاتورة شراء مع عكس أثرها على المخزون.
     */
    public function delete(SupplierPurchase $purchase): void
    {
        DB::transaction(function () use ($purchase): void {
            $this->reverseItems($purchase);
            $purchase->items()->delete();
            $purchase->delete();
        });
    }

    /**
     * الإجمالي المحسوب من الأصناف (لا يؤخذ من النموذج).
     */
    protected function totalOf(array $data): float
    {
        $total = 0.0;

        foreach ($data['items'] as $item) {
            $total += (float) $item['purchase_price'] * (int) $item['quantity'];
        }

        return round($total, 2);
    }

    /**
     * تسجيل الأصناف: زيادة كمية المنتج الموجود بنفس الكود وتحديث أسعاره،
     * أو إنشاء منتج جديد مرتبط بمورد الفاتورة وتصنيف الصنف.
     */
    protected function applyItems(SupplierPurchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $purchase->items()->create([
                'name' => $item['name'],
                'code' => $item['code'],
                'category_id' => $item['category_id'],
                'purchase_price' => $item['purchase_price'],
                'selling_price' => $item['selling_price'],
                'wholesale_price' => $item['wholesale_price'],
                'quantity' => $item['quantity'],
            ]);

            $product = Product::query()->lockForUpdate()->where('code', $item['code'])->first();

            if ($product) {
                $product->update([
                    'purchase_price' => $item['purchase_price'],
                    'selling_price' => $item['selling_price'],
                    'wholesale_price' => $item['wholesale_price'],
                    'quantity' => $product->quantity + (int) $item['quantity'],
                ]);
            } else {
                $product = Product::create([
                    'supplier_id' => $purchase->supplier_id,
                    'category_id' => $item['category_id'],
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'purchase_price' => $item['purchase_price'],
                    'selling_price' => $item['selling_price'],
                    'wholesale_price' => $item['wholesale_price'],
                    'quantity' => (int) $item['quantity'],
                ]);
            }

            // صورة المنتج المرفوعة مع الصنف (تستبدل الصورة الحالية إن وُجدت).
            if (! empty($item['image'])) {
                $product->addMedia($item['image'])->toMediaCollection('image');
            }
        }
    }

    /**
     * عكس أثر أصناف الفاتورة على المخزون قبل تعديلها أو حذفها.
     */
    protected function reverseItems(SupplierPurchase $purchase): void
    {
        foreach ($purchase->items as $item) {
            $product = Product::query()->lockForUpdate()->where('code', $item->code)->first();

            if (! $product) {
                continue;
            }

            if ($product->quantity < $item->quantity) {
                throw ValidationException::withMessages([
                    'items' => __('messages.errors.cannot_reverse_purchase', [
                        'product' => $product->name,
                    ]),
                ]);
            }

            $product->decrement('quantity', $item->quantity);
        }
    }
}
