@extends('layout')

@section('title', 'projects')

@section('content')
    <h4>display projects</h4>

    @foreach($projects as $project)
        <li> {{ $project->title }} </li>
    @endforeach

@endsection    
