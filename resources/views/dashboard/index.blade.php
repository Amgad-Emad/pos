@extends('layouts.pos')

@section('title', __('messages.dashboard.title'))
@section('page-icon', 'layout-dashboard')

@section('content')

    {{-- بطاقات المؤشرات --}}
    <div class="row g-3 mb-3">
        @php($cards = [
            ['label' => __('messages.dashboard.today_sales'), 'value' => number_format($kpis['today_sales'], 2).' '.__('messages.currency'), 'icon' => 'banknote', 'color' => 'success'],
            ['label' => __('messages.dashboard.month_sales'), 'value' => number_format($kpis['month_sales'], 2).' '.__('messages.currency'), 'icon' => 'trending-up', 'color' => 'primary'],
            ['label' => __('messages.dashboard.products_count'), 'value' => number_format($kpis['products_count']), 'icon' => 'package', 'color' => 'info'],
            ['label' => __('messages.dashboard.stock_value'), 'value' => number_format($kpis['stock_value'], 2).' '.__('messages.currency'), 'icon' => 'warehouse', 'color' => 'secondary'],
            ['label' => __('messages.dashboard.clients_remaining'), 'value' => number_format($kpis['clients_remaining'], 2).' '.__('messages.currency'), 'icon' => 'user-minus', 'color' => 'danger'],
            ['label' => __('messages.dashboard.suppliers_remaining'), 'value' => number_format($kpis['suppliers_remaining'], 2).' '.__('messages.currency'), 'icon' => 'truck', 'color' => 'warning'],
        ])
        @foreach ($cards as $card)
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card mb-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="avatar-title bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }} rounded d-inline-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;">
                            <i data-lucide="{{ $card['icon'] }}" style="width:22px;height:22px;"></i>
                        </span>
                        <div>
                            <p class="text-muted mb-1 small">{{ $card['label'] }}</p>
                            <h5 class="mb-0 fw-bold">{{ $card['value'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- الرسوم البيانية --}}
    <div class="row g-3 mb-3">
        <div class="col-xl-7">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.dashboard.sales_last_30_days') }}</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px;"><canvas id="salesLineChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.dashboard.sales_by_payment_method') }}</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px;"><canvas id="paymentDoughnutChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.dashboard.top_products') }}</h5>
                </div>
                <div class="card-body">
                    <div style="height: 320px;"><canvas id="topProductsChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0 text-danger">
                        <i data-lucide="alert-triangle" style="width:18px;height:18px;"></i>
                        {{ __('messages.dashboard.low_stock_alert') }}
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>{{ __('messages.fields.code') }}</th>
                                <th>{{ __('messages.fields.name') }}</th>
                                <th>{{ __('messages.fields.category') }}</th>
                                <th>{{ __('messages.fields.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lowStockProducts as $product)
                                <tr>
                                    <td><span class="badge bg-light text-dark border">{{ $product->code }}</span></td>
                                    <td class="fw-medium">{{ $product->name }}</td>
                                    <td>{{ $product->category?->name }}</td>
                                    <td><span class="badge bg-danger">{{ $product->quantity }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">{{ __('messages.dashboard.no_low_stock') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const salesData = @json($salesChart);
            const topProducts = @json($topProducts);
            const paymentData = @json($paymentChart);

            new CustomChartJs({
                selector: '#salesLineChart',
                options: () => ({
                    type: 'line',
                    data: {
                        labels: salesData.map(point => point.label),
                        datasets: [{
                            label: @js(__('messages.dashboard.sales_label')),
                            data: salesData.map(point => point.value),
                            borderColor: ins('chart-primary'),
                            backgroundColor: ins('chart-primary-rgb', .12),
                            tension: .4,
                            fill: true,
                            pointRadius: 2,
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        plugins: { legend: { display: false } },
                    },
                }),
            });

            new CustomChartJs({
                selector: '#topProductsChart',
                options: () => ({
                    type: 'bar',
                    data: {
                        labels: topProducts.map(product => product.name),
                        datasets: [{
                            label: @js(__('messages.dashboard.quantity_sold')),
                            data: topProducts.map(product => Number(product.qty)),
                            backgroundColor: ins('chart-primary'),
                            borderRadius: 4,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                    },
                }),
            });

            new CustomChartJs({
                selector: '#paymentDoughnutChart',
                options: () => ({
                    type: 'doughnut',
                    data: {
                        labels: paymentData.map(method => method.label),
                        datasets: [{
                            data: paymentData.map(method => method.value),
                            backgroundColor: [
                                ins('chart-primary'),
                                ins('chart-secondary'),
                                ins('chart-gray'),
                                ins('chart-dark'),
                            ],
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        scales: { x: { display: false }, y: { display: false } },
                        plugins: { legend: { display: true, position: 'bottom' } },
                    },
                }),
            });
        });
    </script>
@endpush
