<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\DataTables\DataTables;
use App\Models\Machine;
use App\Models\MachineType;

use App\Http\Requests\Machine\StoreMachineRequest;
use App\Http\Requests\Machine\UpdateMachineRequest;


use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MachineController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // Permiso global para todo el recurso
            new Middleware('permission:machines.view', only: ['index']),
            
            // Permisos específicos por acción
            new Middleware('permission:machines.create', only: ['create', 'store']),
            new Middleware('permission:machines.edit', only: ['edit', 'update']),
            new Middleware('permission:machines.delete', only: ['destroy']),
            
            // Tu ruta personalizada de Datatables también necesita protección
            new Middleware('permission:machines.view', only: ['table']),
        ];
    }

    // Método para mostrar la lista de máquinas
    public function index()
    {
        return Inertia::render('Machine/Index');
    }

    // Método para proporcionar los datos de las máquinas en formato DataTables
    public function table(Request $request)
    {
        $machines = Machine::query()->with('type');

        if ($request->boolean('show_deleted')) {
            $machines->withTrashed();
        }

        return DataTables::of($machines)
            ->addColumn('deleted', fn ($machine) => $machine->trashed())
            ->make(true);
    }

    // Método para mostrar el formulario de creación
    public function create()
    {
        return Inertia::render('Machine/Form', [
            'machine' => null,
            'types' => MachineType::select('id', 'name')->get(),
        ]);
    }

    // Método para mostrar el formulario de edición
   public function edit(Machine $machine)
    {
        $machine->load('type');

        return Inertia::render('Machine/Form', [
            'machine' => $machine,
            'types' => MachineType::select('id','name')->get(),
        ]);
    }

    // Método para manejar la creación de una nueva máquina
    public function store(StoreMachineRequest $request)
    {
        Machine::create($request->validated());

        return redirect()
            ->route('admin.machines.index')
            ->with('success', 'Maquinaria creada correctamente');
    }

    // Método para manejar la actualización de una máquina existente
    public function update(UpdateMachineRequest $request, Machine $machine)
    {
        $machine->update($request->validated());

        return redirect()
            ->route('admin.machines.index')
            ->with('success', 'Maquinaria actualizada correctamente');
    }

    // Método para manejar la eliminación de una máquina
    public function destroy(Machine $machine)
    {
        $machine->delete();

        return back()->with('success', 'Maquinaria eliminada correctamente');
    }

}

