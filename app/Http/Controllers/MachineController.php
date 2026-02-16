<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\DataTables\DataTables;
use App\Http\Requests\StoreMachineRequest;
use App\Http\Requests\UpdateMachineRequest;
use App\Models\Machine;
use App\Models\MachineType;

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
        $machines = Machine::with('type');

        // Filtrar por deleted_at
        if (!$request->boolean('show_deleted')) {
            $machines->whereNull('deleted_at');
        } else {
            $machines->withTrashed();
        }
        return DataTables::of($machines)
            ->addColumn('deleted', function ($machine) {
                return $machine->deleted_at ? true : false;
            })
            ->make(true);
    }

    // Método para mostrar el formulario de creación
    public function create()
    {
        return Inertia::render('Machine/Form', [
            'machine' => null,
            'types' => MachineType::all(),
        ]);
    }

    // Método para mostrar el formulario de edición
    public function edit($id)
    {
        $machine = Machine::findOrFail($id);
        return Inertia::render('Machine/Form', [
            'machine' => $machine,
            'types' => MachineType::all(),
        ]);
    }

    // Método para manejar la creación de una nueva máquina
    public function store(Request $request)
    {
        $data = $request->validate([
            'internal_id' => 'required|unique:machines',
            'plate' => 'required|unique:machines',
            'machine_type_id' => 'required|exists:machine_types,id',
            'brand' => 'nullable',
            'model' => 'nullable',
            'observations' => 'nullable',
            'fuel_type' => 'required',
            'fuel_capacity' => 'required|integer',
        ]);
        Machine::create($data);
        
        return redirect()->route('admin.machines.index')->with('success', 'Maquinaria creada correctamente');
    }

    // Método para manejar la actualización de una máquina existente
    public function update(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);
        $data = $request->validate([
            'internal_id' => 'required|unique:machines,internal_id,' . $machine->id,
            'plate' => 'required|unique:machines,plate,' . $machine->id,
            'brand' => 'nullable',
            'model' => 'nullable',
            'observations' => 'nullable',
            'machine_type_id' => 'required|exists:machine_types,id',
            'fuel_type' => 'required',
            'fuel_capacity' => 'required|integer',
        ]);

        $machine->update($data);

        return redirect()->route('admin.machines.index')->with('success', 'Maquinaria actualizada correctamente');
    }

    // Método para manejar la eliminación de una máquina
    public function destroy($id)
    {
        $machine = Machine::findOrFail($id);
        $machine->delete();
        return back()->with('success', 'Maquinaria eliminada correctamente');
    }
}

