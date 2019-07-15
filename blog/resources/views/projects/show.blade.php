@extends('layouts.layout')

@section('title', 'show project')

@section('content')
    <h4>show project</h4>
    <li> <b>title:</b> {{ $project->title }} </li>
    <li> <b>description:</b> {{ $project->description }} </li>
    <li> <b>task:</b> {{ $project->tasks[0]->description }} </li>
@endsection