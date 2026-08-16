<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * قائمة المخزن (للمدير والبائع). عمود سعر الجملة لا يُمرر للبائع إطلاقًا.
     */
    public function index(Request $request): View
    {
        $showWholesale = $request->user()->can('view-wholesale-price');

        $columns = ['id', 'name', 'code', 'category_id', 'supplier_id', 'selling_price', 'quantity', 'created_at', 'updated_at'];

        if ($showWholesale) {
            $columns[] = 'wholesale_price';
            $columns[] = 'purchase_price';
        }

        $products = Product::query()
            ->select($columns)
            ->with(['category:id,name', 'supplier:id,name', 'media'])
            ->search($request->string('q')->toString())
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('inventory.index', [
            'products' => $products,
            'showWholesale' => $showWholesale,
        ]);
    }
}
