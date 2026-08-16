@extends('layouts.pos')

@section('title', __('messages.suppliers.details'))
@section('page-icon', 'truck')

@section('title-actions')
    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-soft-primary btn-sm">
        {{ __('messages.actions.edit') }}
    </a>
    <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">{{ __('messages.actions.back') }}</a>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">{{ $supplier->name }}</h5>
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <span class="text-muted">{{ __('messages.fields.phone') }}:</span>
                            <span dir="ltr">{{ $supplier->phone }}</span>
                        </div>
                        <div>
                            <span class="text-muted">{{ __('messages.fields.address') }}:</span>
                            {{ $supplier->address ?? '—' }}
                        </div>
                        <div>
                            <span class="text-muted">{{ __('messages.suppliers.total_remaining') }}:</span>
                            <span class="fw-semibold {{ $supplier->total_remaining > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($supplier->total_remaining, 2) }} {{ __('messages.currency') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('messages.suppliers.purchases_history') }}</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th>{{ __('messages.fields.date') }}</th>
                                <th>{{ __('messages.fields.items_count') }}</th>
                                <th>{{ __('messages.fields.total_amount') }}</th>
                                <th>{{ __('messages.fields.amount_paid') }}</th>
                                <th>{{ __('messages.fields.remaining_amount') }}</th>
                                <th>{{ __('messages.purchases.recorded_by') }}</th>
                                <th>{{ __('messages.actions.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->date->format('Y-m-d') }}</td>
                                    <td>{{ $purchase->items_count }}</td>
                                    <td>{{ number_format($purchase->total_amount, 2) }}</td>
                                    <td>{{ number_format($purchase->amount_paid, 2) }}</td>
                                    <td class="{{ $purchase->remaining_amount > 0 ? 'text-danger fw-semibold' : '' }}">
                                        {{ number_format($purchase->remaining_amount, 2) }}
                                    </td>
                                    <td>{{ $purchase->user?->name }}</td>
                                    <td>
                                        <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-soft-info btn-icon" title="{{ __('messages.actions.view') }}">
                                            <i data-lucide="eye" style="width:16px;height:16px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-0">
                                        @include('partials.empty-state', ['message' => __('messages.suppliers.no_purchases'), 'icon' => 'package-plus'])
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-center">
                    {{ $purchases->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
