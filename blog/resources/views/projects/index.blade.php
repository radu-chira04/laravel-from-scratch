@extends('layout')

@section('title', 'projects')

@section('content')
    <h4>display projects</h4>
    <style>
        a:link {
            text-decoration: none;
        }

        a:visited {
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        a:active {
            text-decoration: underline;
        }
    </style>

    <ol>
        @foreach($projects as $project)
            <li>
                <a href="/projects/{{ $project->id }}/edit">
                    {{ $project->title }}
                </a>
            </li>
        @endforeach
    </ol>

@endsection    
