<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $saleService)
    {
    }

    public function index(Request $request): View
    {
        $sales = Sale::query()
            ->with('user:id,name')
            ->withCount('items')
            ->unless($request->user()->isAdmin(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(
                fn ($inner) => $inner
                    ->where('invoice_number', 'like', "%{$term}%")
                    ->orWhere('client_name', 'like', "%{$term}%")
                    ->orWhere('client_phone', 'like', "%{$term}%")
            ))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    /**
     * شاشة البيع (POS).
     */
    public function create(): View
    {
        return view('sales.create', [
            'paymentMethods' => PaymentMethod::options(),
        ]);
    }

    /**
     * بحث المنتجات لشاشة البيع (JSON) — بدون أي أسعار جملة أو شراء.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $term = $request->string('q')->toString();

        $products = Product::query()
            ->select(['id', 'name', 'code', 'selling_price', 'quantity'])
            ->search($term)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'selling_price' => (float) $product->selling_price,
                'quantity' => $product->quantity,
            ]);

        return response()->json($products);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $sale = $this->saleService->create($request->validated(), $request->user());

        return redirect()
            ->route('invoices.show', $sale)
            ->with('success', __('messages.flash.sale_created'));
    }

    public function edit(Sale $sale): View
    {
        $this->authorize('update', $sale);

        $sale->load('items.product:id,name,code,selling_price,quantity');

        return view('sales.edit', [
            'sale' => $sale,
            'paymentMethods' => PaymentMethod::options(),
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        $this->authorize('update', $sale);

        $this->saleService->update($sale, $request->validated());

        return redirect()
            ->route('invoices.show', $sale)
            ->with('success', __('messages.flash.updated'));
    }

    public function destroy(Request $request, Sale $sale): RedirectResponse
    {
        $this->authorize('delete', $sale);

        $this->saleService->delete($sale);

        return redirect()->route('sales.index')->with('success', __('messages.flash.deleted'));
    }
}
