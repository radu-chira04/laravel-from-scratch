@extends('layout')

@section('title', 'create task')

@section('content')
    <div class="container">
        <h4 class="title">create new task</h4>

        <form method="post" action="/tasks">
            @csrf
            <div class="form-group">
                <select class="form-control" name="project_id">
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <textarea class="form-control" name="description" placeholder="Task description">{{ old('description') }}</textarea>
            </div>
            <div>
                <button class="btn btn-primary" type="submit">create task</button>
            </div>
        </form>

        @include('errors')

    </div>
@endsection
