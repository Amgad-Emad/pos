<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReturnRequest;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Services\ReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function __construct(private readonly ReturnService $returnService)
    {
    }

    public function index(Request $request): View
    {
        $returns = SaleReturn::query()
            ->with(['user:id,name', 'sale:id,invoice_number,client_name'])
            ->withCount('items')
            ->unless($request->user()->isAdmin(), fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(
                fn ($inner) => $inner
                    ->where('return_number', 'like', "%{$term}%")
                    ->orWhereHas('sale', fn ($sale) => $sale
                        ->where('invoice_number', 'like', "%{$term}%")
                        ->orWhere('client_name', 'like', "%{$term}%"))
            ))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('returns.index', compact('returns'));
    }

    /**
     * شاشة الإرجاع: البحث عن فاتورة ثم اختيار الأصناف والكميات.
     */
    public function create(Request $request): View
    {
        return view('returns.create', [
            'invoiceNumber' => $request->string('invoice')->toString(),
        ]);
    }

    /**
     * جلب فاتورة بأصنافها والكميات القابلة للإرجاع (JSON).
     */
    public function searchSale(Request $request): JsonResponse
    {
        $term = trim($request->string('q')->toString());

        // البحث بمطابقة رقم الفاتورة بالكامل: العميل يعود بالإيصال لأي بائع،
        // والمطابقة التامة تمنع تصفح فواتير الآخرين.
        $sale = Sale::query()
            ->with('items.product:id,name,code')
            ->where('invoice_number', $term)
            ->first();

        if (! $sale) {
            return response()->json(['found' => false]);
        }

        $returned = $this->returnService->returnedQuantities($sale);

        return response()->json([
            'found' => true,
            'sale' => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'date' => $sale->date->format('Y-m-d'),
                'client_name' => $sale->client_name,
                'total_amount' => (float) $sale->total_amount,
                'sale_amount' => (float) $sale->sale_amount,
                'total_after_sale' => (float) $sale->total_after_sale,
                'items' => $sale->items->map(fn ($item) => [
                    'sale_item_id' => $item->id,
                    'name' => $item->product?->name,
                    'code' => $item->product?->code,
                    'qty' => $item->qty,
                    'price' => (float) $item->price,
                    'unit_refund' => (float) $item->price_after_sale,
                    'returnable' => $item->qty - ($returned[$item->id] ?? 0),
                ])->values(),
            ],
        ]);
    }

    public function store(StoreReturnRequest $request): RedirectResponse
    {
        $saleReturn = $this->returnService->create($request->validated(), $request->user());

        return redirect()
            ->route('returns.show', $saleReturn)
            ->with('success', __('messages.flash.return_created'));
    }

    public function show(Request $request, SaleReturn $return): View
    {
        $this->authorize('view', $return);

        $return->load(['sale', 'user:id,name', 'items.product:id,name,code']);

        return view('returns.show', [
            'saleReturn' => $return,
            'mode' => $request->string('mode')->toString() === 'receipt' ? 'receipt' : 'a4',
            'shop' => \App\Models\ShopSetting::current(),
        ]);
    }

    public function destroy(SaleReturn $return): RedirectResponse
    {
        $this->authorize('delete', $return);

        $this->returnService->delete($return);

        return redirect()->route('returns.index')->with('success', __('messages.flash.deleted'));
    }
}
