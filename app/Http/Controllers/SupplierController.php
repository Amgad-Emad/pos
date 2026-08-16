<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->withCount('products')
            ->withSum('purchases', 'remaining_amount')
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(
                fn ($inner) => $inner->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")
            ))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')->with('success', __('messages.flash.created'));
    }

    public function show(Supplier $supplier): View
    {
        $purchases = $supplier->purchases()
            ->with('user:id,name')
            ->withCount('items')
            ->orderByDesc('date')
            ->paginate(10);

        return view('suppliers.show', compact('supplier', 'purchases'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('success', __('messages.flash.updated'));
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->products()->exists() || $supplier->purchases()->exists()) {
            return back()->with('error', __('messages.suppliers.has_relations'));
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', __('messages.flash.deleted'));
    }
}
