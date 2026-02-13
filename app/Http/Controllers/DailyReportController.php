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
            'project_id' => 'required|exists:projects,id',
            'machine_id' => 'required|exists:machines,id',
            'date' => 'required|date',
            'initial_km' => 'nullable|numeric',
            'final_km' => 'nullable|numeric',
            'initial_hm' => 'nullable|numeric',
            'final_hm' => 'nullable|numeric',
            'work_description' => 'nullable|string',
            'fuel_quantity' => 'nullable|numeric',
            'fuel_observation' => 'nullable|string',
            'total_km' => 'nullable|numeric',
            'total_hm' => 'nullable|numeric',
        ]);
        // calcular totales
        $data['total_km'] = ($data['final_km'] ?? 0) - ($data['initial_km'] ?? 0);
        $data['total_hm'] = ($data['final_hm'] ?? 0) - ($data['initial_hm'] ?? 0);

        DailyReport::create([ ...$data,'user_id' => auth()->id(),]);

        return redirect()->route('daily-reports.index')
            ->with('success', 'Reporte creado correctamente');
            
    }


    public function update(Request $request, $id)
    {
        $report = DailyReport::findOrFail($id);

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'machine_id' => 'required|exists:machines,id',
            'date' => 'required|date',
            'initial_km' => 'nullable|numeric',
            'final_km' => 'nullable|numeric',
            'initial_hm' => 'nullable|numeric',
            'final_hm' => 'nullable|numeric',
            'work_description' => 'nullable|string',
            'fuel_quantity' => 'nullable|numeric',
            'fuel_observation' => 'nullable|string',
            'total_km' => 'nullable|numeric',
            'total_hm' => 'nullable|numeric',
            'finished_at' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        // recalcular 
        $data['total_km'] = ($data['final_km'] ?? 0) - ($data['initial_km'] ?? 0);
        $data['total_hm'] = ($data['final_hm'] ?? 0) - ($data['initial_hm'] ?? 0);

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

        $data = ['title' => 'Daily Report '. $dailyReport->id, 'report' => $dailyReport];
        $pdf = Pdf::loadView('report.daily_report', $data); // Load a view named 'pdf.document' and pass data

        //return $pdf->download('document.pdf'); // Download the PDF file
        return $pdf->stream('daily_report_'.$dailyReport->id.'.pdf');
    }

}
