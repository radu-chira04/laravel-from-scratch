@extends('layout')

@section('title', 'create project')

@section('content')
    <div class="container">
        <h4 class="title">create a new project</h4>

        <form method="post" action="/projects">
            {{ csrf_field() }}
            <div class="form-group">
                <input class="form-control" type="text" name="title" value="{{ old('title') }}" placeholder="Project title"/>
            </div>
            <div class="form-group">
                <textarea class="form-control" name="description" placeholder="Project description">{{ old('description') }}</textarea>
            </div>
            <div>
                <button class="btn btn-primary" type="submit">create project</button>
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