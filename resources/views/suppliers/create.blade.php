@extends('layouts.pos')

@section('title', __('messages.suppliers.create'))
@section('page-icon', 'truck')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                @include('suppliers._form')
            </form>
        </div>
    </div>
@endsection
