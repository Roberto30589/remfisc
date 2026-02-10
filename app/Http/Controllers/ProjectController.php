<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class ProjectController extends Controller
{
    public function index()
    {
        return Inertia::render('Project/Index');
    }

    public function add()
    {
        return Inertia::render('Project/Form', [
            'project' => null,
        ]);
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);

        return Inertia::render('Project/Form', [
            'project' => $project,
        ]);
    }

    public function table(Request $request)
    {
        $projects = Project::query();

        if (!$request->boolean('show_deleted')) {
            $projects->whereNull('deleted_at');
        } else {
            $projects->withTrashed();
        }

        return DataTables::of($projects)
            ->addColumn('deleted', function ($project) {
                return $project->deleted_at ? true : false;
            })
            ->make(true);
    }

    public function create(Request $request)
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

        return redirect()
            ->route('projects.index')
            ->with('success', 'Proyecto creado correctamente');
    }
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

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

        return redirect()
            ->route('projects.index')
            ->with('success', 'Proyecto actualizado correctamente');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return back()->with('success', 'Proyecto eliminado correctamente');
    }
}
