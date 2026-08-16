@extends('layouts.pos')

@section('title', __('messages.sales.edit').' — '.$sale->invoice_number)
@section('page-icon', 'shopping-cart')

@section('content')
    <form method="POST" action="{{ route('sales.update', $sale) }}">
        @csrf
        @method('PUT')
        @include('sales._form')
    </form>
@endsection
