@extends('layouts.pos')

@section('title', __('messages.sales.title'))
@section('page-icon', 'shopping-cart')

@section('title-actions')
    <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        {{ __('messages.sales.create') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0">{{ __('messages.sales.title') }}</h5>
            @include('partials.search-form')
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>{{ __('messages.fields.invoice_number') }}</th>
                        <th>{{ __('messages.fields.date') }}</th>
                        <th>{{ __('messages.fields.client_name') }}</th>
                        <th>{{ __('messages.fields.items_count') }}</th>
                        <th>{{ __('messages.fields.total_after_sale') }}</th>
                        <th>{{ __('messages.fields.paid_amount') }}</th>
                        <th>{{ __('messages.fields.remaining_amount') }}</th>
                        <th>{{ __('messages.fields.payment_method') }}</th>
                        @if (auth()->user()->isAdmin())
                            <th>{{ __('messages.fields.seller') }}</th>
                        @endif
                        <th>{{ __('messages.actions.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $sale) }}" class="fw-medium">{{ $sale->invoice_number }}</a>
                            </td>
                            <td>{{ $sale->date->format('Y-m-d') }}</td>
                            <td>{{ $sale->client_name ?: __('messages.invoices.cash_client') }}</td>
                            <td>{{ $sale->items_count }}</td>
                            <td class="fw-semibold">{{ number_format($sale->total_after_sale, 2) }}</td>
                            <td>{{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="{{ $sale->remaining_amount > 0 ? 'text-danger fw-semibold' : '' }}">
                                {{ number_format($sale->remaining_amount, 2) }}
                            </td>
                            <td><span class="badge badge-soft-info">{{ $sale->payment_method->label() }}</span></td>
                            @if (auth()->user()->isAdmin())
                                <td>{{ $sale->user?->name }}</td>
                            @endif
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('invoices.show', $sale) }}" class="btn btn-sm btn-soft-info btn-icon" title="{{ __('messages.actions.print') }}">
                                        <i data-lucide="printer" style="width:16px;height:16px;"></i>
                                    </a>
                                    <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-soft-primary btn-icon" title="{{ __('messages.actions.edit') }}">
                                        <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                                    </a>
                                    @include('partials.delete-form', ['action' => route('sales.destroy', $sale)])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 10 : 9 }}" class="p-0">
                                @include('partials.empty-state', ['message' => __('messages.sales.no_sales'), 'icon' => 'shopping-cart', 'actionUrl' => route('sales.create'), 'actionLabel' => __('messages.sales.create')])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {{ $sales->links() }}
        </div>
    </div>
@endsection
