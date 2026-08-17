<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * قائمة فواتير المبيعات مع الفلاتر. البائع يرى فواتيره فقط.
     */
    public function index(Request $request): View
    {
        $isAdmin = $request->user()->isAdmin();

        $sales = Sale::query()
            ->with('user:id,name')
            ->withCount(['items', 'returns'])
            ->withSum('returns as returns_total', 'total_refund')
            ->unless($isAdmin, fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(
                fn ($inner) => $inner
                    ->where('invoice_number', 'like', "%{$term}%")
                    ->orWhere('client_name', 'like', "%{$term}%")
                    ->orWhere('client_phone', 'like', "%{$term}%")
            ))
            ->when($request->date('from_date'), fn ($query, $from) => $query->whereDate('date', '>=', $from))
            ->when($request->date('to_date'), fn ($query, $to) => $query->whereDate('date', '<=', $to))
            ->when($request->string('payment_method')->toString(), fn ($query, $method) => $query->where('payment_method', $method))
            ->when(
                $isAdmin && $request->integer('seller_id'),
                fn ($query, $sellerId) => $query->where('user_id', $sellerId)
            )
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', [
            'sales' => $sales,
            'paymentMethods' => PaymentMethod::options(),
            'sellers' => $isAdmin ? User::orderBy('name')->get(['id', 'name']) : collect(),
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * عرض الفاتورة القابلة للطباعة (A4 + إيصال 80مم).
     */
    public function show(Request $request, Sale $sale): View
    {
        $this->authorize('view', $sale);

        $sale->load(['items.product:id,name,code', 'user:id,name', 'returns.items']);

        // الكميات المرتجعة لكل صنف لعرض الفاتورة قبل وبعد الإرجاع.
        $returnedByItem = $sale->returns
            ->flatMap->items
            ->groupBy('sale_item_id')
            ->map(fn ($items) => (int) $items->sum('qty'));

        return view('invoices.show', [
            'sale' => $sale,
            'returnedByItem' => $returnedByItem,
            'returnsTotal' => (float) $sale->returns->sum('total_refund'),
            'mode' => $request->string('mode')->toString() === 'receipt' ? 'receipt' : 'a4',
            'shop' => \App\Models\ShopSetting::current(),
        ]);
    }
}
