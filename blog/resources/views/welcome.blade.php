@extends('layout')

@section('title', 'laravel')

@section('content')
    <h4>welcome {{ auth()->user()->name }} !!!</h4>

    <ul>
        @foreach ($tasks as $task)
            <li>{{ $task }}</li>
        @endforeach
    </ul>

@endsection

