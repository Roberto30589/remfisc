<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        return Inertia::render('Projects/Index', [
            'projects' => Project::paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Projects/Form', [
            'project' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'internal_id' => 'required|unique:projects',
            'region' => 'required',
            'comuna' => 'required',
            'started_at' => 'nullable|date',
            'planned_finish_at' => 'nullable|date',
            'actual_finish_at' => 'nullable|date',
        ]);

        Project::create($data);

        return redirect()->route('projects.index');
    }

    public function edit(Project $project)
    {
        return Inertia::render('Projects/Form', [
            'project' => $project,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required',
            'internal_id' => 'required|unique:projects,internal_id,' . $project->id,
            'region' => 'required',
            'comuna' => 'required',
            'started_at' => 'nullable|date',
            'planned_finish_at' => 'nullable|date',
            'actual_finish_at' => 'nullable|date',
        ]);

        $project->update($data);

        return redirect()->route('projects.index');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return back();
    }
}

