<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\SupplierPurchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * اقتراحات البحث الفورية (JSON) لحقول البحث في كل الشاشات.
 *
 * كل عنصر مقترح يُعاد بالشكل: label (النص الأساسي)، meta (نص جانبي)،
 * value (ما يُملأ في حقل البحث عند الاختيار)، url (صفحة العنصر إن وُجدت).
 */
class SearchSuggestionController extends Controller
{
    /** أنواع البحث المدعومة — تُستخدم أيضًا في تقييد معامل المسار. */
    public const TYPES = [
        'products',
        'inventory',
        'suppliers',
        'purchases',
        'sales',
        'invoices',
        'returns',
        'returns-sale',
        'users',
    ];

    private const LIMIT = 8;

    public function __invoke(Request $request, string $type): JsonResponse
    {
        $term = trim($request->string('q')->toString());

        if ($term === '') {
            return response()->json([]);
        }

        $suggestions = match ($type) {
            'products' => $this->products($request, $term),
            'inventory' => $this->inventory($request, $term),
            'suppliers' => $this->suppliers($request, $term),
            'purchases' => $this->purchases($request, $term),
            'sales' => $this->sales($request, $term),
            'invoices' => $this->invoices($request, $term),
            'returns' => $this->returns($request, $term),
            'returns-sale' => $this->returnsSale($request, $term),
            'users' => $this->users($request, $term),
            default => abort(404),
        };

        return response()->json($suggestions->values());
    }

    private function products(Request $request, string $term): Collection
    {
        $this->allow($request, 'manage-products');

        return Product::query()
            ->select(['id', 'name', 'code'])
            ->search($term)
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Product $product) => [
                'label' => $product->name,
                'meta' => $product->code,
                'value' => $product->name,
                'url' => route('products.edit', $product),
            ]);
    }

    private function inventory(Request $request, string $term): Collection
    {
        $this->allow($request, 'view-inventory');

        return Product::query()
            ->select(['id', 'name', 'code', 'quantity'])
            ->search($term)
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Product $product) => [
                'label' => $product->name,
                'meta' => $product->code.' — '.__('messages.fields.quantity').': '.$product->quantity,
                // لا توجد صفحة عرض للمنتج داخل المخزن: يُفلتَر الجدول بالكود.
                'value' => $product->code,
                'url' => null,
            ]);
    }

    private function suppliers(Request $request, string $term): Collection
    {
        $this->allow($request, 'manage-suppliers');

        return Supplier::query()
            ->select(['id', 'name', 'phone'])
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%"))
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Supplier $supplier) => [
                'label' => $supplier->name,
                'meta' => $supplier->phone,
                'value' => $supplier->name,
                'url' => route('suppliers.show', $supplier),
            ]);
    }

    private function purchases(Request $request, string $term): Collection
    {
        $this->allow($request, 'manage-purchases');

        return SupplierPurchase::query()
            ->with('supplier:id,name')
            ->whereHas('supplier', fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (SupplierPurchase $purchase) => [
                'label' => $purchase->supplier->name,
                'meta' => $purchase->date->format('Y-m-d').' — '.number_format((float) $purchase->total_amount, 2),
                'value' => $purchase->supplier->name,
                'url' => route('purchases.show', $purchase),
            ]);
    }

    private function sales(Request $request, string $term): Collection
    {
        $this->allow($request, 'manage-sales');

        $canViewInvoice = $request->user()->can('view-invoices');

        return $this->salesQuery($request, $term)
            ->get()
            ->map(fn (Sale $sale) => [
                'label' => $sale->invoice_number,
                'meta' => $this->saleMeta($sale),
                'value' => $sale->invoice_number,
                'url' => $canViewInvoice ? route('invoices.show', $sale) : null,
            ]);
    }

    private function invoices(Request $request, string $term): Collection
    {
        $this->allow($request, 'view-invoices');

        return $this->salesQuery($request, $term)
            ->get()
            ->map(fn (Sale $sale) => [
                'label' => $sale->invoice_number,
                'meta' => $this->saleMeta($sale),
                'value' => $sale->invoice_number,
                'url' => route('invoices.show', $sale),
            ]);
    }

    private function returns(Request $request, string $term): Collection
    {
        $this->allow($request, 'manage-returns');

        return SaleReturn::query()
            ->with('sale:id,invoice_number,client_name')
            ->unless($request->user()->isAdmin(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->where(fn ($query) => $query
                ->where('return_number', 'like', "%{$term}%")
                ->orWhereHas('sale', fn ($sale) => $sale
                    ->where('invoice_number', 'like', "%{$term}%")
                    ->orWhere('client_name', 'like', "%{$term}%")))
            ->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (SaleReturn $saleReturn) => [
                'label' => $saleReturn->return_number,
                'meta' => $saleReturn->sale?->invoice_number,
                'value' => $saleReturn->return_number,
                'url' => route('returns.show', $saleReturn),
            ]);
    }

    /**
     * اقتراحات فواتير البيع في شاشة إنشاء إذن الإرجاع (تُحمَّل الفاتورة بالمعرّف).
     */
    private function returnsSale(Request $request, string $term): Collection
    {
        $this->allow($request, 'manage-returns');

        return Sale::query()
            ->where(fn ($query) => $query
                ->where('invoice_number', 'like', "%{$term}%")
                ->orWhere('client_name', 'like', "%{$term}%")
                ->orWhere('client_phone', 'like', "%{$term}%"))
            ->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Sale $sale) => [
                'id' => $sale->id,
                'label' => $sale->invoice_number,
                'meta' => $this->saleMeta($sale),
                'value' => $sale->invoice_number,
                'url' => null,
            ]);
    }

    private function users(Request $request, string $term): Collection
    {
        $this->allow($request, 'manage-users');

        return User::query()
            ->select(['id', 'name', 'email'])
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"))
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (User $user) => [
                'label' => $user->name,
                'meta' => $user->email,
                'value' => $user->name,
                'url' => route('users.edit', $user),
            ]);
    }

    /**
     * @return Builder<Sale>
     */
    private function salesQuery(Request $request, string $term)
    {
        return Sale::query()
            ->unless($request->user()->isAdmin(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->where(fn ($query) => $query
                ->where('invoice_number', 'like', "%{$term}%")
                ->orWhere('client_name', 'like', "%{$term}%")
                ->orWhere('client_phone', 'like', "%{$term}%"))
            ->orderByDesc('id')
            ->limit(self::LIMIT);
    }

    private function saleMeta(Sale $sale): string
    {
        return trim(($sale->client_name ?: __('messages.invoices.cash_client')).' — '.$sale->date->format('Y-m-d'));
    }

    private function allow(Request $request, string $permission): void
    {
        abort_unless((bool) $request->user()?->can($permission), 403);
    }
}
