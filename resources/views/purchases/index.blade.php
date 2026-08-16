@extends('layouts.pos')

@section('title', __('messages.purchases.title'))
@section('page-icon', 'package-plus')

@section('title-actions')
    <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        {{ __('messages.purchases.create') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0">{{ __('messages.purchases.title') }}</h5>
            @include('partials.search-form')
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>{{ __('messages.fields.date') }}</th>
                        <th>{{ __('messages.fields.supplier') }}</th>
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
                            <td class="fw-medium">{{ $purchase->supplier?->name }}</td>
                            <td>{{ $purchase->items_count }}</td>
                            <td>{{ number_format($purchase->total_amount, 2) }}</td>
                            <td>{{ number_format($purchase->amount_paid, 2) }}</td>
                            <td class="{{ $purchase->remaining_amount > 0 ? 'text-danger fw-semibold' : '' }}">
                                {{ number_format($purchase->remaining_amount, 2) }}
                            </td>
                            <td>{{ $purchase->user?->name }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-soft-info btn-icon" title="{{ __('messages.actions.view') }}">
                                        <i data-lucide="eye" style="width:16px;height:16px;"></i>
                                    </a>
                                    <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-soft-primary btn-icon" title="{{ __('messages.actions.edit') }}">
                                        <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                                    </a>
                                    @include('partials.delete-form', ['action' => route('purchases.destroy', $purchase)])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-0">
                                @include('partials.empty-state', ['message' => __('messages.purchases.no_purchases'), 'icon' => 'package-plus', 'actionUrl' => route('purchases.create'), 'actionLabel' => __('messages.purchases.create')])
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
@endsection
