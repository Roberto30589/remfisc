<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\DataTables;

use App\Http\Requests\StoreMachineRequest;
use App\Http\Requests\UpdateMachineRequest;
use App\Models\Machine;
use App\Models\MachineType;

class MachineController extends Controller
{
    //VISTAS MÁQUINAS
    public function index()
    {
        return Inertia::render('Machine/Index');
    }

    public function add()
    {
        return Inertia::render('Machine/Form', [
            'machine' => null,
            'types' => MachineType::all(),
        ]);
    }

    public function table(Request $request)
    {
        $machines = Machine::with('type');
        return DataTables::of($machines)->make(true);
    }

    public function edit($id)
    {
        $machine = Machine::findOrFail($id);
        return Inertia::render('Machine/Form', [
            'machine' => $machine,
            'types' => MachineType::all(),
        ]);
    }

    //CRUDS MÁQUINAS
    public function create(Request $request)
    {
        $data = $request->validate([
            'internal_id' => 'required|unique:machines',
            'plate' => 'nullable',
            'name' => 'required',
            'machine_type_id' => 'required|exists:machine_types,id',
            'fuel_type' => 'required',
            'fuel_capacity' => 'required|integer',
        ]);
        Machine::create($data);
        
        return redirect()->route('admin.machines.index')->with('success', 'Maquinaria creada correctamente');
    }

    public function update(Request $request, $id)
    {
        $machine = Machine::findOrFail($id);
        $data = $request->validate([
            'internal_id' => 'required|unique:machines,internal_id,' . $machine->id,
            'plate' => 'nullable',
            'name' => 'required',
            'machine_type_id' => 'required|exists:machine_types,id',
            'fuel_type' => 'required',
            'fuel_capacity' => 'required|integer',
        ]);

        $machine->update($data);

        return redirect()->route('admin.machines.index')->with('success', 'Maquinaria actualizada correctamente');
    }

    public function destroy($id)
    {
        $machine = Machine::findOrFail($id);
        $machine->delete();
        return back()->with('success', 'Maquinaria eliminada correctamente');
    }
}

