<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SupplierPurchase;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();

        $kpis = [
            'today_sales' => (float) Sale::whereDate('date', $today)->sum('total_after_sale'),
            'month_sales' => (float) Sale::query()
                ->whereDate('date', '>=', now()->startOfMonth())
                ->whereDate('date', '<=', now()->endOfMonth())
                ->sum('total_after_sale'),
            'products_count' => Product::count(),
            'stock_value' => (float) Product::sum(DB::raw('quantity * purchase_price')),
            'clients_remaining' => (float) Sale::sum('remaining_amount'),
            'suppliers_remaining' => (float) SupplierPurchase::sum('remaining_amount'),
        ];

        // المبيعات آخر 30 يوم
        $salesByDay = Sale::query()
            ->toBase()
            ->whereDate('date', '>=', now()->subDays(29)->toDateString())
            ->groupByRaw('DATE(date)')
            ->orderByRaw('DATE(date)')
            ->selectRaw('DATE(date) as day, SUM(total_after_sale) as total')
            ->pluck('total', 'day');

        $salesChart = collect(range(29, 0))
            ->map(function (int $daysAgo) use ($salesByDay): array {
                $date = now()->subDays($daysAgo)->toDateString();

                return [
                    'label' => now()->subDays($daysAgo)->format('m/d'),
                    'value' => (float) ($salesByDay[$date] ?? 0),
                ];
            });

        // أعلى 10 منتجات مبيعًا
        $topProducts = SaleItem::query()
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc(DB::raw('SUM(sale_items.qty)'))
            ->limit(10)
            ->selectRaw('products.name, SUM(sale_items.qty) as qty')
            ->get();

        // المبيعات حسب طريقة الدفع
        $paymentTotals = Sale::query()
            ->groupBy('payment_method')
            ->selectRaw('payment_method, SUM(total_after_sale) as total')
            ->pluck('total', 'payment_method');

        $paymentChart = collect(PaymentMethod::cases())->map(fn (PaymentMethod $method) => [
            'label' => $method->label(),
            'value' => (float) ($paymentTotals[$method->value] ?? 0),
        ]);

        $lowStockProducts = Product::query()
            ->lowStock()
            ->with('category')
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'kpis',
            'salesChart',
            'topProducts',
            'paymentChart',
            'lowStockProducts',
        ));
    }
}
