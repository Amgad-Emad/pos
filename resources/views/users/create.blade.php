@extends('layouts.pos')

@section('title', __('messages.users.create'))
@section('page-icon', 'users')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('users._form')
            </form>
        </div>
    </div>
@endsection
