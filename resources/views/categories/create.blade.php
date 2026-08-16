@extends('layouts.pos')

@section('title', __('messages.categories.create'))
@section('page-icon', 'folder-tree')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                @include('categories._form')
            </form>
        </div>
    </div>
@endsection
