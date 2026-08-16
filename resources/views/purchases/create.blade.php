@extends('layouts.pos')

@section('title', __('messages.purchases.create'))
@section('page-icon', 'package-plus')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('purchases.store') }}" enctype="multipart/form-data">
                @csrf
                @include('purchases._form')
            </form>
        </div>
    </div>
@endsection
