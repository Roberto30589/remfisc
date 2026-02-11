<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Machine;

class DailyReportController extends Controller
{
    // VISTAS REPORTES DIARIOS
    public function index()
    {
        return Inertia::render('DailyReport/Index');
    }

    public function add()
    {
        return Inertia::render('DailyReport/Form', [
            'dailyReport' => null,
            'projects'    => Project::all(),
            'machines'    => Machine::all(),
        ]);
    }

    public function edit($id)
    {
        $dailyReport = DailyReport::findOrFail($id);

        return Inertia::render('DailyReport/Form', [
            'dailyReport' => $dailyReport,
            'projects'    => Project::all(),
            'machines'    => Machine::all(),
        ]);
    }

    // CRUD REPORTES DIARIOS
    public function create(Request $request)
    {
        $data = $request->validate([
            'project_id'        => 'required|exists:projects,id',
            'machine_id'        => 'required|exists:machines,id',
            'date'              => 'required|date',
            'initial_km'        => 'nullable|integer',
            'final_km'          => 'nullable|integer',
            'initial_hm'        => 'nullable|integer',
            'final_hm'          => 'nullable|integer',
            'work_description'  => 'nullable|string',
            'fuel_quantity'     => 'nullable|numeric',
            'fuel_observation'  => 'nullable|string',
        ]);

        $data['user_id'] = auth()->id();

        DailyReport::create($data);

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Reporte diario creado correctamente');
    }

    public function update(Request $request, $id)
    {
        $dailyReport = DailyReport::findOrFail($id);

        $data = $request->validate([
            'project_id'        => 'required|exists:projects,id',
            'machine_id'        => 'required|exists:machines,id',
            'date'              => 'required|date',
            'initial_km'        => 'nullable|integer',
            'final_km'          => 'nullable|integer',
            'initial_hm'        => 'nullable|integer',
            'final_hm'          => 'nullable|integer',
            'work_description'  => 'nullable|string',
            'fuel_quantity'     => 'nullable|numeric',
            'fuel_observation'  => 'nullable|string',
        ]);

        $dailyReport->update($data);

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Reporte diario actualizado correctamente');
    }

    public function destroy($id)
    {
        $dailyReport = DailyReport::findOrFail($id);
        $dailyReport->delete();

        return back()->with('success', 'Reporte diario eliminado correctamente');
    }

    // DATATABLE
    public function table(Request $request)
    {
        $reports = DailyReport::with(['user', 'project', 'machine']);

        if (!$request->boolean('show_deleted')) {
            $reports->whereNull('deleted_at');
        } else {
            $reports->withTrashed();
        }

        return DataTables::of($reports)->make(true);
    }

    public function show(DailyReport $dailyReport)
    {
        $dailyReport->load(['user', 'project', 'machine']);

        return Inertia::render('DailyReport/Show', [
            'dailyReport' => $dailyReport,
        ]);
    }


}
