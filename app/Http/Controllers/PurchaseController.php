<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\SupplierPurchase;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseService $purchaseService)
    {
    }

    public function index(Request $request): View
    {
        $purchases = SupplierPurchase::query()
            ->with(['supplier:id,name', 'user:id,name'])
            ->withCount('items')
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->whereHas(
                'supplier', fn ($inner) => $inner->where('name', 'like', "%{$term}%")
            ))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        return view('purchases.create', $this->formOptions());
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $this->purchaseService->create($request->validated(), $request->user());

        return redirect()->route('purchases.index')->with('success', __('messages.flash.purchase_created'));
    }

    public function show(SupplierPurchase $purchase): View
    {
        $purchase->load(['supplier', 'user:id,name', 'items.category:id,name']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(SupplierPurchase $purchase): View
    {
        $purchase->load('items.category:id,parent_id');

        return view('purchases.edit', ['purchase' => $purchase] + $this->formOptions());
    }

    public function update(UpdatePurchaseRequest $request, SupplierPurchase $purchase): RedirectResponse
    {
        $this->purchaseService->update($purchase, $request->validated());

        return redirect()->route('purchases.index')->with('success', __('messages.flash.updated'));
    }

    public function destroy(SupplierPurchase $purchase): RedirectResponse
    {
        $this->purchaseService->delete($purchase);

        return redirect()->route('purchases.index')->with('success', __('messages.flash.deleted'));
    }

    /**
     * @return array{suppliers: \Illuminate\Support\Collection, categories: \Illuminate\Support\Collection}
     */
    protected function formOptions(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->get(),
            'categories' => Category::with('children')->whereNull('parent_id')->orderBy('name')->get(),
        ];
    }
}
