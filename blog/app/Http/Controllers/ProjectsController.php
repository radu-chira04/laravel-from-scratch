<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;

class ProjectsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $projects = auth()->user()->projects;
        //$projects = Project::where('owner_id', auth()->id())->get();

        return view('projects.index', ['projects' => $projects]);
    }

    public function create()
    {
        return view('projects.create');
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function store()
    {
        $attributes = $this->validateProject();
        $attributes['owner_id'] = auth()->id();
        Project::create($attributes);

        return redirect('/projects');
    }

    public function update(Project $project)
    {
        $project->update($this->validateProject());

        return redirect('/projects');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect('/projects');
    }

    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    public function validateProject()
    {
        return \request()->validate([
            'title' => ['required', 'min:3', 'max:255'],
            'description' => ['required', 'min:3'],
        ]);
    }

}
