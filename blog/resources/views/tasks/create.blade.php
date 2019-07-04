@extends('layout')

@section('title', 'create task')

@section('content')
    <div class="container">
        <h4 class="title">create new task</h4>

        <form method="post" action="/tasks">
            @csrf
            <div class="form-group">
                <input class="form-control" type="text" name="project_id" value="{{ old('project_id') }}" placeholder="Project ID"/>
            </div>
            <div class="form-group">
                <textarea class="form-control" name="description" placeholder="Task description">{{ old('description') }}</textarea>
            </div>
            <div>
                <button class="btn btn-primary" type="submit">create task</button>
            </div>
        </form>

        @if($errors->any())
            <br/>
            <div class="alert alert-danger">
                <ul class="list-group">
                    @foreach($errors->all() as $error)
                        <li class="list-group">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>
@endsection
