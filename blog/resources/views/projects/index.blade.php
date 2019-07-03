@extends('layout')

@section('title', 'projects')

@section('content')
    <h4>display projects</h4>
    <ol>
        @foreach($projects as $project)
            <li>
                <a class="stretched-link" href="/projects/{{ $project->id }}/edit">
                    {{ $project->title }}
                </a>
            </li>
        @endforeach
    </ol>

@endsection    
