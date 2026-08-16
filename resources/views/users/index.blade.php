@extends('layouts.pos')

@section('title', __('messages.users.title'))
@section('page-icon', 'users')

@section('title-actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i data-lucide="plus" style="width:16px;height:16px;"></i>
        {{ __('messages.users.create') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0">{{ __('messages.users.title') }}</h5>
            @include('partials.search-form')
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>{{ __('messages.fields.name') }}</th>
                        <th>{{ __('messages.fields.email') }}</th>
                        <th>{{ __('messages.fields.role') }}</th>
                        <th>{{ __('messages.fields.status') }}</th>
                        <th>{{ __('messages.fields.created_at') }}</th>
                        <th>{{ __('messages.actions.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="fw-medium">{{ $user->name }}</td>
                            <td dir="ltr" class="text-end">{{ $user->email }}</td>
                            <td>
                                @if ($user->isAdmin())
                                    <span class="badge badge-soft-primary">{{ __('messages.roles.admin') }}</span>
                                @else
                                    <span class="badge badge-soft-secondary">{{ __('messages.roles.seller') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->active)
                                    <span class="badge badge-soft-success">{{ __('messages.fields.active') }}</span>
                                @else
                                    <span class="badge badge-soft-danger">{{ __('messages.fields.inactive') }}</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-soft-primary btn-icon" title="{{ __('messages.actions.edit') }}">
                                        <i data-lucide="pencil" style="width:16px;height:16px;"></i>
                                    </a>
                                    @unless ($user->isAdmin())
                                        @include('partials.delete-form', ['action' => route('users.destroy', $user)])
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                @include('partials.empty-state', ['message' => __('messages.users.no_users'), 'icon' => 'users', 'actionUrl' => route('users.create'), 'actionLabel' => __('messages.users.create')])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {{ $users->links() }}
        </div>
    </div>
@endsection
