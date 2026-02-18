<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\DailyReport;
use App\Models\Project;
use App\Models\Machine;
use App\Http\Requests\DailyReport\StoreDailyReportRequest;
use App\Http\Requests\DailyReport\UpdateDailyReportRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyReportController extends Controller
{
    public function __construct()
    {
        // Middleware de autorización para todas las rutas REST
        $this->authorizeResource(DailyReport::class, 'dailyReport');
    }

    // LISTADO
    public function index()
    {
        $this->authorize('viewAny', DailyReport::class);

        return Inertia::render('DailyReport/Index');
    }

    // DATATABLE
    public function table(Request $request)
    {
        $this->authorize('viewAny', DailyReport::class);

        $reports = DailyReport::query()
            ->with(['user', 'project', 'machine']);

        // Solo ver los suyos si no es administrador
        if (!auth()->user()->hasAnyRole(['Administrador','Super-Administrador'])) {
            $reports->where('user_id', auth()->id());
        }

        if ($request->boolean('show_deleted')) {
            $reports->withTrashed();
        }

        return DataTables::of($reports)
            ->addColumn('deleted', fn ($report) => $report->trashed())
            ->make(true);
    }

    // FORM CREAR
    public function create()
    {
        $this->authorize('create', DailyReport::class);

        $lastReport = DailyReport::where('user_id', auth()->id())
            ->whereNull('finished_at')
            ->latest()
            ->first();

        return Inertia::render('DailyReport/Form', [
            'dailyReport' => $lastReport,
            'projects'    => Project::select('id','name')->get(),
            'machines'    => Machine::select('id','plate','internal_id')->get(),
            'infoMessage' => $lastReport 
                ? 'Tienes un reporte sin finalizar. Continuando desde donde quedaste.'
                : null,
        ]);
    }

    // FORM EDITAR
    public function edit(DailyReport $dailyReport)
    {
        $dailyReport->load(['project','machine','user']);

        return Inertia::render('DailyReport/Form', [
            'dailyReport' => $dailyReport,
            'projects'    => Project::select('id','name')->get(),
            'machines'    => Machine::select('id','plate','internal_id')->get(),
        ]);
    }

    // STORE
    public function store(StoreDailyReportRequest $request)
    {
        $this->authorize('create', DailyReport::class);

        $openReport = DailyReport::where('user_id', auth()->id())
            ->whereNull('finished_at')
            ->latest()
            ->first();

        if ($openReport) {
            return redirect()
                ->route('daily-reports.edit', $openReport)
                ->with('info', 'Tienes un reporte sin finalizar. Continúa con ese.');
        }

        DailyReport::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Reporte creado correctamente');
    }

    // UPDATE
    public function update(UpdateDailyReportRequest $request, DailyReport $dailyReport)
    {
        $this->authorize('update', $dailyReport);

        $data = $request->validated();

        if ($request->boolean('is_finished')) {
            $data['finished_at'] = now();
        }

        $dailyReport->update($data);

        return redirect()
            ->route('daily-reports.index')
            ->with('success', 'Reporte actualizado correctamente');
    }

    // DELETE
    public function destroy(DailyReport $dailyReport)
    {
        $this->authorize('delete', $dailyReport);

        $dailyReport->delete();

        return back()->with('success', 'Reporte eliminado correctamente');
    }

    // PDF (método personalizado → requiere authorize manual)
    public function report(DailyReport $dailyReport)
    {
        $this->authorize('view', $dailyReport);

        $dailyReport->load(['user','project','machine']);

        $pdf = Pdf::loadView('report.daily_report', [
            'title'  => 'Daily Report ' . $dailyReport->id,
            'report' => $dailyReport
        ]);

        return $pdf->stream('daily_report_' . $dailyReport->id . '.pdf');
    }
}
