<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMachineRequest;
use App\Http\Requests\UpdateMachineRequest;
use App\Models\Machine;

class MachineController extends Controller
{
    public function index()
    {
        return Inertia::render('Machines/Index', [
            'machines' => Machine::with('type')->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Machines/Form', [
            'machine' => null,
            'types' => MachineType::all(),
        ]);
    }

    public function store(Request $request)
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

        return redirect()->route('machines.index');
    }

    public function edit(Machine $machine)
    {
        return Inertia::render('Machines/Form', [
            'machine' => $machine,
            'types' => MachineType::all(),
        ]);
    }

    public function update(Request $request, Machine $machine)
    {
        $data = $request->validate([
            'internal_id' => 'required|unique:machines,internal_id,' . $machine->id,
            'plate' => 'nullable',
            'name' => 'required',
            'machine_type_id' => 'required',
            'fuel_type' => 'required',
            'fuel_capacity' => 'required|integer',
        ]);

        $machine->update($data);

        return redirect()->route('machines.index');
    }

    public function destroy(Machine $machine)
    {
        $machine->delete();
        return back();
    }
}

