@extends('layouts.pos')

@section('title', __('messages.invoices.title'))
@section('page-icon', 'receipt')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3 col-12">
                    <label for="q" class="form-label mb-1">{{ __('messages.actions.search') }}</label>
                    <input type="search" id="q" name="q" value="{{ request('q') }}"
                           class="form-control form-control-sm"
                           placeholder="{{ __('messages.invoices.search_placeholder') }}">
                </div>
                <div class="col-md-2 col-6">
                    <label for="from_date" class="form-label mb-1">{{ __('messages.fields.from_date') }}</label>
                    <input type="date" id="from_date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 col-6">
                    <label for="to_date" class="form-label mb-1">{{ __('messages.fields.to_date') }}</label>
                    <input type="date" id="to_date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 col-6">
                    <label for="payment_method" class="form-label mb-1">{{ __('messages.fields.payment_method') }}</label>
                    <select id="payment_method" name="payment_method" class="form-select form-select-sm">
                        <option value="">{{ __('messages.invoices.all_methods') }}</option>
                        @foreach ($paymentMethods as $value => $label)
                            <option value="{{ $value }}" @selected(request('payment_method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($isAdmin)
                    <div class="col-md-2 col-6">
                        <label for="seller_id" class="form-label mb-1">{{ __('messages.fields.seller') }}</label>
                        <select id="seller_id" name="seller_id" class="form-select form-select-sm">
                            <option value="">{{ __('messages.invoices.all_sellers') }}</option>
                            @foreach ($sellers as $seller)
                                <option value="{{ $seller->id }}" @selected(request('seller_id') == $seller->id)>{{ $seller->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-auto col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">{{ __('messages.actions.filter') }}</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-light">{{ __('messages.actions.reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>{{ __('messages.fields.invoice_number') }}</th>
                        <th>{{ __('messages.fields.date') }}</th>
                        <th>{{ __('messages.fields.client_name') }}</th>
                        <th>{{ __('messages.fields.total_after_sale') }}</th>
                        <th>{{ __('messages.fields.paid_amount') }}</th>
                        <th>{{ __('messages.fields.remaining_amount') }}</th>
                        <th>{{ __('messages.fields.payment_method') }}</th>
                        <th>{{ __('messages.fields.status') }}</th>
                        @if ($isAdmin)
                            <th>{{ __('messages.fields.seller') }}</th>
                        @endif
                        <th>{{ __('messages.actions.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td><a href="{{ route('invoices.show', $sale) }}" class="fw-medium">{{ $sale->invoice_number }}</a></td>
                            <td>{{ $sale->date->format('Y-m-d') }}</td>
                            <td>{{ $sale->client_name ?: __('messages.invoices.cash_client') }}</td>
                            <td class="fw-semibold">{{ number_format($sale->total_after_sale, 2) }}</td>
                            <td>{{ number_format($sale->paid_amount, 2) }}</td>
                            <td class="{{ $sale->remaining_amount > 0 ? 'text-danger fw-semibold' : '' }}">
                                {{ number_format($sale->remaining_amount, 2) }}
                            </td>
                            <td><span class="badge badge-soft-info">{{ $sale->payment_method->label() }}</span></td>
                            <td>
                                @if ($sale->returns_count > 0)
                                    <span class="badge {{ $sale->returns_total >= $sale->total_after_sale ? 'badge-soft-danger' : 'badge-soft-warning' }}">
                                        {{ $sale->returns_total >= $sale->total_after_sale ? __('messages.invoices.returned_fully') : __('messages.invoices.returned_partially') }}
                                    </span>
                                    <small class="text-danger d-block mt-1">−{{ number_format($sale->returns_total, 2) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            @if ($isAdmin)
                                <td>{{ $sale->user?->name }}</td>
                            @endif
                            <td>
                                <a href="{{ route('invoices.show', $sale) }}" class="btn btn-sm btn-soft-info btn-icon" title="{{ __('messages.actions.print') }}">
                                    <i data-lucide="printer" style="width:16px;height:16px;"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 10 : 9 }}" class="p-0">
                                @include('partials.empty-state', ['message' => __('messages.invoices.no_invoices'), 'icon' => 'receipt'])
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
