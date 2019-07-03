<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Project;

class ProjectsController extends Controller
{
    public function index()
    {
        $projects = Project::all();

        return view('projects.index', ['projects' => $projects]);
    }

    public function create()
    {
        return view('projects.create');
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);

        return view('projects.edit', compact('project'));
    }

    public function store()
    {
        $project = new Project();
        $project->title = \request('title');
        $project->description = \request('description');
        $project->save();

        return redirect('/projects');
    }

    public function storeOtherVersion()
    {
        Project::create([
            'title' => \request('title'),
            'description' => \request('description')
        ]);

        return redirect('/projects');
    }

    public function update($id)
    {
        $project = Project::findOrFail($id);
        $project->title = \request('title');
        $project->description = \request('description');
        $project->save();

        return redirect('/projects');
    }

    public function destroy($id)
    {
        Project::find($id)->delete();

        return redirect('/projects');
    }

    public function destroyOtherVersion(Project $project)
    {
        $project->delete();

        return redirect('/projects');
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);

        return view('projects.show', compact('project'));
    }

    public function showOtherVersion(Project $project)
    {
        return view('projects.show', compact('project'));
    }

}
