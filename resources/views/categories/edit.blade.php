@extends('layouts.pos')

@section('title', __('messages.categories.edit'))
@section('page-icon', 'folder-tree')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('categories.update', $category) }}">
                @csrf
                @method('PUT')
                @include('categories._form')
            </form>
        </div>
    </div>
@endsection
