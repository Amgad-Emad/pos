@extends('layouts.pos')

@section('title', __('messages.sales.pos'))
@section('page-icon', 'shopping-cart')

@section('content')
    <form method="POST" action="{{ route('sales.store') }}">
        @csrf
        @include('sales._form')
    </form>
@endsection
