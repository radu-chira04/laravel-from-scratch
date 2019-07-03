@extends('layout')

@section('title', 'show project')

@section('content')
    <h4>show project</h4>
    <li> {{ $project->title }} </li>

@endsection