@extends('layouts.pos')

@section('title', __('messages.purchases.purchase_details'))
@section('page-icon', 'package-plus')

@section('title-actions')
    <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-soft-primary btn-sm">{{ __('messages.actions.edit') }}</a>
    <a href="{{ route('purchases.index') }}" class="btn btn-light btn-sm">{{ __('messages.actions.back') }}</a>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body d-flex flex-column gap-2">
                    <div>
                        <span class="text-muted">{{ __('messages.fields.date') }}:</span>
                        <span class="fw-medium">{{ $purchase->date->format('Y-m-d') }}</span>
                    </div>
                    <div>
                        <span class="text-muted">{{ __('messages.fields.supplier') }}:</span>
                        <a href="{{ route('suppliers.show', $purchase->supplier) }}" class="fw-medium">{{ $purchase->supplier->name }}</a>
                    </div>
                    <div>
                        <span class="text-muted">{{ __('messages.purchases.recorded_by') }}:</span>
                        {{ $purchase->user?->name }}
                    </div>
                    <hr class="my-1">
                    <div>
                        <span class="text-muted">{{ __('messages.fields.total_amount') }}:</span>
                        <span class="fw-semibold">{{ number_format($purchase->total_amount, 2) }} {{ __('messages.currency') }}</span>
                    </div>
                    @if ($purchase->sale_price)
                        <div>
                            <span class="text-muted">{{ __('messages.fields.sale_amount') }}:</span>
                            {{ number_format($purchase->sale_price, 2) }} {{ __('messages.currency') }}
                        </div>
                    @endif
                    <div>
                        <span class="text-muted">{{ __('messages.fields.amount_paid') }}:</span>
                        {{ number_format($purchase->amount_paid, 2) }} {{ __('messages.currency') }}
                    </div>
                    <div>
                        <span class="text-muted">{{ __('messages.fields.remaining_amount') }}:</span>
                        <span class="fw-semibold {{ $purchase->remaining_amount > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($purchase->remaining_amount, 2) }} {{ __('messages.currency') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.purchases.items') }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>{{ __('messages.fields.name') }}</th>
                                <th>{{ __('messages.fields.code') }}</th>
                                <th>{{ __('messages.fields.category') }}</th>
                                <th>{{ __('messages.fields.purchase_price') }}</th>
                                <th>{{ __('messages.fields.selling_price') }}</th>
                                <th>{{ __('messages.fields.wholesale_price') }}</th>
                                <th>{{ __('messages.fields.quantity') }}</th>
                                <th>{{ __('messages.fields.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->items as $item)
                                <tr>
                                    <td class="fw-medium">{{ $item->name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $item->code }}</span></td>
                                    <td>{{ $item->category?->name }}</td>
                                    <td>{{ number_format($item->purchase_price, 2) }}</td>
                                    <td>{{ number_format($item->selling_price, 2) }}</td>
                                    <td>{{ number_format($item->wholesale_price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="fw-semibold">{{ number_format($item->purchase_price * $item->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
