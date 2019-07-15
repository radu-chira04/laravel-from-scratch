@extends('layouts.layout')

@section('title', 'edit project')

@section('content')

    <div class="container">
        <h4 class="title">edit project</h4>
        <form method="post" action="/projects/{{ $project->id }}" style="margin-bottom: 5px;">
            @method('PATCH')
            @csrf
            <div class="form-group">
                <input class="form-control" type="text" name="title" value="{{ $project->title }}" placeholder="Project title"/>
            </div>
            <div>
                <textarea class="form-control" name="description" placeholder="Project description">{{ $project->description }}</textarea>
            </div>
            <br/>
            <div>
                <button class="btn btn-primary" type="submit">edit project</button>
            </div>
        </form>

        <form method="post" action="/projects/{{ $project->id }}">
            @method('DELETE')
            @csrf
            <div>
                <button class="btn btn-danger" type="submit">delete project</button>
            </div>
        </form>
    </div>

@endsection