<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\Project;

class ProjectTasksController extends Controller
{
    public function update(Task $task)
    {
        $task->complete(request()->has('completed'));

        return redirect('/projects');
    }

    public function create()
    {
        return view('tasks.create');
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
