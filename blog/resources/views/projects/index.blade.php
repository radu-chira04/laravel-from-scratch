@extends('layout')

@section('title', 'projects')

@section('content')
    <h4>projects</h4>

    @foreach($projects as $project)
        <li> {{ $project->title }} </li>
        <li> {{ $project->description }} </li>
        <br/>
    @endforeach

@endsection    
