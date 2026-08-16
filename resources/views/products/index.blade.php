@extends('layouts.pos')

@section('title', __('messages.products.title'))
@section('page-icon', 'package')

@section('title-actions')
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        {{ __('messages.products.create') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0">{{ __('messages.products.title') }}</h5>
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
                        <th>{{ __('messages.fields.purchase_price') }}</th>
                        <th>{{ __('messages.fields.selling_price') }}</th>
                        <th>{{ __('messages.fields.wholesale_price') }}</th>
                        <th>{{ __('messages.fields.quantity') }}</th>
                        <th>{{ __('messages.actions.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($product->hasMedia('image'))
                                    <img src="{{ $product->getFirstMediaUrl('image', 'thumb') }}" alt="{{ $product->name }}"
                                         class="rounded" width="40" height="40">
                                @else
                                    <span class="avatar-title bg-body-secondary text-muted rounded d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                        <i data-lucide="image" style="width:18px;height:18px;"></i>
                                    </span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $product->code }}</span></td>
                            <td class="fw-medium">{{ $product->name }}</td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->supplier?->name }}</td>
                            <td>{{ number_format($product->purchase_price, 2) }}</td>
                            <td>{{ number_format($product->selling_price, 2) }}</td>
                            <td>{{ number_format($product->wholesale_price, 2) }}</td>
                            <td>
                                <span class="fw-semibold {{ $product->isLowStock() ? 'text-danger' : '' }}">{{ $product->quantity }}</span>
                                @if ($product->isLowStock())
                                    <span class="badge badge-soft-danger">{{ __('messages.inventory.low_stock') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-soft-primary btn-icon" title="{{ __('messages.actions.edit') }}">
                                        <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                                    </a>
                                    @include('partials.delete-form', ['action' => route('products.destroy', $product)])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-0">
                                @include('partials.empty-state', ['message' => __('messages.products.no_products'), 'icon' => 'package', 'actionUrl' => route('products.create'), 'actionLabel' => __('messages.products.create')])
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
