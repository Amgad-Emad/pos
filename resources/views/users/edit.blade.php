@extends('layouts.pos')

@section('title', __('messages.users.edit'))
@section('page-icon', 'users')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('users._form')
            </form>
        </div>
    </div>
@endsection
