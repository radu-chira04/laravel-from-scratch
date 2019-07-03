@extends('layout')

@section('title', 'edit project')

@section('content')
    <h4>edit project</h4>
    <form method="post" action="/projects/{{ $project->id }}" style="margin-bottom: 3px;">
        {{ method_field('PATCH') }}
        {{ csrf_field() }}
        <div>
            <input type="text" name="title" value="{{ $project->title }}" placeholder="Project title"/>
        </div>
        <div>
            <textarea name="description" placeholder="Project description">{{ $project->description }}</textarea>
        </div>
        <div>
            <button type="submit">edit project</button>
        </div>
    </form>

    <form method="post" action="/projects/{{ $project->id }}">
        {{ method_field('DELETE') }}
        {{ csrf_field() }}
        <div>
            <button type="submit">delete project</button>
        </div>
    </form>


@endsection