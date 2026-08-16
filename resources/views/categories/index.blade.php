@extends('layouts.pos')

@section('title', __('messages.categories.title'))
@section('page-icon', 'folder-tree')

@section('title-actions')
    <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        {{ __('messages.categories.create') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>{{ __('messages.fields.name') }}</th>
                        <th>{{ __('messages.fields.parent_category') }}</th>
                        <th>{{ __('messages.fields.products_count') }}</th>
                        <th>{{ __('messages.actions.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="bg-light-subtle">
                            <td class="fw-semibold">
                                <i data-lucide="folder" style="width:16px;height:16px;" class="text-warning"></i>
                                {{ $category->name }}
                            </td>
                            <td><span class="badge badge-soft-secondary">{{ __('messages.categories.main_category') }}</span></td>
                            <td>{{ $category->products->count() }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-soft-primary btn-icon" title="{{ __('messages.actions.edit') }}">
                                        <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                                    </a>
                                    @include('partials.delete-form', ['action' => route('categories.destroy', $category)])
                                </div>
                            </td>
                        </tr>
                        @foreach ($category->children as $child)
                            <tr>
                                <td class="ps-4">
                                    <span class="text-muted">└</span>
                                    {{ $child->name }}
                                </td>
                                <td>{{ __('messages.categories.sub_of', ['parent' => $category->name]) }}</td>
                                <td>{{ $child->products->count() }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('categories.edit', $child) }}" class="btn btn-sm btn-soft-primary btn-icon" title="{{ __('messages.actions.edit') }}">
                                            <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                                        </a>
                                        @include('partials.delete-form', ['action' => route('categories.destroy', $child)])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="4" class="p-0">
                                @include('partials.empty-state', ['message' => __('messages.categories.no_categories'), 'icon' => 'folder-tree', 'actionUrl' => route('categories.create'), 'actionLabel' => __('messages.categories.create')])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {{ $categories->links() }}
        </div>
    </div>
@endsection
