@extends('layouts.pos')

@section('title', __('messages.returns.title'))
@section('page-icon', 'undo-2')

@section('title-actions')
    <a href="{{ route('returns.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        {{ __('messages.returns.create') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0">{{ __('messages.returns.title') }}</h5>
            @include('partials.search-form')
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>{{ __('messages.fields.return_number') }}</th>
                        <th>{{ __('messages.fields.invoice_number') }}</th>
                        <th>{{ __('messages.fields.date') }}</th>
                        <th>{{ __('messages.fields.client_name') }}</th>
                        <th>{{ __('messages.fields.items_count') }}</th>
                        <th>{{ __('messages.fields.total_refund') }}</th>
                        @if (auth()->user()->isAdmin())
                            <th>{{ __('messages.fields.seller') }}</th>
                        @endif
                        <th>{{ __('messages.actions.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $saleReturn)
                        <tr>
                            <td>
                                <a href="{{ route('returns.show', $saleReturn) }}" class="fw-medium">{{ $saleReturn->return_number }}</a>
                            </td>
                            <td>
                                <a href="{{ route('invoices.show', $saleReturn->sale) }}">{{ $saleReturn->sale?->invoice_number }}</a>
                            </td>
                            <td>{{ $saleReturn->date->format('Y-m-d') }}</td>
                            <td>{{ $saleReturn->sale?->client_name ?: __('messages.invoices.cash_client') }}</td>
                            <td>{{ $saleReturn->items_count }}</td>
                            <td class="fw-semibold text-danger">{{ number_format($saleReturn->total_refund, 2) }}</td>
                            @if (auth()->user()->isAdmin())
                                <td>{{ $saleReturn->user?->name }}</td>
                            @endif
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('returns.show', $saleReturn) }}" class="btn btn-sm btn-soft-info btn-icon" title="{{ __('messages.actions.view') }}">
                                        <i data-lucide="eye" style="width:16px;height:16px;"></i>
                                    </a>
                                    @include('partials.delete-form', ['action' => route('returns.destroy', $saleReturn)])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isAdmin() ? 8 : 7 }}" class="p-0">
                                @include('partials.empty-state', ['message' => __('messages.returns.no_returns'), 'icon' => 'undo-2', 'actionUrl' => route('returns.create'), 'actionLabel' => __('messages.returns.create')])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {{ $returns->links() }}
        </div>
    </div>
@endsection
