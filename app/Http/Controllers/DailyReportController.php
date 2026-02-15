<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\User;
use App\Models\Machine;

use Barryvdh\DomPDF\Facade\Pdf;

class DailyReportController extends Controller
{
    // VISTAS REPORTES DIARIOS
    public function index()
    {
        return Inertia::render('DailyReport/Index');
    }

    public function add()
    {
        $lastReport = DailyReport::where('user_id', auth()->id())
            ->latest()
            ->first();

        $dailyReport = null;
        $message = null;

        if ($lastReport && is_null($lastReport->finished_at)) {
            $dailyReport = $lastReport;
            $lastReport = null;
            $message = 'Tienes un reporte sin finalizar. Continuando desde donde quedaste.';
        }

        return Inertia::render('DailyReport/Form', [
            'dailyReport' => $dailyReport,
            'lastReport'  => $lastReport,
            'projects'    => Project::all(),
            'machines'    => Machine::all(),
            'infoMessage' => $message,
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

    // CREAR
    public function create(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'machine_id' => 'required|exists:machines,id',
            'date' => 'required|date',

            'initial_km' => 'required|numeric',
            'initial_hm' => 'required|numeric',

            'final_km' => 'nullable|numeric|gte:initial_km',
            'final_hm' => 'nullable|numeric|gte:initial_hm',

            'work_description' => 'nullable|string',
            'fuel_quantity' => 'nullable|numeric',
            'fuel_observation' => 'nullable|string',
        ]);

        // calcular totales
        $data['total_km'] = ($data['final_km'] ?? 0) - $data['initial_km'];
        $data['total_hm'] = ($data['final_hm'] ?? 0) - $data['initial_hm'];

        DailyReport::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('daily-reports.index')
            ->with('success', 'Reporte creado correctamente');
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $report = DailyReport::findOrFail($id);

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'machine_id' => 'required|exists:machines,id',
            'date' => 'required|date',

            'initial_km' => 'required|numeric',
            'initial_hm' => 'required|numeric',

            'final_km' => 'nullable|numeric|gte:initial_km',
            'final_hm' => 'nullable|numeric|gte:initial_hm',

            'work_description' => 'nullable|string',
            'fuel_quantity' => 'nullable|numeric',
            'fuel_observation' => 'nullable|string',

            'finished_at' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

     

        // recalcular totales
        $data['total_km'] = ($data['final_km'] ?? 0) - $data['initial_km'];
        $data['total_hm'] = ($data['final_hm'] ?? 0) - $data['initial_hm'];

        $report->update($data);

        return redirect()->route('daily-reports.index')
            ->with('success', 'Reporte actualizado correctamente');
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

        if (!auth()->user()->hasRole('Administrador')) {
            $reports->where('user_id', auth()->id());
        }

        if (!$request->boolean('show_deleted')) {
            $reports->whereNull('deleted_at');
        } else {
            $reports->withTrashed();
        }

        return DataTables::of($reports)->make(true);
    }

    public function report($id)
    {
        $dailyReport = DailyReport::findOrFail($id);
        $dailyReport->load(['user', 'project', 'machine']);

        $data = [
            'title' => 'Daily Report ' . $dailyReport->id,
            'report' => $dailyReport
        ];

        $pdf = Pdf::loadView('report.daily_report', $data);

        return $pdf->stream('daily_report_' . $dailyReport->id . '.pdf');
    }
}
