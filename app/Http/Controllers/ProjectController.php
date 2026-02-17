<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;

class ProjectController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // Permiso global para todo el recurso
            new Middleware('permission:projects.view', only: ['index']),
            
            // Permisos específicos por acción
            new Middleware('permission:projects.create', only: ['create', 'store']),
            new Middleware('permission:projects.edit', only: ['edit', 'update']),
            new Middleware('permission:projects.delete', only: ['destroy']),
            
            // Tu ruta personalizada de Datatables también necesita protección
            new Middleware('permission:projects.view', only: ['table']),
        ];
    }

    // Método para mostrar la lista de proyectos
    public function index()
    {
        return Inertia::render('Project/Index');
    }
    
    // Método para proporcionar los datos de los proyectos en formato DataTables
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

    // Método para mostrar el formulario de creación
    public function create()
    {
        return Inertia::render('Project/Form', [
            'project' => null,
        ]);
    }

    // Método para mostrar el formulario de edición
    public function edit($id)
    {
        $project = Project::findOrFail($id);

        return Inertia::render('Project/Form', [
            'project' => $project,
        ]);
    }

    // Método para manejar la creación de un nuevo proyecto
    public function store(StoreProjectRequest $request)
    {
        Project::create($request->validated());

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Proyecto creado correctamente');
    }


    // Método para manejar la actualización de un proyecto existente
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Proyecto actualizado correctamente');
    }


    // Método para manejar la eliminación de un proyecto
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return back()->with('success', 'Proyecto eliminado correctamente');
    }
}
