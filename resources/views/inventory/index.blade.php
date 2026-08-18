@extends('layouts.pos')

@section('title', __('messages.inventory.title'))
@section('page-icon', 'warehouse')

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <p class="text-muted mb-0">{{ __('messages.inventory.subtitle') }}</p>
            @include('partials.search-form', ['placeholder' => __('messages.inventory.search_placeholder')])
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>{{ __('messages.fields.image') }}</th>
                        <th>{{ __('messages.fields.code') }}</th>
                        <th>{{ __('messages.fields.name') }}</th>
                        <th>{{ __('messages.fields.category') }}</th>
                        <th>{{ __('messages.fields.supplier') }}</th>
                        @if ($showWholesale)
                            <th>{{ __('messages.fields.purchase_price') }}</th>
                        @endif
                        <th>{{ __('messages.fields.selling_price') }}</th>
                        @if ($showWholesale)
                            <th>{{ __('messages.fields.wholesale_price') }}</th>
                        @endif
                        <th>{{ __('messages.fields.quantity') }}</th>
                        <th>{{ __('messages.fields.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($product->hasMedia('image'))
                                    <img src="{{ $product->getFirstMediaUrl('image', 'thumb') }}" alt="{{ $product->name }}"
                                         class="rounded border zoomable" width="48" height="48"
                                         data-lightbox-src="{{ $product->getFirstMediaUrl('image', 'large') ?: $product->getFirstMediaUrl('image') }}"
                                         data-lightbox-caption="{{ $product->name }}"
                                         title="{{ __('messages.actions.view') }}">
                                @else
                                    <span class="avatar-title bg-body-secondary text-muted rounded d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                        <i data-lucide="image" style="width:20px;height:20px;"></i>
                                    </span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $product->code }}</span></td>
                            <td class="fw-medium">{{ $product->name }}</td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->supplier?->name }}</td>
                            @if ($showWholesale)
                                <td>{{ number_format($product->purchase_price, 2) }}</td>
                            @endif
                            <td>{{ number_format($product->selling_price, 2) }}</td>
                            @if ($showWholesale)
                                <td>{{ number_format($product->wholesale_price, 2) }}</td>
                            @endif
                            <td class="fw-semibold">{{ $product->quantity }}</td>
                            <td>
                                @if ($product->quantity === 0)
                                    <span class="badge badge-soft-danger">{{ __('messages.inventory.out_of_stock') }}</span>
                                @elseif ($product->isLowStock())
                                    <span class="badge badge-soft-danger">{{ __('messages.inventory.low_stock') }}</span>
                                @else
                                    <span class="badge badge-soft-success">{{ __('messages.inventory.in_stock') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-0">
                                @include('partials.empty-state', ['message' => __('messages.table.no_results'), 'icon' => 'warehouse'])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    </div>
@endsection
