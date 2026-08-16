@extends('layouts.pos')

@section('title', __('messages.products.edit'))
@section('page-icon', 'package')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('products._form')
            </form>
        </div>
    </div>
@endsection
