@extends('layouts.pos')

@section('title', __('messages.suppliers.title'))
@section('page-icon', 'truck')

@section('title-actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        {{ __('messages.suppliers.create') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0">{{ __('messages.suppliers.title') }}</h5>
            @include('partials.search-form')
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>{{ __('messages.fields.name') }}</th>
                        <th>{{ __('messages.fields.phone') }}</th>
                        <th>{{ __('messages.fields.address') }}</th>
                        <th>{{ __('messages.fields.products_count') }}</th>
                        <th>{{ __('messages.suppliers.total_remaining') }}</th>
                        <th>{{ __('messages.actions.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="fw-medium">
                                <a href="{{ route('suppliers.show', $supplier) }}">{{ $supplier->name }}</a>
                            </td>
                            <td dir="ltr" class="text-end">{{ $supplier->phone }}</td>
                            <td>{{ $supplier->address ?? '—' }}</td>
                            <td>{{ $supplier->products_count }}</td>
                            <td class="fw-semibold {{ ($supplier->purchases_sum_remaining_amount ?? 0) > 0 ? 'text-danger' : '' }}">
                                {{ number_format($supplier->purchases_sum_remaining_amount ?? 0, 2) }} {{ __('messages.currency') }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-soft-info btn-icon" title="{{ __('messages.actions.details') }}">
                                        <i data-lucide="eye" style="width:16px;height:16px;"></i>
                                    </a>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-soft-primary btn-icon" title="{{ __('messages.actions.edit') }}">
                                        <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                                    </a>
                                    @include('partials.delete-form', ['action' => route('suppliers.destroy', $supplier)])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                @include('partials.empty-state', ['message' => __('messages.suppliers.no_suppliers'), 'icon' => 'truck', 'actionUrl' => route('suppliers.create'), 'actionLabel' => __('messages.suppliers.create')])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {{ $suppliers->links() }}
        </div>
    </div>
@endsection
