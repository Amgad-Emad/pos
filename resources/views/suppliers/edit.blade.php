@extends('layouts.pos')

@section('title', __('messages.suppliers.edit'))
@section('page-icon', 'truck')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                @csrf
                @method('PUT')
                @include('suppliers._form')
            </form>
        </div>
    </div>
@endsection
