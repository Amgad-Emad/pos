@extends('layouts.pos')

@section('title', __('messages.products.create'))
@section('page-icon', 'package')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" id="product-form">
                @csrf
                @include('products._form')
            </form>
        </div>
    </div>
@endsection
