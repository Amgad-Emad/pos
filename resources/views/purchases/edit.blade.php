@extends('layouts.pos')

@section('title', __('messages.purchases.edit'))
@section('page-icon', 'package-plus')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('purchases.update', $purchase) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('purchases._form')
            </form>
        </div>
    </div>
@endsection
