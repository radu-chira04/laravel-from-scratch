<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\Project;

class ProjectTasksController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Task $task)
    {
        $method = request()->has('completed') ? 'complete' : 'incomplete';
        $task->$method();

        return redirect('/projects');
    }

    public function create()
    {
        $projects = Project::where('owner_id', auth()->id())->get();

        return view('tasks.create', ['projects' => $projects]);
    }

    public function store()
    {
        \request()->validate([
            'project_id' => ['required'],
            'description' => ['required', 'min:3', 'max:255'],
        ]);
        Task::create(\request(['project_id', 'description']));

        return redirect('/projects');
    }

}
