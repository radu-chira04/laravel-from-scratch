@extends('layout')

@section('title', 'create project')

@section('content')
    <h4>create a new project</h4>
    <form method="post" action="/projects">
        {{ csrf_field() }}
        <div>
            <input type="text" name="title" placeholder="Project title"/>
        </div>
        <div>
            <textarea name="description" placeholder="Project description"></textarea>
        </div>
        <div>
            <button type="submit">create project</button>
        </div>
    </form>

@endsection