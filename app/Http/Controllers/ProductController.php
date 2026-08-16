<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category:id,name', 'supplier:id,name', 'media'])
            ->search($request->string('q')->toString())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create', $this->formOptions());
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('image');
        }

        return redirect()->route('products.index')->with('success', __('messages.flash.created'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', ['product' => $product] + $this->formOptions());
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('image');
        }

        return redirect()->route('products.index')->with('success', __('messages.flash.updated'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->saleItems()->exists()) {
            return back()->with('error', __('messages.products.has_sales'));
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', __('messages.flash.deleted'));
    }

    /**
     * @return array{categories: \Illuminate\Support\Collection, suppliers: \Illuminate\Support\Collection}
     */
    protected function formOptions(): array
    {
        return [
            'categories' => Category::with('children')->whereNull('parent_id')->orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ];
    }
}
