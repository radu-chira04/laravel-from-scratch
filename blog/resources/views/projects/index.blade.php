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

            @if($project->tasks->count())
                @foreach($project->tasks as $task)
                    <form method="post" action="/tasks/{{ $task->id }}">
                        @method('PATCH')
                        @csrf

                        <span class="badge badge-light">
                            <label class="checkbox {{ $task->completed ? 'line-through' : '' }}"
                                   for="completed_task_{{ $task->id }}"
                                   {{ $task->completed ? '' : '' }}>
                                <input id="completed_task_{{ $task->id }}"
                                       type="checkbox" name="completed"
                                       onchange="this.form.submit()" {{ $task->completed ? 'checked' : '' }}>
                                {{ $task->description }}
                            </label>
                        </span>
                    </form>
                @endforeach
            @endif

        @endforeach
    </ol>

@endsection    
